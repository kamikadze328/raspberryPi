<template>
  <div class="under-table-container">
    <div class="default-box">
      <div class="search-label">
        <label>
          <input v-model="inputText" :placeholder="'Поиск по ' + searchByPlaceHolder" class="pretty-input search-input"
                 type="text" @input="handleInput">
        </label>
        <div v-show="withCalendar" class="show-additional-buttons svg-box svg-img clickable"
             @click="toggleAllButtons"></div>

      </div>

      <div v-show="withCalendar" ref="dateButtonBox" class="date-button-box">
        <button :class="{'button-focus': isYesterdayDates}" class="pretty-input my-button clickable"
                @click="setYesterdayDates">Вчера
        </button>
        <button :class="{'button-focus': isTodayDates}" class="pretty-input my-button clickable"
                @click="setTodayDates">Сегодня
        </button>
        <button :class="{'button-focus': isWeekAgoDates}" class="pretty-input my-button clickable"
                @click="setWeekAgoDates">Неделя
        </button>
      </div>
      <div v-show="withCalendar" ref="calendarBox" class="calendar-box">
        <flatpickr v-model="dateStr" :config="dateConfig" class="flatpickr"/>
      </div>
    </div>
    <div class="left-buttons-under-table">
      <button class="pretty-input my-button clickable update-button svg-img" @click="updateData"></button>
      <button v-show="withRedButton" class="pretty-input my-button clickable red-button add-button"
              @click="$emit('red-button-click')">{{ redButtonText }}
      </button>
      <button v-show="withYellowButton" class="pretty-input my-button clickable yellow-button add-button"
              @click="$emit('yellow-button-click')">{{ yellowButtonText }}
      </button>
      <button v-show="withGreenButton" class="pretty-input my-button clickable green-button add-button"
              @click="$emit('green-button-click')">{{ greenButtonText }}
      </button>
    </div>

  </div>
</template>

<script>
import Vueflatpickr from "vue-flatpickr-component";
import "flatpickr/dist/flatpickr.min.css";
import flatpickr from "flatpickr";
import {Russian} from "flatpickr/dist/l10n/ru.js";

flatpickr.localize(Russian);

export default {
  name: "AdmitPanelOverTable",
  components: {
    flatpickr: Vueflatpickr,
  },
  props: {
    value: {
      type: String,
      required: true
    },
    defaultDateRange: {
      type: String,
      required: false,
      default: 'today'
    },
    withGreenButton: {
      type: Boolean,
      required: false,
      default: false
    },
    withYellowButton: {
      type: Boolean,
      required: false,
      default: false
    },
    withRedButton: {
      type: Boolean,
      required: false,
      default: false
    },
    withCalendar: {
      type: Boolean,
      required: false,
      default: false
    },
    searchByPlaceHolder: {
      type: String,
      required: false,
      default: 'имени'
    },
    greenButtonText: {
      type: String,
      required: false,
      default: ''
    },
    yellowButtonText: {
      type: String,
      required: false,
      default: ''
    },
    redButtonText: {
      type: String,
      required: false,
      default: ''
    },
  },
  watch: {
    defaultDateRange: function (val) {
      this.confirmDefaultDateRange(val)
    }
  },
  computed: {
    isYesterdayDates() {
      return new Date(this.date.min).getTime() === new Date(this.yesterdayDate()).getTime()
          && new Date(this.date.max).getTime() === new Date(this.todayDate()).getTime()
    },
    isTodayDates() {
      return new Date(this.date.min).getTime() === new Date(this.todayDate()).getTime()
          && new Date(this.date.max).getTime() === new Date(this.tomorrowDate()).getTime()
    },
    isWeekAgoDates() {
      return new Date(this.date.min).getTime() === new Date(this.weekAgoDate()).getTime()
          && new Date(this.date.max).getTime() === new Date(this.tomorrowDate()).getTime()
    },
    dateStr: {
      get: function () {
        return `${this.beautyDateStr(this.date.min)} — ${this.beautyDateStr(this.date.max)}`
      },
      set: function (dates) {
        dates = dates.split(' — ')
        if (dates.length > 1) {
          const convert = (date) => new Date(date).getTime() + new Date(date).getTimezoneOffset() * 60000
          const min = convert(dates[0])
          const max = convert(dates[1])
          this.setMinMaxDates(min, max)
          this.updateDates()
        }
      }
    },
  },
  data() {
    const dateTomorrow = new Date(new Date(new Date().setDate(new Date().getDate() + 1)).setHours(0, 0, 0, 0))

    return {
      inputText: this.value,
      date: {
        min: new Date().setHours(0, 0, 0, 0),
        max: dateTomorrow
      },
      dateConfig: {
        altInput: true,
        altFormat: "F j, Y",
        maxDate: dateTomorrow,
        mode: 'range'
      },
    }
  },
  methods: {
    handleInput(e) {
      this.$emit('input', e.target.value)
    },
    setMinMaxDates(min, max) {
      if (min !== null && min !== undefined && max !== null && max !== undefined) {
        this.date.min = new Date(min)
        this.date.max = new Date(max)
      }
    },
    tomorrowDate() {
      return new Date(new Date(new Date().setDate(new Date().getDate() + 1)).setHours(0, 0, 0, 0))
    },
    todayDate() {
      return new Date(new Date().setHours(0, 0, 0, 0))
    },
    yesterdayDate() {
      return new Date(new Date(new Date - 86400000).setHours(0, 0, 0, 0))
    },
    weekAgoDate() {
      return new Date(new Date(new Date - 518400000).setHours(0, 0, 0, 0))
    },

    setYesterdayDates() {
      this.setMinMaxDates(this.yesterdayDate(), this.todayDate())
    },
    setTodayDates() {
      this.setMinMaxDates(this.todayDate(), this.tomorrowDate())
    },
    setWeekAgoDates() {
      this.setMinMaxDates(this.weekAgoDate(), this.tomorrowDate())
    },
    beautyDateStr: function (date) {
      const YYYY = new Intl.DateTimeFormat('ru', {year: 'numeric'}).format(date)
      const MM = new Intl.DateTimeFormat('ru', {month: '2-digit'}).format(date)
      const DD = new Intl.DateTimeFormat('ru', {day: '2-digit'}).format(date)
      return `${YYYY}-${MM}-${DD}`
    },
    confirmDefaultDateRange() {
      const val = this.defaultDateRange
      if (val === 'today')
        this.setTodayDates()
      else if (val === 'week')
        this.setWeekAgoDates()
      else if (val === 'yesterday')
        this.setYesterdayDates()
    },
    updateDates() {
      this.$emit('update-date', this.date.min, this.date.max)
    },
    toggleAllButtons(e) {
      e.target.classList.toggle('closed-addition-buttons')
      if (this.$refs['calendarBox'].style.display === '') this.openAllButtons()
      else this.$refs['calendarBox'].style.display === 'none' ? this.openAllButtons() : this.closeAllButtons()
    },
    openAllButtons() {
      this.$refs['calendarBox'].style.display = 'block'
      this.$refs['dateButtonBox'].style.display = 'flex'
    },
    closeAllButtons() {
      this.$refs['calendarBox'].style.display = 'none'
      this.$refs['dateButtonBox'].style.display = 'none'
    },
    updateData() {
      if (this.withCalendar) this.updateDates()
      else this.$emit('update-data')
    }
  },
  mounted() {
    this.confirmDefaultDateRange()
  }
}
</script>
<style>
.flatpickr {
  min-width: 310px;
  width: 350px;
  text-align: center;
  font-size: 1rem;
  line-height: 30px;
  padding: 0;
  border-radius: 8px;
  z-index: 1;
  border: #e0e0dc solid 1px;
  height: 46px;
}

.flatpickr {
  transition: all .3s cubic-bezier(.6, 0, .4, 1);
}

.flatpickr:hover, .flatpickr:focus {
  background: #348fe2;
  color: white;
  cursor: pointer !important;

}

</style>
<style scoped>
.under-table-container {
  display: flex;
  align-items: flex-end;
  margin-bottom: 10px;
}

.under-table-container > :first-child {
  margin-right: 10px;
}

.default-box {
  display: flex;
  align-items: center;
  justify-content: flex-start;
}

.date-button-box {
  display: flex;
  justify-content: center;
  margin: 0 10px;
}

.date-button-box > button {
  border-radius: 0;
  border: #e0e0dc solid 1px;
  width: 100px;
  text-indent: 0;
}

.date-button-box > button:first-child {
  border-radius: 8px 0 0 8px;
}

.date-button-box > button:last-child {
  border-radius: 0 8px 8px 0;
}

.add-button {
  padding: 0 15px;
  white-space: nowrap;
}

.show-additional-buttons {
  height: 25px;
  width: 25px;
  margin-left: 20px;
  transform: rotate(0);
  display: none;
  -webkit-animation: .3s linear forwards;
  animation: .3s linear forwards;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' x='0px' y='0px' viewBox='0 0 451.847 451.847'%3E%3Cg%3E%3Cpath d='M225.923,354.706c-8.098,0-16.195-3.092-22.369-9.263L9.27,151.157c-12.359-12.359-12.359-32.397,0-44.751 c12.354-12.354,32.388-12.354,44.748,0l171.905,171.915l171.906-171.909c12.359-12.354,32.391-12.354,44.744,0 c12.365,12.354,12.365,32.392,0,44.751L248.292,345.449C242.115,351.621,234.018,354.706,225.923,354.706z'/%3E%3C/g%3E%3C/svg%3E%0A");
}


.search-label {
  display: flex;
  align-items: center;
}

.closed-addition-buttons {
  transform: rotate(180deg);
}

.left-buttons-under-table {
  display: flex;
}

.left-buttons-under-table > * {
  margin-left: 10px;
  text-indent: 0;
  min-width: min-content;
}

.left-buttons-under-table > *:first-child {
  margin-left: 0;
}

.update-button {
  width: 46px;
  min-width: 46px;
  background-repeat: no-repeat !important;
  background-position: center center !important;
  background-image: url("data:image/svg+xml,%3Csvg viewBox='-2 0 512 512' fill='%23348fe2' width='28px' height='28px' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='m45.390625 363.96875c-10.210937 4.234375-21.90625-.625-26.132813-10.820312-12.777343-30.828126-19.257812-63.511719-19.257812-97.148438 0-140.394531 113.652344-253.933594 254-253.933594 59.765625 0 119.546875 20.886719 167.804688 63.375v-45.441406c0-11.046875 8.957031-20 20-20 11.046874 0 20 8.953125 20 20v95.402344c.601562 11.421875-8.640626 21.089844-20 21.089844h-96.519532c-11.046875 0-20-8.953126-20-20 0-11.046876 8.953125-20 20-20h51.285156c-37.949218-33.902344-87.601562-54.425782-142.570312-54.425782-118.289062 0-214 95.699219-214 213.933594 0 28.351562 5.453125 55.886719 16.210938 81.832031 4.226562 10.207031-.613282 21.90625-10.820313 26.136719zm443.351563-205.117188c-4.230469-10.207031-15.929688-15.046874-26.132813-10.820312-10.207031 4.230469-15.046875 15.933594-10.820313 26.136719 10.757813 25.945312 16.210938 53.480469 16.210938 81.832031 0 118.234375-95.710938 213.933594-214 213.933594-54.96875 0-104.621094-20.523438-142.570312-54.425782h51.285156c11.046875 0 20-8.957031 20-20 0-11.046874-8.953125-20-20-20h-96.519532c-11.335937 0-20.605468 9.578126-20 21.089844v95.402344c0 11.046875 8.953126 20 20 20 11.042969 0 20-8.953125 20-20v-45.441406c48.257813 42.488281 108.039063 63.375 167.804688 63.375 140.347656 0 254-113.539063 254-253.933594 0-33.636719-6.480469-66.320312-19.257812-97.148438zm0 0'/%3E%3C/svg%3E");
}

.update-button:focus, .update-button:hover {
  background-image: url("data:image/svg+xml,%3Csvg viewBox='-2 0 512 512' fill='%23fff' width='28px' height='28px' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='m45.390625 363.96875c-10.210937 4.234375-21.90625-.625-26.132813-10.820312-12.777343-30.828126-19.257812-63.511719-19.257812-97.148438 0-140.394531 113.652344-253.933594 254-253.933594 59.765625 0 119.546875 20.886719 167.804688 63.375v-45.441406c0-11.046875 8.957031-20 20-20 11.046874 0 20 8.953125 20 20v95.402344c.601562 11.421875-8.640626 21.089844-20 21.089844h-96.519532c-11.046875 0-20-8.953126-20-20 0-11.046876 8.953125-20 20-20h51.285156c-37.949218-33.902344-87.601562-54.425782-142.570312-54.425782-118.289062 0-214 95.699219-214 213.933594 0 28.351562 5.453125 55.886719 16.210938 81.832031 4.226562 10.207031-.613282 21.90625-10.820313 26.136719zm443.351563-205.117188c-4.230469-10.207031-15.929688-15.046874-26.132813-10.820312-10.207031 4.230469-15.046875 15.933594-10.820313 26.136719 10.757813 25.945312 16.210938 53.480469 16.210938 81.832031 0 118.234375-95.710938 213.933594-214 213.933594-54.96875 0-104.621094-20.523438-142.570312-54.425782h51.285156c11.046875 0 20-8.957031 20-20 0-11.046874-8.953125-20-20-20h-96.519532c-11.335937 0-20.605468 9.578126-20 21.089844v95.402344c0 11.046875 8.953126 20 20 20 11.042969 0 20-8.953125 20-20v-45.441406c48.257813 42.488281 108.039063 63.375 167.804688 63.375 140.347656 0 254-113.539063 254-253.933594 0-33.636719-6.480469-66.320312-19.257812-97.148438zm0 0'/%3E%3C/svg%3E");
}

@media (max-width: 500px) {
  .under-table-container {
    flex-direction: column;
    align-items: flex-start;
    min-height: 104px;
    overflow-x: auto;
  }

  .under-table-container > :first-child {
    margin-bottom: 10px;
  }
}

@media (max-width: 1300px) {
  .under-table-container {
    justify-content: space-between;
  }

  .default-box {
    flex-direction: column;
    align-items: flex-start;
  }

  .date-button-box {
    margin: 10px 0;
    display: none;
  }

  .calendar-box, .date-button-box {
    display: none;
  }

  .show-additional-buttons {
    display: block;
  }

}
</style>