import Vue from 'vue'
import Router from 'vue-router'
import Auth from '@/views/Auth'
import Profile from "@/views/Profile"
import VueCookies from 'vue-cookies'


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
                if (VueCookies.isKey(userMeta)) next()
                else next({name: 'auth'})
            },
        },
        {
            name: 'admin-panel',
            path: '/admin',
            component: () => import(/* webpackChunkName: "group-admin-panel" */ '@/views/AdminPanel'),
            meta: {title: 'Панель управления'},
            beforeEnter: (to, from, next) => {
                if (VueCookies.isKey(userMeta) && VueCookies.isKey(userMeta) && VueCookies.get(userMeta).isAdmin)
                    next()
                else next({name: 'auth'})

            },
            children: [
                {
                    path: 'stats',
                    component: () => import(/* webpackChunkName: "group-admin-panel" */ '@/views/adminPanel/Stats'),
                    name: 'admin-panel-stats',
                    meta: {title: 'Статистика'},
                    props: true

                },
                {
                    path: 'users',
                    component: () => import(/* webpackChunkName: "group-admin-panel" */ '@/views/adminPanel/Users'),
                    name: 'admin-panel-users',
                    meta: {title: 'Пользователи'},
                },
                {
                    path: 'roles',
                    component: () => import(/* webpackChunkName: "group-admin-panel" */ '@/views/adminPanel/Roles'),
                    name: 'admin-panel-roles',
                    meta: {title: 'Пользователи'},
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
    document.title = to.meta.title
    next()
})
export default router;
