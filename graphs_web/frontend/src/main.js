import Vue from 'vue'
import App from './App.vue'

Vue.config.productionTip = false
import Vuex from 'vuex'
Vue.use(Vuex)

const store =  new Vuex.Store({
  debug: true,
  state: {
    tags: {
      tagsTemperature: [
        {id: 10101, description: 'Топка'},
        {id: 10102, description: 'Сушилка левая'},
        {id: 10103, description: 'Дым газы'},
        {id: 10104, description: 'Сушилка правая'},
        {id: 10105, description: 'Реактор левый'},
        {id: 10106, description: 'Выгрузка углерода левая'},
        {id: 10107, description: 'Реактор правый'},
        {id: 10108, description: 'Выгрузка углерода правая'},
      ],
      tagsDigitalInput: [
        {id: 12101, description: 'Бункер подачи сырья'},
        {id: 12102, description: 'Пересыпка сушилки левая'},
        {id: 12103, description: 'Пересыпка сушилки правая'},
        {id: 12104, description: 'Выход углерода левый'},
        {id: 12105, description: 'Выход углерода правый'},
      ],
    },
    data: {
      dataTemperature: [],
      dataDigitalInputs: [],
    },
    duration: 'week',
    date: {min: new Date, max: new Date},
    isDataReady: {temper: false, di: false},
  },
  mutations: {
    updateDate(state, {min, max}) {
      state.date.max = max
      state.date.min = min
    },
    setTemperatureReady(state, {isReady}) {
      state.isDataReady.temper = isReady
    },
    setDigitalInputReady(state, {isReady}) {
      state.isDataReady.di = isReady
    },
    setDataTemperature(state, {newData}) {
      this.commit('prepareNewData', {newData, oldData: state.data.dataTemperature})
    },
    setDataDigitalInputs(state, {newData}) {
      this.commit('prepareNewData', {newData, oldData: state.data.dataDigitalInputs})
    },
    setDuration(state, {duration}) {
      state.duration = duration
    },
    prepareNewData(state, {newData, oldData}){
      oldData.length = 0
      newData.forEach((tag, i) => {
        newData[i].data.forEach((data, j) => {
          newData[i].data[j].date = new Date(data.date)
        })
        oldData.push(newData[i])
      })
    },
  },
  actions: {

  },
  getters: {
    date: state => {
      let min = new Date
      const max = new Date
      const duration = state.duration
      if (duration === 'day')
        min = min.setDate(max.getDate() - 1)
      else if (duration === 'week')
        min = min.setDate(max.getDate() - 7)
      else
        min = min.setHours(max.getHours() - 1)

      store.commit('updateDate', {max, min: new Date(min)})
      return state.date
    },
    duration: state => {
      return state.duration
    },
    tagsTemperature: state => {
      return state.tags.tagsTemperature
    },
    tagsDigitalInput: state => {
      return state.tags.tagsDigitalInput
    },
    isDataReady: state => {
      return state.isDataReady
    },
  },
})

new Vue({
  render: h => h(App),
  store
}).$mount('#app')
