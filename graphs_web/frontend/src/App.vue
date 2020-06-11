<template>
  <div id="app" v-on:changeduration="console.log('ураа1')">
    <Header/>
    <div v-on:click="closeAll"
         v-on:changeduration="console.log('ураа')"
         class="wrapper">
      <div id="row-list">
        <GraphRow v-for="row in graphConfigs"
                  v-bind:config="row"
                  v-bind:key="row.id"/>
      </div>
      <SettingsButton/>
      <SettingsPanel/>
    </div>
  </div>
</template>

<script>
import Header from "@/components/MyHeader";
import GraphRow from "@/components/GraphRow";
import axios from 'axios';
import SettingsButton from "@/components/SettingsButton";
import SettingsPanel from "@/components/SettingsPanel";

export default {
  name: 'App',
  components: {
    SettingsPanel,
    SettingsButton,
    GraphRow,
    Header,
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

      if (!minDate) minDate = this.$root.store.getMinDate().getTime()
      if (!maxDate) maxDate = this.$root.store.getMaxDate().getTime()

      return axios({
        timeout: 5000,
        method: 'post',
        url: dir + '/php/get_data.php',
        data: {
          minDate,
          maxDate,
          tags
        }
      }).then(response => response.data)
        .catch(error => { console.log(error.response ? error.response : error)})
    },
    getTemperatureData: function () {
      const tags = this.getOnlyTags(this.$root.store.state.tagsTemperature)
      this.getServerData(tags)
              .then(data => {
                if (data) {
                  this.$root.store.setDataTemperature(data)
                  this.$root.store.setTemperatureReady(true)
                }
              })
              .catch(error => {
                console.log(error.response ? error.response : error)
              })
    },
    getDigitalInputData: function () {
      const tags = this.getOnlyTags(this.$root.store.state.tagsDigitalInput)
      this.getServerData(tags)
              .then(data => {
                if (data) {
                  this.$root.store.setDataDigitalInputs(data)
                  this.$root.store.setDigitalInputReady(true)
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
    //console.log(dataTemper)

    //  this.$root.store.setDataTemperature([{"id":10101,"data":[{"value":36,"date":1591766844000},{"value":36,"date":1591766907000},{"value":36,"date":1591766969000},{"value":36,"date":1591767032000},{"value":36,"date":1591767095000},{"value":36,"date":1591767158000},{"value":36,"date":1591767221000},{"value":36,"date":1591767283000},{"value":36,"date":1591767348000},{"value":36,"date":1591767411000},{"value":36,"date":1591767474000},{"value":36,"date":1591767537000},{"value":36,"date":1591767600000},{"value":36,"date":1591767660000},{"value":36,"date":1591767723000},{"value":36,"date":1591768050000},{"value":36,"date":1591768112000},{"value":36,"date":1591768174000},{"value":36,"date":1591768236000},{"value":36,"date":1591768298000},{"value":36,"date":1591768360000},{"value":36,"date":1591768422000},{"value":36,"date":1591768484000},{"value":36,"date":1591768545000},{"value":36,"date":1591768607000},{"value":36,"date":1591768670000},{"value":36,"date":1591768732000},{"value":36,"date":1591768794000},{"value":36,"date":1591768856000},{"value":36,"date":1591768917000},{"value":36,"date":1591768979000},{"value":36,"date":1591769041000},{"value":36,"date":1591769103000},{"value":36,"date":1591769165000},{"value":36,"date":1591769227000},{"value":36,"date":1591769289000},{"value":36,"date":1591769351000},{"value":36,"date":1591769413000},{"value":36,"date":1591769475000},{"value":36,"date":1591769537000},{"value":36,"date":1591769598000},{"value":36,"date":1591769660000},{"value":36,"date":1591769723000},{"value":36,"date":1591769785000},{"value":36,"date":1591769847000},{"value":36,"date":1591769909000},{"value":36,"date":1591769971000},{"value":36,"date":1591770033000},{"value":36,"date":1591770095000},{"value":36,"date":1591770157000},{"value":36,"date":1591770219000},{"value":36,"date":1591770281000},{"value":36,"date":1591770343000}]},{"id":"10102","data":[{"value":21,"date":1591766844000},{"value":21,"date":1591766907000},{"value":21,"date":1591766969000},{"value":21,"date":1591767032000},{"value":21,"date":1591767095000},{"value":21,"date":1591767158000},{"value":21,"date":1591767221000},{"value":21,"date":1591767284000},{"value":21,"date":1591767348000},{"value":21,"date":1591767411000},{"value":21,"date":1591767474000},{"value":21,"date":1591767537000},{"value":21,"date":1591767600000},{"value":21,"date":1591767660000},{"value":21,"date":1591767723000},{"value":22,"date":1591768050000},{"value":22,"date":1591768112000},{"value":22,"date":1591768174000},{"value":22,"date":1591768236000},{"value":22,"date":1591768298000},{"value":22,"date":1591768360000},{"value":22,"date":1591768422000},{"value":22,"date":1591768484000},{"value":22,"date":1591768545000},{"value":22,"date":1591768607000},{"value":22,"date":1591768670000},{"value":22,"date":1591768732000},{"value":22,"date":1591768794000},{"value":22,"date":1591768856000},{"value":22,"date":1591768917000},{"value":22,"date":1591768979000},{"value":22,"date":1591769041000},{"value":22,"date":1591769103000},{"value":22,"date":1591769165000},{"value":22,"date":1591769227000},{"value":22,"date":1591769289000},{"value":22,"date":1591769351000},{"value":22,"date":1591769413000},{"value":22,"date":1591769475000},{"value":22,"date":1591769537000},{"value":22,"date":1591769598000},{"value":22,"date":1591769660000},{"value":22,"date":1591769723000},{"value":22,"date":1591769785000},{"value":22,"date":1591769847000},{"value":22,"date":1591769909000},{"value":22,"date":1591769971000},{"value":22,"date":1591770033000},{"value":22,"date":1591770095000},{"value":22,"date":1591770157000},{"value":22,"date":1591770219000},{"value":22,"date":1591770281000},{"value":22,"date":1591770343000}]},{"id":"10103","data":[{"value":36,"date":1591766844000},{"value":36,"date":1591766907000},{"value":36,"date":1591766969000},{"value":36,"date":1591767032000},{"value":36,"date":1591767095000},{"value":36,"date":1591767158000},{"value":36,"date":1591767221000},{"value":36,"date":1591767284000},{"value":36,"date":1591767348000},{"value":36,"date":1591767411000},{"value":36,"date":1591767474000},{"value":36,"date":1591767537000},{"value":36,"date":1591767600000},{"value":36,"date":1591767660000},{"value":36,"date":1591767723000},{"value":36,"date":1591768050000},{"value":36,"date":1591768112000},{"value":36,"date":1591768174000},{"value":36,"date":1591768236000},{"value":36,"date":1591768298000},{"value":36,"date":1591768360000},{"value":36,"date":1591768422000},{"value":36,"date":1591768484000},{"value":35,"date":1591768541000},{"value":35,"date":1591768603000},{"value":35,"date":1591768666000},{"value":35,"date":1591768728000},{"value":35,"date":1591768789000},{"value":35,"date":1591768851000},{"value":35,"date":1591768913000},{"value":35,"date":1591768975000},{"value":34,"date":1591768997000},{"value":34,"date":1591769059000},{"value":34,"date":1591769121000},{"value":34,"date":1591769182000},{"value":34,"date":1591769245000},{"value":34,"date":1591769307000},{"value":34,"date":1591769369000},{"value":34,"date":1591769431000},{"value":33,"date":1591769488000},{"value":33,"date":1591769550000},{"value":33,"date":1591769612000},{"value":33,"date":1591769675000},{"value":33,"date":1591769737000},{"value":33,"date":1591769799000},{"value":33,"date":1591769860000},{"value":33,"date":1591769923000},{"value":33,"date":1591769985000},{"value":32,"date":1591769989000},{"value":33,"date":1591770002000},{"value":32,"date":1591770051000},{"value":32,"date":1591770113000},{"value":32,"date":1591770175000},{"value":32,"date":1591770237000},{"value":32,"date":1591770299000},{"value":32,"date":1591770361000}]},{"id":"10104","data":[{"value":21,"date":1591766863000},{"value":21,"date":1591766926000},{"value":21,"date":1591766989000},{"value":21,"date":1591767052000},{"value":21,"date":1591767114000},{"value":21,"date":1591767177000},{"value":21,"date":1591767240000},{"value":21,"date":1591767303000},{"value":21,"date":1591767367000},{"value":21,"date":1591767430000},{"value":21,"date":1591767494000},{"value":21,"date":1591767557000},{"value":21,"date":1591767620000},{"value":21,"date":1591767680000},{"value":21,"date":1591768050000},{"value":21,"date":1591768112000},{"value":21,"date":1591768174000},{"value":21,"date":1591768236000},{"value":21,"date":1591768298000},{"value":21,"date":1591768360000},{"value":21,"date":1591768422000},{"value":21,"date":1591768484000},{"value":21,"date":1591768545000},{"value":21,"date":1591768607000},{"value":21,"date":1591768670000},{"value":21,"date":1591768732000},{"value":21,"date":1591768794000},{"value":21,"date":1591768856000},{"value":21,"date":1591768917000},{"value":21,"date":1591768979000},{"value":21,"date":1591769041000},{"value":21,"date":1591769103000},{"value":22,"date":1591769125000},{"value":21,"date":1591769129000},{"value":22,"date":1591769147000},{"value":22,"date":1591769209000},{"value":22,"date":1591769272000},{"value":22,"date":1591769333000},{"value":22,"date":1591769395000},{"value":22,"date":1591769457000},{"value":22,"date":1591769519000},{"value":22,"date":1591769581000},{"value":22,"date":1591769643000},{"value":22,"date":1591769706000},{"value":22,"date":1591769768000},{"value":22,"date":1591769829000},{"value":22,"date":1591769891000},{"value":22,"date":1591769954000},{"value":22,"date":1591770016000},{"value":22,"date":1591770077000},{"value":22,"date":1591770139000},{"value":22,"date":1591770201000},{"value":22,"date":1591770263000},{"value":22,"date":1591770325000},{"value":22,"date":1591770387000}]},{"id":"10105","data":[{"value":35,"date":1591766878000},{"value":35,"date":1591766940000},{"value":35,"date":1591767003000},{"value":35,"date":1591767066000},{"value":35,"date":1591767129000},{"value":35,"date":1591767192000},{"value":35,"date":1591767255000},{"value":35,"date":1591767317000},{"value":35,"date":1591767382000},{"value":35,"date":1591767445000},{"value":35,"date":1591767508000},{"value":35,"date":1591767571000},{"value":35,"date":1591767634000},{"value":35,"date":1591767699000},{"value":35,"date":1591768050000},{"value":35,"date":1591768112000},{"value":35,"date":1591768174000},{"value":35,"date":1591768236000},{"value":35,"date":1591768298000},{"value":35,"date":1591768360000},{"value":35,"date":1591768422000},{"value":35,"date":1591768484000},{"value":35,"date":1591768545000},{"value":35,"date":1591768607000},{"value":35,"date":1591768670000},{"value":34,"date":1591768714000},{"value":35,"date":1591768719000},{"value":34,"date":1591768736000},{"value":35,"date":1591768741000},{"value":34,"date":1591768745000},{"value":35,"date":1591768754000},{"value":34,"date":1591768759000},{"value":34,"date":1591768820000},{"value":34,"date":1591768882000},{"value":34,"date":1591768944000},{"value":34,"date":1591769006000},{"value":34,"date":1591769068000},{"value":34,"date":1591769129000},{"value":34,"date":1591769191000},{"value":34,"date":1591769254000},{"value":34,"date":1591769316000},{"value":34,"date":1591769378000},{"value":34,"date":1591769439000},{"value":34,"date":1591769501000},{"value":34,"date":1591769563000},{"value":34,"date":1591769625000},{"value":34,"date":1591769688000},{"value":34,"date":1591769750000},{"value":34,"date":1591769812000},{"value":34,"date":1591769874000},{"value":34,"date":1591769936000},{"value":34,"date":1591769998000},{"value":34,"date":1591770060000},{"value":34,"date":1591770122000},{"value":34,"date":1591770184000},{"value":34,"date":1591770246000},{"value":34,"date":1591770308000},{"value":34,"date":1591770369000}]},{"id":"10106","data":[{"value":21,"date":1591766844000},{"value":21,"date":1591766907000},{"value":21,"date":1591766969000},{"value":21,"date":1591767032000},{"value":21,"date":1591767095000},{"value":21,"date":1591767158000},{"value":21,"date":1591767221000},{"value":21,"date":1591767284000},{"value":21,"date":1591767348000},{"value":21,"date":1591767411000},{"value":21,"date":1591767474000},{"value":21,"date":1591767537000},{"value":21,"date":1591767600000},{"value":21,"date":1591767660000},{"value":21,"date":1591767723000},{"value":21,"date":1591768050000},{"value":21,"date":1591768112000},{"value":21,"date":1591768174000},{"value":21,"date":1591768236000},{"value":21,"date":1591768298000},{"value":21,"date":1591768360000},{"value":21,"date":1591768422000},{"value":21,"date":1591768484000},{"value":21,"date":1591768545000},{"value":21,"date":1591768607000},{"value":21,"date":1591768670000},{"value":21,"date":1591768732000},{"value":21,"date":1591768794000},{"value":21,"date":1591768856000},{"value":22,"date":1591768891000},{"value":22,"date":1591768953000},{"value":22,"date":1591769015000},{"value":22,"date":1591769076000},{"value":22,"date":1591769138000},{"value":22,"date":1591769200000},{"value":22,"date":1591769263000},{"value":22,"date":1591769325000},{"value":22,"date":1591769386000},{"value":22,"date":1591769448000},{"value":22,"date":1591769510000},{"value":22,"date":1591769572000},{"value":22,"date":1591769634000},{"value":22,"date":1591769697000},{"value":22,"date":1591769759000},{"value":22,"date":1591769821000},{"value":22,"date":1591769882000},{"value":22,"date":1591769945000},{"value":22,"date":1591770007000},{"value":22,"date":1591770069000},{"value":22,"date":1591770130000},{"value":22,"date":1591770192000},{"value":22,"date":1591770255000},{"value":22,"date":1591770316000},{"value":22,"date":1591770378000}]},{"id":"10107","data":[{"value":33,"date":1591766844000},{"value":33,"date":1591766907000},{"value":33,"date":1591766969000},{"value":33,"date":1591767032000},{"value":33,"date":1591767095000},{"value":33,"date":1591767158000},{"value":33,"date":1591767221000},{"value":33,"date":1591767284000},{"value":33,"date":1591767348000},{"value":33,"date":1591767411000},{"value":32,"date":1591767470000},{"value":33,"date":1591767474000},{"value":32,"date":1591767479000},{"value":33,"date":1591767508000},{"value":32,"date":1591767542000},{"value":32,"date":1591767605000},{"value":33,"date":1591767634000},{"value":32,"date":1591767639000},{"value":32,"date":1591767704000},{"value":32,"date":1591768050000},{"value":32,"date":1591768112000},{"value":32,"date":1591768174000},{"value":32,"date":1591768236000},{"value":32,"date":1591768298000},{"value":32,"date":1591768360000},{"value":32,"date":1591768422000},{"value":32,"date":1591768484000},{"value":32,"date":1591768545000},{"value":32,"date":1591768607000},{"value":32,"date":1591768670000},{"value":32,"date":1591768732000},{"value":32,"date":1591768794000},{"value":32,"date":1591768856000},{"value":32,"date":1591768917000},{"value":32,"date":1591768979000},{"value":32,"date":1591769041000},{"value":32,"date":1591769103000},{"value":32,"date":1591769165000},{"value":32,"date":1591769227000},{"value":31,"date":1591769272000},{"value":32,"date":1591769280000},{"value":32,"date":1591769342000},{"value":31,"date":1591769373000},{"value":31,"date":1591769435000},{"value":31,"date":1591769497000},{"value":31,"date":1591769559000},{"value":31,"date":1591769621000},{"value":31,"date":1591769684000},{"value":31,"date":1591769746000},{"value":31,"date":1591769807000},{"value":31,"date":1591769869000},{"value":31,"date":1591769932000},{"value":30,"date":1591769954000},{"value":30,"date":1591770016000},{"value":31,"date":1591770024000},{"value":30,"date":1591770033000},{"value":31,"date":1591770069000},{"value":30,"date":1591770073000},{"value":30,"date":1591770135000},{"value":30,"date":1591770197000},{"value":30,"date":1591770259000},{"value":30,"date":1591770321000},{"value":30,"date":1591770383000}]},{"id":"10108","data":[{"value":21,"date":1591766844000},{"value":21,"date":1591766907000},{"value":21,"date":1591766969000},{"value":21,"date":1591767032000},{"value":21,"date":1591767095000},{"value":21,"date":1591767158000},{"value":21,"date":1591767221000},{"value":21,"date":1591767284000},{"value":21,"date":1591767348000},{"value":21,"date":1591767411000},{"value":21,"date":1591767474000},{"value":21,"date":1591767537000},{"value":21,"date":1591767600000},{"value":21,"date":1591767660000},{"value":21,"date":1591767723000},{"value":21,"date":1591768050000},{"value":21,"date":1591768112000},{"value":21,"date":1591768174000},{"value":21,"date":1591768236000},{"value":21,"date":1591768298000},{"value":21,"date":1591768360000},{"value":21,"date":1591768422000},{"value":22,"date":1591768484000},{"value":21,"date":1591768488000},{"value":22,"date":1591768506000},{"value":22,"date":1591768568000},{"value":22,"date":1591768630000},{"value":22,"date":1591768692000},{"value":22,"date":1591768754000},{"value":22,"date":1591768816000},{"value":22,"date":1591768878000},{"value":22,"date":1591768940000},{"value":22,"date":1591769001000},{"value":22,"date":1591769063000},{"value":22,"date":1591769125000},{"value":22,"date":1591769187000},{"value":22,"date":1591769250000},{"value":22,"date":1591769311000},{"value":22,"date":1591769373000},{"value":22,"date":1591769435000},{"value":22,"date":1591769497000},{"value":22,"date":1591769559000},{"value":22,"date":1591769621000},{"value":22,"date":1591769684000},{"value":22,"date":1591769746000},{"value":22,"date":1591769807000},{"value":22,"date":1591769869000},{"value":22,"date":1591769932000},{"value":22,"date":1591769993000},{"value":22,"date":1591770055000},{"value":22,"date":1591770117000},{"value":22,"date":1591770179000},{"value":23,"date":1591770224000},{"value":23,"date":1591770285000},{"value":23,"date":1591770347000}]}])

  },
}
</script>

<style>
  @import "assets/css/style.css";
  @import "assets/css/new_style.css";
  @import "assets/css/style.css";
</style>
