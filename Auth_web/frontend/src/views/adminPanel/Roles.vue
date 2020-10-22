<template>
  <div class="admin-content-inside-container">
    <AdmitPanelOverTable ref="overTable" v-model="inputText"
                         :green-button-text="greenButtonText"
                         :red-button-text="'Отмена'"
                         :search-by-place-holder="'названию'"
                         :with-green-button="true"
                         :with-red-button="withRedButton"
                         :with-yellow-button="withYellowButton"
                         :yellow-button-text="'Изменить роль'"
                         @green-button-click="onClickGreenButton"
                         @red-button-click="cancelAllModes"
                         @yellow-button-click="isChangingMode = !!activeRole"
                         @update-data="getRolesAndUrls"/>
    <div class="roles-box">
      <div class="role-names-box" ref="roleNamesBox"
      :class="isOpenedListRoles ? 'open-role-names-box' : 'close-role-names-box'">
        <div v-for="role in filteredData" v-show="!isLoading && !(isChangingMode && changedActiveRole.id === role.id && !isSuperSmallMobile)"
             :key="role.id"
             :class="{'active-role-name':activeRole && activeRole.id === role.id}"
             :title="role.description"
             class="clickable role-name"
             @click="e => chooseRole(e, role)">
          {{ role.name }}
        </div>
        <div v-show="(isAddingNewRole || isChangingMode) && !isSuperSmallMobile" class="role-name active-role-name">
          <label :class="{'invalid':!newRoleNameValid}" class="warning">
            <input v-model="changedActiveRole.name" class="pretty-input new-role-name" maxlength="50" minlength="3"
                   placeholder="Название"
                   type="text">
            <span :data-validate="errors.newRoleName"></span>
          </label>
        </div>
        <div v-show="isNothingFound && !isLoading">Ничего не найдено</div>
        <div v-show="isLoading">Загрузка...</div>

      </div>
      <div class="roles-table-box" ref="rolesTableBox">
        <div v-show="isSuperSmallMobile" class="mobile-menu">
          <div v-show="!isOpenedListRoles" class="svg-img btn-open-role-names-box clickable" @click="isMayBeOpenedListRoles = true"></div>
          <div v-if="isAddingNewRole || isChangingMode" class="role-name active-role-name mobile-role-name mobile-input">
            <label :class="{'invalid':!newRoleNameValid}" class="warning">
              <input v-model="changedActiveRole.name" class="pretty-input new-role-name" maxlength="50" minlength="3"
                     placeholder="Название"
                     type="text">
              <span :data-validate="errors.newRoleName"></span>
            </label>
          </div>
          <div v-else class="role-name active-role-name mobile-role-name">{{ activeRole ? activeRole.name : '' }}</div>

        </div>
        <div class="response-message">
          <p ref="message" :class="response.status ? 'green' : 'red'" class="visible">
            {{ response.message }}</p>
        </div>
        <label v-show="(isAddingNewRole || isChangingMode) && !isLoading" :class="{'invalid':!newRoleDescriptionValid}"
               class="warning">
            <textarea v-model="changedActiveRole.description" class="role-description pretty-input" maxlength="200"
                      minlength="3"
                      placeholder="Описание роли"></textarea>
          <span :data-validate="errors.newRoleDescription"></span>
        </label>
        <div v-show="!isAddingNewRole && !isChangingMode && !isLoading" class="role-description">
          {{ activeRole ? activeRole.description : '' }}
        </div>
        <table v-show="!isLoading && ((activeRole && !isNothingFound) || isAddingNewRole)" class="permissions-box">
          <thead>
          <tr>
            <th>URL</th>
            <th>Чтение</th>
            <th>Запись</th>
          </tr>
          </thead>
          <tbody>
          <tr v-for="url in urlsData" :key="url.id">
            <td>{{ url.name }}</td>
            <td>
              <label>
                <input v-model="getPermissionByUrlId(url.id).r" :checked="getReadPermissionByUrlId(url.id)"
                       :disabled="!isChangingMode && !isAddingNewRole"
                       class="permission-checkbox" type="checkbox">
              </label>
            </td>
            <td>
              <label>
                <input v-model="getPermissionByUrlId(url.id).w" :checked="getWritePermissionByUrlId(url.id)"
                       :disabled="!isChangingMode && !isAddingNewRole"
                       class="permission-checkbox" type="checkbox">
              </label>
            </td>
          </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script>
import AdmitPanelOverTable from "@/components/AdmitPanelOverTable";

export default {
  name: "Roles",
  components: {AdmitPanelOverTable},
  data() {
    return {
      width: 0,
      inputText: '',
      rolesData: [],
      urlsData: [],
      isLoading: false,
      isChangingMode: false,
      isAddingNewRole: false,
      activeRole: null,
      changedActiveRole: {
        id: null,
        name: '',
        permissions: [],
        description: ''
      },
      response: {
        status: null,
        message: ''
      },
      newRoleDescriptionValid: true,
      newRoleNameValid: true,
      errors: {
        newRoleDescription: '',
        newRoleName: ''
      },
      isWaitingServer: false,
      isMayBeOpenedListRoles: true,
    }
  },
  computed: {
    isOpenedListRoles: function () {
      return this.isSuperSmallMobile ? this.isMayBeOpenedListRoles : true
    },
    isSuperSmallMobile: function () {
      return this.width <= 500
    },
    filteredData: function () {
      return this.rolesData.filter(role => {
        return role.name.toLowerCase().indexOf(this.inputText) > -1
      })
    },
    isNothingFound: function () {
      return this.filteredData.length === 0
    },
    greenButtonText: function () {
      if (this.isWaitingServer) return 'Ожидайте...'
      return this.isChangingMode || this.isAddingNewRole ? 'Сохранить' : 'Добавить роль'
    },
    isActiveDefaultRole: function () {
      return this.activeRole && this.$mydata.DEFAULT_ROLE_IDs.includes(this.activeRole.id)
    },
    withRedButton: function () {
      return (this.isChangingMode || this.isAddingNewRole) && !this.isActiveDefaultRole
    },
    withYellowButton: function () {
      return !this.isChangingMode && !!this.activeRole && !this.isActiveDefaultRole
    }
  },
  watch: {
    activeRole: function (val) {
      if (val) this.cancelAllModes()
    },
    isOpenedListRoles: function (val) {
      console.log('here')
      if(this.isSuperSmallMobile) {
        if (val) {
          this.$refs['roleNamesBox'].style.display = 'block'
          this.$refs['rolesTableBox'].style.display = 'none'
        } else {
          setTimeout(() => this.$refs['roleNamesBox'].style.display = 'none', 200)
          setTimeout(() => this.$refs['rolesTableBox'].style.display = 'block', 250)
        }
      }
    },
    isSuperSmallMobile: function (val){
      if(!val){
        this.$refs['roleNamesBox'].style.display = 'block'
        this.$refs['rolesTableBox'].style.display = 'block'
      }
    }
  },

  methods: {
    cancelAllModes() {
      this.isChangingMode = false
      this.isAddingNewRole = false
      this.clearForms()
      if (this.activeRole)
        this.changedActiveRole = JSON.parse(JSON.stringify(this.activeRole))
    },
    clearForms() {
      this.newRoleDescriptionValid = true
      this.newRoleNameValid = true
      this.errors.newRoleName = ''
      this.errors.newRoleDescription = ''
      this.response.message = ''
    },
    onClickGreenButton() {
      this.isChangingMode ? this.updateActiveRole() : this.addNewRole()
    },
    updateActiveRole() {
      if (this.checkForms()) {
        this.updateRoleRequest().then(ret => {
          if (ret) {
            this.activeRole = JSON.parse(JSON.stringify(this.changedActiveRole))
            this.isChangingMode = false
            this.getRolesAndUrls()
          }
        })
      }
    },
    checkForms() {
      const name = this.changedActiveRole.name
      const description = this.changedActiveRole.description
      if (name.length < 3) {
        this.newRoleNameValid = false
        this.errors.newRoleName = 'Слишном коротко'
      } else if (name.length > 50) {
        this.newRoleNameValid = false
        this.errors.newRoleName = 'Слишном длинно'
      } else this.newRoleNameValid = true

      if (description.length < 3) {
        this.newRoleDescriptionValid = false
        this.errors.newRoleDescription = 'Слишном коротко'
      } else if (description.length > 200) {
        this.newRoleDescriptionValid = false
        this.errors.newRoleDescription = 'Слишном длинно'
      } else this.newRoleDescriptionValid = true
      return this.newRoleDescriptionValid && this.newRoleNameValid
    },
    addNewRole() {
      if (this.isAddingNewRole) {
        if (this.checkForms()) {
          this.createRoleRequest().then(ret => {
            if (ret) {
              this.changedActiveRole.id = Number(ret.role_id)
              this.activeRole = JSON.parse(JSON.stringify(this.changedActiveRole))
              this.isAddingNewRole = false
              this.getRolesAndUrls()
            }
          })
        }
      } else {
        this.clearForms()
        this.activeRole = null
        this.changedActiveRole.name = ''
        this.changedActiveRole.description = ''
        this.changedActiveRole.permissions.splice(0, this.changedActiveRole.permissions.length)
        this.urlsData.forEach(url => this.changedActiveRole.permissions.push({
          r: false,
          w: false,
          url_id: url.id
        }), this)
        this.isAddingNewRole = true
      }
    },
    getReadPermissionByUrlId(urlId) {
      return this.changedActiveRole.permissions.length ? this.changedActiveRole.permissions.find(p => p.url_id === urlId).r : null
    },
    getWritePermissionByUrlId(urlId) {
      return this.changedActiveRole.permissions.length ? this.changedActiveRole.permissions.find(p => p.url_id === urlId).w : null
    },
    getPermissionByUrlId(urlId) {
      return this.changedActiveRole.permissions.length ? this.changedActiveRole.permissions.find(p => p.url_id === urlId) : {
        r: null,
        w: null
      }
    },
    chooseRole(e, role) {
      this.isMayBeOpenedListRoles = false
      this.activeRole = role
      this.changedActiveRole = JSON.parse(JSON.stringify(role))
    },
    createRoleRequest() {
      const url = this.$mydata.server.URL.admin
      const data = {
        purpose: this.$mydata.server.action.CREATE_ROLE,
        name: this.changedActiveRole.name,
        description: this.changedActiveRole.description,
        permissions: JSON.stringify(this.changedActiveRole.permissions)
      }
      return this.request(url, data)

    },
    updateRoleRequest() {
      const url = this.$mydata.server.URL.admin
      const data = {
        purpose: this.$mydata.server.action.UPDATE_ROLE,
        role_id: this.activeRole.id,
        name: this.changedActiveRole.name,
        description: this.changedActiveRole.description,
        permissions: JSON.stringify(this.changedActiveRole.permissions)
      }
      return this.request(url, data)
    },
    request(url, data) {
      this.isWaitingServer = true
      return this.$axios({
        timeout: 5000,
        method: 'post',
        url,
        data
      }).then((response) => {
        this.isWaitingServer = false
        console.log(response)
        if (response.data) {
          if (response.data.error) {
            throw response.data.error
          } else if (response.data.code !== 200) {
            this.setMessage('Что-то пошло не так', false)
            return false
          } else {
            this.setMessage(response.data.message, true)
            console.log(response.data.message)
            return response.data.data ? response.data.data : true
          }
        } else throw response
      }).catch(error => {
        this.isWaitingServer = false
        console.log(error)
        console.log(error.response)
        if (error.code) {
          this.response.status = error.code;
          this.setMessage(error.message, false)
        } else {
          //js error
          if (error.message === 'NetworkError') {
            this.setMessage('Нет соединения с интернетом', false)
          } else {
            const message = error.response.status === 403 ? 'Запрещено' : 'Ошибка сервера'
            this.setMessage(message, false)
          }
        }
        return false
      })
    },
    setMessage(message, isOK) {
      console.log(message)
      this.response.status = isOK
      this.response.message = message
      const elem = this.$refs['message']
      elem.classList.remove('visible')
      setTimeout(() => elem.classList.add('visible'), 150)
    },
    getRolesAndUrls() {
      this.cancelAllModes()
      if (!this.isLoading) {
        this.isLoading = true
        this.$axios({
          timeout: 30000,
          method: 'post',
          url: this.$mydata.server.URL.admin,
          data: {
            purpose: 'roles',
          }
        }).then(response => {
          if (response.data.error) throw response.data.error
          else {
            this.$mydata.data.users = response.data.data ? response.data.data : []
            if (response.data.data) {
              this.$mydata.data.roles = response.data.data.roles ? response.data.data.roles : []
              this.$mydata.data.urls = response.data.data.urls ? response.data.data.urls : []
              this.rolesData = this.$mydata.data.roles
              this.urlsData = this.$mydata.data.urls
              return response.data.data
            } else return false
          }
        }).catch(error => {
          console.log(error.response)
          console.log(error)
        }).finally(() => {
          this.isLoading = false
        })
      }
    },
    onResize() {
      this.width = document.body.getBoundingClientRect().width
    },
  },

  mounted() {
    console.log('mounted')
    if(this.isSuperSmallMobile)
      this.$refs['rolesTableBox'].style.display = 'none'

    if (this.$mydata.data.roles.length === 0 && !this.isLoading)
      this.getRolesAndUrls()
    else {
      this.urlsData = this.$mydata.data.urls
      this.rolesData = this.$mydata.data.roles
    }

    this.onResize()
    window.removeEventListener('resize', this.onResize)
    window.addEventListener('resize', this.onResize)
  },
  beforeRouteLeave(to, from, next) {
    this.cancelAllModes()
    next()
  }
}
</script>

<style scoped>
.roles-box {
  height: 90%;
  display: flex;
  flex-direction: row;
  overflow-y: auto;
}


.role-names-box {
  overflow-y: auto;
  padding-right: 10px;
  max-width: 180px;
  min-width: 180px;
}

.role-names-box > * {
  padding: 5px 10px;
  font-weight: 400;
}

.role-name {
  border-right: 3px solid transparent;
  text-transform: uppercase;
  word-break: break-all;
}

.role-name:focus, .role-name:hover {
  background-color: #daeefd;
  color: #348fe2;
}

.role-names-box > .active-role-name:focus, .role-names-box > .active-role-name:hover {
  font-weight: bold;
}

.active-role-name {
  font-weight: bold;
  color: #348fe2 !important;
  border-right: 3px solid #348fe2;
}

.permission-checkbox {
  width: 20px;
  height: 20px;
}

.role-description {
  font-size: 1.25rem;
  height: 3rem;
  max-height: 3rem;
  margin-bottom: 10px;
  text-overflow: ellipsis;
  overflow: hidden;
  display: -webkit-box;
  -webkit-box-orient: vertical;
  -webkit-line-clamp: 2;
  word-break: break-word;
}

.new-role-name {
  height: 30px;
  text-indent: 10px;
}

textarea.role-description {
  text-indent: 0;
  padding: 10px;
}

.warning {
  margin: 0;
}

.roles-table-box {
  overflow-y: auto;
  margin-right: 10px;
}

.mobile-role-name {
  border-right: 3px solid transparent;
  font-size: 1.2rem;
}

.mobile-menu {
  display: flex;
  align-items: center;
  margin-bottom: 10px;
}

.btn-open-role-names-box {
  width: 25px;
  min-width: 25px;
  height: 25px;
  min-height: 25px;
  margin-right: 5px;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' x='0px' y='0px' viewBox='0 0 492.004 492.004'%3E%3Cg%3E%3Cpath d='M382.678,226.804L163.73,7.86C158.666,2.792,151.906,0,144.698,0s-13.968,2.792-19.032,7.86l-16.124,16.12 c-10.492,10.504-10.492,27.576,0,38.064L293.398,245.9l-184.06,184.06c-5.064,5.068-7.86,11.824-7.86,19.028 c0,7.212,2.796,13.968,7.86,19.04l16.124,16.116c5.068,5.068,11.824,7.86,19.032,7.86s13.968-2.792,19.032-7.86L382.678,265 c5.076-5.084,7.864-11.872,7.848-19.088C390.542,238.668,387.754,231.884,382.678,226.804z'/%3E%3C/g%3E%3C/svg%3E");
}

.close-role-names-box{
  animation: close-menu .2s linear forwards;
}
.open-role-names-box{
  animation: open-menu .2s linear forwards;
}
@keyframes close-menu {
  0%   { transform: translate(0, 0); }
  100% { transform: translate(-210px, 0); }
}
@keyframes open-menu {
  0%   { transform: translate(-210px, 0); }
  100% { transform: translate(0, 0); }
}
.mobile-input{
  width: 100%;
}
.mobile-input label input{
  height: 40px;
}

@media (max-width: 500px) {
  .role-description {
    font-size: 1rem;
    max-height: 5rem;
    -webkit-line-clamp: 4;
    overflow-y: auto;
  }
}
</style>