<template>
  <div :style="style" class="_temperature-container">
    <div class="_temperature-name">
      {{ config.name }}
    </div>
    <div class="_temperature-avg">
      {{ prettyNumberFormatStr(currentAvgValue(config.id)) + (currentAvgValue(config.id) === undefined ? '' : '&deg;C') }}
    </div>
    <div class="_temperature-list-tempers">
      <div v-for="tagId in config.tags" v-show="config.tags.length > 1" :key="tagId">
        {{ prettyNumberFormatStr(dynDataById(tagId)) }}
      </div>
    </div>
  </div>
</template>

<script>
import {mapGetters} from 'vuex'
import {mapState} from 'vuex';

export default {
  name: "TemperatureComponent",
  props: {
    config: {
      name: String,
      tags: Array,
      color: String,
      backgroundColor: String,
      isHighValue: Boolean
    }
  },
  computed: {
    ...mapGetters([
      'configById', 'currentTemperature', 'dynDataById', 'currentAvgValue'
    ]),
    ...mapState(['isOnlyHighValues']),
    style() {
      return {
        borderColor: this.isOnlyHighValues === this.config.isHighValue ? this.config.color : 'black',
        backgroundColor: this.isOnlyHighValues === this.config.isHighValue ? this.config.backgroundColor : 'transparent'
      }
    },
  },
  data() {
    return {}
  },
  methods: {

    prettyRound(x) {
      return this.isFiniteNumber(x) ? Math.round((Number(x) + Number.EPSILON) * 10) / 10 : undefined
    },
    isFiniteNumber(x) {
      return x !== undefined && x !== null && !isNaN(x) && isFinite(x)
    },
    prettyNumberFormatStr(x) {
      x = this.prettyRound(x)
      return x === undefined ? 'NaN' : x.toString()
    }
  },
  mounted() {
  }
}
</script>

<style scoped>
._temperature-list-tempers {
  grid-area: list-tempers;
}

._temperature-avg {
  grid-area: avg;
  font-weight: bolder;
  font-size: 1.8rem;
}

._temperature-name {
  grid-area: name;
  font-weight: bold;
  font-size: 1.1rem;
}

._temperature-container {
  display: grid;
  align-items: center;
  grid-template-columns: auto 2.5rem;
  grid-template-rows: 1.1rem auto;
  grid-template-areas:
      'name name'
      'avg list-tempers';

  border: 2px solid transparent;
}

</style>