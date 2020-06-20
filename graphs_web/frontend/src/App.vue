<template>
  <div id="app" v-on:changeduration="console.log('ураа1')">
    <MyHeader v-bind:errorMessaga="errorMessage"/>
    <div v-on:click="closeAll"
         v-on:changeduration="console.log('ураа')"
         class="wrapper">
      <div id="row-list">
        <GraphRow v-for="row in graphConfigs"
                  :config="row"
                  :key="row.id"/>
      </div>
      <SettingsButton/>
      <SettingsPanel/>
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
          id: 1,
          config: {}
        },
        {
          id: 2,
          config: {}
        }],
      errorMessage:'',

    };
  },
  methods: {
    closeAll: function (e) {
      document.querySelectorAll(".select-items").forEach(elem => {
        if (!e.target.classList.contains('select-clickable') && e.target.parentNode.tagName !== 'LABEL' && e.target.tagName !== 'LABEL')
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
        timeout: 5000,
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
                console.log(error)
                if (error.response)
                  this.errorMessage = error.response.status + ': ' + error.response.statusText
                else
                  this.errorMessage = error.message
              })
    },
    getTemperatureData: function () {
      const tags = this.getOnlyTags(this.$store.getters.tagsTemperature)
      this.getServerData(tags)
              .then(data => {
                if (data && data.length) {
                  this.$store.commit('setDataTemperature', {newData:data})
                  this.$store.commit('setTemperatureReady', {isReady:true})
                }
              })
              .catch(error => {
                console.log(error.response ? error.response : error)
              })
    },
    getDigitalInputData: function () {
      const tags = this.getOnlyTags(this.$store.getters.tagsDigitalInput)
      this.getServerData(tags)
              .then(data => {
                if (data && data.length) {
                  this.$store.commit('setDataDigitalInputs', {newData:data})
                  this.$store.commit('setDigitalInputReady', {isReady:true})
                }
              })
              .catch(error => {
                console.log(error.response ? error.response : error)
              })
    },
    getOnlyTags: function(data){
      let tags = []
      data.forEach(tag => tags.push(tag.id))
      return tags
    }
  },
  mounted() {
    this.$on('change-duration', ()=>console.log('ураааа'))
    this.getTemperatureData()
    this.getDigitalInputData()
    //const dataTemper = this.getServerData(tagsTemperature)


    //this.$root.store.setDigitalInputReady(true)
  },
}
</script>

<style>
  @import "assets/css/style.css";
  @import "assets/css/new_style.css";
  @import "assets/css/style.css";
</style>
