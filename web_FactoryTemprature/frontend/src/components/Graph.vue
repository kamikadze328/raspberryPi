<template>
  <div ref="graph-wrapper" class="disable-selection-text graph-wrapper">
    <svg :id="htmlID.svg" class="svg-content">
<!--      <clipPath :id="htmlID.clip">
        <rect/>
      </clipPath>-->
      <g :clip-path="'url(#' + htmlID.clip + ')'" class="x axis axisWhite"/>
      <g class="y axis axisWhite"/>
      <g :clip-path="'url(#' + htmlID.clip + ')'" class="lines">
        <path v-for="d in currentConfigs"
              :id="htmlID.line(d.id)"
              :key="d.id"
              :stroke="d.color"
              d=""
        />
      </g>
    </svg>
  </div>
</template>

<script>
import {mapGetters} from "vuex";

export default {
  name: "Graph",
  computed: {
    ...mapGetters({
      minMaxData: 'temperatureAvgMinMax',
      dataById: 'temperatureAvgDataById',
      data: 'temperaturesAvg',
      getMinMaxById: 'temperatureAvgMinMaxById'
    }),
    ...mapGetters([
      'currentConfigs',
      'currentDuration',
      'currentTags',
      'isOnlyHighValues',
        'dates'
    ]),
    format() {
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
    margin() {
      const right = 0
      return {
        top: 15,
        right: right,
        bottom: 10,
        left: 35,
        forClipPath: 0,
      }
    }
  },
  data() {
    return {
      htmlID: {
        svg: 'svg',
        clip: 'clip',
        line: (id) => 'line-' + id
      },
      ruLocale: this.d3.timeFormatLocale({
        "dateTime": "%A, %e %B %Y г. %X",
        "date": "%d.%m.%Y",
        "time": "%H:%M:%S",
        "periods": ["AM", "PM"],
        "days": ["Воскресенье", "Понедельник", "Вторник", "Среда", "Четверг", "Пятница", "Суббота"],
        "shortDays": ["Вс", "Пн", "Вт", "Ср", "Чт", "Пт", "Сб"],
        "months": ["Январь", "Февраль", "Март", "Апрель", "Май", "Июнь", "Июль", "Август", "Сентябрь", "Октябрь", "Ноябрь", "Декабрь"],
        "shortMonths": ["Янв", "Фев", "Мар", "Апр", "Май", "Июн", "Июл", "Авг", "Сен", "Окт", "Ноя", "Дек"]
      }),
      ticks: {
        minimumHorizontal: 5,
        vertical: 7
      },
      xScale: null,
      yScale: null,
      xAxis: null,
      yAxis: null,
      svgD3: null
    }
  },
  watch: {
    currentConfigs() {
      console.log('update graph (watch currentConfigs)')
      this.$nextTick(this.updateGraph)
    },
    data: {
      handler() {
        console.log('update graph (watch data)')
        this.updateGraph()
      },
      deep: true
    }
  },
  methods: {
    /*minMaxData(){
      console.log(this.minMaxDataStore)
      return this.minMaxDataStore
    },*/
    initGraph() {
      this.xScale = this.d3.scaleTime()
          .domain([this.dates().min, this.dates().max])
      this.yScale = this.d3.scaleLinear()
          .domain([this.minMaxData.min, this.minMaxData.max])
      this.xAxis = this.d3.axisBottom()
          .scale(this.xScale)
      this.yAxis = this.d3.axisLeft()
          .scale(this.yScale)

      this.svgD3 = this.d3.select('#' + this.htmlID.svg)
      this.updateGraph()
    },
    drawLines() {
      for (const config of this.currentConfigs) {
        const data = this.dataById(config.id)
        if (data) {
          const idHTML = this.htmlID.line(config.id)
              , line = this.d3.line()
              .defined(d => !isNaN(d.value))
              .x(d => this.xScale(d.date))
              .y(d => this.yScale(d.value))

          this.svgD3.select('#' + idHTML)
              .datum(data)
              .attr("d", line)
        }
      }
    },
    updateGraph() {
      this.reArrangeChart()
      this.resize()
      this.drawLines()
    },
    getWrapperWidth() {
      return this.$refs['graph-wrapper'].clientWidth - this.margin.right - this.margin.left
    },
    getWrapperHeight() {
      return this.$refs['graph-wrapper'].clientHeight - this.margin.top - this.margin.bottom
    },
    getXRange() {
      return [this.margin.left, this.getWrapperWidth() - this.margin.right]
    },
    getYRange() {
      return [this.getWrapperHeight() - this.margin.bottom, this.margin.top]
    },
    getTransformX: function () {
      return `translate(0, ${this.getWrapperHeight() - this.margin.bottom})`
    },
    getTransformY: function () {
      return `translate(${this.margin.left},0)`
    },
    getWidthTickNumber: function () {
      const mbNumber = this.getWrapperWidth() / 130
      return (mbNumber < this.ticks.minimumHorizontal) ? this.ticks.minimumHorizontal : mbNumber
    },
    multiFormat(date) {
      return (this.d3.timeSecond(date) < date ? this.format.millisecond
          : this.d3.timeMinute(date) < date ? this.format.second
              : this.d3.timeHour(date) < date ? this.format.minute
                  : this.d3.timeDay(date) < date ? this.format.hour
                      : this.d3.timeMonth(date) < date ? (this.d3.timeWeek(date) < date ? this.format.day : this.format.week)
                          : this.d3.timeYear(date) < date ? this.format.month
                              : this.format.year)(date);
    },
    resize: function () {
      this.xScale.range(this.getXRange())
      this.yScale.range(this.getYRange())
      this.svgD3.select(".x.axis")
          .attr("transform", this.getTransformX())
          .call(this.xAxis
              .ticks(this.getWidthTickNumber())
              .tickSize(this.margin.top + this.margin.bottom - this.getWrapperHeight())
              .tickFormat(this.multiFormat))
      this.svgD3.select(".y.axis")
          .attr("transform", this.getTransformY())
          .call(this.yAxis
              .ticks(this.ticks.vertical)
              .tickSize(this.margin.left + this.margin.right - this.getWrapperWidth()))

      this.svgD3.selectAll(".x.axis .tick text")
          .attr("y", 5)

      /*this.svgD3.select('#' + this.htmlID.clip + '>rect')
          .attr("y", -this.getWrapperHeight())
          .attr("x", this.margin.left)
          .attr("width", this.getWrapperWidth() - this.margin.forClipPath)
          .attr("height", this.getWrapperHeight() + this.margin.bottom + this.margin.top)*/
    },
    reArrangeChart() {
      this.xScale = this.d3.scaleTime()
          .domain([this.dates().min, this.dates().max])
      this.yScale = this.d3.scaleLinear()
          .domain([this.minMaxData.min, this.minMaxData.max])
      this.xAxis = this.d3.axisBottom()
          .scale(this.xScale)
      this.yAxis = this.d3.axisLeft()
          .scale(this.yScale)
    },
  },
  mounted() {
    this.initGraph()
    window.addEventListener("resize", this.updateGraph)
  }
}
</script>

<style scoped>
.graph-wrapper {
  background: white;
  flex-grow: 1;
  border-radius: 3px;
}

svg {
  width: 100%;
  height: 100%;
}

.axisWhite ::v-deep(line), .axisWhite ::v-deep(path) {
  stroke: #333;
  opacity: .3;
}

* ::v-deep(path), * ::v-deep(line) {
  shape-rendering: auto;
}

.lines ::v-deep(path) {
  fill: none;
  stroke-width: 1.5;
}

.axisWhite ::v-deep(text) {
  fill: #333;
  font-size: 1rem;
  font-weight: bold;
}
</style>