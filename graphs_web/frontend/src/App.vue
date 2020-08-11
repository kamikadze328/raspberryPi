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
                          v-for="row in configs"/>
                <div class="add-btn disable-selection-text" v-on:click="addConfig">
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

export default {
    name: 'App',
    components: {
        GraphRow,
        MyHeader,
    },
    data() {
        return {

            configs: [
                {
                    id: 0,
                    tags: {}
                },
                {
                    id: 1,
                    tags: {}
                }
            ],
            errorInfo: {
                message: '',
                tags: []
            }
        }
    },
    methods: {
        removeRow: function (configId) {
            this.configs.splice(this.configs.findIndex(config => config.id === configId), 1)
        },
        clearErrorMsg: function () {
            this.errorInfo.message = null
            this.errorInfo.tags.splice(0)
        },
        setErrorMessage: function (message){
            this.errorInfo.message = message
        },
        setErrorTags: function (tags){
            for(const tagId of tags) this.errorInfo.tags.push(tagId)
        },
        addConfig: function () {
            let id = 0
            while (this.configs.findIndex(config => config.id === id) > -1)
                id++

            this.configs.push({id, tags: {}})
        },
        closeAll: function (e) {
            for (const graph of this.$refs['graph'])
                graph.closeAll(e.target)
        },
        getServerData: function (tags, minDate, maxDate) {
            const loc = window.location.pathname
            const dir = loc.substring(0, loc.lastIndexOf('/'))

            if (!minDate) minDate = this.$store.getters.minDate.getTime()
            if (!maxDate) maxDate = this.$store.getters.maxDate.getTime()
            console.log(new Date(minDate))
            console.log(new Date(maxDate))
            return axios({
                timeout: 50000,
                method: 'post',
                url: dir + '/php/get_data.php',
                data: {
                    minDate,
                    maxDate,
                    tags
                }
            }).then(response => {
                if (response.data.error) throw response.data.error
                else return response.data
            })
                .catch(error => {
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
            for (const graph of this.$refs['graph'])
                graph.beforeUpdate()
            const tags = this.getAllSelectedTags()
            console.log(tags)
            this.$store.commit('clearTagsData')
            if (tags && tags.length) {
                this.getAllServerData(tags).then(() => {
                    this.updateAllGraphs()
                })
            } else this.updateAllGraphs()
        },
        updateAllGraphs: function () {
            for (const graph of this.$refs['graph'])
                graph.updateCharts()
        },
        getAllSelectedTags: function () {
            let uniqueTagsId = new Set()
            for (const graph of this.$refs['graph'])
                for (const selectedTagId of graph.selectedTagsId)
                    uniqueTagsId.add(selectedTagId)
            return Array.from(uniqueTagsId)

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
            })
                .catch(error => {
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
                    if(this.$store.getters.isTagsLoaded(tag.id))
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
    },
}
</script>

<style>
@import "assets/css/style.css";
@import "assets/css/style.css";

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
    cursor: pointer;
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
    cursor: pointer;
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
</style>
