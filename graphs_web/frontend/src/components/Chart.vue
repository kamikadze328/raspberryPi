<template>
    <div :id="'chart-wrapper-' + configId" class="extended-info card" ref="chart-wrapper">
        <svg :id="'svg-'+ configId" class="svg-content"
             @mouseout="showTooltip=false">
            <clipPath :id="'clip-'+ configId">
                <rect/>
            </clipPath>
            <g :clip-path="`url(#clip-${configId})`" class="x axis axisWhite"/>
            <g class="y axis axisWhite"/>
            <g :clip-path="`url(#clip-${configId})`" class="lines" fill="none"
               stroke-width="1.5">
                <path :id="getLineIdHTML(id, false)"
                      :key="id"
                      d=""
                      v-for="id in selectedTagsId"
                />
            </g>
        </svg>
        <Tooltip ref="tooltip" :lines="lines" :show-tooltip="tooltip.show" :tooltip-date="tooltip.date" :translate="tooltip.translate"/>
    </div>
</template>

<script>
    import * as d3 from 'd3';
    import Tooltip from "./Tooltip";
    import { mapGetters } from 'vuex';

    export default {
        name: "Chart",
        components: {Tooltip},
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
                ]),
            coefficient: function () {
                return {
                    AI: 1,
                    DI: this.minMaxData.maxValue * 0.66,
                    DO: this.minMaxData.maxValue * 0.66
                }
            },
        },
        data() {
            return {
                tooltip:{
                    date: new Date,
                    show: false,
                    translate: {x: 0, y:0},
                },
                curveD3: {
                    AI: d3.curveLinear,
                    DI: d3.curveStepAfter,
                    DO: d3.curveStepAfter,
                },
                margin: ({top: 10, right: 0, bottom: 10, left: 45}),
                xScale: null,
                yScale: null,
                xAxis: null,
                yAxis: null,
                lines: [],
                colors: [],
                svgD3: null,
                tooltipD3: null,
                minMaxData: {
                    minValue: +Infinity,
                    maxValue: -Infinity,
                },
                zoom: null,
                currentColorsNumber: 0,
            }
        },
        methods: {
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
            onZoom: function () {
                this.$emit('zoomer')
            },
            onMouseMove: function(){
                this.$emit('mouse-moover')
            },
            zoomer: function () {
                this.xScale.range(this.getXRange().map(d => d3.event.transform.applyX(d)))
                console.log(d3.event.transform)
                this.redrawLines()
                this.svgD3.select(".x.axis")
                    .call(this.xAxis
                        .scale(this.xScale)
                        .ticks(this.getWidthTickNumber()))
            },
            onResize: function () {
                this.resize()
                this.redrawLines()
            },
            resize: function () {
                this.xScale.range(this.getXRange())
                this.yScale.range(this.getYRange())
                this.svgD3.select(".x.axis")
                    .attr("transform", this.getTransformX())
                    .call(this.xAxis.ticks(this.getWidthTickNumber()))
                this.svgD3.select(".y.axis")
                    .attr("transform", this.getTransformY())
                    .call(this.yAxis.ticks(5))
                this.svgD3.select('#clip-' + this.configId + '>rect')
                    .attr("x", this.margin.left)
                    .attr("width", this.getWrapperWidth() - this.margin.right - 10)
                    .attr("height", this.getWrapperHeight())
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
            setMaxMinVariables: function (minMaxData) {
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
                    .call(this.xAxis.ticks(this.getWidthTickNumber()))
                this.svgD3.select(".y.axis")
                    .attr("transform", this.getTransformY())
                    .call(this.yAxis.ticks(5))

                this.svgD3.select('#clip-' + this.configId + '>rect')
                    .attr("x", this.margin.left)
                    .attr("width", this.getWrapperWidth() - this.margin.right - 10)
                    .attr("height", this.getWrapperHeight())

                this.zoom = d3.zoom()
                    .scaleExtent([1, 30])
                    .extent([[this.margin.left, 0], [this.getWrapperWidth() - this.margin.right, this.getWrapperHeight()]])
                    .translateExtent([[this.margin.left, -Infinity], [this.getWrapperWidth() - this.margin.right, Infinity]])
                    .on("zoom", this.onZoom)
                this.svgD3.call(this.zoom)

                this.svgD3.on('mousemove', this.onMouseMove)
            },

            moover: function () {
                const x = d3.event.offsetX,
                    y = d3.event.offsetY
                this.tooltip.show = this.lines.length
                    && x > this.margin.left
                    && x < this.getWrapperWidth() - this.margin.right
                    && y > this.margin.top
                    && y < this.getWrapperHeight() - this.margin.bottom
                if (this.tooltip.show) {
                    const date = this.xScale.invert(x)
                    const bisectDate = d3.bisector(d => d.date).left
                    for (const line of this.lines) {
                        const tagId = line.tagId,
                            data = this.$store.getters.tagById(tagId).data
                        const i = bisectDate(data, date, 1),
                            d0 = i ? data[i - 1] : null,
                            d1 = data[i]
                        let value
                        if (d0.value === undefined) value = d1.value
                        else if (d1.value === undefined) value = d0.value
                        else value = (date - d0.date > d1.date - date) ? d1.value : d0.value
                        this.tooltip.date = date
                        this.lines[this.getIndexLineById(tagId)].value = value
                    }
                    const tooltipHTML = this.$refs['tooltip'].$el

                    if(x + 30 + tooltipHTML.clientWidth < this.getWrapperWidth())
                        this.tooltip.translate.x = x + 30
                    else
                        this.tooltip.translate.x = x - 30 - tooltipHTML.clientWidth

                    if(y + tooltipHTML.clientHeight / 2 > this.getWrapperHeight())
                        this.tooltip.translate.y = this.getWrapperHeight() - tooltipHTML.clientHeight
                    else if(y - tooltipHTML.clientHeight / 2 < 0)
                        this.tooltip.translate.y = 0
                    else
                        this.tooltip.translate.y = y - tooltipHTML.clientHeight / 2
                }
            },
            addLine: function (tag) {
                console.log('addLine: ' + tag.id)
                this.setMaxMinVariables(tag.minMaxData)
                const type = tag.type,
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

                this.removeColor(tagId, index)

                this.updateAllMinMax()
                this.reArrangeChart()
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
            updateCharts: function () {
                this.clearMinMax()
                this.updateLines()
                this.onResize()
            },
            updateAllMinMax: function () {
                this.clearMinMax()
                for (const tagId of this.selectedTagsId)
                    this.setMaxMinVariables(this.$store.getters.tagById(tagId).minMaxData)
            },
            updateLines: function () {
                for (let i = 0; i < this.lines.length; i++)
                    this.reDrawLine(this.$store.getters.tagById(this.selectedTagsId[i]), this.lines[i])
            },
        },
        mounted() {
            window.addEventListener("resize", this.onResize);
            this.initChart()
            //this.initChart()

            /*if (this.dataTemper && this.dataTemper.length)
                this.initTemperCharts()*/
        },
    }


</script>

<style scoped>
    svg {
        width: 100%;
        height: 100%;
    }

    .axisWhite >>> line {
        shape-rendering: optimizeSpeed;
        stroke: white;

    }

    .axisWhite >>> path {
        stroke: white;
        stroke-width: 1px;
        shape-rendering: optimizeSpeed;

    }

    .axisWhite >>> text {
        fill: white;
        font-size: 0.8rem;
    }

</style>