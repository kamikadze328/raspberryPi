<template>
    <svg :id="'svg-'+ id" class="svg-content">
        <g class="x axis axisWhite"></g>
        <g class="y axis axisWhite"></g>
        <g class="lines" stroke="#348fe2" fill="none" stroke-width="1.5">
            <path v-for="tag in this.$root.store.state.tagsTemperature"
                  :key="'line-temper-'+id + '-' + tag.id"
                  :id="'line-temper-'+id + '-' + tag.id"
                  display="none"/>
        </g>
        <g class="lines" stroke="#ff5b57" fill="none" stroke-width="1.5">
            <path v-for="tag in this.$root.store.state.tagsDigitalInput"
                  :key="'line-di-'+id + '-' + tag.id"
                  :id="'line-di-'+id + '-' + tag.id"
                  display="none"/>
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
            isDataReady: function (val) {
                if (val[0])
                    this.initTemperCharts()
                if (val[0] && val[1])
                    this.initDICharts()
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
                linesDI:[],
                svgD3: null,
                dataTemper: null,
                isDataReady: null,
                maxValue: 0,
                minValue: 0,
            }
        },
        methods: {
            watchSelectedCharts: function(val, oldVal, chartName){
                const isAdded = val.length > oldVal.length
                for (let i = 0; i < (isAdded ? val.length : val.length + 1); i++)
                    if (val[i] !== oldVal[i]) {
                        const id = isAdded ? val[i] : oldVal[i]
                        this.svgD3.select('#line-' + chartName + '-' + this.id + '-' + id)
                            .attr('display', isAdded ? 'block' : 'none')
                        break
                    }
            },
            resize: function () {
                this.updateSizeHTML()
                this.xScale.range(this.getXRange())
                this.yScale.range(this.getYRange())
                this.svgD3.select(".x.axis")
                    .attr("transform", this.getTransformX())
                    .call(this.xAxis.ticks(this.width / 80 <= 10 ? this.width / 80 : 10))
                this.svgD3.select(".y.axis")
                    .attr("transform", this.getTransformY())
                    .call(this.yAxis.ticks(5))

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
            initTemperCharts: function () {
                const dataTemper = this.$root.store.state.dataTemperature
                if (dataTemper && dataTemper.length) {
                    let minValue = dataTemper[0].data[0].value
                    let maxValue = dataTemper[0].data[0].value
                    let minDate = dataTemper[0].data[0].date
                    let maxDate = dataTemper[0].data[0].date
                    dataTemper.forEach(tag => {
                        tag.data.forEach(data => {
                            if (data.value > maxValue) maxValue = data.value
                            if (data.value < minValue) minValue = data.value
                            if (data.date > maxDate) maxDate = data.date
                            if (data.date < minDate) minDate = data.date
                        })
                    })
                    this.minValue = minValue - 5
                    this.maxValue = maxValue + 5
                    this.xScale = d3.scaleTime()
                        .domain([minDate, maxDate])
                    this.yScale = d3.scaleLinear()
                        .domain([minValue - 5, maxValue + 5]).nice()
                    this.xAxis = d3.axisBottom()
                        .scale(this.xScale)
                    this.yAxis = d3.axisLeft()
                        .scale(this.yScale)
                    this.svgD3 = d3.select('#svg-' + this.id)

                    this.xScale.range(this.getXRange())
                    this.yScale.range(this.getYRange())
                    this.svgD3.select(".x.axis")
                        .attr("transform", this.getTransformX())
                        .call(this.xAxis.ticks(this.width / 80 <= 10 ? this.width / 80 : 10))
                    this.svgD3.select(".y.axis")
                        .attr("transform", this.getTransformY())
                        .call(this.yAxis.ticks(5))


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
                }
            },
            initDICharts: function(){
                const dataDigitalInputs = this.$root.store.state.dataDigitalInputs
                if (dataDigitalInputs && dataDigitalInputs.length) {

                    this.svgD3 = d3.select('#svg-' + this.id)
                    const coefficient = (this.maxValue + this.minValue)/2
                    dataDigitalInputs.forEach(tag => {
                        const line = d3.line()
                            .x(d => this.xScale(d.date))
                            .y(d => this.yScale(d.value ? d.value*coefficient : this.minValue))
                        .curve(d3.curveStepAfter)
                        console.log(this.minValue)
                        const id = '#line-di-' + this.id + '-' + tag.id
                        this.svgD3.select(id)
                            .datum(tag.data)
                            .attr("d", line)

                        this.lines.push({line, id})
                    }, this)
                }
            }
        },
        mounted() {
            this.idParent = 'chart-' + this.id
            window.addEventListener("resize", this.resize);
            this.updateSizeHTML()
            this.dataTemper = this.$root.store.state.dataTemperature
            if(this.dataTemper && this.dataTemper.length)
                this.initTemperCharts()
            this.isDataReady = this.$root.store.state.isDataReady
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