<template>
  <div ref="chart-wrapper" class="chart-wrapper" :style="'width: calc(100% + ' + (addedPixels) + 'px);'">
    <svg :id="htmlID.svg" >
      <g class="x axis axisWhite"/>
      <g class="y axis axisWhite"/>
      <g class="bars">
        <g v-for="place in places" :key="place.id" :fill="place.color">
          <rect v-for="i in [...new Array(months.length).keys()]" :key="i"></rect>
        </g>
      </g>
    </svg>
  </div>
</template>

<script>
import {mapGetters, mapState} from "vuex";

export default {
  name: "Chart",
  data() {
    return {
      margin: {
        top: 20,
        left: 30,
        right: 0,
        bottom: 10
      },
      scaleSettings: {
        paddingInner: .05,
        align: .2
      },
      htmlID: {
        svg: 'svg'
      },
      ticks: {
        vertical: 10
      },
      svgD3: null,
      xScale: null,
      yScale: null,
      xAxis: null,
      yAxis: null,
      xDomainObj: new Map(),
      isChartTooSmall: false,
      addedPixels: 0,
    }
  },
  computed: {
    ...mapState(['places', 'MONTH_NUM_MIN', 'MONTH_NUM_STEP']),
    ...mapGetters([
      'sumData',
      'months',
      'monthCurrDateStrByDate'
    ]),
    d3Stack() {
      return this.d3.stack().keys(this.places.map(place => place.name))
    },
    preparedData() {
      const dataLength = Math.min(this.months.length, this.sumData.length)
      const data = new Array(dataLength)
      for (let i = 0; i < dataLength; i++) {
        data[i] = this.sumData[i]
      }
      return this.d3Stack(data)
    },
    maxData() {
      let max = 0
      if (this.preparedData.length) {
        this.preparedData[this.preparedData.length - 1].forEach(d => {
          max = d[1] > max ? d[1] : max
        })
      }
      return max
    },
  },
  watch: {
    months() {
      if(this.months.length === this.MONTH_NUM_MIN) this.addedPixels = 0
      this.$nextTick(this.updateGraph)
    },
    sumData() {
      this.$nextTick(this.updateGraph)
    },
    isChartTooSmall(val){
      if(val)
        this.addedPixels += 29 * this.MONTH_NUM_STEP
      this.$nextTick(this.updateGraph)
    }
  },
  methods: {
    initChart() {
      this.svgD3 = this.d3.select('#' + this.htmlID.svg)

      this.xScale = this.d3.scaleBand()
          .paddingInner(this.scaleSettings.paddingInner)
          .align(this.scaleSettings.align)
          .domain(this.getXDomain())
      this.yScale = this.d3.scaleLinear()
          .domain([0, this.maxData])
      this.xAxis = this.d3.axisBottom()
          .scale(this.xScale)
      this.yAxis = this.d3.axisLeft()
          .scale(this.yScale)
    },
    resize() {
      this.xScale.rangeRound(this.getXRange())
      this.yScale.range(this.getYRange())
      this.svgD3.select(".x.axis")
          .attr("transform", this.getTransformX())
          .call(this.xAxis)
      this.svgD3.select(".y.axis")
          .attr("transform", this.getTransformY())
          .call(this.yAxis)
    },
    drawBars() {
      this.svgD3.select('.bars')
          .selectAll('g')
          .data(this.preparedData)
          .selectAll('rect')
          .data(d => d)
          .attr('x', d => this.xScale(this.xDomainObj.get(d.data.date.getTime())))
          .attr("y", d => this.yScale(d[1]))
          .attr("height", d => this.yScale(d[0]) - this.yScale(d[1]))
          .attr("width", this.xScale.bandwidth())
    },
    updateGraph() {
      this.initChart()
      this.resize()
      this.drawBars()
      this.isChartTooSmall = this.xScale ? this.xScale.bandwidth() < 29 : false
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
    getWrapperWidth() {
      return this.$refs['chart-wrapper'] ? this.$refs['chart-wrapper'].clientWidth - this.margin.right - this.margin.left : 0
    },
    getWrapperHeight() {
      return this.$refs['chart-wrapper'] ? this.$refs['chart-wrapper'].clientHeight - this.margin.top - this.margin.bottom : 0
    },
    getXDomain() {
      this.xDomainObj = new Map()
      const months = []
      for (const date of this.months) {
        const str = this.beautyDateStr(date)
        this.xDomainObj.set(date.getTime(), str)
        months.push(str)
      }
      return months
    },
    beautyDateStr(date) {
      const month = date.getMonth()
      return (month + 1 < 10 ? '0' : '') + (month + 1) + ('.' + date.getFullYear() % 1000)
    }
  },
  mounted() {
    this.updateGraph()
    window.addEventListener("resize", this.updateGraph)
  }
}
</script>

<style scoped>
.chart-wrapper {
  overflow-x: auto;
  overflow-y: hidden;
  flex-grow: 1;
  height: 400px;
}

svg {
  width: 100%;
  height: 100%;
}
@media (max-width: 1300px) {
  .chart-wrapper{
    height: 250px;
  }
}
</style>