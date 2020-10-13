import Vue from 'vue'
import App from './App.vue'
import router from './router'
import axios from 'axios';
import VueCookie from 'vue-cookies'

Vue.prototype.$axios = axios
Vue.config.productionTip = false;
Vue.prototype.$mydata = {
    server: {
        URL: {
            auth: '/api/auth_utils/auth.php',
            changePassword: '/api/change_users/password_change.php',
            admin: '/api/admin_utils/admin.php',
        },
        action:{
            CREATE_USER: 'create_usr',
            RESET_USER_PASSWORD: 'reset_usr_passwd',
            DELETE_USER: 'delete_usr',
            LOGIN: 'login',
            CHANGING_PASSWORD: 'change_password',
            CHANGE_USER_ROLE: 'change_user_role'
        }
    },
    LOCAL_AUTH_CODES: {
        LOGIN: 0,
        CHANGING_PASSWORD: 1,
        CREATE_USER: 2,
        DELETE_USER: 3,
        RESET_PASSWORD_USER: 4,
        CHANGE_USER_ROLE: 5
    },
    data: {
        roles: [
            {
                name: 'Admin',
                id: 1,
            },
            {
                name: 'Пользователь',
                id: 0
            }
        ],
        stats: [],
        users: [{
            login: '123',
            id: 3
        }]
    },
    dateMainMax: {
        date_min: new Date(new Date - 86400000).setHours(0, 0, 0, 0),
        date_max: new Date().setHours(0, 0, 0, 0)
    },
    cookieName: 'user_meta',

    isAuth() {
        return VueCookie.isKey(this.cookieName)
    },
    currentName() {
        return VueCookie.get(this.cookieName).name
    },
    isAdmin() {
        return VueCookie.get(this.cookieName).isAdmin
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