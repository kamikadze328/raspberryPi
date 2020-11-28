import {createApp} from 'vue'
import App from './App.vue'
const app = createApp(App)

import {store} from './store'
app.use(store)

import axios from 'axios'
import VueAxios from 'vue-axios'
app.use(VueAxios, axios)


import Emitter from 'tiny-emitter';
const emitter = new Emitter();
app.config.globalProperties.emitter = emitter

import d3 from './d3Importer.js';
app.config.globalProperties.d3 = d3

app.mount('#app')