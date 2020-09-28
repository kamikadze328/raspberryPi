import Vue from 'vue'
import Router from 'vue-router'
import Auth from '@/views/Auth'
import Profile from "@/views/Profile"
import VueCookies from 'vue-cookies'
import AdminPanel from "@/views/AdminPanel";
import UserStats from "@/views/leftPanel/UserStats";
import ListUsers from "@/views/leftPanel/ListUsers";

Vue.use(Router);
Vue.use(VueCookies)
const userMeta = 'user_meta'
const router = new Router({
    base: '/',
    mode: 'history',
    routes: [
        {
            name: 'auth',
            path: '/auth',
            component: Auth,
            props: {authCode: 0},
            meta: {title: 'Авторизация'},
            beforeEnter: (to, from, next) => {
                console.log(VueCookies.get(userMeta))
                if (VueCookies.isKey(userMeta)) next({name: 'profile'})
                else next()

            }
        },
        {
            name: 'profile',
            path: '/',
            component: Profile,
            meta: {title: 'Профиль'},
            beforeEnter: (to, from, next) => {
                console.log(VueCookies.get(userMeta))

                console.log(to)
                console.log(from)
                console.log(document.cookie)
                console.log(VueCookies.isKey(userMeta))
                if (VueCookies.isKey(userMeta)) next()

                else next({name: 'auth'})
            },
        },
        {
            name: 'admin-panel',
            path: '/admin',
            component: AdminPanel,
            meta: {title: 'Панель управления'},
            beforeEnter: (to, from, next) => {
                console.log(VueCookies.get(userMeta))

                if (VueCookies.isKey(userMeta) && VueCookies.isKey('isAdmin') && VueCookies.get('isAdmin') === 'true')
                    next()
                else next({name: 'auth'})

            },
            children: [
                {
                    path: 'stats',
                    component: UserStats,
                    name: 'admin-panel-stats',
                    meta: {title: 'Список пользователей'},

                },
                {
                    path: 'users',
                    component: ListUsers,
                    name: 'admin-panel-list-users',
                    meta: {title: 'Статистика'},
                }
                ]
        },
        {
            path: '*',
            redirect: '/'
        }
    ]
});
router.beforeEach((to, from, next) => {
    console.log(to)
    console.log(from)
    document.title = to.meta.title
    console.log(VueCookies.get(userMeta))

    next()
})
export default router;
