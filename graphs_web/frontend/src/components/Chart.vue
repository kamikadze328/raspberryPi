<template>
    <svg :id="'svg-'+ id" class="svg-content">
        <clipPath :id="'clip-'+ id">
            <rect/>
        </clipPath>
        <g class="x axis axisWhite" :clip-path="'url(#clip-' + id + ')'"></g>
        <g class="y axis axisWhite"></g>
        <g class="lines" stroke="#348fe2" fill="none" stroke-width="1.5">
            <path v-for="tag in this.$store.state.tagsTemperature"
                  :key="'line-temper-'+id + '-' + tag.id"
                  :id="'line-temper-'+id + '-' + tag.id"
                  :clip-path="'url(#clip-' + id + ')'"
                  display="none" opacity="0.7" d=""/>
        </g>
        <g class="lines" stroke="#ff5b57" fill="none" stroke-width="1.5" :clip-path="'url(#clip-' + id + ')'">
            <path v-for="tag in this.$store.state.tagsDigitalInput"
                  :key="'line-di-'+id + '-' + tag.id"
                  :id="'line-di-'+id + '-' + tag.id"
                  display="none" opacity="0.7" d=""/>
        </g>
    </svg>
</template>

<script>
    import * as d3 from 'd3';

    export default {
        name: "Chart",

        props: {
            id: Number,
            selectedTagsTemperature: {
                type: Array,
                required: true
            },
            selectedTagsDigitalInputs: {
                type: Array,
                required: true
            }
        },
        watch: {
            selectedTagsTemperature: function (val, oldVal) {
                this.watchSelectedCharts(val, oldVal, 'temper')
            },
            selectedTagsDigitalInputs: function (val, oldVal) {
                this.watchSelectedCharts(val, oldVal, 'di')
            },
            dataTemper: function () {
                //this.initTemperCharts()
            },
            isDataReady: function (val, oldValue) {


                console.log(1)

                console.log(val)
                console.log(oldValue)
                console.log(val.temper)
                console.log(oldValue.temper)
                if (val.temper !== oldValue.temper && !this.isInit.temper) {
                    this.initTemperCharts()
                }
                if (val.temper && val.di !== oldValue.di && this.isInit.temper) {
                    console.log('default call initDI')
                    this.initDICharts()
                }

            }
        },
        data() {
            return {
                idParent: '',
                margin: ({top: 10, right: 0, bottom: 10, left: 35}),
                width: 0,
                height: 0,
                xScale: null,
                yScale: null,
                xAxis: null,
                yAxis: null,
                lines: [],
                linesDI: [],
                svgD3: null,
                dataTemper: null,
                isDataReady: {temper:false, di:false},
                isInit:{temper:false, di:false},
                minMaxData: {
                    minValue: null,
                    maxValue: null,
                    minDate: null,
                    MaxDate: null
                },
                zoom: null,

            }
        },
        methods: {
            watchSelectedCharts: function (val, oldVal, chartName) {
                const isAdded = val.length > oldVal.length
                for (let i = 0; i < (isAdded ? val.length : val.length + 1); i++)
                    if (val[i] !== oldVal[i]) {
                        const id = isAdded ? val[i] : oldVal[i]
                        this.svgD3.select('#line-' + chartName + '-' + this.id + '-' + id)
                            .attr('display', isAdded ? 'block' : 'none')
                        break
                    }
            },
            onZoom: function () {
                this.$emit('zoomer')
                this.xScale.range(this.getXRange().map(d => d3.event.transform.applyX(d)))
                this.redrawLines()
                this.svgD3.select(".x.axis")
                    .call(this.xAxis
                        .scale(this.xScale)
                        .ticks(this.getWidthTickNumber()))

            },

            resize: function () {
                this.updateSizeHTML()
                console.log(this.yScale)
                this.xScale.range(this.getXRange())
                this.yScale.range(this.getYRange())
                this.svgD3.select(".x.axis")
                    .attr("transform", this.getTransformX())
                    .call(this.xAxis.ticks(this.getWidthTickNumber()))
                this.svgD3.select(".y.axis")
                    .attr("transform", this.getTransformY())
                    .call(this.yAxis.ticks(5))
                this.svgD3.select('#clip-' + this.id + ' > rect')
                    .attr("x", this.margin.left)
                    .attr("width", this.width - this.margin.right - 10)
                    .attr("height", this.height)

                this.redrawLines()
                //d3.dispatch('zoom')
            },
            redrawLines: function () {
                this.lines.forEach(line => this.svgD3.select(line.id).attr("d", line.line), this)
                this.linesDI.forEach(line => this.svgD3.select(line.id).attr("d", line.line), this)
            },
            getXRange: function () {
                return [this.margin.left, this.width - this.margin.right]
            },
            getYRange: function () {
                return [this.height - this.margin.bottom, this.margin.top]
            },
            getTransformX: function () {
                return `translate(0, ${this.height - this.margin.bottom})`
            },
            getTransformY: function () {
                return `translate(${this.margin.left},0)`
            },
            updateSizeHTML: function () {
                this.width = document.getElementById(this.idParent).clientWidth - this.margin.right - this.margin.left
                this.height = document.getElementById(this.idParent).clientHeight - this.margin.top - this.margin.bottom
            },
            getWidthTickNumber: function () {

                const mbNumber = this.svgD3.select('.x.axis').node().getBoundingClientRect().width / 100

                return (mbNumber >= 5) ? mbNumber : 5
            },
            setMaxMinVariables: function (data) {
                if (data && data.length) {
                    let minValue = data[0].data[0].value
                    let maxValue = data[0].data[0].value
                    let minDate = data[0].data[0].date
                    let maxDate = data[0].data[0].date
                    data.forEach(tag => {
                        tag.data.forEach(data => {
                            if (data.value > maxValue) maxValue = data.value
                            if (data.value < minValue) minValue = data.value
                            if (data.date > maxDate) maxDate = data.date
                            if (data.date < minDate) minDate = data.date
                        })
                    })
                    this.minMaxData = {minValue, maxValue, minDate, maxDate}
                    return true
                }
            },
            initChart: function () {
                this.updateSizeHTML()
                this.xScale = d3.scaleTime()
                    .domain([this.minMaxData.minDate, this.minMaxData.maxDate])
                this.yScale = d3.scaleLinear()
                    .domain([this.minMaxData.minValue, this.minMaxData.maxValue])
                this.xAxis = d3.axisBottom()
                    .scale(this.xScale)
                this.yAxis = d3.axisLeft()
                    .scale(this.yScale)
                this.svgD3 = d3.select('#svg-' + this.id)

                this.xScale.range(this.getXRange())
                this.yScale.range(this.getYRange())
                this.svgD3.select(".x.axis")
                    .attr("transform", this.getTransformX())
                    .call(this.xAxis.ticks(this.getWidthTickNumber()))
                this.svgD3.select(".y.axis")
                    .attr("transform", this.getTransformY())
                    .call(this.yAxis.ticks(5))
                console.log(this.width)

                this.svgD3.select('clipPath>rect')
                    .attr("x", this.margin.left)
                    .attr("width", this.width - this.margin.right - 10)
                    .attr("height", this.height)


                this.zoom = d3.zoom()
                    .scaleExtent([1, 30])
                    .extent([[this.margin.left, 0], [this.width - this.margin.right, this.height]])
                    .translateExtent([[this.margin.left, -Infinity], [this.width - this.margin.right, Infinity]])
                    .on("zoom", this.onZoom)
                this.svgD3.call(this.zoom)

            },

            initTemperCharts: function () {
                const dataTemper = this.$store.getters.dataTemperature
                if (dataTemper && dataTemper.length && this.setMaxMinVariables(dataTemper) && !this.isInit.temper) {
                    this.initChart()
                    dataTemper.forEach(tag => {
                        const line = d3.line()
                            //.defined((d, i) => !isNaN(tag.values[i]))
                            .x(d => this.xScale(d.date))
                            .y(d => this.yScale(d.value))
                        const id = '#line-temper-' + this.id + '-' + tag.id
                        this.svgD3.select(id)
                            .datum(tag.data)
                            .attr("d", line)
                        this.lines.push({line, id})
                    }, this)

                    this.isInit.temper = true
                }
            },
            initDICharts: function () {
                console.log("init")
                const dataDigitalInputs = this.$store.getters.dataDigitalInputs
                console.log('test init: ' + (dataDigitalInputs && dataDigitalInputs.length && !isNaN(this.minMaxData.minValue) && !this.isInit.di))
                console.log('!this.isInit.di: ' + !this.isInit.di)

                if (dataDigitalInputs && dataDigitalInputs.length && !isNaN(this.minMaxData.minValue) && !this.isInit.di) {
                    console.log("really init")

                    this.svgD3 = d3.select('#svg-' + this.id)
                    const coefficient = (this.maxValue + this.minMaxData.minValue) / 2
                    dataDigitalInputs.forEach(tag => {
                        const line = d3.line()
                            .x(d => this.xScale(d.date))
                            .y(d => this.yScale(d.value ? d.value * coefficient : this.minMaxData.minValue))
                            .curve(d3.curveStepAfter)
                        console.log(this.minMaxData.minValue)
                        const id = '#line-di-' + this.id + '-' + tag.id
                        this.svgD3.select(id)
                            .datum(tag.data)
                            .attr("d", line)

                        this.lines.push({line, id})
                    }, this)

                    this.isInit.di = true
                    console.log("inited. this.isInit.di:" + this.isInit.di )
                }
            }

        },
        mounted() {
            this.idParent = 'chart-' + this.id
            window.addEventListener("resize", this.resize);
            this.updateSizeHTML()
            this.initChart()

            this.dataTemper = this.$store.getters.dataTemperature
            if (this.dataTemper && this.dataTemper.length)
                this.initTemperCharts()
            this.isDataReady = this.$store.getters.isDataReady
        },
    }


</script>

<style scoped>
    svg {
        width: 100%;
        height: 100%;
    }

    .line {
        fill: none;
        stroke: steelblue;
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