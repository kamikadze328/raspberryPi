<template>
  <div class="_config-wrapper">
    <ButtonHideContent :is-opened="isVisibleConfig" :is-visible="isMobile"
                       @toggle="isVisibleConfig = !isVisibleConfig"/>

    <div v-show="isVisibleConfig" class="_config-params-wrapper">
      <ButtonsDuration @changed-duration="setDates" :current-duration="currentDuration" :durations="DURATIONS"/>
      <ButtonsOnlyHighValues/>
    </div>
    <ButtonUpdate @clicked="emitter.emit('update-data')"/>
  </div>
</template>

<script>
import {mapMutations, mapActions, mapState, mapGetters} from 'vuex';
import ButtonsDuration from "./ButtonsDuration";
import ButtonsOnlyHighValues from "./ButtonsOnlyHighValues";
import ButtonUpdate from "./ButtonUpdate";
import ButtonHideContent from "./ButtonHideContent";

export default {
  name: "ChartConfig",
  components: {ButtonsDuration, ButtonsOnlyHighValues, ButtonUpdate, ButtonHideContent},
  data() {
    return {
      isVisibleConfig: false,
      width: 0
    }
  },
  watch: {
    isMobile(val) {
      this.isVisibleConfig = !val
    }
  },
  computed: {
    ...mapState(['DURATIONS']),
    ...mapGetters(['currentDuration']),
    isMobile() {
      return this.width <= 890
    },
  },
  methods: {
    ...mapMutations(['updateDates']),
    ...mapActions(['updateCurrentDuration']),
    onResize() {
      this.width = document.body.getBoundingClientRect().width
    },
    initResizeListener() {
      this.onResize()
      window.removeEventListener('resize', this.onResize)
      window.addEventListener('resize', this.onResize)
    },
    setDates(value){
      value = Number(value)
      if(value !== this.currentDuration.value) {
        this.updateCurrentDuration({value})
        this.updateDates({min: this.getCurrentDateDurationAgo(), max: new Date(new Date + 3600000)})
        this.emitter.emit('changed-duration')
      }
    },

    getCurrentDateDurationAgo(){
      return new Date - this.currentDuration.duration_ms
    }
  },
  mounted() {
    this.updateDates({min: this.getCurrentDateDurationAgo(), max: new Date})
    this.initResizeListener()
  }
}
</script>

<style scoped>
._config-wrapper {
  display: flex;
}

._config-wrapper > :last-child {
  margin-left: 30px;
}

._config-wrapper > :first-child {
  margin: 0 10px;
}

._config-params-wrapper {
  display: flex;
}

._config-params-wrapper > * {
  margin-bottom: 10px;
}

._config-params-wrapper > :first-child {
  margin-right: 25px;
}

@media (max-width: 890px) {
  ._config-params-wrapper {
    flex-direction: column;
  }
}

</style>