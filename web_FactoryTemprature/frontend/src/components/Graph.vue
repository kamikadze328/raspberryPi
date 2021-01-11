<template>
  <div ref="graph-wrapper" class="disable-selection-text graph-wrapper" @mousemove="onMouseMove">
    <svg :id="htmlID.svg" class="svg-content">

      <g class="x axis axisWhite"/>
      <g class="y axis axisWhite"/>
      <g class="lines">
        <path v-for="d in currentConfigs"
              :id="htmlID.line(d.id)"
              :key="d.id"
              :stroke="d.color"
              d=""/>
      </g>
      <g>
        <line v-show="tooltip.isVisible" :x1="clientCursorOffset.x"
              :x2="clientCursorOffset.x" :y1="getWrapperHeight() - margin.bottom"
              :y2="margin.top"
              class="tooltip-vertical-line"/>
      </g>
    </svg>
    <Tooltip ref="tooltip" :date="tooltip.date" :is-visible="tooltip.isVisible" :lines="linesForTooltip"
             :translate="tooltip.translate"/>
  </div>
</template>

<script>
import {mapGetters} from "vuex";
import Tooltip from "@/components/Tooltip";

export default {
  name: "Graph",
  components: {Tooltip},
  data() {
    return {
      margin: {
        top: 15,
        right: 0,
        bottom: 10,
        left: 35,
        forClipPath: 0,
        forTooltip: 35
      },
      clientCursorOffset: {
        x: 0, y: 0,
      },
      htmlID: {
        svg: 'svg',
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
  computed: {
    ...mapGetters({
      minMaxData: 'temperatureAvgMinMax',
      dataById: 'temperatureAvgDataById',
      data: 'temperaturesAvg',
      valueByIdAndDate: 'temperatureAvgValByIdAndDate'
    }),
    ...mapGetters([
      'currentConfigs',
      'currentDuration',
      'currentTags',
      'isOnlyHighValues',
      'dates',
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
    linesForTooltip() {
      const lines = []
      for (const c of this.currentConfigs) {
        lines.push({color: c.color, name: c.name, id: c.id, value: undefined})
      }
      return lines
    },
    tooltip() {
      let x = this.clientCursorOffset.x, y = this.clientCursorOffset.y
      let isVisible =
          x > this.margin.left
          && x < this.getWrapperWidth()
          && y > this.margin.top
          && y < this.getWrapperHeight()
      const date = this.xScale ? this.xScale.invert(x) : null

      if (isVisible && date) {
        let isThereData = false
        for (const l of this.linesForTooltip) {
          const value = this.valueByIdAndDate(l.id, date)
          if (value === undefined) {
            isThereData ||= false
          } else {
            l.value = value
            isThereData = true
          }
        }
        if (isThereData) {
          const tooltipHTML = this.$refs['tooltip'].$el
          if (x + this.margin.forTooltip + tooltipHTML.clientWidth < this.getWrapperWidth())
            x = x + this.margin.forTooltip
          else
            x = x - this.margin.forTooltip - tooltipHTML.clientWidth

          const offsetToXAxis = 9
          if (y + tooltipHTML.clientHeight / 2 > this.getWrapperHeight() - offsetToXAxis)
            y = this.getWrapperHeight() - tooltipHTML.clientHeight - offsetToXAxis
          else if (y - tooltipHTML.clientHeight / 2 < 0)
            y = 0
          else
            y = y - tooltipHTML.clientHeight / 2
        } else isVisible = false
      }
      return {
        isVisible,
        translate: {x, y},
        date
      }
    }
  },
  watch: {
    currentConfigs() {
      this.$nextTick(this.updateGraph)
    },
    data: {
      handler() {
        this.updateGraph()
      },
      deep: true
    },
    lines: {
      handler() {
        console.log('update lines')
      },
      deep: true
    }
  },
  methods: {
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
      this.initGraph()
      this.resize()
      this.drawLines()
    },
    getWrapperWidth() {
      return this.$refs['graph-wrapper'] ? this.$refs['graph-wrapper'].clientWidth - this.margin.right - this.margin.left : 0
    },
    getWrapperHeight() {
      return this.$refs['graph-wrapper'] ? this.$refs['graph-wrapper'].clientHeight - this.margin.top - this.margin.bottom : 0
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
    resize () {
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
    },
    onMouseMove(e) {
      this.clientCursorOffset.x = e.offsetX
      this.clientCursorOffset.y = e.offsetY
    }
  },
  mounted() {
    this.updateGraph()
    window.addEventListener("resize", this.updateGraph)
  }
}
</script>

<style scoped>
.graph-wrapper {
  background: white;
  flex-grow: 1;
  border-radius: 3px;
  position: relative;
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

.tooltip-vertical-line {
  stroke-width: .5;
  stroke: #2d353c;
  fill: none;
}
</style>