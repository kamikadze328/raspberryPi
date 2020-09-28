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
        createUser: '/api/change_users/create.php',
        getStats: '/api/admin_utils/statistics/get_stats.php',
    },
    stats: {
        data: [{
            "device": 0,
            "username": "123",
            "stats": [{
                "start_time": 1601211313,
                "duration_sec": 1005,
                "url_name": "graphs",
                "url_path": "carbon-dv.ru/graphs/"
            }, {
                "start_time": 1601211584,
                "duration_sec": 0,
                "url_name": "unp",
                "url_path": "carbon-dv.ru/unp/"
            }, {
                "start_time": 1601222396,
                "duration_sec": 38,
                "url_name": "admin",
                "url_path": "carbon-dv.ru/admin"
            }, {
                "start_time": 1601223118,
                "duration_sec": 3681,
                "url_name": "admin",
                "url_path": "carbon-dv.ru/admin"
            }, {
                "start_time": 1601226329,
                "duration_sec": 0,
                "url_name": "graphs",
                "url_path": "carbon-dv.ru/graphs/"
            }, {
                "start_time": 1601226379,
                "duration_sec": 7,
                "url_name": "main",
                "url_path": "carbon-dv.ru/"
            }, {
                "start_time": 1601226551,
                "duration_sec": 90,
                "url_name": "syncdata",
                "url_path": "carbon-dv.ru/syncdata/"
            }, {"start_time": 1601307562, "duration_sec": 8, "url_name": "admin", "url_path": "carbon-dv.ru/admin"}]
        }, {
            "device": 12,
            "username": "123",
            "stats": [{
                "start_time": 1601211441,
                "duration_sec": 0,
                "url_name": "main",
                "url_path": "carbon-dv.ru/"
            }, {"start_time": 1601211445, "duration_sec": 550, "url_name": "unp", "url_path": "carbon-dv.ru/unp/"}]
        }, {
            "device": 0,
            "username": "anonim",
            "stats": [{
                "start_time": 1601220158,
                "duration_sec": 0,
                "url_name": "info_dev_err",
                "url_path": "carbon-dv.ru/unp/info_dev_err.html"
            }, {
                "start_time": 1601220179,
                "duration_sec": 51,
                "url_name": "info_tag",
                "url_path": "carbon-dv.ru/unp/info_teg.html"
            }]
        }, {
            "device": 0,
            "username": "anonim",
            "stats": [{
                "start_time": 1601221548,
                "duration_sec": 577,
                "url_name": "info_tag",
                "url_path": "carbon-dv.ru/unp/info_teg.html"
            }]
        }, {
            "device": 0,
            "username": "anonim",
            "stats": [{
                "start_time": 1601222134,
                "duration_sec": 234,
                "url_name": "info_dev_err",
                "url_path": "carbon-dv.ru/unp/info_dev_err.html"
            }]
        }, {
            "device": 0,
            "username": "123",
            "stats": [{
                "start_time": 1601222376,
                "duration_sec": 0,
                "url_name": "main",
                "url_path": "carbon-dv.ru/"
            }, {"start_time": 1601222384, "duration_sec": 9, "url_name": "admin", "url_path": "carbon-dv.ru/admin"}]
        }, {
            "device": 0,
            "username": "123",
            "stats": [{
                "start_time": 1601223052,
                "duration_sec": 13,
                "url_name": "admin",
                "url_path": "carbon-dv.ru/admin"
            }]
        }, {
            "device": 12,
            "username": "Maxim_admin_Vietnam",
            "stats": [{"start_time": 1601239895, "duration_sec": 0, "url_name": "main", "url_path": "carbon-dv.ru/"}]
        }, {
            "device": 0,
            "username": "123",
            "stats": [{
                "start_time": 1601277586,
                "duration_sec": 0,
                "url_name": "main",
                "url_path": "carbon-dv.ru/"
            }, {
                "start_time": 1601277590,
                "duration_sec": 0,
                "url_name": "admin",
                "url_path": "carbon-dv.ru/admin"
            }]
        }]
    },
    dateMainMax: {
        date_min: new Date(new Date - 86400000).setHours(0, 0, 0, 0),
        date_max: new Date().setHours(0, 0, 0, 0)
    },
    cookieName: 'user_meta',
    LOCAL_AUTH_CODES: {
        LOGIN: 0,
        CHANGING_PASSWORD: 1,
        REGISTER: 2,
    },
    isAuth: function () {
        return VueCookie.isKey(this.cookieName)
    },
    currentName: function () {
        return VueCookie.get(this.cookieName).name
    }
};

Vue.use(VueCookie)

new Vue({
    render: h => h(App),
    router,
}).$mount('#app');