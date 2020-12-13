import {createStore} from 'vuex';
import {tagGroups, places, groupsAndPlaces} from './tagsAndPlaces.js';

export const store = createStore({
    state: {
        MONTH: [
            'Январь',
            'Февраль',
            'Март',
            'Апрель',
            'Май',
            'Июнь',
            'Июль',
            'Август',
            'Сентябрь',
            'Октябрь',
            'Ноябрь',
            'Декабрь'
        ],
        tagGroups,
        places,
        groupsAndPlaces,
        currentMonthData: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38, 39, 40, 41, 42, 43],
        data: [{
            place: 0,
            month: 11,
            data: 100
        }, {
            place: 1,
            month: 11,
            data: 100
        }, {
            place: 2,
            month: 11,
            data: 100
        }, {
            place: 3,
            month: 11,
            data: 100
        }, {
            place: 0,
            month: 10,
            data: 200
        }, {
            place: 1,
            month: 10,
            data: 200
        }, {
            place: 2,
            month: 10,
            data: 200
        }, {
            place: 3,
            month: 10,
            data: 200
        }, {
            place: 0,
            month: -1,
            data: 100
        }, {
            place: 1,
            month: -1,
            data: 100
        }, {
            place: 2,
            month: -1,
            data: 100
        }, {
            place: 3,
            month: -1,
            data: 100
        }]
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
        sumByPlace: (state, getters) => (placeId, monthId) => {
            const d = getters.sumData.find(d => d.place === placeId && d.month === monthId)
            return d === undefined ? d : d.data
        },
        sumAllPlacesByMonth: (state, getters) => (monthId) => {
            let sum = 0
            getters.places.forEach(p => {
                const sumByPlace = getters.sumByPlace(p.id, monthId)
                if(sumByPlace)
                    sum += sumByPlace
            })
            return sum
        },
        sumCurrByPlace: (state, getters) => (placeId) => {
            getters.sumByPlace(placeId, getters.currentMonthId)
        },
        tagGroups: state => {
            return state.tagGroups
        },
        places: state => {
            return state.places
        },

        month: (state, getters) => id => {
            return state.MONTH[getters.monthId(id)]
        },
        monthId: () => id => {
            while (id < 0) id += 12
            return id
        },
        currentMonth: (state, getters) => {
            return getters.month(getters.currentMonthId)
        },
        currentMonthId: () => {
            return new Date().getMonth()
        },
        currentYear: () => {
            return new Date().getFullYear()
        },
        yearByMonthId: (state, getters) => id => {
            id = Number(id)
            let count = 0
            while (id < 0) {
                id += 12
                count++
            }
            return getters.currentYear - count
        }
    },
    mutations: {},
    actions: {},
})