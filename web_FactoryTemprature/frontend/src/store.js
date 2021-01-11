import {createStore} from 'vuex';

const COLORS = {
    RED: 'rgba(255, 91, 87, 1)',
    RED_LIGHT: 'rgba(255, 91, 87, .4)',

    BLUE: 'rgba(52, 143, 226, 1)',
    BLUE_LIGHT: 'rgba(52, 143, 226, .4)',

    GREEN: 'rgba(50, 169, 50, 1)',
    GREEN_LIGHT: 'rgba(50, 169, 50, .4)',
}
const DURATIONS = {
    DAY: {
        value: 1,
        duration_ms: 86400000,
        name: 'День'
    },
    WEEK: {
        value: 2,
        duration_ms: 604800000,
        name: 'Неделя'
    },
    MONTH: {
        value: 3,
        duration_ms: 2592000000,
        name: 'Месяц'
    },
    YEAR: {
        value: 4,
        duration_ms: 31622400000,
        name: 'Год'
    },
};
export const store = createStore({
    state: {
        DURATIONS,
        currentDuration: DURATIONS.DAY,
        SERVER: {
            URL: {
                DATA: '/api/data/data.php',
            },
            PURPOSE: {
                GET_DATA: 'data',
                GET_DATA_DYN: 'data_dyn',
                GET_AVG_DATA: 'data_avg'
            }
        },
        dates: {
            min: new Date(),
            max: new Date(),
        },
        isOnlyHighValues: false,
        configs: [
            {
                id: 210000,
                name: 'Улица',
                tags: [210101],
                color: COLORS.RED,
                backgroundColor: COLORS.RED_LIGHT,
                isHighValue: false
            },
            {
                id: 211000,
                name: 'ЦЕХ',
                tags: [211101, 211102, 211103, 211104, 211105],
                color: COLORS.GREEN,
                backgroundColor: COLORS.GREEN_LIGHT,
                isHighValue: false
            },
            {
                id: 212000,
                name: 'АБК',
                tags: [212104, 212105, 212106],
                color: COLORS.BLUE,
                backgroundColor: COLORS.BLUE_LIGHT,
                isHighValue: false
            },
            {
                id: 213000,
                name: 'Отопление ЦЕХ',
                tags: [210102, 210103],
                color: COLORS.RED,
                backgroundColor: COLORS.RED_LIGHT,
                isHighValue: true
            },
            {
                id: 214000,
                name: 'Отопление АБК',
                tags: [1, 0],
                color: COLORS.GREEN,
                backgroundColor: COLORS.GREEN_LIGHT,
                isHighValue: true
            }
        ],
        temperaturesAvg: [],
        temperaturesDyn: [],

    },
    getters: {
        currentDuration: (state) => {
            return state.currentDuration
        },
        duration: (state) => (value) => {
            value = Number(value)
            for (const i in Object.keys(state.DURATIONS)) {
                const d = state.DURATIONS[Object.keys(state.DURATIONS)[i]]
                if (d.value === value)
                    return d
            }
            return undefined
        },
        updateInterval: (state, getters) => {
            const curr = getters.currentDuration
            if (curr === state.DURATIONS.DAY) return 60000
            if (curr === state.DURATIONS.WEEK) return 600000
            if (curr === state.DURATIONS.MONTH) return 1800000
            if (curr === state.DURATIONS.YEAR) return 21600000
        },
        minDate: (state, getters) => {
            return getters.dates().min
        },
        maxDate: (state, getters) => {
            return getters.dates().max
        },
        DATA_MAX_LENGTH_GAP: () => (minDate, maxDate) => {
            const WEEK = 604800,
                MONTH = 2592000,
                THREE_MONTHS = 7776000,
                diff = Math.floor(maxDate / 1000) - Math.floor(minDate / 1000);
            return (diff < WEEK) ? 2 * 60000 //ms
                : ((diff < MONTH) ? 10 * 2 * 60000
                    : ((diff < THREE_MONTHS) ? 30 * 2 * 60000
                        : 360 * 2 * 60000))
        },
        DATA_MAX_LENGTH_GAP_CURRENT: (state, getters) => {
            return getters.DATA_MAX_LENGTH_GAP(getters.minDate, getters.maxDate)
        },
        temperaturesAvg: (state) => {
            return state.temperaturesAvg
        },
        temperaturesDyn: (state) => {
            return state.temperaturesDyn
        },
        configs: (state) => {
            return state.configs
        },
        config: (state, getters) => (configId) => {
            configId = Number(configId)
            return getters.configs.find(c => c.id === configId)
        },
        currentConfigs: (state, getters) => {
            return getters.configs.filter(c => getters.isOnlyHighValues === c.isHighValue)
        },
        isOnlyHighValues: (state) => {
            return state.isOnlyHighValues
        },
        temperatureAvgById: (state, getters) => (id) => {
            id = Number(id)
            return (getters.temperaturesAvg.length >= id) ?
                getters.temperaturesAvg[id] :
                undefined
        },
        temperatureDynById: (state, getters) => (id) => {
            id = Number(id)
            return (getters.temperaturesDyn.length >= id) ?
                getters.temperaturesDyn[id] :
                undefined
        },
        temperatureAvgDataById: (state, getters) => (id) => {
            const tag = getters.temperatureAvgById(id)
            return tag ? tag.data : undefined
        },
        dynDataById: (state, getters) => (tagId) => {
            const data = getters.temperatureDynById(tagId)
            return data === undefined ? undefined : data.value
        },
        temperatureAvgMinMaxById: (state, getters) => (id) => {
            const tag = getters.temperatureAvgById(id)
            return tag ? {min: tag.min, max: tag.max} : undefined
        },
        temperatureAvgLastById: (state, getters) => (id) => {
            const tag = getters.temperatureAvgById(id)
            return tag ? tag.data[tag.data.length - 1] : undefined
        },
        currentAvgValue: (state, getters) => (configId) => {
            const config = getters.config(configId)
            if (config !== undefined) {
                let sum = 0, count = 0
                config.tags.forEach(tag => {
                    const value = getters.dynDataById(tag)
                    const isUndefined = value === undefined
                    sum += isUndefined ? 0 : value
                    count += Number(!isUndefined)
                })
                return count > 0 ? sum / count : undefined
            }
            return undefined
        },
        currentLastDynDate: (state, getters) => {
            for (const d of getters.temperaturesDyn)
                if (d) return d.date
            return undefined
        },
        configColor: (state, getters) => (id) => {
            const tag = getters.configs(id)
            return tag ? tag.color : undefined
        },
        allTags: (state, getters) => {
            let tags = []
            getters.configs.forEach(device => tags = [...device.tags, ...tags])
            return tags.sort()
        },
        currentTags: (state, getters) => {
            let tags = []
            getters.currentConfigs.forEach(device => tags = [...device.tags, ...tags])
            return tags.sort()
        },
        temperatureAvgMinMax: (state, getters) => {
            let max = -Infinity, min = +Infinity
            getters.currentConfigs.forEach(config => {
                const minMax = getters.temperatureAvgMinMaxById(config.id)
                if (minMax) {
                    if (minMax.min < min) min = minMax.min
                    if (minMax.max > max) max = minMax.max
                }
            })
            return {min, max}
        },
        temperatureAvgValByIdAndDate: (state, getters) => (id, date) =>{
            const data = getters.temperatureAvgDataById(id)
            if(data && data.length) {
                let value
                for (let i = 0; i < data.length; i++) {
                    if (date < data[i].date) {
                        value = data[i].value === undefined ? null : data[i].value
                        break
                    }
                }
                if(value === undefined || date < data[0].date) {
                    value = null
                }
                return value
            } return undefined
        },
        clearConfigs: (state, getters) => {
            let configs = []
            getters.configs.forEach(c => configs.push({id: c.id, tags: c.tags}))
            return configs
        },
        configByTagId: (state, getters) => (tagId) => {
            return getters.configs.find(c => c.tags.includes(tagId))
        },
        dates: (state, getters) => {
            return () => {
                const max = new Date
                const min = new Date(max - getters.currentDuration.duration_ms)
                return {min, max}
            }
        },
    },
    mutations: {
        updateCurrentDuration(state, {duration}) {
            state.currentDuration = duration
        },
        updateDates(state, {min, max}) {
            state.dates.min = new Date(min)
            state.dates.max = new Date(max)
        },
        updateIsOnlyHighValues(state, {newVal}) {
            if (state.isOnlyHighValues !== newVal)
                state.isOnlyHighValues = newVal
        },
        setData(state, {id, data, min, max}) {
            state.temperaturesAvg[id] = {data, min, max}
        },
        addData(state, {id, date, value}) {
            state.temperaturesAvg[id].data.shift()
            state.temperaturesAvg[id].data.push({date, value})
            if (value > state.temperaturesAvg[id].max) state.temperaturesAvg[id].max = value
            if (value < state.temperaturesAvg[id].min) state.temperaturesAvg[id].min = value
        },
        setDynData(state, {id, value, date}) {
            state.temperaturesDyn[id] = {date, value}
        },
    },
    actions: {
        initData: ({commit, getters}, {newData}) => {
            for (const tag of newData) {
                let minValue = +Infinity,
                    maxValue = -Infinity,
                    isPrevNull = true,
                    dataLength = tag.data.length,
                    data = [],
                    i = 0,
                    prevDate = 0

                while (i < dataLength) {
                    const d = tag.data[i]
                    const isCurrNull = d.value === null
                    const value = isCurrNull ? undefined : d.value
                    const date = new Date(d.date)

                    if (value > maxValue) maxValue = value
                    if (value < minValue) minValue = value

                    if (!isPrevNull && !isCurrNull && date - prevDate > getters.DATA_MAX_LENGTH_GAP_CURRENT) {
                        data.push({date: new Date(date.getTime() - 1), value: undefined})
                        isPrevNull = true
                    } else isPrevNull = isCurrNull

                    data.push({date, value})

                    prevDate = date
                    i++
                }
                commit('setData', {id: tag.id, data, min: minValue, max: maxValue})
            }
        },
        initDynData: ({commit, getters}, {newData}) => {
            for (const tag of newData) {
                const date = new Date(tag.date), id = tag.id,
                value = tag.value === null ? undefined : ((new Date() - date < getters.updateInterval && getters.currentLastDynDate && getters.currentLastDynDate.getTime() === date.getTime()) ? tag.value : undefined)
                commit('setDynData', {id, value, date})

            }
        },
        tryUpdateData: ({commit, getters}) => {
            const date = getters.currentLastDynDate
            for (const config of getters.configs) {
                const dynData = getters.temperatureAvgLastById(config.id)
                if (dynData && date - dynData.date > getters.updateInterval)
                    commit('addData', {id: config.id, value: getters.currentAvgValue(config.id), date})
            }
        },
        updateCurrentDuration: ({commit, getters}, {value}) => {
            const duration = getters.duration(value)
            if (duration)
                commit('updateCurrentDuration', {duration})
        },
    }
})