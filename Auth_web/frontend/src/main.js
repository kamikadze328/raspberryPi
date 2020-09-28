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
        data: []
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