<template>
    <svg :id="'svg-'+ id" class="svg-content">
        <g class="x axis axisWhite"></g>
        <g class="y axis axisWhite"></g>
    </svg>
</template>

<script>
    import * as d3 from 'd3';

    export default {
        name: "Chart",

        props: {
            id: Number,
            tagsTemperature: {
                type: Array,
                required: true
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
                data: [{date: new Date(1000000010), value: Math.random() * 10}, {
                    date: new Date(),
                    value: Math.random() * 10
                }, {date: new Date(), value: Math.random() * 10}]
            }
        },
        methods: {
            resize: function () {
                this.updateSize()
                this.xScale.range(this.getXRange())
                this.yScale.range(this.getYRange())
                this.svg.select(".x.axis")
                    .attr("transform", this.getTransformX())
                    .call(this.xAxis.ticks(this.width / 80 <= 10 ? this.width / 80 : 10))
                this.svg.select(".y.axis")
                    .attr("transform", this.getTransformY())
                    .call(this.yAxis.ticks(5))
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
            updateSize: function () {
                this.width = document.getElementById(this.idParent).clientWidth - this.margin.right - this.margin.left
                this.height = document.getElementById(this.idParent).clientHeight - this.margin.top - this.margin.bottom
            }
        },
        mounted() {
            this.idParent = 'chart-' + this.id
            window.addEventListener("resize", this.resize);
            this.updateSize()

            this.xScale = d3.scaleTime()
                .domain(d3.extent(this.data, d => d.date))
            const maxValue = d3.max(this.data, d => d.value)
            this.yScale = d3.scaleLinear()
                .domain([0, maxValue ? maxValue : 3]).nice()
            this.xAxis = d3.axisBottom()
                .scale(this.xScale)
            this.yAxis = d3.axisLeft()
                .scale(this.yScale)
            this.svg = d3.select('#svg-' + this.id)

            this.resize()

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