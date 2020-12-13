<template>
  <table>
    <template v-for="group in tagGroups" :key="group.id">
      <tr class="green"
          :class="{'group-rows-top': group.tags.indexOf(tag)===0 ,
            'group-rows-bottom': tagGroups.indexOf(group)===tagGroups.length-1 && group.tags.indexOf(tag)===group.tags.length-1}"
          v-for="tag in group.tags" :key="tag.id">
        <th  :class="{'border-bottom': tagGroups.indexOf(group)===tagGroups.length-1 && group.tags.indexOf(tag)===0}"
            v-if="group.tags.indexOf(tag)===0" :rowspan="group.tags.length"> {{group.name}}</th>
        <th>{{ tag.name }}</th>
        <td v-for="place in places" :key="place.id"> {{currentDataByGroupPlaceIDs(tag.id, place.id)}}</td>
        <td class="border-left"></td>
      </tr>

    </template>

    <tr class="blue">
      <th class="text-center" colspan="2">{{ currentYear + ' ' + currentMonth }}</th>
      <td v-for="place in places" :key="place.id">{{sumByPlace(place.id, currentMonthId)}}</td>
      <td class="border-left">{{sumAllPlacesByMonth(currentMonthId)}}</td>
    </tr>
    <tr class="wrapper-text-center group-rows-bottom group-rows-top red border-top-bottom-bold">
      <th colspan="2"></th>
      <th v-for="place in places" :key="place.id">{{place.name}}</th>
      <th class="border-left">ИТОГО</th>
    </tr>
    <tr v-for="ago in monthAgo" :key="ago" class="blue">
      <th colspan="2">{{yearByMonthId(currentMonthId - ago)  + ' ' + month(currentMonthId - ago)}}</th>
      <td v-for="place in places" :key="place.id">{{sumByPlace(place.id, currentMonthId - ago)}}</td>
      <td class="border-left">{{sumAllPlacesByMonth(currentMonthId - ago)}}</td>
    </tr>
    <tr class="group-rows-bottom group-rows-top">
      <td></td>
      <td colspan="3" class="clickable" @click="historyMonthNum += historyMonthNumStep">Показать ещё</td>
      <td colspan="3" :class="{clickable: canDecreaseHistoryMonthNum}" @click="historyMonthNum = historyMonthNumMin">
        {{ canDecreaseHistoryMonthNum ? 'Свернуть' : '' }}</td>
    </tr>
  </table>
</template>

<script>

import {mapGetters} from "vuex";

export default {
  name: "CurrentTable",
  computed: {
    ...mapGetters([
      'currentMonth',
      'currentYear',
      'currentMonthId',
      'month',
      'yearByMonthId',
      'tagGroups',
      'places',
        'currentDataByGroupPlaceIDs',
        'sumByPlace',
        'sumAllPlacesByMonth'
    ]),
    monthAgo(){
      let arr = [...Array(this.historyMonthNum + 1).keys()]
      arr.shift()
      return arr
    },
    canDecreaseHistoryMonthNum(){
      return this.historyMonthNum > this.historyMonthNumStep
    }
  },
  data() {
    return {
      historyMonthNum: 5,
      historyMonthNumMin: 5,
      historyMonthNumStep: 5
    }
  },
  methods: {

  },
}
</script>

<style scoped>
table{
  border-spacing: 0;
}
.text-center, .wrapper-text-center * {
  text-align: center;
}
.red th{
  background-color: rgba(255, 91, 87, .4);
}
.blue th{
  background-color: rgba(52, 143, 226, .4);
}
.yellow th{
  background-color: rgba(255, 217, 0, .4);
}
.green th{
  background-color: rgba(50, 169, 50, .4);
}
th {
  text-align: left;
}
td{
  width: 100px;
}
.group-rows-top * {
  border-top: 1px solid;
}
table{
  border-left: 1px solid;
  border-right: 1px solid;
}
.group-rows-bottom *, .border-bottom{
  border-bottom: 1px solid;
}
.border-top-bottom-bold *{
  border-top-width: 2px !important;
  border-bottom-width: 2px !important;
}
.border-left{
  border-left: 1px solid;

}

</style>