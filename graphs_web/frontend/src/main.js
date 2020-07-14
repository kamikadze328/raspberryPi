import Vue from 'vue'
import App from './App.vue'

Vue.config.productionTip = false
import Vuex from 'vuex'

Vue.use(Vuex)

const store = new Vuex.Store({
    debug: true,
    state: {
        devices: [
            {
                id: 10000,
                description: 'Контроллер 8 аналоговых входов',
                tags: [
                    {id: 10101, description: 'Топка'},
                    {id: 10102, description: 'Сушилка левая'},
                    {id: 10103, description: 'Дым газы'},
                    {id: 10104, description: 'Сушилка правая'},
                    {id: 10105, description: 'Реактор левый'},
                    {id: 10106, description: 'Выгрузка углерода левая'},
                    {id: 10107, description: 'Реактор правый'},
                    {id: 10108, description: 'Выгрузка углерода правая'},
                ]
            },
            {
                id: 12000,
                description: 'Контроллер дискретных выходов (24 реле)',
                tags: [
                    {id: 12101, description: 'Бункер подачи сырья'},
                    {id: 12102, description: 'Пересыпка сушилки левая'},
                    {id: 12103, description: 'Пересыпка сушилки правая'},
                    {id: 12104, description: 'Выход углерода левый'},
                    {id: 12105, description: 'Выход углерода правый'},
                ]
            },
        ],
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
            temperature: [],
            digitalInputs: [],
        },
        tagsData: [
            //{id, type, data}
        ],
        settings: {
            date: {min: new Date, max: new Date},
        },
        colorScheme: [
            '#348fe2',
            '#f59c1a',
            '#32a932',
            '#ff5b57',
            '#00acac',
            '#8753de',
            '#fb5597',
            '#6c757d',
            '#ffd900',
        ],
    },
    mutations: {
        updateDate(state, {min, max}) {
            state.settings.date.max = max
            state.settings.date.min = min
        },
        addNewTag(state, {newTag}) {
            let minValue = Number.MAX_VALUE,
                maxValue = Number.MIN_VALUE

            const id = Number(newTag.id),
                  type = String(newTag.type).toUpperCase()

            newTag.data.forEach(d => {
                const value = d.value ? Number(d.value) : undefined,
                      date = new Date(d.date)

                if (d && value > maxValue) maxValue = value
                if (d && value < minValue) minValue = value
                return {date, value}
            })

            const data = newTag.data,
                  minDate = data[0].date,
                  maxDate = data[data.length - 1].date

            Vue.set(state.tagsData, newTag.id, {id, type, data, minMaxData: {minValue, maxValue, minDate, maxDate}})
            console.log(state.tagsData)
        },
        setDevicesAndTags(state, {data}){
            state.devices = data
        },
    },
    actions: {
    },
    getters: {
        devices: state => {
            return state.devices
        },
        loadedTags: state => {
            let tags = []
            for(const tag of  state.tagsData) tags.push(tag.id)
            return tags
        },
        isTagsLoaded: state => id => {
            return !!state.tagsData[id]
        },
        tagsData: state => {
            return state.tagsData
        },
        tagById: state => id => {
            return state.tagsData[id]
        },
        minDate: state => {
            return state.settings.date.min
        },
        maxDate: state => {
            return state.settings.date.max
        },
        duration: state => {
            return state.settings.duration
        },

        getTagsDescription: state => id => {
            for (let device of state.devices)
                if (Math.floor(device.id / 1000) === Math.floor(id / 1000))
                    for (let tag of device.tags)
                        if (tag.id === id)
                            return tag.description
        },
        color: state => currentColors => {
            const currentNumber = currentColors.length
            let index = 0
            console.log('number of colors: ' + currentNumber)
            let color
            if (currentNumber < state.colorScheme.length)
                do {
                    color = state.colorScheme[index]
                    index++
                } while (currentColors.indexOf(color) >= 0)
            else
                color = '#' + (Math.random().toString(16) + '000000').substring(2, 8)

            store.commit('addColor', {color})
            return color
        }
    },
})

new Vue({
    render: h => h(App),
    store
}).$mount('#app')
