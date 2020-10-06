import Vue from 'vue'
import App from './App.vue'
import router from './router'
import axios from 'axios';
import VueCookie from 'vue-cookies'

Vue.prototype.$axios = axios
Vue.config.productionTip = false;
Vue.prototype.$mydata = {
    URL: {
        auth: '/api/auth_utils/auth.php',
        changePassword: '/api/change_users/password_change.php',
        admin: '/api/admin_utils/admin.php',
    },
    data: {
        stats: [/*
            {
                "device": 0,
                "username": "123",
                "ip": '83.102.0.0',
                "stats": [{
                    "start_time": 1601211313,
                    "duration_sec": 1005,
                    "url_name": "graphs",
                    "url_path": "/graphs/"
                }, {
                    "start_time": 1601211584,
                    "duration_sec": 0,
                    "url_name": "unp",
                    "url_path": "/unp/"
                }, {
                    "start_time": 1601222396,
                    "duration_sec": 38,
                    "url_name": "admin",
                    "url_path": "/admin"
                }, {
                    "start_time": 1601223118,
                    "duration_sec": 3681,
                    "url_name": "admin",
                    "url_path": "/admin"
                }, {
                    "start_time": 1601226329,
                    "duration_sec": 0,
                    "url_name": "graphs",
                    "url_path": "/graphs/"
                }, {
                    "start_time": 1601226379,
                    "duration_sec": 7,
                    "url_name": "main",
                    "url_path": "/"
                }, {
                    "start_time": 1601226551,
                    "duration_sec": 90,
                    "url_name": "syncdata",
                    "url_path": "/syncdata/"
                }, {
                    "start_time": 1601307562,
                    "duration_sec": 8,
                    "url_name": "admin",
                    "url_path": "/admin"
                }, {
                    "start_time": 1601326955,
                    "duration_sec": 467,
                    "url_name": "admin",
                    "url_path": "/admin"
                }, {
                    "start_time": 1601360334,
                    "duration_sec": 0,
                    "url_name": "syncdata",
                    "url_path": "/syncdata/"
                }]
            },
            {
                "device": 12,
                "username": "123",
                "stats": [{
                    "start_time": 1601211441,
                    "duration_sec": 0,
                    "url_name": "main",
                    "url_path": "/"
                }, {"start_time": 1601211445, "duration_sec": 550, "url_name": "unp", "url_path": "/unp/"}]
            },
            {
                "device": 0,
                "username": "anonim",
                "stats": [{
                    "start_time": 1601220158,
                    "duration_sec": 0,
                    "url_name": "info_dev_err",
                    "url_path": "/unp/info_dev_err.html"
                }, {
                    "start_time": 1601220179,
                    "duration_sec": 51,
                    "url_name": "info_tag",
                    "url_path": "/unp/info_teg.html"
                }]
            },
            {
                "device": 0,
                "username": "anonim",
                "stats": [{
                    "start_time": 1601221548,
                    "duration_sec": 577,
                    "url_name": "info_tag",
                    "url_path": "/unp/info_teg.html"
                }]
            },
            {
                "device": 0,
                "username": "anonim",
                "stats": [{
                    "start_time": 1601222134,
                    "duration_sec": 234,
                    "url_name": "info_dev_err",
                    "url_path": "/unp/info_dev_err.html"
                }]
            },
            {
                "device": 0,
                "username": "123",
                "stats": [{
                    "start_time": 1601222376,
                    "duration_sec": 0,
                    "url_name": "main",
                    "url_path": "/"
                }, {"start_time": 1601222384, "duration_sec": 9, "url_name": "admin", "url_path": "/admin"}]
            },
            {
                "device": 0,
                "username": "123",
                "stats": [{
                    "start_time": 1601223052,
                    "duration_sec": 13,
                    "url_name": "admin",
                    "url_path": "/admin"
                }]
            },
            {
                "device": 12,
                "username": "anonim",
                "stats": [{"start_time": 1601239895, "duration_sec": 0, "url_name": "main", "url_path": "/"}]
            },
            {
                "device": 0,
                "username": "123",
                "stats": [{
                    "start_time": 1601277586,
                    "duration_sec": 0,
                    "url_name": "main",
                    "url_path": "/"
                }, {"start_time": 1601277590, "duration_sec": 0, "url_name": "admin", "url_path": "/admin"}]
            },
            {
                "device": 0,
                "username": "123",
                "stats": [{
                    "start_time": 1601307476,
                    "duration_sec": 0,
                    "url_name": "main",
                    "url_path": "/"
                }, {"start_time": 1601307481, "duration_sec": 3, "url_name": "admin", "url_path": "/admin"}]
            },
            {
                "device": 0,
                "username": "123",
                "stats": [{
                    "start_time": 1601309307,
                    "duration_sec": 0,
                    "url_name": "admin",
                    "url_path": "/admin"
                }]
            },
            {
                "device": 0,
                "username": "123",
                "stats": [{
                    "start_time": 1601311811,
                    "duration_sec": 0,
                    "url_name": "graphs",
                    "url_path": "/graphs/"
                }]
            },
            {
                "device": 21,
                "username": "123",
                "stats": [{
                    "start_time": 1601312901,
                    "duration_sec": 0,
                    "url_name": "admin",
                    "url_path": "/admin"
                }]
            },
            {
                "device": 0,
                "username": "123",
                "stats": [{"start_time": 1601326929, "duration_sec": 19, "url_name": "main", "url_path": "/"}]
            },
            {
                "device": 0,
                "username": "anonim",
                "stats": [{"start_time": 1601342259, "duration_sec": 0, "url_name": "main", "url_path": "/"}]
            },
            {
                "device": 12,
                "username": "anonim",
                "stats": [{"start_time": 1601345636, "duration_sec": 0, "url_name": "main", "url_path": "/"}]
            },
            {
                "device": 22,
                "username": "123",
                "stats": [{
                    "start_time": 1601360120,
                    "duration_sec": 148,
                    "url_name": "syncdata",
                    "url_path": "/syncdata/"
                }]
            },
            {
                "device": 11,
                "username": "123",
                "stats": [{
                    "start_time": 1601362978,
                    "duration_sec": 0,
                    "url_name": "admin",
                    "url_path": "/admin"
                }]
            }*/
        ],
        users: []
    },
    dateMainMax: {
        date_min: new Date(new Date - 86400000).setHours(0, 0, 0, 0),
        date_max: new Date().setHours(0, 0, 0, 0)
    },
    cookieName: 'user_meta',
    LOCAL_AUTH_CODES: {
        LOGIN: 0,
        CHANGING_PASSWORD: 1,
        CREATE_USER: 2,
        DELETE_USER: 3,
        RESET_PASSWORD_USER: 4
    },
    isAuth() {
        return VueCookie.isKey(this.cookieName)
    },
    currentName() {
        return VueCookie.get(this.cookieName).name
    },
    secToHms(secNum){
        let hours = Math.floor(secNum / 3600);
        let minutes = Math.floor((secNum - (hours * 3600)) / 60);
        let seconds = secNum - (hours * 3600) - (minutes * 60);

        if (hours > 0)
            if (hours < 10)
                hours = "0" + hours;
        hours = (hours > 0) ? (hours + ':') : ""

        if (minutes < 10)
            minutes = "0" + minutes;
        minutes += ':'

        if (seconds < 10)
            seconds = "0" + seconds;

        return hours + minutes + seconds;
    },
    secToDate(sec) {
        return this.dateToLocaleStr(sec * 1000)
    },
    dateToLocaleStr(date) {
        const options = {
            year: '2-digit',
            month: '2-digit',
            day: '2-digit',
            timezone: 'UTC',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        }
        return new Date(date).toLocaleString("ru", options)
    },
}

Vue.use(VueCookie)

new Vue({
    render: h => h(App),
    router,
}).$mount('#app');