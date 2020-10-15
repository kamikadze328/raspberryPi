<template>
  <div class="under-table-container">
    <div class="default-box">
      <div class="search-label">
        <label>
          <input v-model="inputText" class="pretty-input search-input" placeholder="Поиск по имени"
                 type="text" @input="handleInput">
        </label>
        <div class="show-additional-buttons svg-box svg-img clickable" @click="toggleAllButtons"></div>

      </div>

      <div ref="dateButtonBox" class="date-button-box">
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
      <div ref="calendarBox" class="calendar-box">
        <flatpickr v-model="dateStr" :config="dateConfig" class="flatpickr"/>
      </div>
    </div>
    <button v-show="withAddButton" class="pretty-input my-button clickable green-button add-button"
            @click="$emit('add-button-click')">Добавить
      пользователя
    </button>

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
    withAddButton: {
      type: Boolean,
      required: false,
      default: false
    },
    data: String
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
  width: 220px;
  text-indent: 0;
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

@media (max-width: 500px) {
  .under-table-container {
    flex-direction: column;
    align-items: flex-start;
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