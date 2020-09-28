<template>
  <div class="stat-container">
    <table>
      <thead>
      <tr>
        <td></td>
        <td>Имя</td>
        <td>Устройство</td>
        <td>Дата и время</td>
        <td>Длительность сеанса</td>
        <td>Посещено страниц</td>
      </tr>
      </thead>
      <tbody>
      <tr v-for="session in statsData" :key="statsData.indexOf(session)">
        <td>
          <div class="svg-img svg-size plus-icon clickable"></div>
        </td>
        <td>{{ session.username }}</td>
        <td>{{ session.device }}</td>
        <td>{{ getStartTime(session) }}</td>
        <td>{{ getDurationTime(session) }}</td>
        <td>{{ getCountStatsInSession(session) }}</td>
      </tr>
      </tbody>
    </table>
  </div>
</template>

<script>
export default {
  name: "UserStats",
  data() {
    return {
      statsData: []
    }
  },
  mounted() {
    if (!this.$mydata.stats.data)
      this.get_stats()
    this.updateLocalStatsData()
  },

  methods: {
    secToHms(sec_num) {
      let hours = Math.floor(sec_num / 3600);
      let minutes = Math.floor((sec_num - (hours * 3600)) / 60);
      let seconds = sec_num - (hours * 3600) - (minutes * 60);

      if (hours > 0)
        if (hours < 10)
          hours = "0" + hours;
      hours = (hours > 0) ? (hours + ':') : ""

      if (minutes < 10)
          minutes = "0" + minutes;
      minutes += ':'

      if (seconds < 10)
        seconds = "0" + seconds;

      return hours + minutes + seconds;
    },
    dateToLocaleStr(date) {
      const options = {
        year: '2-digit',
        month: '2-digit',
        day: '2-digit',
        timezone: 'UTC',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
      }
      return new Date(date).toLocaleString("ru", options)
    },
    getStartTime(session) {
      return this.dateToLocaleStr(Math.min.apply(null, session.stats.map(stat => stat.start_time)) * 1000)
    },
    getDurationTime(session) {
      let sum = 0
      session.stats.forEach(stat => sum += stat.duration_sec)
      return this.secToHms(sum)
    },
    getCountStatsInSession(session) {
      return session.stats.length
    },
    get_stats() {
      this.$axios({
        timeout: 30000,
        method: 'post',
        url: this.$mydata.URL.getStats,
        data: this.$mydata.dateMainMax
      }).then(response => {
        if (response.data.error) throw response.data.error
        else {
          console.log(response)
          this.$mydata.stats.data = JSON.parse(response.data.data)
          this.updateLocalStatsData()
          return JSON.parse(response.data.data)
        }
      }).catch(error => {
        console.log(error.response)
        console.log(error)
      })
    },
    updateLocalStatsData() {
      this.statsData = this.$mydata.stats.data
    }
  }
}
</script>

<style scoped>
.stat-container {
  display: flex;
  flex-direction: column;
}

.svg-size {
  width: 15px;
  height: 15px;
}

.plus-icon {
  margin: 0 4px;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' x='0px' y='0px' viewBox='0 0 512 512' fill='%23b8b8b8'%3E%3Cg%3E%3Cpath d='M492,236H276V20c0-11.046-8.954-20-20-20c-11.046,0-20,8.954-20,20v216H20c-11.046,0-20,8.954-20,20s8.954,20,20,20h216 v216c0,11.046,8.954,20,20,20s20-8.954,20-20V276h216c11.046,0,20-8.954,20-20C512,244.954,503.046,236,492,236z'/%3E%3C/g%3E%3C/svg%3E");
}
</style>