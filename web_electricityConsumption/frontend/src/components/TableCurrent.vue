<template>
  <table>
    <template v-for="group in tagGroups" :key="group.id">
      <tr v-for="tag in group.tags"
          :key="tag.id"
          :class="{'group-rows-top': group.tags.indexOf(tag)===0 ,
            'group-rows-bottom': tagGroups.indexOf(group)===tagGroups.length-1 && group.tags.indexOf(tag)===group.tags.length-1}"
          class="green">
        <th v-if="group.tags.indexOf(tag)===0"
            :class="{'border-bottom': tagGroups.indexOf(group)===tagGroups.length-1 && group.tags.indexOf(tag)===0}"
            :rowspan="group.tags.length"> {{ group.name }}
        </th>
        <th>{{ tag.name }}</th>
        <td v-for="place in places" :key="place.id"> {{ currentDataByGroupPlaceIDs(tag.id, place.id) }}</td>
        <td>{{ sumByGroup(tag.id) }}</td>
      </tr>

    </template>

    <tr class="blue">
      <th colspan="2">{{ monthCurrDateStrByDate(months[0]) }}</th>
      <td v-for="place in places" :key="place.id">{{ sumByPlace(place.id, months[0]) }}</td>
      <td>{{ sumAllPlacesByDate(months[0]) }}</td>
    </tr>
    <tr class="wrapper-text-center group-rows-bottom group-rows-top border-top-bottom-bold">
      <th colspan="2"></th>
      <th v-for="place in places" :key="place.id" :style="{'background-color': place.color}">{{ place.name }}</th>
      <th :style="{'background-color': 'rgba(255, 91, 87, .4)'}">ИТОГО</th>
    </tr>
    <tr v-for="date in historyMonths" :key="date" class="blue">
      <th colspan="2">{{ monthCurrDateStrByDate(date) }}</th>
      <td v-for="place in places" :key="place.id">{{ sumByPlace(place.id, date) }}</td>
      <td>{{ sumAllPlacesByDate(date) }}</td>
    </tr>
    <tr class="group-rows-bottom group-rows-top table-button">
      <th></th>
      <th class="clickable text-center" colspan="3" @click="addMonthNum">Показать ещё</th>
      <th :class="{clickable: isPossibleToToResetMonthNum}" class="text-center" colspan="3"
          @click="resetMonthNum">
        {{ isPossibleToToResetMonthNum ? 'Свернуть' : '' }}
      </th>
    </tr>
  </table>
</template>

<script>

import {mapMutations, mapGetters, mapState} from "vuex";

export default {
  name: "CurrentTable",
  data() {
    const historyMonthNumMin = 6
    return {
      historyMonthNum: historyMonthNumMin,
      historyMonthNumMin,
      historyMonthNumStep: 6,
    }
  },
  computed: {
    ...mapState([
      'monthCurrId',
      'yearCurr',
      'MONTH_NUM_MIN',
      'MONTH_NUM_STEP',
      'month_num',
    ]),
    ...mapGetters([
      'monthCurrDate',
      'monthCurrDateStrByDate',
      'monthCurrDateStr',
      'tagGroups',
      'places',
      'currentDataByGroupPlaceIDs',
      'sumByPlace',
      'sumAllPlacesByDate',
      'sumCurrByPlace',
      'sumByGroup',
      'isPossibleToToResetMonthNum',
      'months'
    ]),
    historyMonths(){
      const historyMonths = [...this.months]
      historyMonths.shift()
      return historyMonths
    }
  },
  methods: {
    addMonthNum(){
      this.increaseMonthNum()
      this.$nextTick(() => window.scrollTo(0,document.body.scrollHeight))
    },
    ...mapMutations(['increaseMonthNum', 'resetMonthNum']),
  },
}
</script>

<style scoped>
table {
  border-spacing: 0;
  text-align: center;
}

.text-center, .wrapper-text-center * {
  text-align: center;
}

.red th {
  background-color: rgba(255, 91, 87, .4);
}

.blue th {
  background-color: rgba(52, 143, 226, .4);
}

.yellow th {
  background-color: rgba(255, 217, 0, .4);
}

.green th {
  background-color: rgba(50, 169, 50, .4);
}

th {
  text-align: left;
}

td {
  width: 100px;
}

.group-rows-top * {
  border-top: 1px solid;
}

table {
  border-left: 1px solid;
  border-right: 1px solid;
}

.group-rows-bottom *, .border-bottom {
  border-bottom: 1px solid;
}

.border-top-bottom-bold * {
  border-top-width: 2px !important;
  border-bottom-width: 2px !important;
}

tr > :last-child {
  border-left: 1px solid;
}

</style>