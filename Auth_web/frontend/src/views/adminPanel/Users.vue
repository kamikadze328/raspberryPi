<template>
  <div class="stat-container">
    <AdmitPanelOverTable ref="overTable" v-model="inputText" :default-date-range="defaultDateRange"
                         :with-add-button="true"
                         @add-button-click="createUser" @update-date="updateDate"/>
    <table>
      <thead>
      <tr>
        <td v-for="header in headers" :key="header.id">
          {{ header.name }}
        </td>
        <td></td>
      </tr>
      </thead>
      <tbody>
      <tr v-for="user in filteredData" v-show="!isLoading" :key="usersData.indexOf(user)">
        <td>{{ user.login }}</td>
        <td>{{ user.role }}</td>
        <td>{{ user.description }}</td>
        <td>{{ $mydata.secToDate(Math.floor(Number(user.last_session))) }}</td>
        <td>{{ Number(user.count_sessions) }}</td>
        <td>{{ $mydata.secToHms(Math.floor(Number(user.average_time_session))) }}</td>
        <td>{{ user.count_distinct_places }}</td>
        <td ref="clickable" class="clickable svg-box svg-img more-icon" @click="handleMenuClick">
          <div ref="dropMenu" class="small-drop-menu more-info-menu" style="opacity: 0; visibility: hidden">
            <button class="clickable" @click.stop="goToStats(user)">Статистика</button>
            <button class="clickable" @click.stop="changeRole(user)">Изменить роль</button>
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
      defaultDateRange: 'week',
      headers: [
        {id: 0, name: 'Имя'},
        {id: 1, name: 'Роль'},
        {id: 2, name: 'Описание'},
        {id: 3, name: 'Последний сеанс'},
        {id: 4, name: 'Количество сеансов'},
        {id: 5, name: 'Средняя продолжительность'},
        {id: 6, name: 'Количество мест'}
      ],
      inputText: '',
      usersData: [],
      isLoading: false,
    }
  },
  computed: {
    filteredData: function () {
      return this.usersData.filter(user => {
        return user.login.toLowerCase().indexOf(this.inputText) > -1
      })
    },
    isNothingFound: function () {
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
    handleMenuClick(e) {
      this.toggleMenuVisibility(e.target.firstChild)
    },
    closeAll(e) {
      if (!this.isNothingFound)
        this.$refs['clickable'].forEach(clickable => {
          if (clickable !== e.target && clickable.firstChild.style.visibility === 'visible')
            this.toggleMenuVisibility(clickable.firstChild)
        })

    },
    updateDate(min, max) {
      this.get_users(min, max)
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
    changeRole(user) {
      this.$emit('change-user-role', {id: user.id, login: user.login, role: user.role})
    },
    updateLocalUsersData(data) {
      console.log(data)
      this.usersData = data
      this.isLoading = false
    },
    goToStats(user) {
      this.$router.push({name: 'admin-panel-stats', params: {inputText: ('/^' + user.login + '$/')}})
    },
    get_users(min, max) {
      if (!this.isLoading) {
        this.isLoading = true
        this.$axios({
          timeout: 30000,
          method: 'post',
          url: this.$mydata.server.URL.admin,
          data: {
            purpose: 'users',
            date_min: new Date(min).getTime(),
            date_max: new Date(max).getTime()
          }
        }).then(response => {
          if (response.data.error) throw response.data.error
          else {
            console.log(response)
            this.$mydata.data.users = response.data.data
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
    }
  },
  mounted() {
    console.log(this.$mydata.data.users.length)
    document.addEventListener('click', this.closeAll)
    if (this.$mydata.data.users.length === 0 && !this.isLoading)
      this.updateUsers()
    else this.updateLocalUsersData(this.$mydata.data.users)
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
  position: relative;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' x='0px' y='0px' viewBox='0 0 384 384' %3E%3Cg%3E%3Cg%3E%3Ccircle style='fill:%23b8b8b8;' cx='192' cy='42.667' r='35'/%3E%3C/g%3E%3C/g%3E%3Cg%3E%3Cg%3E%3Ccircle style='fill:%23b8b8b8;' cx='192' cy='192' r='35'/%3E%3C/g%3E%3C/g%3E%3Cg%3E%3Cg%3E%3Ccircle style='fill:%23b8b8b8;' cx='192' cy='341.333' r='35'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
}

.more-info-menu {
  position: absolute;
  left: -130px;
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
</style>