<template>
    <div id="app">
        <MyHeader v-bind:errorMessage="errorMessage"/>
        <div class="wrapper" v-on:click="closeAll">
            <div id="row-list">
                <GraphRow :config="row"
                          :key="row.id"
                          ref="graph"
                          @newtag="getTagData"
                          @zoomer="zoomAll"
                          v-for="row in graphConfigs"/>
                <div class="add-btn" v-on:click="addConfig">
                    <div class="text-add-btn">&#x2b;</div>
                </div>
            </div>
            <SettingsButton/>
            <SettingsPanel @hangeduration="updateCharts"
                           @updatecharts="updateCharts"/>
        </div>
    </div>
</template>

<script>

    import MyHeader from "./components/MyHeader";
    import GraphRow from "./components/GraphRow";
    import axios from 'axios';
    import SettingsButton from "./components/SettingsButton";
    import SettingsPanel from "./components/SettingsPanel";

    export default {
        name: 'App',
        components: {
            SettingsPanel,
            SettingsButton,
            GraphRow,
            MyHeader,
        },
        data() {
            return {
                graphConfigs: [
                    {
                        id: 0,
                        config: {}
                    },
                    {
                        id: 1,
                        config: {}
                    }],
                errorMessage: '',

            };
        },
        methods: {
            addConfig: function(){
                this.graphConfigs.push({
                    id: this.graphConfigs.length,
                    config: {}
                },)
            },
            closeAll: function (e) {
                document.querySelectorAll(".select-items").forEach(elem => {
                    if ((!e.target.classList.contains('select-clickable') && e.target.parentNode.tagName !== 'LABEL' && e.target.tagName !== 'LABEL'))
                        elem.style.display = "none"
                })
            },
            getServerData: function (tags, minDate, maxDate) {
                const loc = window.location.pathname
                const dir = loc.substring(0, loc.lastIndexOf('/'))

                if (!minDate) minDate = this.$store.getters.date.min.getTime()
                if (!maxDate) maxDate = this.$store.getters.date.max.getTime()
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
                        if (error.response)
                            this.errorMessage = error.response.status + ': ' + error.response.statusText
                        else
                            this.errorMessage = error.message
                    })
            },
            updateCharts: function () {
                const tags = this.$store.getters.loadedTags
                console.log(tags)
                if (tags && tags.length) {
                    this.getAllServerData(tags).then(() => {
                        this.graphConfigs.forEach(config => {
                            this.$refs['graph'][config.id].updateCharts()
                        })
                    })
                }
            },
            getAllServerData: function (tags) {
                console.log(tags)
                this.errorMessage = ''
                return this.getServerData(tags)
                    .then(data => {
                        if(data)
                            data.forEach(tag => {
                                this.$store.commit('addNewTag', {newTag: tag})
                            })
                    })
                    .catch(error => {
                        console.log(error.response ? error.response : error)
                    })
            },
            getDevicesAndTags: function() {
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
                        if (error.response)
                            this.errorMessage = error.response.status + ': ' + error.response.statusText
                        else
                            this.errorMessage = error.message
                    })
            },

            zoomAll: function () {
                console.log('zoomer')
                this.graphConfigs.forEach(graph => {
                    this.$refs['graph'][graph.id].refChart.zoomer()
                })
            },
            getTagData: function (tagId) {
                this.getServerData([tagId])
                    .then(data => {
                        if (data && data.length) {
                            this.$store.commit('addNewTag', {newTag: data[0]})
                        }
                    })
                    .catch(error => {
                        console.log(error.response ? error.response : error)
                    })
            },
        },


        mounted() {
            this.getDevicesAndTags()
        },
    }
</script>

<style>
    @import "assets/css/style.css";
    @import "assets/css/style.css";
    .add-btn{
        margin: 5px 8px;
        background-color: #fff;
        height: 50px;
        color: #2d353c;
        text-align: center;
        display: flex;
    }
    .add-btn:hover, .add-btn:focus{
        background: #f2f4f5;
        cursor: pointer;
    }
    .text-add-btn{
        margin: auto auto;
        font-weight: bolder;
        font-size: 3rem;
    }
</style>
