<template>
  <div :style="{opacity: (isVisible ? 1 : 0),
                  transform: `translate(${translate.x}px, ${translate.y}px)`}"
       class="tooltip">
    <table>
      <thead>
      <tr>
        <td colspan="3"><strong class="x-value">{{ tooltipDateStr }}</strong></td>
      </tr>
      </thead>
      <tbody>
      <tr v-for="tag in lines" :key="tag.id">
        <td class="legend-color-guide">
          <div :style="{'background-color': tag.color}"></div>
        </td>
        <td class="tooltip-key">{{ tag.name }}</td>
        <td class="tooltip-value">{{ tag.value !== null ? tag.value : 'null' }}</td>
      </tr>
      </tbody>
    </table>
  </div>
</template>

<script>
export default {
  name: "Tooltip",
  props: {
    lines: Array,
    isVisible: Boolean,
    date: Date,
    translate: {x: Number, y: Number},
  },
  data() {
    return {
    }
  },
  computed: {
    tooltipDateStr() {
      const options = {
        year: '2-digit',
        month: '2-digit',
        day: '2-digit',
        timezone: 'UTC',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
      }
      return this.date ? this.date.toLocaleString("ru", options) : undefined
    },
  },
  watch: {
    lines: {
      handler() {
        console.log('update lines tooltip')
      },
      deep: true
    }
  },
  methods: {
    compareLines(l1, l2) {
      const l1Val = l1.value === null ? this.prevLines.find(l => l.id === l1.id).value : l1.value
      const l2Val = l2.value === null ? this.prevLines.find(l => l.id === l2.id).value : l2.value
      return l1Val - l2Val
    }
  }
}
</script>

<style scoped>
.tooltip {
  top: 0;
  left: 0;
  position: absolute;
  font-family: inherit;
  font-size: 12px;
  border: none;
  padding: 5px 10px;
  -webkit-box-shadow: 0 4px 16px rgba(0, 0, 0, .15);
  box-shadow: 0 4px 16px rgba(0, 0, 0, .15);
  -webkit-border-radius: 8px;
  border-radius: 8px;
  background-color: #2d353c;
  color: white;
}

.tooltip table td {
  padding: 2px;
  vertical-align: middle;
}

.legend-color-guide {
  padding: 2px;
  vertical-align: middle;
}

.legend-color-guide > div {
  width: 12px;
  height: 12px;
  border-radius: 4px;
  border: none;
  vertical-align: middle;
}

.tooltip-value {
  text-align: right;
  font-weight: 700;
}

td strong {
  font-weight: bolder;
}
</style>