import Vue from 'vue'
import App from './App.vue'

Vue.config.productionTip = false


let store = {
  debug: true,
  state: {
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
    dataTemperature: [],
    dataDigitalInputs: [],
    duration: ['hour'],
    minDate: new Date,
    maxDate: new Date,
    isDataReady: [false, false],
  },
  setDuration(duration){

    this.state.duration.splice(0, 1, duration)
  },
  getDuration(){
    return this.state.duration[0]
  },
  setTemperatureReady(isReady) {
    this.state.isDataReady.splice(0, 1, isReady)
  },
  setDigitalInputReady(isReady) {
    this.state.isDataReady.splice(1, 1, isReady)

  },
  setDataTemperature(newData) {
    this.state.dataTemperature.length = 0
    newData.forEach((tag, i) => {
      newData[i].data.forEach((data, j) => {
        newData[i].data[j].date = new Date(data.date)
      })
      this.state.dataTemperature.push(newData[i])
    })
  },
  setDataDigitalInputs(newData) {
    this.state.dataDigitalInputs.length = 0
    newData.forEach((tag, i) => {
      newData[i].data.forEach((data, j) => {
        newData[i].data[j].date = new Date(data.date)
      })
      this.state.dataDigitalInputs.push(newData[i])
    })
  },
  getMinDate() {
    let date = new Date
    if (this.state.duration === 'day')
      date = date.setDate(this.getMaxDate().getDate() - 1)
    else if (this.state.duration === 'week')
      date = date.setDate(this.getMaxDate().getDate() - 7)
    else
      date = date.setHours(this.getMaxDate().getHours() - 1)
    this.state.minDate = new Date(date)
    return this.state.minDate
  },
  getMaxDate() {
    this.state.maxDate = new Date()
    return this.state.maxDate
  },
}

new Vue({
  render: h => h(App),
  data: {
    store,
  },
}).$mount('#app')
