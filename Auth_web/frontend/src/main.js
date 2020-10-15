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
            CREATE_USER: 'create_user',
            RESET_USER_PASSWORD: 'reset_user_password',
            DELETE_USER: 'delete_user',
            LOGIN: 'login',
            CHANGING_PASSWORD: 'change_password',
            UPDATE_USER: 'update_user'
        }
    },
    LOCAL_AUTH_CODES: {
        LOGIN: 0,
        CHANGING_PASSWORD: 1,
        CREATE_USER: 2,
        DELETE_USER: 3,
        RESET_PASSWORD_USER: 4,
        UPDATE_USER: 5
    },
    data: {
        roles: [],
        stats: [],
        users: [],
        /*stats: [{"device":0,"username":"123","ip":"83.102.0.0","stats":[{"start_time":1602256423,"duration_sec":0,"url_name":"Главная","url_path":"/"}]},{"device":0,"username":"123","ip":"83.102.0.0","stats":[{"start_time":1602256432,"duration_sec":0,"url_name":"Главная","url_path":"/"}]},{"device":0,"username":"123","ip":"83.102.0.0","stats":[{"start_time":1602258179,"duration_sec":24,"url_name":"Главная","url_path":"/"},{"start_time":1602258184,"duration_sec":0,"url_name":"Syncdata","url_path":"/syncdata/"},{"start_time":1602258245,"duration_sec":4,"url_name":"Панель администратора","url_path":"/admin"}]},{"device":0,"username":"123","ip":"83.102.0.0","stats":[{"start_time":1602259935,"duration_sec":1637,"url_name":"Панель администратора","url_path":"/admin"},{"start_time":1602260430,"duration_sec":157,"url_name":"Главная","url_path":"/"},{"start_time":1602260440,"duration_sec":0,"url_name":"Syncdata","url_path":"/syncdata/"},{"start_time":1602260992,"duration_sec":66,"url_name":"Информация об устройствах","url_path":"/unp/info_teg.html"},{"start_time":1602261570,"duration_sec":0,"url_name":"Главная","url_path":"/"}]},{"device":0,"username":"123","ip":"83.102.0.0","stats":[{"start_time":1602262258,"duration_sec":178,"url_name":"Главная","url_path":"/"},{"start_time":1602262441,"duration_sec":764,"url_name":"Панель администратора","url_path":"/admin"}]},{"device":0,"username":"123","ip":"83.102.0.0","stats":[{"start_time":1602263237,"duration_sec":0,"url_name":"Главная","url_path":"/"},{"start_time":1602263240,"duration_sec":85,"url_name":"Панель администратора","url_path":"/admin"}]},{"device":0,"username":"123","ip":"83.102.0.0","stats":[{"start_time":1602263341,"duration_sec":0,"url_name":"Главная","url_path":"/"},{"start_time":1602263343,"duration_sec":765,"url_name":"Панель администратора","url_path":"/admin"}]},{"device":0,"username":"123","ip":"83.102.0.0","stats":[{"start_time":1602264144,"duration_sec":658,"url_name":"Главная","url_path":"/"},{"start_time":1602264804,"duration_sec":131,"url_name":"Панель администратора","url_path":"/admin"}]},{"device":0,"username":"123","ip":"83.102.0.0","stats":[{"start_time":1602264943,"duration_sec":2,"url_name":"Главная","url_path":"/"}]},{"device":0,"username":"123","ip":"83.102.0.0","stats":[{"start_time":1602265059,"duration_sec":6,"url_name":"Главная","url_path":"/"}]},{"device":0,"username":"123","ip":"83.102.0.0","stats":[{"start_time":1602265109,"duration_sec":0,"url_name":"Главная","url_path":"/"},{"start_time":1602265113,"duration_sec":0,"url_name":"Панель администратора","url_path":"/admin"}]},{"device":0,"username":"123","ip":"83.102.0.0","stats":[{"start_time":1602265139,"duration_sec":461,"url_name":"Главная","url_path":"/"}]},{"device":0,"username":"123","ip":"83.102.0.0","stats":[{"start_time":1602265632,"duration_sec":0,"url_name":"Главная","url_path":"/"}]},{"device":0,"username":"123","ip":"83.102.0.0","stats":[{"start_time":1602265644,"duration_sec":148,"url_name":"Главная","url_path":"/"}]},{"device":0,"username":"123","ip":"83.102.0.0","stats":[{"start_time":1602265810,"duration_sec":250,"url_name":"Главная","url_path":"/"}]},{"device":0,"username":"123","ip":"83.102.0.0","stats":[{"start_time":1602266067,"duration_sec":673,"url_name":"Главная","url_path":"/"},{"start_time":1602266417,"duration_sec":309,"url_name":"Syncdata","url_path":"/syncdata/"}]},{"device":0,"username":"123","ip":"83.102.0.0","stats":[{"start_time":1602266755,"duration_sec":524,"url_name":"Главная","url_path":"/"},{"start_time":1602266759,"duration_sec":228,"url_name":"Графики","url_path":"/graphs/"},{"start_time":1602267237,"duration_sec":0,"url_name":"Syncdata","url_path":"/syncdata/"},{"start_time":1602267266,"duration_sec":0,"url_name":"Панель администратора","url_path":"/admin"}]},{"device":0,"username":"123","ip":"83.102.0.0","stats":[{"start_time":1602267554,"duration_sec":257,"url_name":"Главная","url_path":"/"},{"start_time":1602267597,"duration_sec":0,"url_name":"Syncdata","url_path":"/syncdata/"},{"start_time":1602267635,"duration_sec":24,"url_name":"Информация об устройствах","url_path":"/unp/info_teg.html"},{"start_time":1602267640,"duration_sec":12,"url_name":"Ошибки устройств","url_path":"/unp/info_dev_err.html"}]},{"device":0,"username":"123","ip":"83.102.0.0","stats":[{"start_time":1602267992,"duration_sec":0,"url_name":"Главная","url_path":"/"},{"start_time":1602267993,"duration_sec":0,"url_name":"UNP","url_path":"/unp/"}]},{"device":0,"username":"123","ip":"83.102.0.0","stats":[{"start_time":1602268886,"duration_sec":0,"url_name":"Информация об устройствах","url_path":"/unp/info_teg.html"},{"start_time":1602268906,"duration_sec":0,"url_name":"Главная","url_path":"/"}]},{"device":0,"username":"Edward","ip":"188.170.0.0","stats":[{"start_time":1602311750,"duration_sec":0,"url_name":"Главная","url_path":"/"},{"start_time":1602311752,"duration_sec":28,"url_name":"Панель администратора","url_path":"/admin"}]},{"device":12,"username":"123","ip":"217.66.0.0","stats":[{"start_time":1602313357,"duration_sec":0,"url_name":"Главная","url_path":"/"}]},{"device":12,"username":"123","ip":"217.66.0.0","stats":[{"start_time":1602313370,"duration_sec":23,"url_name":"Главная","url_path":"/"},{"start_time":1602313376,"duration_sec":828,"url_name":"Панель администратора","url_path":"/admin"}]},{"device":0,"username":"123","ip":"83.102.0.0","stats":[{"start_time":1602321976,"duration_sec":0,"url_name":"Главная","url_path":"/"}]},{"device":0,"username":"anonim","ip":"83.102.0.0","stats":[{"start_time":1602321985,"duration_sec":1261,"url_name":"Панель администратора","url_path":"/admin"},{"start_time":1602322608,"duration_sec":0,"url_name":"Главная","url_path":"/"}]},{"device":12,"username":"123","ip":"217.66.0.0","stats":[{"start_time":1602322500,"duration_sec":0,"url_name":"Панель администратора","url_path":"/admin"}]},{"device":12,"username":"123","ip":"217.66.0.0","stats":[{"start_time":1602326771,"duration_sec":134,"url_name":"Панель администратора","url_path":"/admin"}]},{"device":12,"username":"123","ip":"217.66.0.0","stats":[{"start_time":1602330759,"duration_sec":0,"url_name":"Панель администратора","url_path":"/admin"}]},{"device":12,"username":"123","ip":"217.66.0.0","stats":[{"start_time":1602331626,"duration_sec":209,"url_name":"Панель администратора","url_path":"/admin"}]},{"device":12,"username":"123","ip":"83.102.0.0","stats":[{"start_time":1602331657,"duration_sec":442,"url_name":"Панель администратора","url_path":"/admin"}]},{"device":0,"username":"123","ip":"83.102.0.0","stats":[{"start_time":1602334093,"duration_sec":99,"url_name":"Графики","url_path":"/graphs/"},{"start_time":1602334399,"duration_sec":494,"url_name":"Панель администратора","url_path":"/admin"}]},{"device":12,"username":"123","ip":"217.66.0.0","stats":[{"start_time":1602334336,"duration_sec":0,"url_name":"Панель администратора","url_path":"/admin"}]},{"device":12,"username":"123","ip":"217.66.0.0","stats":[{"start_time":1602334336,"duration_sec":788,"url_name":"Панель администратора","url_path":"/admin"},{"start_time":1602335130,"duration_sec":0,"url_name":"Главная","url_path":"/"}]},{"device":12,"username":"123","ip":"217.66.0.0","stats":[{"start_time":1602337451,"duration_sec":0,"url_name":"Главная","url_path":"/"},{"start_time":1602337456,"duration_sec":18,"url_name":"Панель администратора","url_path":"/admin"}]},{"device":12,"username":"Edward","ip":"46.42.0.0","stats":[{"start_time":1602434979,"duration_sec":0,"url_name":"Главная","url_path":"/"},{"start_time":1602434999,"duration_sec":61,"url_name":"Панель администратора","url_path":"/admin"}]},{"device":0,"username":"123","ip":"83.102.0.0","stats":[{"start_time":1602440927,"duration_sec":0,"url_name":"Главная","url_path":"/"},{"start_time":1602440931,"duration_sec":410,"url_name":"Панель администратора","url_path":"/admin"}]},{"device":0,"username":"Edward","ip":"46.42.0.0","stats":[{"start_time":1602442054,"duration_sec":291,"url_name":"Главная","url_path":"/"},{"start_time":1602442059,"duration_sec":949,"url_name":"Панель администратора","url_path":"/admin"},{"start_time":1602443011,"duration_sec":169,"url_name":"Главная","url_path":"/"},{"start_time":1602443178,"duration_sec":0,"url_name":"Графики","url_path":"/graphs/"}]},{"device":0,"username":"123","ip":"83.102.0.0","stats":[{"start_time":1602442531,"duration_sec":3,"url_name":"Панель администратора","url_path":"/admin"}]},{"device":0,"username":"testUser","ip":"46.42.0.0","stats":[{"start_time":1602442571,"duration_sec":22,"url_name":"Главная","url_path":"/"},{"start_time":1602442581,"duration_sec":0,"url_name":"UNP","url_path":"/unp/"},{"start_time":1602442597,"duration_sec":0,"url_name":"Графики","url_path":"/graphs/"}]},{"device":0,"username":"123","ip":"83.102.0.0","stats":[{"start_time":1602443183,"duration_sec":233,"url_name":"Панель администратора","url_path":"/admin"}]},{"device":0,"username":"testUser","ip":"46.42.0.0","stats":[{"start_time":1602443553,"duration_sec":0,"url_name":"Графики","url_path":"/graphs/"}]},{"device":0,"username":"123","ip":"83.102.0.0","stats":[{"start_time":1602444765,"duration_sec":13,"url_name":"Панель администратора","url_path":"/admin"}]},{"device":0,"username":"123","ip":"83.102.0.0","stats":[{"start_time":1602494647,"duration_sec":0,"url_name":"Панель администратора","url_path":"/admin"}]},{"device":0,"username":"123","ip":"83.102.0.0","stats":[{"start_time":1602500372,"duration_sec":332,"url_name":"Панель администратора","url_path":"/admin"}]},{"device":0,"username":"123","ip":"83.102.0.0","stats":[{"start_time":1602505051,"duration_sec":4,"url_name":"Панель администратора","url_path":"/admin"}]},{"device":0,"username":"Edward","ip":"46.42.0.0","stats":[{"start_time":1602527931,"duration_sec":0,"url_name":"Главная","url_path":"/"}]},{"device":0,"username":"Edward","ip":"46.42.0.0","stats":[{"start_time":1602530695,"duration_sec":0,"url_name":"Главная","url_path":"/"}]},{"device":0,"username":"123","ip":"83.102.0.0","stats":[{"start_time":1602571258,"duration_sec":108,"url_name":"Панель администратора","url_path":"/admin"}]},{"device":0,"username":"123","ip":"83.102.0.0","stats":[{"start_time":1602571385,"duration_sec":19,"url_name":"Главная","url_path":"/"}]},{"device":0,"username":"123","ip":"83.102.0.0","stats":[{"start_time":1602571412,"duration_sec":0,"url_name":"Главная","url_path":"/"},{"start_time":1602571416,"duration_sec":481,"url_name":"Панель администратора","url_path":"/admin"}]},{"device":0,"username":"123","ip":"83.102.0.0","stats":[{"start_time":1602572699,"duration_sec":117,"url_name":"Панель администратора","url_path":"/admin"}]},{"device":0,"username":"123","ip":"83.102.0.0","stats":[{"start_time":1602576489,"duration_sec":0,"url_name":"Панель администратора","url_path":"/admin"}]},{"device":0,"username":"123","ip":"83.102.0.0","stats":[{"start_time":1602577161,"duration_sec":303,"url_name":"Панель администратора","url_path":"/admin"}]},{"device":0,"username":"123","ip":"83.102.0.0","stats":[{"start_time":1602578136,"duration_sec":97,"url_name":"Панель администратора","url_path":"/admin"}]},{"device":0,"username":"123","ip":"83.102.0.0","stats":[{"start_time":1602585206,"duration_sec":0,"url_name":"Панель администратора","url_path":"/admin"}]},{"device":0,"username":"123","ip":"83.102.0.0","stats":[{"start_time":1602587070,"duration_sec":0,"url_name":"Панель администратора","url_path":"/admin"}]},{"device":0,"username":"123","ip":"83.102.0.0","stats":[{"start_time":1602587676,"duration_sec":242,"url_name":"Панель администратора","url_path":"/admin"}]},{"device":0,"username":"123","ip":"83.102.0.0","stats":[{"start_time":1602599856,"duration_sec":2,"url_name":"Панель администратора","url_path":"/admin"}]},{"device":0,"username":"123","ip":"83.102.0.0","stats":[{"start_time":1602602703,"duration_sec":80,"url_name":"Панель администратора","url_path":"/admin"}]},{"device":0,"username":"123","ip":"83.102.0.0","stats":[{"start_time":1602603846,"duration_sec":540,"url_name":"Панель администратора","url_path":"/admin"}]},{"device":0,"username":"123","ip":"83.102.0.0","stats":[{"start_time":1602607180,"duration_sec":0,"url_name":"Панель администратора","url_path":"/admin"}]},{"device":0,"username":"123","ip":"83.102.0.0","stats":[{"start_time":1602612337,"duration_sec":1198,"url_name":"Панель администратора","url_path":"/admin"}]},{"device":0,"username":"123","ip":"83.102.0.0","stats":[{"start_time":1602613621,"duration_sec":0,"url_name":"Главная","url_path":"/"},{"start_time":1602613629,"duration_sec":2456,"url_name":"Панель администратора","url_path":"/admin"},{"start_time":1602614253,"duration_sec":0,"url_name":"Главная","url_path":"/"}]},{"device":0,"username":"123","ip":"83.102.0.0","stats":[{"start_time":1602751949,"duration_sec":0,"url_name":"Главная","url_path":"/"},{"start_time":1602751953,"duration_sec":4,"url_name":"Панель администратора","url_path":"/admin"}]}],
        users: [{"id":"4","login":"123","role":"Администратор","description":null,"last_session":"1602756074","count_sessions":"88","average_time_session":"235.5682","count_distinct_places":"2"},{"id":"28","login":"CarbonAdmin","role":"Администратор","description":"ASDFGASDFGASDFGASDFGASDFGASDFGASDFGASDFGASDFGASDFGASDFGASDFGASDFGASDFGASDFGASDFGASDFGASDFGASDFGASDFGASDFGASDFGASDFGASDFGASDFGASDFGASDFGASDFGASDFGASDFGASDFGASDFGASDFGASDFGASDFGASDFGASDFGASDFGASDFGASDFG","last_session":"0","count_sessions":"0","average_time_session":"0.0000","count_distinct_places":"0"},{"id":"6","login":"Edward","role":"Администратор","description":null,"last_session":"1602530695","count_sessions":"10","average_time_session":"149.8000","count_distinct_places":"2"},{"id":"37","login":"lol","role":"Пользователь","description":"bebebe","last_session":"0","count_sessions":"0","average_time_session":"0.0000","count_distinct_places":"0"},{"id":"32","login":"testUser","role":"Пользователь","description":"пробный юзер","last_session":"1602443553","count_sessions":"4","average_time_session":"5.5000","count_distinct_places":"1"}]
    */},
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