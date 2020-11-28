<template>
    <div @click="closeAll" id="app">
        <MyHeader @clear-error-msg="clearErrorMsg"
                  @update-date="updateCharts"
                  :error-info="errorInfo"
                  ref="header"
        />
        <div class="wrapper">

            <div id="row-list">
                <GraphRow :config="row"
                          :key="row.id"
                          @newtag="getTagData"
                          @remove-row="removeRow"
                          ref="graph"
                          v-for="row in currentConfig.charts"/>
                <div class="clickable add-btn disable-selection-text" v-on:click="addConfig('graph', [])">
                    <div class="text-add-btn">&#x2b;</div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>

import MyHeader from "./components/MyHeader";
import GraphRow from "./components/GraphRow";
import axios from 'axios';
import {mapGetters} from 'vuex';

export default {
    name: 'App',
    components: {
        GraphRow,
        MyHeader,
    },
    data() {
        return {
            errorInfo: {
                message: '',
                tags: []
            }
        }
    },
    computed: {
        ...mapGetters(['currentConfig', 'configurations']),
    },
    watch: {
        currentConfig: function(){
            this.confirmConfig()
            console.log(this.currentConfig.charts[0].tags)
        },
        configurations: {
            handler: function (){
                console.log('update-conf')
                this.updateConfigs()
            },
            deep: true
        }
    },
    methods: {
        loadConfigs: function () {
            const loc = window.location.pathname
            const dir = loc.substring(0, loc.lastIndexOf('/'))
            this.$store.commit('setCurrentConfig', this.$store.state.EMPTY_CONFIG)
            axios({
                timeout: 50000,
                url: dir + '/php/configurations.php',
            }).then(response => {
                if (response.data.error) throw response.data.error
                else {
                    console.log(response.data)
                    this.$store.commit('setConfigs', response.data)
                }
            }).catch(error => {
                console.log(error.response)
                console.log(error)
                this.setErrorMessage(error.message)
            })
        },
        updateConfigs: function (){
            const loc = window.location.pathname
            const dir = loc.substring(0, loc.lastIndexOf('/'))
            axios({
                timeout: 50000,
                method: 'post',
                url: dir + '/php/configurations.php',
                data: {
                    configs: JSON.stringify(this.$store.getters.configurations)
                }
            }).then(response => {
                if (response.data.error) throw response.data.error
                else {
                    console.log(response.data)
                }
            }).catch(error => {
                console.log(error.response)
                console.log(error)
                this.setErrorMessage(error.message)
            })
        },
        removeRow: function (configId) {
            this.currentConfig.charts.splice(this.currentConfig.charts.findIndex(config => config.id === configId), 1)
        },
        clearErrorMsg: function () {
            this.errorInfo.message = null
            this.errorInfo.tags.splice(0)
        },
        setErrorMessage: function (message) {
            this.errorInfo.message = message
        },
        setErrorTags: function (tags) {
            for (const tagId of tags) this.errorInfo.tags.push(tagId)
        },
        addConfig: function (name, tags) {
            let id = 0
            while (this.currentConfig.charts.findIndex(config => config.id === id) > -1)
                id++

            this.currentConfig.charts.push({id, name, tags})
        },
        closeAll: function (e) {
            this.$refs['header'].closeAll(e.target)
            for (const graph of this.$refs['graph'])
                graph.closeAll(e.target)
        },
        getServerData: function (tags, minDate, maxDate) {
            if (!minDate) minDate = this.$store.getters.minDate.getTime()
            if (!maxDate) maxDate = this.$store.getters.maxDate.getTime()
            console.log(new Date(minDate))
            console.log(new Date(maxDate))
            console.log(tags)
            return axios({
                timeout: 50000,
                method: 'post',
                url: '/api/data/data.php',
                data: {
                    minDate,
                    maxDate,
                    tags,
                    purpose: 'data'
                }
            }).then(response => {
              if (response.data) {
                if (response.data.error) {
                  throw response.data.error
                } else if (response.data.code !== 200 || !response.data.data) {
                  this.setErrorMessage('Ошибка: ' + response.data.message)
                  this.setErrorTags(tags)
                  return false
                } else {
                  console.log(response.data.data)
                  return response.data.data ? response.data.data : true
                }
              } else throw response
            }).catch(error => {
                console.log(error.response)
                console.log(error)
                if (error.errno && error.errno === 2) {
                    const tags = error['request-body'].tags
                    this.setErrorMessage(error.message)
                    this.setErrorTags(tags)
                    for (const tagId of tags) this.setCheckedTag(tagId, false)
                }
                this.setErrorMessage(error.message)
            })
        },
        setCheckedTag: function (tagId, isWithData) {
            for (const graph of this.$refs['graph'])
                graph.setColorSelectedInput(tagId, isWithData)
        },
        updateCharts: function () {
            console.log('update')
            /*for (const graph of this.$refs['graph'])
                graph.beforeUpdate()*/
            const tags = this.getAllSelectedTags()
            console.log(tags)
            this.$store.commit('clearTagsData')
            if (tags && tags.length) {
                this.getAllServerData(tags).then(() => {
                    this.updateAllGraphs()
                })
            } else this.updateAllGraphs()
        },
        confirmConfig: function () {
            for (const graph of this.$refs['graph'])
                graph.beforeUpdate()
            /*const tags = Array.from(this.$store.getters.setOfCurrentNotLoadedSelectedTags)*/
            const tags = this.getAllSelectedTags()
            if (tags && tags.length)
                this.getAllServerData(tags)

        },
        updateAllGraphs: function () {
            for (const graph of this.$refs['graph'])
                graph.updateCharts()
        },
        getAllSelectedTags: function () {
            return Array.from(this.$store.getters.setOfCurrentSelectedTags)
        },
        getAllServerData: function (tags) {
            console.log(tags)
            this.clearErrorMsg()
            return this.getServerData(tags)
                .then(data => {
                    if (data)
                        for (const tag of data)
                            this.addNewTag(tag)
                })
                .catch(error => {
                    console.log(error.response ? error.response : error)
                })
        },
        getDevicesAndTags: function () {
            console.log('get devices')
            const loc = window.location.pathname
            const dir = loc.substring(0, loc.lastIndexOf('/'))

            axios({
                timeout: 50000,
                url: dir + '/php/get_devices_with_tags.php',
            }).then(response => {
                if (response.data.error) throw response.data.error
                else {
                    this.$store.commit('setDevicesAndTags', {data: response.data})
                }
            }).catch(error => {
                console.log(error.response)
                const message = error.response ? (error.response.status + ': ' + error.response.statusText) : error.message
                this.setErrorMessage(message)
            })
        },
        getTagData: function (tagId) {
            this.getServerData([tagId])
                .then(data => {
                    if (data && data.length) {
                        this.addNewTag(data[0])
                    }
                })
                .catch(error => {
                    console.log(error.response ? error.response : error)
                })
        },
        addNewTag: function (tag) {
            if (tag) {
                if (tag.data && tag.data.length) {
                    this.$store.commit('addNewTag', {newTag: tag})
                    if (this.$store.getters.isTagsLoaded(tag.id))
                        this.setCheckedTag(tag.id, true)
                    else {
                        this.setErrorMessage('no available data')
                        this.setErrorTags([tag.id])
                        this.setCheckedTag(tag.id, false)
                    }
                } else this.setCheckedTag(tag.id, false)
            }
        }
    },
    mounted() {
        this.getDevicesAndTags()
        this.loadConfigs()
    },
}
</script>

<style>
@import "assets/css/style.css";
@import "assets/css/style.css";

.clickable:hover, .clickable:focus{
    cursor: pointer;
}
.animation{
    user-select: none;
    -webkit-transition: right .4s linear;
    -moz-transition: right .4s linear;
    -ms-transition: right .4s linear;
    -o-transition: right .4s linear;
    transition: right .4s linear;
}
.add-btn {
    margin: 5px 8px 12px 8px;
    background-color: #fff;
    height: 50px;
    color: #2d353c;
    text-align: center;
    display: flex;
    border-radius: 3px;
    box-shadow: 0 0 8px rgba(0,0,0,.05)
}

.add-btn:hover, .add-btn:focus {
    background: #f2f4f5;
}

.text-add-btn {
    margin: auto auto;
    font-weight: bolder;
    font-size: 3rem;
}

.wrapper {
    position: absolute;
}

#row-list {
    height: calc(100% - 65px);
    margin-bottom: 5px;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    width: 100%;
    position: fixed;
}

.remove-tag-btn {
    visibility: hidden;
    color: #ff5b57;
    margin-right: 5px;
}

.disable-selection-text::selection, .disable-selection-text::-moz-selection {
    background: transparent;
}

.disable-selection-text {
    -moz-user-select: none;
    -ms-user-select: none;
    -khtml-user-select: none;
    -webkit-user-select: none;
    -webkit-touch-callout: none;
}
.open-right{
  -moz-transform: rotate(-90deg);
  -webkit-transform: rotate(-90deg);
  transform: rotate(-90deg);
}
.open-close-animation{
  -webkit-animation: .3s linear forwards;
  -moz-animation: .3s linear forwards;
  animation: .3s linear forwards;
}
.close-down-animation{
  -webkit-animation-name: spinClose;
  -moz-animation-name: spinClose;
  animation-name: spinClose;
}
.open-down-animation{
  -webkit-animation-name: spinOpen;
  -moz-animation-name: spinOpen;
  animation-name: spinOpen;
}
@-moz-keyframes spinClose {
  from {
    -moz-transform: rotate(0deg);
  }
  to {
    -moz-transform: rotate(-90deg);
  }
}
@-webkit-keyframes spinClose {
  from {
    -webkit-transform: rotate(0deg);
  }
  to {
    -webkit-transform: rotate(-90deg);
  }
}
@keyframes spinClose {
  from {
    -webkit-transform: rotate(0deg);
    transform: rotate(0deg);
  }
  to {
    -webkit-transform: rotate(-90deg);
    transform: rotate(-90deg);
  }
}
@-moz-keyframes spinOpen {
  from {
    -moz-transform: rotate(-90deg);
  }
  to {
    -moz-transform: rotate(0deg);
  }
}
@-webkit-keyframes spinOpen {
  from {
    -webkit-transform: rotate(-90deg);
  }
  to {
    -webkit-transform: rotate(0deg);
  }
}
@keyframes spinOpen {
  from {
    -webkit-transform: rotate(-90deg);
    transform: rotate(-90deg);
  }
  to {
    -webkit-transform: rotate(0deg);
    transform: rotate(0deg);
  }
}
.svg-img-child{
  width: 20px;
  height: 20px;
}
.triangle-svg{
  background-image: url('data:image/svg+xml,\
  <svg xmlns="http://www.w3.org/2000/svg" width="100%" height="20px" viewBox="0 0 400 400">\
  <path d="M 100 100 L 300 100 L 200 300 z"/>\
  </svg>');

}
.svg-img{
  background-repeat: no-repeat;
  background-position: center center;
}
</style>
