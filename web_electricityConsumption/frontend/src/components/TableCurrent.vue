<template>
  <table>
    <tr class="group-rows-top green">
      <th rowspan="3"> P (kVt)</th>
      <th class="blue">P актив</th>
      <td>0</td><td>0</td><td>0</td><td>0</td><td>0</td>
    </tr>
    <tr class="green">
      <th>P реакт</th>
      <td>0</td><td>0</td><td>0</td><td>0</td><td>0</td>
    </tr>
    <tr class="group-rows-bottom green">
      <th>P сумма</th>
      <td>0</td><td>0</td><td>0</td><td>0</td><td>0</td>
    </tr>
    <tr class="green">
      <th rowspan="4"> U (V)</th>
      <th>Фаза А</th>
      <td>0</td><td>0</td><td>0</td><td>0</td><td>0</td>
    </tr>
    <tr class="green">
      <th>Фаза B</th>
      <td>0</td><td>0</td><td>0</td><td>0</td><td>0</td>
    </tr>
    <tr class="green">
      <th>Фаза C</th>
      <td>0</td><td>0</td><td>0</td><td>0</td><td>0</td>
    </tr>
    <tr class="green">
      <th>Сумма</th>
      <td>0</td><td>0</td><td>0</td><td>0</td><td>0</td>
    </tr>
    <tr class="group-rows-top green">
      <th rowspan="4"> A (A)</th>
      <th>Фаза А</th>
      <td>0</td><td>0</td><td>0</td><td>0</td><td>0</td>
    </tr>
    <tr class="green">
      <th>Фаза B</th>
      <td>0</td><td>0</td><td>0</td><td>0</td><td>0</td>
    </tr>
    <tr class="green">
      <th>Фаза C</th>
      <td>0</td><td>0</td><td>0</td><td>0</td><td>0</td>
    </tr>
    <tr class="group-rows-bottom green">
      <th>Сумма</th>
      <td>0</td><td>0</td><td>0</td><td>0</td><td>0</td>
    </tr>
    <tr class="blue">
      <th class="text-center" colspan="2">{{ currentYear + ' ' + currentMonth }}</th>
      <td>0</td><td>0</td><td>0</td><td>0</td><td>0</td>
    </tr>
    <tr class="wrapper-text-center group-rows-bottom group-rows-top red border-bold">
      <th colspan="2"></th>
      <th>ЦЕХ</th><th>ЦЕХ Свет</th><th>АБК</th><th>УНП</th><th>ИТОГО</th>
    </tr>
    <tr v-for="ago in monthAgo" :key="ago" class="blue">
      <th colspan="2">{{yearByMonthId(currentMonthId - ago)  + ' ' + month(currentMonthId - ago)}}</th>
      <td>0</td><td>0</td><td>0</td><td>0</td><td>0</td>
    </tr>
    <tr class="group-rows-bottom ">
      <td colspan="7" class="clickable" @click="historyMonthNumber += 5">Показать ещё</td>
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
      'yearByMonthId'
    ]),
    monthAgo(){
      let arr = [...Array(this.historyMonthNumber + 1).keys()]
      arr.shift()
      return arr
    }
  },
  data() {
    return {
      historyMonthNumber: 5
    }
  }
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
.group-rows-bottom * , .group-rows-top *:first-child{
  border-bottom: 1px solid;
}
.border-bold *{
  border-width: 2px !important;
}
</style>