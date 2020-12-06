import {createStore} from 'vuex';

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
        ]
    },
    getters:{
        month: state => id => {
            console.log(id)
            id = Number(id)
            while(id < 0) id +=12
            console.log(state.MONTH[Number(id)])
            return state.MONTH[Number(id)]
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
        yearByMonthId: (state, getters) => id =>{
            id = Number(id)
            let count = 0
            while (id < 0) {
                id +=12
                count++
            }
            return getters.currentYear - count
        }
    },
    mutations:{

    },
    actions:{

    },
})