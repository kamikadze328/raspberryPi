<template>
  <div class="container">
    <header/>
    <div class="container-temperatures-main">
      <TemperatureComponent v-for="config in configs" :key="config.id"
                            :config="config"/>
    </div>
    <GraphContainer class="container-graph"/>
  </div>
</template>

<script>
import {mapState, mapGetters, mapActions} from 'vuex';

import TemperatureComponent from './components/TemperatureComponent';
import GraphContainer from "./components/GraphContainer";

export default {
  name: 'App',
  components: {GraphContainer, TemperatureComponent},
  computed: {
    data() {
      return {
        serverUpdater: null,
        localUpdater: null
      }
    },
    ...mapState([
      'SERVER',
    ]),
    ...mapGetters([
        'dates',
      'configs',
      'allTags',
      'clearConfigs',
      'currentDuration',
      'updateInterval',
    ])
  },
  data() {
    return {}
  },
  watch: {
    currentDuration() {
      this.setUpdateInterval()
      this.getData()
    },
  },
  methods: {
    ...mapActions([
      'initData',
      'tryUpdateData',
      'initDynData'
    ]),
    requestDynData() {
      return this.axios.request({
        method: 'POST',
        url: this.SERVER.URL.DATA,
        data: {
          purpose: this.SERVER.PURPOSE.GET_DATA_DYN,
          tags: this.allTags
        }
      }).then(response => {
        if (response.data.data) {
          console.log(response.data.data)
          return response.data.data
        } else throw response
      }).catch(error => {
        console.log(error)
      })
    },
    getAndInitDynData() {
      this.requestDynData().then(newData => {
        if (newData)
          this.initDynData({newData})
      })
    },
    getData() {
      this.axios.request({
        method: 'POST',
        url: this.SERVER.URL.DATA,
        data: {
          purpose: this.SERVER.PURPOSE.GET_AVG_DATA,
          configs: this.clearConfigs,
          minDate: this.dates().min.getTime(),
          maxDate: this.dates().max.getTime()
        }
      }).then(response => {
        if (response.data.data) {
          console.log(response.data.data)
          this.initData({newData: response.data.data})
        } else throw response
      }).catch(error => {
        console.log(error)
      })
    },
    initDataStore() {
      this.getData()
      this.getAndInitDynData()
    },
    setUpdateInterval() {
      clearInterval(this.localUpdater)
      this.localUpdater = setInterval(this.getAndInitDynData, 60000)

      clearInterval(this.serverUpdater)
      this.serverUpdater = setInterval(this.tryUpdateData, this.updateInterval)
    },
  },
  mounted() {
    this.emitter.on('update-data', this.getAndInitDynData)
    this.initDataStore()
    this.setUpdateInterval()
  }
}
</script>

<style>
body, html, #app {
  padding: 0;
  margin: 0;
  width: 100%;
  height: 100%;
}

#app {
  font-family: Avenir, Helvetica, Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  text-align: center;
  color: #2c3e50;
  background-color: #dee2e6;
  overflow-y: auto;
}

header {
  grid-area: header;
}

.container-temperatures-main > * {
  grid-area: temper-main;
  overflow-y: auto;
}

.container-temperatures-main > *:not(:last-child) {
  border-bottom: none;
}

.container-temperatures-main > *:last-child {
  border-radius: 0 0 8px 8px;
}

.container-temperatures-main > *:first-child {
  border-radius: 8px 8px 0 0;
}

.container-graph {
  grid-area: graph;
}

.container {
  margin: 10px;
  display: grid;
  column-gap: 10px;
  grid-template-rows: 0 auto;
  grid-template-columns: 180px auto;
  grid-template-areas:
      'header header'
      'temper-main graph';
}

.pretty-input {
  overflow: hidden;
  background: #fff;
  border: 1px solid #fff;
  border-radius: 8px;
  font-size: 16px;
  height: 46px;
  opacity: 1;
  transition: all .2s ease-in-out;
  outline: 0;
}

.my-button {
  border: 2px solid #348fe2;
  transition: all .3s cubic-bezier(.6, 0, .4, 1);
}

.my-button:hover, .my-button:focus, .button-focus {
  background: #348fe2;
  color: white !important;
}

.button-box {
  display: flex;
  justify-content: center;
}

.button-box > button {
  border-radius: 0;
  border: #e0e0dc solid 1px;
  max-width: 100px;
}

.button-box > button:first-of-type {
  border-radius: 8px 0 0 8px;
}

.button-box > button:last-of-type {
  border-radius: 0 8px 8px 0;
}

.clickable:hover, .clickable:focus {
  cursor: pointer !important;
}

.red-button {
  border-color: #ff5b57 !important;
  color: #ff5b57 !important;
}

.red-button:hover, .red-button:focus {
  background: #ff5b57 !important;
}

.yellow-button {
  border-color: #f59c1a !important;
  color: #f59c1a !important;
}

.yellow-button:hover, .yellow-button:focus {
  background: #f59c1a !important;
}

.green-button {
  border-color: #32A932 !important;
}

.green-button:hover, .green-button:focus {
  background: #32A932;
}

.svg-img {
  background-repeat: no-repeat;
  background-position: center center;
}

.svg-img:hover, .svg-img:focus {
  background-repeat: no-repeat;
  background-position: center center;
}
</style>
