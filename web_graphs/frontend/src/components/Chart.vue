<template>
    <div :id="'chart-wrapper-' + configId" class="extended-info card disable-selection-text" ref="chart-wrapper">
        <svg :id="'svg-'+ configId" @mouseleave="mouseleave"
             class="svg-content">
            <clipPath :id="'clip-'+ configId">
                <rect/>
            </clipPath>
            <g :clip-path="`url(#clip-${configId})`" class="x axis axisWhite"/>
            <g class="y axis axisWhite"/>
            <g :clip-path="`url(#clip-${configId})`" class="lines">
                <path :id="getLineIdHTML(id, false)"
                      :key="id"
                      d=""
                      v-for="id in selectedTagsId"
                />
            </g>
            <g :clip-path="`url(#clip-${configId})`" class="axisWhite tooltip-vertical-line" v-show="areAnyDataThere">
                <line :id="'tooltip-vertical-line-' + configId" :x1="margin.left" :x2="margin.left"
                      :y1="margin.top"
                      ref="tooltip-vertical-line"
                      v-show="isTooltipVisible"
                      y2="0"/>
                <line :key="line"
                      :x1="line.x"
                      :x2="line.x"
                      :y1="line.y1"
                      :y2="line.y2"
                      ref="tooltip-static-line"
                      v-for="line in tooltipLines"/>
            </g>
        </svg>
        <TooltipTextBox :absolutePositionX="line.x" :data="line.data"
                        :key="tooltipLines.indexOf(line)" :max-x="getWrapperWidth() - margin.right" :min-x="margin.left"
                        :padding-bottom="margin.bottom"
                        :padding-top="margin.top" v-for="line in tooltipLines"/>
        <Tooltip :lines="lines" :show-tooltip="isTooltipVisible" :tooltip-date="tooltipCurrentDate" :translate="tooltip.translate"
                 ref="tooltip"/>
    </div>
</template>

<script>
import * as d3 from 'd3';
import Tooltip from "./Tooltip";
import {mapGetters} from 'vuex';
import TooltipTextBox from "./TooltipTextBox";

export default {
    name: "Chart",
    components: {TooltipTextBox, Tooltip},
    props: {
        configId: {
            type: Number,
            required: true
        },
        selectedTagsId: {
            type: Array,
            required: true
        },

    },
    computed: {
        ...mapGetters([
            'minDate',
            'maxDate',
            'tooltipLineDates',
            'tooltipCoordinates',
            'd3Zoom',
            'doTooltipShow',
            'tooltipCurrentDate',
            'getTagType'
        ]),
        coefficient: function () {
            return {
                AI: 1,
                DI: this.minMaxData.maxValue * 0.66,
                DO: this.minMaxData.maxValue * 0.66
            }
        },
        format: function () {
            return {
                millisecond: this.ruLocale.format(".%L"),
                second: this.ruLocale.format(":%S"),
                minute: this.ruLocale.format("%H:%M"),
                hour: this.ruLocale.format("%H:%M"),
                day: this.ruLocale.format("%d.%m %a"),
                week: this.ruLocale.format("%d %b"),
                month: this.ruLocale.format("%B"),
                year: this.ruLocale.format("%Y"),
            }
        },
        areAnyDataThere: function () {
            for (const line of this.lines)
                if (this.$store.getters.isTagsLoaded(line.tagId))
                    return true
            return false
        },
        isTooltipVisible: function (){
            return this.areAnyDataThere && this.doTooltipShow
        },
        margin: function () {
            const right = 0
            return {
                top: 10,
                right: right,
                bottom: 10,
                left: 45,
                forTooltip: 40,
                forClipPath: right + 20,
            }
        }
    },
    watch: {
        tooltipLineDates: function (val){
            if(val.length)
                this.clicker(val[val.length - 1])
            else
                this.clearTooltipLines()
        },
        tooltipCoordinates: {
            handler: function (val){
                this.moover(val.x, val.y)
            },
            deep: true
        },
        d3Zoom: {
            handler: function (val) {
                this.zoomer(val.x, val.k)
            },
            deep: true
        }
    },
    data() {
        return {
            tooltip: {
                translate: {x: 0, y: 0},
            },
            tooltipLines: [],
            curveD3: {
                AI: d3.curveLinear,
                DI: d3.curveStepAfter,
                DO: d3.curveStepAfter,
            },
            xScale: null,
            yScale: null,
            xAxis: null,
            yAxis: null,
            lines: [],
            colors: [],
            svgD3: null,
            minMaxData: {
                minValue: +Infinity,
                maxValue: -Infinity,
            },
            zoom: null,
            ruLocale: d3.timeFormatLocale({
                "dateTime": "%A, %e %B %Y г. %X",
                "date": "%d.%m.%Y",
                "time": "%H:%M:%S",
                "periods": ["AM", "PM"],
                "days": ["Воскресенье", "Понедельник", "Вторник", "Среда", "Четверг", "Пятница", "Суббота"],
                "shortDays": ["Вс", "Пн", "Вт", "Ср", "Чт", "Пт", "Сб"],
                "months": ["Января", "Февраля", "Марта", "Апреля", "Мая", "Июня", "Июля", "Августа", "Сентября", "Октября", "Ноября", "Декабря"],
                "shortMonths": ["Янв", "Фев", "Мар", "Апр", "Май", "Июн", "Июл", "Авг", "Сен", "Окт", "Ноя", "Дек"]
            }),
        }
    },

    methods: {
        multiFormat: function (date) {
            return (d3.timeSecond(date) < date ? this.format.millisecond
                : d3.timeMinute(date) < date ? this.format.second
                    : d3.timeHour(date) < date ? this.format.minute
                        : d3.timeDay(date) < date ? this.format.hour
                            : d3.timeMonth(date) < date ? (d3.timeWeek(date) < date ? this.format.day : this.format.week)
                                : d3.timeYear(date) < date ? this.format.month
                                    : this.format.year)(date);
        },
        mouseleave: function () {
            this.hideTooltip()
        },
        getCurveD3: function (type) {
            return this.curveD3[type] ? this.curveD3[type] : this.curveD3.AI
        },
        getChartCoefficient: function (type) {
            return this.coefficient[type] ? this.coefficient[type] : this.coefficient.AI
        },
        getWrapperWidth: function () {
            return this.$refs['chart-wrapper'].clientWidth - this.margin.right - this.margin.left
        },
        getWrapperHeight: function () {
            return this.$refs['chart-wrapper'].clientHeight - this.margin.top - this.margin.bottom
        },
        getLineIdHTML: function (id, withH) {
            const idHTML = `path-${this.configId}-${id}`
            return withH ? '#' + idHTML : idHTML

        },
        showTooltip: function (){
            this.$store.commit('showTooltip')
        },
        hideTooltip: function (){
            this.$store.commit('hideTooltip')
        },
        onZoom: function () {
            if (this.areAnyDataThere) {
                this.$store.commit('setD3Zoom', {x: d3.event.transform.x, k: d3.event.transform.k})
                this.showTooltip()
            }
        },
        onMouseMove: function () {
            if (this.areAnyDataThere) {
                const payload = {
                    x: d3.event.clientX - this.$el.getBoundingClientRect().left,
                    y: d3.event.clientY - this.$el.getBoundingClientRect().top
                }
                this.$store.commit('setTooltipCoordinates', payload)
                this.$store.commit('setTooltipCurrentDate', this.xScale.invert(payload.x))
                this.showTooltip()
            }
        },
        onClick: function () {
            if (this.areAnyDataThere) {
                this.$store.commit('addTooltipLine', {date: this.xScale.invert(d3.event.offsetX)})
                this.showTooltip()
            }
        },
        onDoubleClick: function () {
            if (this.areAnyDataThere) {
                this.$store.commit('clearTooltipLines')
                this.showTooltip()
            }
        },
        clicker: function (date) {
            let tooltipLine = {
                y1: this.margin.top,
                y2: this.getWrapperHeight() - this.margin.bottom,
                x: this.xScale(date),
                date,
                data: []
            }
            for (const line of this.lines)
                this.addNewDataTooltip(tooltipLine.data, line.value, line.color, line.type)

            this.tooltipLines.push(tooltipLine)
        },
        zoomer: function (x, k) {
            this.hideTooltip()
            this.xScale.range(this.getXRange().map(d => d * k + x))
            this.redrawTooltipLines()
            this.redrawLines()
            this.svgD3.select(".x.axis")
                .call(this.xAxis
                    .scale(this.xScale)
                    .ticks(this.getWidthTickNumber()).tickFormat(this.multiFormat))

            // internal state inside d3
            this.svgD3.node().__zoom.x = x
            this.svgD3.node().__zoom.k = k

            // this.svgD3.node().__zoom -  console.log(d3.zoomTransform(this.svgD3.node()))

            /*It's the way of changing internal state d3 with event generation.
            So if I will be use it it was infinity loop...(change zoom inside handler of changing zoom)

            this.svgD3.call(this.zoom.translateBy, x, 10)
            this.svgD3.call(this.zoom.scaleBy, k)*/

        },
        onResize: function () {
            this.resize()
            this.redrawLines()
            this.redrawTooltipLines()
            this.zoomer(this.d3Zoom.x, this.d3Zoom.k)
        },
        resize: function () {
            this.xScale.range(this.getXRange())
            this.yScale.range(this.getYRange())
            this.svgD3.select(".x.axis")
                .attr("transform", this.getTransformX())
                .call(this.xAxis.ticks(this.getWidthTickNumber()).tickFormat(this.multiFormat))
            this.svgD3.select(".y.axis")
                .attr("transform", this.getTransformY())
                .call(this.yAxis.ticks(5))

            this.svgD3.selectAll(".y.axis>.tick>.tick-line").remove()
            this.svgD3.selectAll('.y.axis>.tick').append('line')
                .attr('class', 'tick-line')
                .attr('x2', this.getWrapperWidth() - this.margin.forClipPath)

            this.svgD3.select('#clip-' + this.configId + '>rect')
                .attr("x", this.margin.left)
                .attr("width", this.getWrapperWidth() - this.margin.forClipPath)
                .attr("height", this.getWrapperHeight())
        },
        moover: function (x, y) {
            const isVisible =
                    x > this.margin.left
                &&  x < this.getWrapperWidth() - this.margin.right
                &&  y > this.margin.top
                &&  y < this.getWrapperHeight() - this.margin.bottom
            this.$store.commit('setTooltipVisibility', isVisible)
            if (this.isTooltipVisible) {

                for (const line of this.lines) {
                    line.value = this.getValueByDate(this.$store.getters.tagDataById(line.tagId), this.tooltipCurrentDate)
                }
                const tooltipHTML = this.$refs['tooltip'].$el

                if (x + this.margin.forTooltip + tooltipHTML.clientWidth < this.getWrapperWidth())
                    this.tooltip.translate.x = x + this.margin.forTooltip
                else
                    this.tooltip.translate.x = x - this.margin.forTooltip - tooltipHTML.clientWidth

                if (y + tooltipHTML.clientHeight / 2 > this.getWrapperHeight())
                    this.tooltip.translate.y = this.getWrapperHeight() - tooltipHTML.clientHeight
                else if (y - tooltipHTML.clientHeight / 2 < 0)
                    this.tooltip.translate.y = 0
                else
                    this.tooltip.translate.y = y - tooltipHTML.clientHeight / 2

                this.$refs['tooltip-vertical-line'].x2.baseVal.value = x
                this.$refs['tooltip-vertical-line'].x1.baseVal.value = x
            }
        },
        clearMinMax: function () {
            this.minMaxData = {
                minValue: +Infinity,
                maxValue: -Infinity,
            }
        },
        redrawLines: function () {
            for (const line of this.lines)
                this.svgD3.select(line.idHTML).attr("d", line.line)
        },
        redrawTooltipLines: function () {
            for (const line of this.tooltipLines)
                line.x = this.xScale(line.date)
        },
        getXRange: function () {
            return [this.margin.left, this.getWrapperWidth() - this.margin.right]
        },
        getYRange: function () {
            return [this.getWrapperHeight() - this.margin.bottom, this.margin.top]
        },
        getTransformX: function () {
            return `translate(0, ${this.getWrapperHeight() - this.margin.bottom})`
        },
        getTransformY: function () {
            return `translate(${this.margin.left},0)`
        },
        getWidthTickNumber: function () {
            const mbNumber = this.svgD3.select('.x.axis').node().getBoundingClientRect().width / 100
            return (mbNumber >= 5) ? mbNumber : 5
        },
        isDigital: function (type) {
            return (type === 'DO' || type === 'DI')
        },
        setMaxMinVariables: function (minMaxData) {
            let isAllDigital = true
            for (const line of this.lines)
                if (!this.isDigital(line.type)) {
                    isAllDigital = false
                    break
                }
            if (isAllDigital) {
                if ((!isFinite(this.minMaxData.maxValue) || !isFinite(this.minMaxData.minValue))) {
                    this.minMaxData.maxValue = 1
                    this.minMaxData.minValue = 0
                }
                this.minMaxData.maxValue = -Infinity
                this.minMaxData.minValue = Infinity
            }

            if (minMaxData.minValue !== undefined && minMaxData.minValue < this.minMaxData.minValue)
                this.minMaxData.minValue = minMaxData.minValue

            if (minMaxData.maxValue !== undefined && minMaxData.maxValue > this.minMaxData.maxValue)
                this.minMaxData.maxValue = minMaxData.maxValue

            this.yScale.domain([this.minMaxData.minValue, this.minMaxData.maxValue])
        },
        reArrangeChart: function () {
            this.xScale = d3.scaleTime()
                .domain([this.minDate, this.maxDate])
            this.yScale = d3.scaleLinear()
                .domain([this.minMaxData.minValue, this.minMaxData.maxValue])
            this.xAxis = d3.axisBottom()
                .scale(this.xScale)
            this.yAxis = d3.axisLeft()
                .scale(this.yScale)

            this.onResize()
            this.reArrangeTooltipLines()
        },
        reArrangeTooltipLines: function () {
            for (const line of this.tooltipLines)
                for (const d of line.data)
                    d.y = this.yScale(d.value ? d.value * this.getChartCoefficient(d.type) : this.minMaxData.minValue)
        },
        initChart: function () {
            this.xScale = d3.scaleTime()
                .domain([this.minDate, this.maxDate])
            this.yScale = d3.scaleLinear()
                .domain([this.minMaxData.minValue, this.minMaxData.maxValue])
            this.xAxis = d3.axisBottom()
                .scale(this.xScale)
            this.yAxis = d3.axisLeft()
                .scale(this.yScale)
            this.svgD3 = d3.select('#svg-' + this.configId)
            this.xScale.range(this.getXRange())
            this.yScale.range(this.getYRange())

            this.svgD3.select(".x.axis")
                .attr("transform", this.getTransformX())
                .call(this.xAxis.ticks(this.getWidthTickNumber()).tickFormat(this.multiFormat))
            this.svgD3.select(".y.axis")
                .attr("transform", this.getTransformY())
                .call(this.yAxis.ticks(5))

            this.svgD3.selectAll(".y.axis>.tick").append('line')
                .attr('class', 'tick-line')
                .attr('x2', this.getWrapperWidth() - this.margin.forClipPath)

            this.svgD3.select('#clip-' + this.configId + '>rect')
                .attr("x", this.margin.left)
                .attr("width", this.getWrapperWidth() - this.margin.forClipPath)
                .attr("height", this.getWrapperHeight())

            this.zoom = d3.zoom()
                .scaleExtent([1, 30])
                .extent([[this.margin.left, 0], [this.getWrapperWidth() - this.margin.right, this.getWrapperHeight()]])
                .translateExtent([[this.margin.left, -Infinity], [this.getWrapperWidth() - this.margin.right, Infinity]])
                .on("zoom", this.onZoom)
            this.svgD3.call(this.zoom)

            d3.select('#chart-wrapper-' + this.configId).on('mousemove', this.onMouseMove)
            this.svgD3.on('click', this.onClick)
            this.svgD3.on('dblclick', this.onDoubleClick)
            this.svgD3.on("dblclick.zoom", null);
            this.$refs['tooltip-vertical-line'].y2.baseVal.value = this.getWrapperHeight() - this.margin.bottom

            for (const date of this.$store.getters.tooltipLineDates) {
                let tooltipLine = {
                    y1: this.margin.top,
                    y2: this.getWrapperHeight() - this.margin.bottom,
                    x: this.xScale(date),
                    date: date,
                    data: []
                }
                for (const line of this.lines)
                    this.addNewDataTooltip(tooltipLine.data, line.value, line.color, line.type)
                this.tooltipLines.push(tooltipLine)
            }
            this.zoomer(this.d3Zoom.x, this.d3Zoom.k)

        },
        getValueByDate: function (data, date) {
            let value
            const correctValue = (d) => d !== undefined ? d : null
            for(let i = 0; i < data.length; i++)
                if(date < data[i].date) {
                    value = i ? correctValue(data[i - 1].value) : null
                    break
                }
            if(value === undefined)
                value = data.length > 1 ? correctValue(data[data.length - 1].value) : null
            return value
        },
        addTooltipTextToLines: function (line) {
            const data = this.$store.getters.tagDataById(line.tagId)

            for (let tooltipLine of this.tooltipLines) {
                const date = tooltipLine.date,
                    value = this.getValueByDate(data, date)

                this.addNewDataTooltip(tooltipLine.data, value, line.color, line.type)

            }
        },
        addNewDataTooltip: function (data, value, color, type) {
            data.push({
                value,
                y: this.yScale(value ? value * this.getChartCoefficient(type) : this.minMaxData.minValue),
                color,
                type
            })
        },

        addLine: function (tag) {
            console.log('addLine: ' + tag.id)
            this.setMaxMinVariables(tag.minMaxData)
            const type = this.getTagType(tag.id),
                curve = this.getCurveD3(type),
                line = d3.line()
                    .defined(d => !isNaN(d.value))
                    .x(d => this.xScale(d.date))
                    .y(d => this.yScale(d.value ? d.value * this.getChartCoefficient(type) : this.minMaxData.minValue))
                    .curve(curve),
                color = this.$store.getters.color(this.colors),
                idHTML = this.getLineIdHTML(tag.id, true)

            this.svgD3.select(idHTML)
                .datum(tag.data)
                .attr("d", line)
                .attr('stroke', color)


            this.lines.push({line, idHTML, type, color, tagId: tag.id, value: undefined})
            this.addColor(color, tag)

            this.addTooltipTextToLines(this.lines[this.lines.length - 1])

            this.reArrangeChart()
        },
        reDrawLine: function (tag, line) {
            console.log('reDrawLine: ' + tag.id)
            this.setMaxMinVariables(tag.minMaxData)

            this.svgD3.select(line.idHTML)
                .datum(tag.data)
                .attr("d", line.line)

            this.reArrangeChart()
        },
        getIndexLineById: function (neededId) {
            for (let i = 0; i < this.lines.length; i++)
                if (this.lines[i].tagId === neededId) return i
        },
        removeLine: function (tagId) {
            console.log('removeLine: ' + tagId)
            const index = this.getIndexLineById(tagId)
            this.lines.splice(index, 1)
            this.removeTooltipLine(index)
            this.removeColor(tagId, index)

            this.updateAllMinMax()
            this.reArrangeChart()
        },
        removeTooltipLine: function (index) {
            for (const tooltipLine of this.tooltipLines)
                tooltipLine.data.splice(index, 1)
        },
        addColor: function (color, tagWithData) {
            const id = tagWithData.id,
                description = this.$store.getters.getTagsDescription(id),
                tag = {id, description}
            this.colors.push(color)
            this.$emit('add-color', color, tag)
        },
        removeColor: function (tagId, indexColor) {
            this.colors.splice(indexColor, 1)
            this.$emit('remove-color', tagId)
        },
        clearTooltipLines: function () {
            this.tooltipLines.splice(0)
        },
        updateCharts: function () {
            this.clearMinMax()
            this.updateLines()
            this.reArrangeChart()
            this.clearTooltipLines()
        },
        updateAllMinMax: function () {
            this.clearMinMax()
            for (const tagId of this.selectedTagsId)
                if (this.$store.getters.tagById(tagId))
                    this.setMaxMinVariables(this.$store.getters.tagById(tagId).minMaxData)
        },
        updateLines: function () {
            for (let i = 0; i < this.selectedTagsId.length; i++)
                if (this.$store.getters.tagById(this.selectedTagsId[i]))
                    this.reDrawLine(this.$store.getters.tagById(this.selectedTagsId[i]), this.lines[i])
        },
    },
    mounted() {
        window.addEventListener("resize", this.onResize);
        this.initChart()
    },
}


</script>

<style scoped>
svg {
    width: 100%;
    height: 100%;
}

.axisWhite >>> line {
    stroke: white;

}

* >>> path, * >>> line {
    /*shape-rendering: optimizeSpeed;*/
}

.axisWhite >>> path {
    stroke: white;
    stroke-width: 1px;
}

.lines >>> path {
    fill: none;
    stroke-width: 1.5;
}

.axisWhite >>> text {
    fill: white;
    font-size: 0.8rem;
}

.axisWhite >>> .tick-line {
    opacity: .05;
}

.tooltip-vertical-line >>> line {
    stroke-width: .5;
    fill: none;
}
</style>