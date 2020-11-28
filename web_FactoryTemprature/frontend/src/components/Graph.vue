<template>
  <div ref="graph-wrapper" class="disable-selection-text graph-wrapper">
    <svg :id="htmlID.svg" class="svg-content">
      <clipPath :id="htmlID.clip">
        <rect/>
      </clipPath>
      <g :clip-path="'url(#' + htmlID.clip + ')'" class="x axis axisWhite"/>
      <g class="y axis axisWhite"/>
      <g :clip-path="'url(#' + htmlID.clip + ')'" class="lines">
        <path v-for="d in configs"
              :id="'line-' + d.id"
              :key="d.id"
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
    ...mapGetters([
      'allTags',
      'temperatureAvgMinMaxById',
      'minDate',
      'maxDate',
      'configs'
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
    minMaxData() {
      let max = -Infinity, min = +Infinity
      for (const tag of this.allTags) {
        const minMax = this.temperatureAvgMinMaxById(tag)
        if (minMax) {
          if (minMax.min < min) min = minMax.min
          if (minMax.max > max) max = minMax.max
        }
      }
      return {min, max}
    },
    margin: function () {
      const right = 0
      return {
        top: 15,
        right: right,
        bottom: 10,
        left: 25,
        forClipPath: 0,
      }
    }
  },
  data() {
    return {
      htmlID: {
        svg: 'svg',
        clip: 'clip'
      },
      ruLocale: this.d3.timeFormatLocale({
        "dateTime": "%A, %e %B %Y г. %X",
        "date": "%d.%m.%Y",
        "time": "%H:%M:%S",
        "periods": ["AM", "PM"],
        "days": ["Воскресенье", "Понедельник", "Вторник", "Среда", "Четверг", "Пятница", "Суббота"],
        "shortDays": ["Вс", "Пн", "Вт", "Ср", "Чт", "Пт", "Сб"],
        "months": ["Января", "Февраля", "Марта", "Апреля", "Мая", "Июня", "Июля", "Августа", "Сентября", "Октября", "Ноября", "Декабря"],
        "shortMonths": ["Янв", "Фев", "Мар", "Апр", "Май", "Июн", "Июл", "Авг", "Сен", "Окт", "Ноя", "Дек"]
      }),
      xScale: null,
      yScale: null,
      xAxis: null,
      yAxis: null,
      svgD3: null
    }
  },
  methods: {
    initGraph() {
      this.xScale = this.d3.scaleTime()
          .domain([this.minDate, this.maxDate])
      this.yScale = this.d3.scaleLinear()
          .domain([this.minMaxData.min, this.minMaxData.max])
      this.xAxis = this.d3.axisBottom()
          .scale(this.xScale)
      this.yAxis = this.d3.axisLeft()
          .scale(this.yScale)

      this.svgD3 = this.d3.select('#' + this.htmlID.svg)
      this.resize()
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
      const mbNumber = this.getWrapperWidth() / 100
      return (mbNumber >= 5) ? mbNumber : 5
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
              .ticks(5)
              .tickSize(this.margin.left + this.margin.right - this.getWrapperWidth()))

      this.svgD3.selectAll(".x.axis .tick text")
          .attr("y", 5)

      this.svgD3.select('#' + this.htmlID.clip + '>rect')
          .attr("y", -this.getWrapperHeight())
          .attr("x", this.margin.left)
          .attr("width", this.getWrapperWidth() - this.margin.forClipPath)
          .attr("height", this.getWrapperHeight() + this.margin.bottom + this.margin.top)
    },
  },
  mounted() {
    this.initGraph()
    window.addEventListener("resize", this.resize)
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