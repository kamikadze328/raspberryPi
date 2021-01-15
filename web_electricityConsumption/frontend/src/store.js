import {createStore} from 'vuex';
import {tagGroups, places, groupsAndPlaces} from './tagsAndPlaces.js';

const MONTH_NUM_MIN = 6
const MONTH_NUM_INITED = 6
export const store = createStore({
    state: {
        MONTH: ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'],
        tagGroups,
        places,
        groupsAndPlaces,
        currentMonthData: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38, 39, 40, 41, 42, 43],
        data: [
            {
                'ЦЕХ': 102,
                'ЦЕХ Свет': 102,
                'АБК': 102,
                'УНП': 102,
                date: new Date(2021, 0),
            },
            {
            'ЦЕХ': 110,
            'ЦЕХ Свет': 100,
            'АБК': 100,
            'УНП': 100,
            date: new Date(2020, 11),
        }, {
            'ЦЕХ': 100,
            'ЦЕХ Свет': 100,
            'АБК': 100,
            'УНП': 100,
            date: new Date(2020, 10),
        }, {
            'ЦЕХ': 90,
            'ЦЕХ Свет': 100,
            'АБК': 100,
            'УНП': 100,
            date: new Date(2020, 9),
        }, {
            'ЦЕХ': 80,
            'ЦЕХ Свет': 100,
            'АБК': 100,
            'УНП': 100,
            date: new Date(2020, 8),
        }, {
            'ЦЕХ': 70,
            'ЦЕХ Свет': 100,
            'АБК': 100,
            'УНП': 100,
            date: new Date(2020, 7),
        }, {
            'ЦЕХ': 60,
            'ЦЕХ Свет': 100,
            'АБК': 100,
            'УНП': 100,
            date: new Date(2020, 6),
        },{
            'ЦЕХ': 50,
            'ЦЕХ Свет': 100,
            'АБК': 100,
            'УНП': 100,
            date: new Date(2020, 5),
        }, {
            'ЦЕХ': 40,
            'ЦЕХ Свет': 100,
            'АБК': 100,
            'УНП': 100,
            date: new Date(2020, 4),
        }, {
            'ЦЕХ': 30,
            'ЦЕХ Свет': 100,
            'АБК': 100,
            'УНП': 100,
            date: new Date(2020, 3),
        }, {
            'ЦЕХ': 20,
            'ЦЕХ Свет': 100,
            'АБК': 100,
            'УНП': 100,
            date: new Date(2020, 2),
        }, {
            'ЦЕХ': 10,
            'ЦЕХ Свет': 100,
            'АБК': 100,
            'УНП': 100,
            date: new Date(2020, 1),
        }, {
            'ЦЕХ': 0,
            'ЦЕХ Свет': 100,
            'АБК': 100,
            'УНП': 100,
            date: new Date(2020, 0),
        },
            {
                'ЦЕХ': 100,
                'ЦЕХ Свет': 110,
                'АБК': 100,
                'УНП': 100,
                date: new Date(2020, -1),
            }, {
                'ЦЕХ': 100,
                'ЦЕХ Свет': 100,
                'АБК': 100,
                'УНП': 100,
                date: new Date(2020, -2),
            }, {
                'ЦЕХ': 100,
                'ЦЕХ Свет': 90,
                'АБК': 100,
                'УНП': 100,
                date: new Date(2020, -3),
            }, {
                'ЦЕХ': 100,
                'ЦЕХ Свет': 80,
                'АБК': 100,
                'УНП': 100,
                date: new Date(2020, -4),
            }, {
                'ЦЕХ': 100,
                'ЦЕХ Свет': 70,
                'АБК': 100,
                'УНП': 100,
                date: new Date(2020, -5),
            }, {
                'ЦЕХ': 100,
                'ЦЕХ Свет': 60,
                'АБК': 100,
                'УНП': 100,
                date: new Date(2020, -6),
            },{
                'ЦЕХ': 100,
                'ЦЕХ Свет': 50,
                'АБК': 100,
                'УНП': 100,
                date: new Date(2020, -7),
            }, {
                'ЦЕХ': 100,
                'ЦЕХ Свет': 40,
                'АБК': 100,
                'УНП': 100,
                date: new Date(2020, -8),
            }, {
                'ЦЕХ': 100,
                'ЦЕХ Свет': 30,
                'АБК': 100,
                'УНП': 100,
                date: new Date(2020, -9),
            }, {
                'ЦЕХ': 100,
                'ЦЕХ Свет': 20,
                'АБК': 100,
                'УНП': 100,
                date: new Date(2020, -10),
            }, {
                'ЦЕХ': 100,
                'ЦЕХ Свет': 10,
                'АБК': 100,
                'УНП': 100,
                date: new Date(2020, -11),
            }, {
                'ЦЕХ': 100,
                'ЦЕХ Свет': 0,
                'АБК': 100,
                'УНП': 100,
                date: new Date(2020, -12),
            }],
        monthCurrId: new Date().getMonth(),
        yearCurr: new Date().getFullYear(),
        MONTH_NUM_MIN,
        MONTH_NUM_INITED,
        MONTH_NUM_STEP: 6,
        month_num: MONTH_NUM_INITED,
    },
    getters: {
        currentData: state => {
            return state.currentMonthData
        },
        sumData: state => {
            return state.data
        },
        currentDataById: (state, getters) => tagId => {
            return getters.currentData[tagId]
        },
        tagIdByGroupPlaceIDs: (state) => (groupId, placeId) => {
            const o = state.groupsAndPlaces.find(o => o.group === groupId && o.place === placeId)
            return o === undefined ? o : o.tag
        },
        currentDataByGroupPlaceIDs: (state, getters) => (groupId, placeId) => {
            return getters.currentDataById(getters.tagIdByGroupPlaceIDs(groupId, placeId))
        },
        sumByPlace: (state, getters) => (placeId, date) => {
            const place = state.places.find(place => place.id === placeId)
            if (place) {
                const d = getters.sumData.find(d => d.date.getTime() === date.getTime())
                return d === undefined ? d : d[place.name]
            } else return undefined
        },
        sumAllPlacesByDate: (state, getters) => (date) => {
            let sum = 0
            getters.places.forEach(p => {
                const sumByPlace = getters.sumByPlace(p.id, date)
                if (sumByPlace)
                    sum += sumByPlace
            })
            return sum
        },
        sumCurrByPlace: (state, getters) => (placeId) => {
            return getters.sumByPlace(placeId, getters.monthCurrDate)
        },
        sumByGroup: (state, getters) => (groupId) => {
            let sum = 0
            state.groupsAndPlaces.forEach(d => {
                if (d.group === groupId) {
                    const value = getters.currentDataById(d.tag)
                    sum += value ? value : 0
                }
            })
            return sum
        },
        tagGroups: state => {
            return state.tagGroups
        },
        places: state => {
            return state.places
        },
        month: (state) => monthId => {
            return state.MONTH[monthId]
        },
        monthDate: (state) => monthAgo => {
            return new Date(state.yearCurr, state.monthCurrId - monthAgo)
        },
        monthCurrDate: (state) => {
            return new Date(state.yearCurr, state.monthCurrId)
        },
        monthCurrName: (state, getters) => {
            return getters.month(state.monthCurrId)
        },
        monthCurrDateStr: (state, getters) => {
            return state.yearCurr + ' ' + getters.monthCurrName
        },
        monthCurrDateStrByDate: (state, getters) => (date) => {
            return date.getFullYear() + ' ' + getters.month(date.getMonth())
        },
        isPossibleToToResetMonthNum: state => {
            return state.month_num !== state.MONTH_NUM_INITED
        },
        months: (state, getters) => {
            const months = []
            for (let i = 0; i < state.month_num; i++) {
                months.push(getters.monthDate(i))
            }
            return months
        },
    },
    mutations: {
        increaseMonthNum(state) {
            state.month_num += state.MONTH_NUM_STEP
        },
        resetMonthNum(state) {
            state.month_num = state.MONTH_NUM_INITED
        }
    },
    actions: {},
})