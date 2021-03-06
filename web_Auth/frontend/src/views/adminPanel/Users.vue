<template>
  <div class="admin-content-inside-container">
    <AdmitPanelOverTable ref="overTable" v-model="inputText" :default-date-range="defaultDateRange"
                         :with-green-button="true"
                         :with-calendar="true"
                         :green-button-text="'Добавить пользователя'"
                         @green-button-click="createUser" @update-date="updateDate"/>
    <div class="table-box">
      <table>
        <thead>
        <tr>
          <th v-for="header in headers" :key="header.id" :class="{'description-header':header.id===2}">
            {{ header.name }}
          </th>
          <th v-show="!isMobile"></th>
        </tr>
        </thead>
        <tbody>
        <tr v-for="user in filteredData" v-show="!isLoading" :key="usersData.indexOf(user)" ref="mobileClickable"
            @click="onClickRow">
          <td :class="{'user-info-column': isMobile}">
            <div v-show="isMobile" ref="clickable" class="relative">
              <div class="small-drop-menu more-info-menu" style="opacity: 0; visibility: hidden">
                <button class="clickable" @click.stop="goToStats(user)">Статистика</button>
                <button class="clickable" @click.stop="updateUser(user)">Изменить пользователя</button>
                <button class="clickable" @click.stop="resetUserPassword(user)">Сбросить пароль</button>
                <button class="clickable" @click.stop="deleteUser(user)">Удалить</button>
              </div>
            </div>
            <div>{{ user.login }}</div>
            <div v-show="isMobile">{{ user.role }}</div>
            <div v-show="isSuperSmallMobile">{{ $mydata.secToDate(Math.floor(Number(user.last_session))) }}</div>
          </td>
          <td v-show="!isSuperSmallMobile">{{ $mydata.secToDate(Math.floor(Number(user.last_session))) }}</td>
          <td v-show="!isMobile">{{ user.role }}</td>
          <td>{{ Number(user.count_sessions) }}</td>
          <td>{{ $mydata.secToHms(Math.floor(Number(user.average_time_session))) }}</td>
          <td>{{ user.count_distinct_places }}</td>
          <td class="description-body">{{ user.description }}</td>
          <td v-show="!isMobile" ref="clickable" class="clickable svg-box svg-img more-icon relative"
              @click="handleMenuClick">
            <div class="small-drop-menu more-info-menu" style="opacity: 0; visibility: hidden">
              <button class="clickable" @click.stop="goToStats(user)">Статистика</button>
              <button class="clickable" @click.stop="updateUser(user)">Изменить пользователя</button>
              <button class="clickable" @click.stop="resetUserPassword(user)">Сбросить пароль</button>
              <button class="clickable" @click.stop="deleteUser(user)">Удалить</button>
            </div>
          </td>
        </tr>
        <tr v-show="isLoading">
          <td colspan="8">Загрузка...</td>
        </tr>
        <tr v-show="isNothingFound && !isLoading">
          <td colspan="8">Ничего не найдено</td>
        </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>
import AdmitPanelOverTable from "@/components/AdmitPanelOverTable";

export default {
  name: "Users",
  components: {
    AdmitPanelOverTable
  },
  data() {
    return {
      width: 0,
      defaultDateRange: 'week',
      inputText: '',
      usersData: [],
      isLoading: false,
    }
  },
  computed: {
    isMobile() {
      return this.width <= 1000
    },
    isSuperSmallMobile() {
      return this.width <= 550
    },
    headers() {
      let login, role, description, lastSession, numberSessions, avgDuration, numberPlaces, headers
      if (this.isSuperSmallMobile) {
        login = 'Пользователь'
        role = 'Роль'
        description = 'Описание'
        lastSession = 'Активность'
        numberSessions = 'Сессий'
        avgDuration = 'Ср. время'
        numberPlaces = 'Мест'
        headers = [
          {id: 0, name: login},
          {id: 4, name: numberSessions},
          {id: 5, name: avgDuration},
          {id: 6, name: numberPlaces},
          {id: 2, name: description}
        ]
      } else if (this.isMobile) {
        login = 'Пользователь'
        role = 'Роль'
        description = 'Описание'
        lastSession = 'Активность'
        numberSessions = 'Сессий'
        avgDuration = 'Ср. время'
        numberPlaces = 'Мест'
        headers = [
          {id: 0, name: login},
          {id: 3, name: lastSession},
          {id: 4, name: numberSessions},
          {id: 5, name: avgDuration},
          {id: 6, name: numberPlaces},
          {id: 2, name: description}
        ]
      } else {
        login = 'Имя'
        role = 'Роль'
        description = 'Описание'
        lastSession = 'Последний сеанс'
        numberSessions = 'Количество сеансов'
        avgDuration = 'Средняя продолжительность'
        numberPlaces = 'Количество мест'
        headers = [
          {id: 0, name: login},
          {id: 3, name: lastSession},
          {id: 1, name: role},
          {id: 4, name: numberSessions},
          {id: 5, name: avgDuration},
          {id: 6, name: numberPlaces},
          {id: 2, name: description}
        ]
      }
      return headers
    },
    filteredData() {
      return this.usersData.filter(user => {
        return user.login.toLowerCase().indexOf(this.inputText) > -1
      })
    },
    isNothingFound() {
      return this.filteredData.length === 0
    },
  },
  methods: {
    toggleMenuVisibility(elem) {
      let style = elem.style
      const isOpened = style.visibility === 'visible'
      if (isOpened === true) {
        setTimeout(() => {
          style.visibility = 'hidden'
        }, 110)
        style.opacity = '0'
      } else {
        style.visibility = 'visible'
        style.opacity = '1'
      }
    },
    onClickRow(e) {
      if (this.isMobile) {
        let row = e.target
        while (row.tagName.toLowerCase() !== 'tr')
          row = row.parentElement
        this.toggleMenuVisibility(row.firstElementChild.firstElementChild.firstElementChild)
      }
    },
    handleMenuClick(e) {
      this.toggleMenuVisibility(e.target.firstChild)
    },
    closeAll(e) {
      if (!this.isNothingFound)
        if (this.isMobile) {
          let row = e.target
          while (row && row.tagName.toLowerCase() !== 'tr')
            row = row.parentElement
          if(this.$refs['mobileClickable']) {
            this.$refs['mobileClickable'].forEach(clickable => {
              if (clickable !== row && clickable.firstElementChild.firstElementChild.firstElementChild.style.visibility === 'visible')
                this.toggleMenuVisibility(clickable.firstElementChild.firstElementChild.firstElementChild)
            })
          }
        } else {
          this.$refs['clickable'].forEach(clickable => {
            if (clickable !== e.target && clickable.firstElementChild.style.visibility === 'visible')
              this.toggleMenuVisibility(clickable.firstElementChild)
          })
        }
    },
    updateDate(min, max) {
      this.getUsers(min, max)
    },
    createUser() {
      this.$emit('create-user')
    },
    deleteUser(user) {
      this.$emit('delete-user', {id: user.id, login: user.login})
    },
    resetUserPassword(user) {
      this.$emit('reset-user-password', {id: user.id, login: user.login})
    },
    updateUser(user) {
      this.$emit('update-user', {id: user.id, login: user.login, role: user.role})
    },
    updateLocalUsersData(data) {
      this.usersData = data
      this.isLoading = false
    },
    goToStats(user) {
      this.$router.push({name: 'admin-panel-stats', params: {inputText: ('/^' + user.login + '$/')}})
    },
    getUsers(min, max) {
      if (!this.isLoading) {
        this.isLoading = true
        this.$axios({
          timeout: 30000,
          method: 'post',
          url: this.$mydata.server.URL.admin,
          data: {
            purpose: this.$mydata.server.action.GET_USERS,
            date_min: new Date(min).getTime(),
            date_max: new Date(max).getTime()
          }
        }).then(response => {
          if (response.data.error) throw response.data.error
          else {
            this.$mydata.data.users = response.data.data ? response.data.data : []
            console.log(response.data.data)
            this.updateLocalUsersData(this.$mydata.data.users)
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
    updateUsers() {
      this.$refs['overTable'].updateDates()
    },
    onResize() {
      this.width = document.body.getBoundingClientRect().width
    },
  },
  mounted() {
    document.addEventListener('click', this.closeAll)
    if (this.$mydata.data.users.length === 0 && !this.isLoading)
      this.updateUsers()
    else this.updateLocalUsersData(this.$mydata.data.users)

    this.onResize()
    window.removeEventListener('resize', this.onResize)
    window.addEventListener('resize', this.onResize)
  },
}
</script>

<style scoped>
.svg-box {
  min-width: 10px;
  width: 15px;
  height: 15px;
}

.more-icon {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' x='0px' y='0px' viewBox='0 0 384 384' %3E%3Cg%3E%3Cg%3E%3Ccircle style='fill:%23b8b8b8;' cx='192' cy='42.667' r='35'/%3E%3C/g%3E%3C/g%3E%3Cg%3E%3Cg%3E%3Ccircle style='fill:%23b8b8b8;' cx='192' cy='192' r='35'/%3E%3C/g%3E%3C/g%3E%3Cg%3E%3Cg%3E%3Ccircle style='fill:%23b8b8b8;' cx='192' cy='341.333' r='35'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
}

.relative {
  position: relative;
}

.more-info-menu {
  position: absolute;
  left: -130px;
  width: 125px;
  top: 0;
}

.more-info-menu::before {
  transform: rotate(45deg);
  position: absolute;
  right: -9px;
  top: 7px;
  height: 15px;
  width: 15px;
}

.description-header {
  text-align: left;
}

.description-body {
  max-width: 700px;
  word-wrap: break-word;
}

@media (max-width: 1000px) {
  .more-info-menu {
    left: 50px;
    top: -6px;
  }

  .more-info-menu::before {
    transform: rotate(-135deg);
    left: -9px;
    right: auto;
  }
}
</style>