<template>
  <div class="admin-content-inside-container">
    <AdmitPanelOverTable ref="overTable"
                         v-model="inputText"
                         @update-date="updateDate"
                         :with-calendar="true"/>
    <div class="table-box">
      <table>
        <thead>
        <tr>
          <th v-show="!isMobile"></th>
          <th v-for="header in headers" :key="header.id">
            <div>{{ header.name }}</div>
            <span :class="''" class="svg-box"></span>
          </th>
        </tr>
        </thead>
        <tbody>
        <template v-for="session in sortedData">
          <tr v-show="!isLoading" :key="statsData.indexOf(session)" @click="onClickRow">
            <td v-show="!isMobile" class="clickable" @click="onClickOpenClose">
              <div :class="openCloseRowClasses.closed" class="svg-img svg-box"></div>
            </td>
            <td :class="{'user-info-column': isSuperSmallMobile}">
              <div>{{ session.username }}</div>
              <div v-show="isSuperSmallMobile">{{ getStartTime(session) }}</div>
              <div v-show="isSuperSmallMobile" :title="deviceName(session.device)" class="device-cell">
                <div>{{ session.ip }}</div>
                <div :class="detectDevice(session.device)" class="svg-device-box svg-img"></div>
                <div :class="detectDeviceOC(session.device)" class="svg-device-box svg-img"></div>
              </div>
            </td>
            <td v-show="!isSuperSmallMobile">{{ getStartTime(session) }}</td>
            <td v-show="!isSuperSmallMobile">
              <div :title="deviceName(session.device)" class="device-cell">
                <div :class="detectDevice(session.device)" class="svg-device-box svg-img"></div>
                <div :class="detectDeviceOC(session.device)" class="svg-device-box svg-img"></div>
                <div>{{ session.ip }}</div>
              </div>
            </td>
            <td>{{ getCountStatsInSession(session) }}</td>
            <td>{{ getDurationTime(session) }}</td>
          </tr>
          <tr v-for="stat in session.stats" :key="statsData.indexOf(session) + '-' + session.stats.indexOf(stat)"
              :style="isLoading ? 'display: none !important;' : ''"
              class="stat-row"
              style="display: none">
            <td :colspan="isSuperSmallMobile ? '2' : (isMobile ? '4' : '5')" class="stat-refs">
              <div>{{ stat.url_name }}</div>
              <a :href="stat.url_path">{{ $mydata.getCurrentDomain() + stat.url_path }}</a>
            </td>
            <td>{{ $mydata.secToHms(stat.duration_sec) }}</td>
          </tr>
        </template>
        <tr v-show="isLoading">
          <td colspan="6">Загрузка...</td>
        </tr>
        <tr v-show="isNothingFound && !isLoading">
          <td colspan="6">Ничего не найдено</td>
        </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>
import AdmitPanelOverTable from "@/components/AdmitPanelOverTable";

export default {

  name: "Stats",
  components: {
    AdmitPanelOverTable
  },
  props: {
    inputText: {
      required: false,
      type: String,
      default: ''
    }
  },
  data() {
    return {
      width: 0,
      sortedBy: {columnId: 4, asc: false},
      isLoading: false,
      statsData: [],
      openCloseRowClasses: {
        opened: 'minus-icon',
        closed: 'plus-icon'
      },
      deviceClasses: {
        android: 'android-icon',
        ios: 'ios-icon',
        pc: 'pc-icon',
        phone: 'phone-icon',
        tablet: 'tablet-icon'
      }
    }
  },

  computed: {
    isMobile: function () {
      return this.width <= 900
    },
    isSuperSmallMobile: function () {
      return this.width <= 550
    },
    headers: function () {
      let login, dateAndTime, device, numberPages, durationSession, headers
      if (this.isSuperSmallMobile) {
        login = 'Пользователь'
        numberPages = 'Страниц'
        durationSession = 'Длительность'
        headers = [
          {id: 1, name: login},
          {id: 4, name: numberPages},
          {id: 5, name: durationSession},
        ]
      } else if (this.isMobile) {
        login = 'Пользователь'
        dateAndTime = 'Время'
        device = 'Устройство'
        numberPages = 'Страниц'
        durationSession = 'Длительность'
        headers = [
          {id: 1, name: login},
          {id: 2, name: dateAndTime},
          {id: 3, name: device},
          {id: 4, name: numberPages},
          {id: 5, name: durationSession},
        ]
      } else {
        login = 'Пользователь'
        dateAndTime = 'Дата и время'
        device = 'Устройство'
        numberPages = 'Посещено страниц'
        durationSession = 'Длительность сеанса'
        headers = [
          {id: 1, name: login},
          {id: 2, name: dateAndTime},
          {id: 3, name: device},
          {id: 4, name: numberPages},
          {id: 5, name: durationSession},
        ]
      }
      return headers
    },
    filteredData: function () {
      const regex = this.inputText.startsWith('/') && this.inputText.endsWith('/') ?
          RegExp(this.inputText.substring(1, this.inputText.length - 1).toLowerCase()) :
          null
      return this.statsData.filter(session => {
        if (regex) return regex.test(session.username.toLowerCase())
        else return session.username.toLowerCase().indexOf(this.inputText) > -1
      })
    },
    sortedData: function () {
      return [...this.filteredData].sort((a, b) => {
        return (this.sortedBy.asc ? 1 : -1) * (this.getMinStartTime(a) - this.getMinStartTime(b))
      })
    },
    isNothingFound: function () {
      return this.filteredData.length === 0
    },
  },
  methods: {
    updateDate(min, max) {
      this.getStats(min, max)
    },
    onClickOpenClose(e) {
      const isChild = e.target.classList.contains('svg-box')
      const child = isChild ? e.target : e.target.firstChild
      child.classList.toggle(this.openCloseRowClasses.closed)
      child.classList.toggle(this.openCloseRowClasses.opened)
      let nextRow = (isChild ? e.target.parentElement : e.target).parentElement.nextElementSibling
      this.toggleVisibilityStatRow(nextRow)
    },
    toggleVisibilityStatRow(row) {
      while (row.classList.contains('stat-row')) {
        row.style.display = row.style.display === 'none' ? 'table-row' : 'none'
        row = row.nextElementSibling
      }
    },
    onClickRow(e) {
      if (this.isMobile) {
        let row = e.target
        while (row.tagName.toLowerCase() !== 'tr')
          row = row.parentElement
        this.toggleVisibilityStatRow(row.nextElementSibling)
      }
    },
    detectDevice(deviceCode) {
      let deviceIconClass = this.deviceClasses.pc
      if (deviceCode >= 10 && deviceCode < 20)
        deviceIconClass = this.deviceClasses.phone
      else if (deviceCode >= 20)
        deviceIconClass = this.deviceClasses.tablet

      return deviceIconClass
    },
    deviceName(deviceCode) {
      let name = 'PC'
      if (deviceCode >= 10 && deviceCode < 20)
        name = 'Телефон'
      else if (deviceCode >= 20)
        name = 'Планшет'
      if (deviceCode % 10 === 1)
        name = name + ' iOS'
      else if (deviceCode % 10 === 2)
        name = name + ' Android'
      return name
    },
    detectDeviceOC(deviceCode) {
      let deviceIconClass = ''
      if (deviceCode % 10 === 1)
        deviceIconClass = this.deviceClasses.ios
      else if (deviceCode % 10 === 2)
        deviceIconClass = this.deviceClasses.android
      return deviceIconClass
    },

    getStartTime(session) {
      return this.$mydata.secToDate(this.getMinStartTime(session))
    },
    getMinStartTime(session) {
      return Math.min.apply(null, session.stats.map(stat => stat.start_time))
    },
    getDurationTime(session) {
      let sum = 0
      session.stats.forEach(stat => sum += stat.duration_sec)
      return this.$mydata.secToHms(sum)
    },
    getCountStatsInSession(session) {
      const uniqueURLSet = new Set()
      session.stats.forEach(stat => uniqueURLSet.add(stat.url_name))
      return uniqueURLSet.size
    },
    getStats(min, max) {
      if (!this.isLoading) {
        this.isLoading = true
        this.$axios({
          timeout: 30000,
          method: 'post',
          url: this.$mydata.server.URL.admin,
          data: {
            purpose: this.$mydata.server.action.GET_STATS,
            date_min: new Date(min).getTime(),
            date_max: new Date(max).getTime()
          }
        }).then(response => {
          if (response.data.error) throw response.data.error
          else {
            this.$mydata.data.stats = response.data.data ? response.data.data : []
            this.updateLocalStatsData(this.$mydata.data.stats)
            return response.data.data
          }
        }).catch(error => {
          console.log(error.response)
          console.log(error)
        }).finally(() => {
          this.isLoading = false
        })
      }
    },
    updateLocalStatsData(data) {
      this.statsData = data
      this.isLoading = false
    },
    onResize() {
      this.width = document.body.getBoundingClientRect().width
    },
  },
  mounted() {
    if (this.$mydata.data.stats.length === 0)
      this.$refs['overTable'].updateDates()
    else this.updateLocalStatsData(this.$mydata.data.stats)

    this.onResize()
    window.removeEventListener('resize', this.onResize)
    window.addEventListener('resize', this.onResize)
  },
}
</script>

<style scoped>

a, a:visited {
  color: #348fe2;
}

a:focus, a:hover {
  color: #115593;
}

.svg-box {
  width: 15px;
  height: 15px;
}

.svg-device-box {
  width: 20px;
  height: 20px;
}

.svg-device-box:last-child {
  margin-left: 5px;
}

.device-cell {
  display: flex;
  justify-content: space-between;
}

.android-icon {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512.007 512.007' style='enable-background:new 0 0 512.007 512.007;' %3E%3Cg%3E%3Cpath style='fill:%238BC34A;' d='M64.004,192.007c-17.664,0-32,14.336-32,32v128c0,17.664,14.336,32,32,32s32-14.336,32-32v-128 C96.004,206.343,81.668,192.007,64.004,192.007z'/%3E%3Cpath style='fill:%238BC34A;' d='M448.004,192.007c-17.664,0-32,14.336-32,32v128c0,17.664,14.336,32,32,32s32-14.336,32-32v-128 C480.004,206.343,465.668,192.007,448.004,192.007z'/%3E%3Cpath style='fill:%238BC34A;' d='M128.004,385.863c0,17.664,14.336,32,32,32v62.144c0,17.664,14.336,32,32,32s32-14.336,32-32 v-62.144h64v62.144c0,17.664,14.336,32,32,32s32-14.336,32-32v-62.144c17.664,0,32-14.336,32-32v-192h-256V385.863z'/%3E%3Cpath style='fill:%238BC34A;' d='M335.876,60.711l28.48-34.528c5.632-6.816,4.672-16.896-2.144-22.528 c-6.848-5.6-16.896-4.672-22.528,2.144l-31.136,37.728c-16.064-7.264-33.76-11.52-52.544-11.52c-19.04,0-36.96,4.416-53.184,11.904 L172.516,6.023c-5.536-6.88-15.584-8.032-22.496-2.496c-6.88,5.536-8,15.584-2.496,22.496l28.096,35.136 c-28.832,23.456-47.616,58.784-47.616,98.848h256C384.004,119.687,364.996,84.167,335.876,60.711z M224.004,112.007 c-8.832,0-16-7.168-16-16s7.168-16,16-16s16,7.168,16,16S232.836,112.007,224.004,112.007z M288.004,112.007 c-8.832,0-16-7.168-16-16s7.168-16,16-16s16,7.168,16,16S296.836,112.007,288.004,112.007z'/%3E%3C/g%3E%3C/svg%3E%0A");
}

.pc-icon {
  background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 58 58' xmlns='http://www.w3.org/2000/svg'%3E%3Cg id='Page-1' fill='none' fill-rule='evenodd'%3E%3Cg id='016---PC-and-Monitor' fill-rule='nonzero'%3E%3Cpath id='Shape' d='m30 58h26c1.1032019-.0032948 1.9967052-.8967981 2-2v-54c-.0032948-1.10320187-.8967981-1.9967052-2-2h-26c-1.1032019.0032948-1.9967052.89679813-2 2v54c0 1.1045695.8954305 2 2 2z' fill='%23285680'/%3E%3Cpath id='Rectangle-path' d='m16 47h12v8h-12z' fill='%23547580'/%3E%3Cpath id='Shape' d='m44 43v1c0 2.209139-1.790861 4-4 4h-36c-2.209139 0-4-1.790861-4-4v-1z' fill='%2384b5cb'/%3E%3Cpath id='Shape' d='m44 18v25h-44v-25c0-2.209139 1.790861-4 4-4h36c2.209139 0 4 1.790861 4 4z' fill='%232980ba'/%3E%3Cpath id='Shape' d='m34 56c-.0081672 1.1011688-.8988312 1.9918328-2 2h-20c-1.1032019-.0032948-1.9967052-.8967981-2-2 .0081672-1.1011688.8988312-1.9918328 2-2h20c1.1032019.0032948 1.9967052.8967981 2 2z' fill='%2384b5cb'/%3E%3Crect id='Rectangle-path' fill='%232c3e50' height='4' rx='1' width='22' x='32' y='5'/%3E%3Ccircle id='Oval' cx='51' cy='16' fill='%23fb7b76' r='2'/%3E%3Ccircle id='Oval' cx='51' cy='24' fill='%234fba6f' r='2'/%3E%3Cg fill='%232c3e50'%3E%3Cpath id='Shape' d='m38 55c-.5522847 0-1-.4477153-1-1v-2c0-.5522847.4477153-1 1-1s1 .4477153 1 1v2c0 .5522847-.4477153 1-1 1z'/%3E%3Cpath id='Shape' d='m42 55c-.5522847 0-1-.4477153-1-1v-2c0-.5522847.4477153-1 1-1s1 .4477153 1 1v2c0 .5522847-.4477153 1-1 1z'/%3E%3Cpath id='Shape' d='m46 55c-.5522847 0-1-.4477153-1-1v-2c0-.5522847.4477153-1 1-1s1 .4477153 1 1v2c0 .5522847-.4477153 1-1 1z'/%3E%3Cpath id='Shape' d='m50 55c-.5522847 0-1-.4477153-1-1v-2c0-.5522847.4477153-1 1-1s1 .4477153 1 1v2c0 .5522847-.4477153 1-1 1z'/%3E%3Cpath id='Shape' d='m54 55c-.5522847 0-1-.4477153-1-1v-2c0-.5522847.4477153-1 1-1s1 .4477153 1 1v2c0 .5522847-.4477153 1-1 1z'/%3E%3C/g%3E%3Cpath id='Shape' d='m36.82 14c-.17 6.09-1.96 24-36.82 24v-20c0-2.209139 1.790861-4 4-4z' fill='%233b97d3'/%3E%3Cpath id='Shape' d='m4 23c-.4043959-.0000863-.76893405-.2437275-.92367798-.6173454-.15474393-.373618-.06922994-.8036603.21667798-1.0896546l4-4c.39237889-.3789722 1.01608478-.3735524 1.40181858.0121814.38573379.3857338.39115363 1.0094397.01218142 1.4018186l-4 4c-.18749273.1875494-.44180519.2929434-.707.293z' fill='%23fff'/%3E%3Cpath id='Shape' d='m4 29c-.4043959-.0000863-.76893405-.2437275-.92367798-.6173454-.15474393-.373618-.06922994-.8036603.21667798-1.0896546l10-10c.3923789-.3789722 1.0160848-.3735524 1.4018186.0121814s.3911536 1.0094397.0121814 1.4018186l-10 10c-.18749273.1875494-.44180519.2929434-.707.293z' fill='%23fff'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
}

.phone-icon {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512' style='enable-background:new 0 0 512 512;' %3E%3Cg%3E%3Cpath style='fill:%23fff;' d='M136.533,503.467h238.933c18.85,0,34.133-15.283,34.133-34.133v-17.067H102.4v17.067 C102.4,488.184,117.683,503.467,136.533,503.467z'/%3E%3Cpath style='fill:%23fff;' d='M409.6,42.667c0-18.85-15.283-34.133-34.133-34.133H136.533c-18.85,0-34.133,15.283-34.133,34.133 v17.067h307.2V42.667z'/%3E%3C/g%3E%3Crect x='102.4' y='59.733' style='fill:%23fff;' width='307.2' height='392.533'/%3E%3Cg%3E%3Cpath d='M375.467,0H136.533c-23.552,0.026-42.641,19.115-42.667,42.667v426.667 c0.026,23.552,19.115,42.641,42.667,42.667h238.933c23.552-0.026,42.641-19.115,42.667-42.667V42.667 C418.108,19.115,399.019,0.026,375.467,0z M136.533,17.067h238.933c14.14,0,25.6,11.46,25.6,25.6V51.2H110.933v-8.533 C110.933,28.527,122.394,17.067,136.533,17.067z M401.067,443.733H110.933V68.267h290.133V443.733z M375.467,494.933H136.533 c-14.14,0-25.6-11.46-25.6-25.6V460.8h290.133v8.533C401.067,483.473,389.606,494.933,375.467,494.933z'/%3E%3Cpath d='M221.867,42.667h17.067c4.71,0,8.533-3.823,8.533-8.533s-3.823-8.533-8.533-8.533h-17.067 c-4.71,0-8.533,3.823-8.533,8.533S217.156,42.667,221.867,42.667z'/%3E%3Cpath d='M264.533,42.667h34.133c4.71,0,8.533-3.823,8.533-8.533s-3.823-8.533-8.533-8.533h-34.133 c-4.71,0-8.533,3.823-8.533,8.533S259.823,42.667,264.533,42.667z'/%3E%3Cpath d='M281.6,469.333h-51.2c-4.71,0-8.533,3.823-8.533,8.533s3.823,8.533,8.533,8.533h51.2 c4.71,0,8.533-3.823,8.533-8.533S286.31,469.333,281.6,469.333z'/%3E%3C/g%3E%3C/svg%3E ");
}

.tablet-icon {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 60 60' style='enable-background:new 0 0 60 60;' %3E%3Cg%3E%3Cg%3E%3Cpath d='M55.822,9H4.178C1.875,9,0,10.875,0,13.178v33.644C0,49.125,1.875,51,4.178,51h51.644C58.125,51,60,49.125,60,46.822 V13.178C60,10.875,58.125,9,55.822,9z M58,46.822C58,48.023,57.023,49,55.822,49H4.178C2.977,49,2,48.023,2,46.822V13.178 C2,11.977,2.977,11,4.178,11h51.644C57.023,11,58,11.977,58,13.178V46.822z'/%3E%3Cpath d='M4,45h52V13H4V45z M6,15h48v28H6V15z'/%3E%3Cpath d='M32,46h-4c-0.552,0-1,0.448-1,1s0.448,1,1,1h4c0.552,0,1-0.448,1-1S32.552,46,32,46z'/%3E%3Cpath d='M9,23c0.256,0,0.512-0.098,0.707-0.293l4-4c0.391-0.391,0.391-1.023,0-1.414s-1.023-0.391-1.414,0l-4,4 c-0.391,0.391-0.391,1.023,0,1.414C8.488,22.902,8.744,23,9,23z'/%3E%3Cpath d='M9,28c0.256,0,0.512-0.098,0.707-0.293l2-2c0.391-0.391,0.391-1.023,0-1.414s-1.023-0.391-1.414,0l-2,2 c-0.391,0.391-0.391,1.023,0,1.414C8.488,27.902,8.744,28,9,28z'/%3E%3Cpath d='M12.29,22.29C12.11,22.48,12,22.73,12,23s0.11,0.52,0.29,0.71C12.48,23.89,12.74,24,13,24s0.52-0.11,0.71-0.29 C13.89,23.52,14,23.27,14,23c0-0.26-0.11-0.52-0.29-0.71C13.33,21.92,12.66,21.92,12.29,22.29z'/%3E%3Cpath d='M14.293,21.707C14.488,21.902,14.744,22,15,22s0.512-0.098,0.707-0.293l3-3c0.391-0.391,0.391-1.023,0-1.414 s-1.023-0.391-1.414,0l-3,3C13.902,20.684,13.902,21.316,14.293,21.707z'/%3E%3Cpath d='M17.293,22.293l-9,9c-0.391,0.391-0.391,1.023,0,1.414C8.488,32.902,8.744,33,9,33s0.512-0.098,0.707-0.293l9-9 c0.391-0.391,0.391-1.023,0-1.414S17.684,21.902,17.293,22.293z'/%3E%3Cpath d='M19.29,20.29C19.11,20.48,19,20.74,19,21s0.11,0.52,0.29,0.71C19.48,21.89,19.74,22,20,22s0.52-0.11,0.71-0.29 C20.89,21.52,21,21.26,21,21s-0.11-0.52-0.29-0.71C20.34,19.92,19.67,19.92,19.29,20.29z'/%3E%3Cpath d='M22.293,17.293l-1,1c-0.391,0.391-0.391,1.023,0,1.414C21.488,19.902,21.744,20,22,20s0.512-0.098,0.707-0.293l1-1 c0.391-0.391,0.391-1.023,0-1.414S22.684,16.902,22.293,17.293z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E%0A");
}

.ios-icon {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512' style='enable-background:new 0 0 512 512;' %3E%3Cg%3E%3Cg%3E%3Cpath d='M457.081,362.584c-14.003-7.049-47.42-28.262-55.26-72.862c-0.717-4.079-4.601-6.802-8.687-6.088 c-4.079,0.717-6.805,4.605-6.088,8.686c8.997,51.185,47.102,75.493,63.188,83.613c-4.334,12.556-14.158,37.284-31.447,62.524 c-18.375,26.867-39.2,57.318-70.12,57.904c-31.048,0.514-42.266-19.777-82.011-19.777c-39.752,0-52.398,19.222-81.827,20.388 c-27.702,1.06-49.923-27.66-72.124-59.749c-41.025-59.34-73.555-169.266-31.209-242.766 c17.707-30.733,47.025-51.569,80.435-57.165c4.085-0.684,6.842-4.551,6.158-8.637s-4.548-6.845-8.637-6.158 c-37.849,6.34-71.001,29.839-90.956,64.472c-45.263,78.562-12.643,194.408,31.87,258.788 c18.316,26.475,45.829,66.248,83.046,66.244c33.553,0,46.154-20.414,83.243-20.416c36.163,0,46.793,20.438,82.29,19.774 c38.656-0.733,62.814-36.053,82.22-64.43c18.326-26.754,28.72-52.962,33.283-66.204 C466.864,373.714,463.697,365.915,457.081,362.584z'/%3E%3C/g%3E%3C/g%3E%3Cg%3E%3Cg%3E%3Cpath d='M363.875,14.506c-0.188-8.95-8.289-15.691-17.175-14.331c-58.293,8.905-100.445,65.247-98.593,114.719 c0.299,7.943,6.883,14.251,14.771,14.251c0.14,0,0.28-0.002,0.42-0.006C317.845,127.596,365.066,70.92,363.875,14.506z M263.091,114.138c-0.874-25.268,12.409-49.994,25.186-64.767c14.729-17.245,39.083-31.046,60.605-34.365 C349.781,62.719,309.703,112.647,263.091,114.138z'/%3E%3C/g%3E%3C/g%3E%3Cg%3E%3Cg%3E%3Cpath d='M447.658,165.468c-22.62-28.835-54.825-43.14-91.835-45.9c-42.603-3.154-78.92,22.862-95.694,22.862 c-13.729,0-37.375-14.517-62.942-19.715c-4.062-0.828-8.019,1.796-8.845,5.855c-0.825,4.059,1.796,8.019,5.855,8.845 c25.02,5.088,47.74,20.016,65.932,20.016c21.872,0,55.367-25.811,94.582-22.904c32.643,2.436,61.016,14.63,81.035,40.061 c-13.025,9.383-43.764,35.909-49.606,80.457c-0.538,4.107,2.355,7.873,6.461,8.412c0.331,0.043,0.66,0.064,0.985,0.064 c3.708,0,6.931-2.75,7.428-6.525c5.09-38.814,32.158-62.078,43.558-70.277C451.318,181.869,452.966,172.233,447.658,165.468z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E ");
}

.plus-icon {
  margin: 0 4px;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' x='0px' y='0px' viewBox='0 0 512 512' fill='%23b8b8b8'%3E%3Cg%3E%3Cpath d='M492,236H276V20c0-11.046-8.954-20-20-20c-11.046,0-20,8.954-20,20v216H20c-11.046,0-20,8.954-20,20s8.954,20,20,20h216 v216c0,11.046,8.954,20,20,20s20-8.954,20-20V276h216c11.046,0,20-8.954,20-20C512,244.954,503.046,236,492,236z'/%3E%3C/g%3E%3C/svg%3E");
}

.minus-icon {
  margin: 0 4px;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' x='0px' y='0px' viewBox='0 0 512 512' fill='%23b8b8b8'%3E%3Cg%3E%3Cg%3E%3Cpath d='M492,236H20c-11.046,0-20,8.954-20,20c0,11.046,8.954,20,20,20h472c11.046,0,20-8.954,20-20S503.046,236,492,236z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
}

.search-input {
  width: 250px;
  border: #e0e0dc solid 1px;
}

.stat-row td {
  padding-top: 0 !important;
  padding-bottom: 0 !important;
}

.stat-refs {
  padding-left: 45px !important;
}

</style>