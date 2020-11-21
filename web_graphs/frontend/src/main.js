import Vue from 'vue'
import App from './App.vue'
import Vuex from 'vuex'

Vue.config.productionTip = true
Vue.config.performance =  true
Vue.use(Vuex)

const store = new Vuex.Store({
    state: {
        devices: [
            {
                id: 10000,
                description: 'Контроллер 8 аналоговых входов',
                tags: [
                    {id: 10101, description: 'Вх. №1 -> Датчик температуры Топка'},
                    {id: 10102, description: 'Вх. №2 -> Датчик температуры Сушилка левая'},
                    {id: 10103, description: 'Вх. №3 -> Датчик температуры Дым газы'},
                    {id: 10104, description: 'Вх. №4 -> Датчик температуры Сушилка правая'},
                    {id: 10105, description: 'Вх. №5 -> Датчик температуры Реактор левый'},
                    {id: 10106, description: 'Вх. №6 -> Датчик температуры Выгрузка углерода левая'},
                    {id: 10107, description: 'Вх. №7 -> Датчик температуры Реактор правый'},
                    {id: 10108, description: 'Вх. №8 -> Датчик температуры Выгрузка углерода правая'},
                ]
            },
            {
                id: 12000,
                description: 'Контроллер дискретных выходов (24 реле)',
                tags: [
                    {id: 12101, description: 'Вых. №1 -> Шнек подачи сырья'},
                    {id: 12102, description: 'Вых. №2 -> Бункер сырья (живой пол)'},
                    {id: 12103, description: 'Вых. №3 -> Главный шнек выгрузки углерода'},
                    {id: 12104, description: 'Вых. №4 -> Последний шнек выгрузки углерода'},
                    {id: 12105, description: 'Вых. №5 -> Аварийный шнек выгрузки углерода'},
                ]
            },
        ],
        data: {
            temperature: [],
            digitalInputs: [],
        },
        tagsData: [
            //{id, data}
        ],
        EMPTY_CONFIG: {
            name: 'Текущая конфигурация',
            id: -1,
            charts: [
                {
                    id: 0,
                    name: 'График 1',
                    tags: []
                },
                {
                    id: 1,
                    name: 'График 2',
                    tags: []
                }
            ],
        },
        currentConfig: {
            name: 'Конфигурация',
            id: -1,
            charts: [
                {
                    id: 0,
                    name: 'Графичушечка 1',
                    tags: []
                },
                {
                    id: 1,
                    name: 'График 2',
                    tags: []
                }
            ],
        },
        configs:[/*
            {
                name: 'config kek',
                id: 0,
                charts: [
                    {
                        id: 0,
                        name: 'График 1',
                        tags: [10101, 10103]
                    },
                    {
                        id: 1,
                        name: 'График 2',
                        tags: [12104]
                    }
                ],
            },
            {
                name: 'config lol',
                id: 2,
                charts: [
                    {
                        id: 0,
                        name: 'График 1',
                        tags: [10102, 10103]
                    },
                    {
                        id: 1,
                        name: 'График 2',
                        tags: [12103]
                    }
                ],
            },*/
        ],
        currentConfigId: 0,
        settings: {
            date: {min: new Date(new Date - 86400000), max: new Date},
        },
        chart: {
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
            tooltipInfo: {
                dates: [],
                coordinates: {x: 0, y: 0},
                showTooltip: false,
                currentDate: null
            },
            zoom: {
                x: 0,
                k: 1
            },
        },
        DATA_MAX_LENGTH_GAP: 120000//mc
    },
    mutations: {
        setTooltipCurrentDate(state, date){
            state.chart.tooltipInfo.currentDate = new Date(date)
        },
        setTooltipVisibility(state, isVisible){
            state.chart.tooltipInfo.showTooltip = isVisible
        },
        showTooltip(state){
          state.chart.tooltipInfo.showTooltip = true
        },
        hideTooltip(state){
            state.chart.tooltipInfo.showTooltip = false
        },
        setD3Zoom(state, {x, k}){
            state.chart.zoom.x = x
            state.chart.zoom.k = k
        },
        setTooltipCoordinates(state, {x, y}){
          state.chart.tooltipInfo.coordinates.x = x
          state.chart.tooltipInfo.coordinates.y = y
        },
        addTooltipLine(state, {date}){
            state.chart.tooltipInfo.dates.push(new Date(date))
        },
        clearTooltipLines(state){
          state.chart.tooltipInfo.dates.splice(0)
        },
        updateDate(state, {min, max}) {
            state.settings.date.max = max
            state.settings.date.min = min
        },
        addNewTag(state, {newTag}) {
            let minValue = Number.MAX_VALUE,
                maxValue = Number.MIN_VALUE
            let isThereData = false
            let dataLength = newTag.data.length
            const id = Number(newTag.id)
            let isPrevNull = false
            for (let i = 0; i < dataLength; i++) {
                const d = newTag.data[i],
                    value = d.value ? Number(d.value) : undefined,
                    date = new Date(d.date)

                if (d && value > maxValue) maxValue = value
                if (d && value < minValue) minValue = value
                newTag.data[i] = {date, value}

                if(i && !isPrevNull && date - newTag.data[i - 1].date > state.DATA_MAX_LENGTH_GAP){
                    newTag.data.splice(i, 0, {data: new Date(date.getTime() + 1), value: undefined})
                    dataLength++
                    i++
                }

                if (value !== undefined) {
                    isThereData = true
                    isPrevNull = false
                } else isPrevNull = true
            }
            if (isThereData) {
                Vue.set(state.tagsData, newTag.id, {id, data: newTag.data, minMaxData: {minValue, maxValue}})
                console.log(state.tagsData)
            }
        },
        clearTagsData(state){
            state.tagsData.splice(0)
        },
        setDevicesAndTags(state, {data}){
            state.devices = data
        },
        setCurrentConfig(state, config){
            state.currentConfig = JSON.parse(JSON.stringify(config))
        },
        clearCurrentConfig(state){
            state.currentConfig = JSON.parse(JSON.stringify(state.EMPTY_CONFIG))
        },
        removeConfig(state, id){
            state.configs.splice(state.configs.findIndex(config => config.id===id), 1)
            if(state.currentConfig.id === id)
                state.currentConfig.id = state.EMPTY_CONFIG.id
        },
        pushTagByChartId(state, {id, tagId}){
            state.currentConfig.charts.find(chart => chart.id === id).tags.push(tagId)
        },
        removeTagByChartId(state, {id, tagId}){
            const tags = state.currentConfig.charts.find(chart => chart.id === id).tags
            tags.splice(tags.indexOf(tagId), 1)
        },
        saveCurrentConfig(state) {
            const newConf = JSON.parse(JSON.stringify(state.currentConfig))
            let confIndex = state.configs.findIndex(c => c.id === state.currentConfig.id)
            if (confIndex > -1) {
                let conf = state.configs[confIndex]
                conf.name = newConf.name
                conf.charts.forEach((c, i) => {
                    c.name = newConf.charts[i].name
                    c.tags.splice(0)
                    c.tags = [...newConf.charts[i].tags]
                })
            }
            else {
                state.configs.push(newConf)
                let i = 0
                while (state.configs.find(c => c.id === i))
                    i++
                state.currentConfig.id = i
                newConf.id = i
            }
        },
        setConfigs(state, configs){
            state.configs = configs
        }

    },
    getters: {
        getTagType: state => tagId =>{
          for (const device of state.devices){
              const tag = device.tags.find(tag => tag.id === tagId)
              if(tag !== undefined) return tag.type
          }
        },
        setOfCurrentSelectedTags: state => {
            let uniqueTagsId = new Set()
            state.currentConfig.charts.forEach(chart => chart.tags.forEach(tag => uniqueTagsId.add(tag)))
            return uniqueTagsId
        },
        compareConfigWithEmpty: (state, getters) => {
          return getters.compareConfigWithCurrent(state.EMPTY_CONFIG)
        },
        compareConfigByIdWithCurrent: (state, getters) => id => {
            return getters.compareConfigWithCurrent(state.configs.find(c => c.id === id))
        },
        compareConfigWithCurrent: state => conf => {
            const curr = JSON.parse(JSON.stringify(state.currentConfig))
            conf = JSON.parse(JSON.stringify(conf))
            return conf.id === curr.id && conf.name === curr.name && conf.charts.length === curr.charts.length
                    && (conf.charts.sort().every((chart, i) => {
                        const currChart = curr.charts.sort()[i]
                        return chart.name === currChart.name && chart.tags.length === currChart.tags.length
                            && chart.tags.sort().every((tagId, j) => tagId === currChart.tags.sort()[j])
                    }))
        },
        containsTagByChartId: (state, getters) => (id, tagId) =>{
            return getters.currentConfigTagsById(id).indexOf(tagId) >= 0
        },
        currentConfigTagsById: state => id =>{
            return state.currentConfig.charts.find(chart => chart.id === id).tags
        },
        currentConfig: state => {
            return state.currentConfig
        },
        configurations: state =>{
            return state.configs
        },
        tooltipCurrentDate: state => {
            return state.chart.tooltipInfo.currentDate
        },
        doTooltipShow: state => {
            return state.chart.tooltipInfo.showTooltip
        },
        d3Zoom: state => {
            return state.chart.zoom
        },
        tooltipCoordinates: state => {
          return state.chart.tooltipInfo.coordinates
        },
        tooltipLineDates: state => {
            return state.chart.tooltipInfo.dates
        },
        devices: state => searchStr =>{
            return state.devices.flatMap(device => {
                let newDevice = {
                    id: device.id,
                    description: device.description,
                    tags: device.tags.filter(tag => (String(tag.id) + ' ' + tag.description).toLowerCase().includes(searchStr.toLowerCase()))
                }
                return newDevice.tags.length ? [newDevice] : []
            })
        },
        loadedTags: state => {
            let tags = []
            console.log(state.tagsData)
            state.tagsData.forEach(tag => tags.push(tag.id))
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
        tagDataById: state => id => {
            return state.tagsData[id].data
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
            let color
            if (currentNumber < state.chart.colorScheme.length)
                do {
                    color = state.chart.colorScheme[index]
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
