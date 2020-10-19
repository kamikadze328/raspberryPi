<template>
  <div id="app">
    <Header ref="header"
            :user-name="userName"
            @toggle-left-admin-panel="toggleLeftAdminPanel"
            @successful-logout="successfulLogout"
            @changing-password="changingPassword"/>
    <router-view ref="routerView"
                 @successful-login="successfulLogin"
                 @create-user="createUser"
                 @delete-user="deleteUser"
                 @reset-user-password="resetUserPassword"
                 @update-user="updateUser"/>
    <div class="alert"></div>
    <Auth v-show="isAuthMenuOpened"
          :auth-code="authMenuCode"
          :isVisible="isAuthMenuOpened"
          :user="authMenuUser"
          class="over-all"
          @close="closeAuthMenu"/>
  </div>
</template>

<script>

import Header from "@/components/Header";
import Auth from "@/views/Auth";

export default {
  name: "App",
  components: {Auth, Header},
  data() {
    return {
      isAuthMenuOpened: false,
      userName: 'carbon-dv.ru',
      defaultUserName: 'carbon-dv.ru',
      authMenuCode: -1,
      authMenuUser: null
    }
  },
  methods: {
    successfulLogin(name) {
      this.userName = name
    },
    successfulLogout() {
      this.userName = this.defaultUserName
    },
    changingPassword() {
      this.authMenuCode = this.$mydata.LOCAL_AUTH_CODES.CHANGING_PASSWORD
      this.openAuthMenu()
    },
    createUser() {
      this.authMenuCode = this.$mydata.LOCAL_AUTH_CODES.CREATE_USER
      this.openAuthMenu()
    },
    deleteUser(user) {
      this.authMenuCode = this.$mydata.LOCAL_AUTH_CODES.DELETE_USER
      this.authMenuUser = user
      this.openAuthMenu()
    },
    resetUserPassword(user) {
      this.authMenuCode = this.$mydata.LOCAL_AUTH_CODES.RESET_PASSWORD_USER
      this.authMenuUser = user
      this.openAuthMenu()
    },
    updateUser(user) {
      this.authMenuCode = this.$mydata.LOCAL_AUTH_CODES.UPDATE_USER
      this.authMenuUser = user
      this.openAuthMenu()
    },
    openAuthMenu() {
      this.isAuthMenuOpened = true
    },
    closeAuthMenu() {
      this.authMenuUser = null
      this.isAuthMenuOpened = false
    },
    toggleLeftAdminPanel() {
      this.$refs['routerView'].toggleLeftPanel()
    }
  },

  mounted() {
    this.userName = this.$mydata.isAuth() ? this.$mydata.currentName() : this.defaultUserName
  }
}
</script>

<style>
@import "assets/RobotoFonts.css";

.svg-img {
  background-repeat: no-repeat;
  background-position: center center;
}

.over-all {
  background-color: rgba(0, 0, 0, 0.7);
}

.main-container {
  max-width: 1264px;
  padding: 0 15px;
  margin: auto;
}

:focus {
  outline: none;
}

.disable-selection-text {
  -moz-user-select: none;
  -ms-user-select: none;
  -khtml-user-select: none;
  -webkit-user-select: none;
  -webkit-touch-callout: none;
}

.clickable:hover, .clickable:focus {
  cursor: pointer !important;
}

* {
  margin: 0;
  padding: 0;
}

html {
  font-size: 16px;
}

body {
  background-color: #f7f7f7;
  font-family: 'Roboto', Arial, sans-serif;
  overflow-x: hidden;
  color: #2c3e50;
}

table {
  border-spacing: 0;
  border-collapse: collapse;
  table-layout: fixed;
}

h1 {
  margin-bottom: 6px;
  font-size: 1.6rem;
  font-weight: 500;
}

a, router-link {
  color: #2c3e50;
  text-decoration: none;
}

h2 {
  margin-bottom: 15px;
  font-weight: 500;
}

input {
  font-family: 'Roboto', Arial, sans-serif;
  outline: 0;
}

button {
  background-color: transparent;
  border: 0;
}

.form-wrapper {
  width: 100%;
  height: 100%;
  position: fixed;
  top: 0;
  left: 0;
  display: flex;
  align-items: center;
  align-content: center;
  justify-content: center;
  overflow: auto;
}

.clearfix::after {
  content: "";
  display: table;
  clear: both;
}

.green {
  color: #32A932;
}

.red {
  color: #FF5B57;
}

.text-center {
  text-align: center;
}

.text-gray {
  color: #949494;
}

.response-message p {
  opacity: 0;
  visibility: hidden;
  transition: opacity .15s;
}

.visible {
  opacity: 1 !important;
  visibility: visible !important;
}

.pretty-input {
  overflow: hidden;
  background: #fff;
  border: 1px solid #fff;
  border-radius: 8px;
  font-size: 16px;
  height: 46px;
  width: 99.6%;
  opacity: 1;
  text-indent: 20px;
  transition: all .2s ease-in-out;
}

.my-button {
  border: 2px solid #348fe2;
  transition: all .3s cubic-bezier(.6, 0, .4, 1);
}

.my-button:hover, .my-button:focus, .button-focus {
  background: #348fe2;
  color: white !important;
}

.red-button {
  border-color: #ff5b57 !important;
  color: #ff5b57 !important;
}

.red-button:hover, .red-button:focus {
  background: #ff5b57 !important;
}

.yellow-button {
  border-color: #f59c1a !important;
  color: #f59c1a !important;
}

.yellow-button:hover, .yellow-button:focus {
  background: #f59c1a !important;
}

.green-button {
  border-color: #32A932 !important;
}

.green-button:hover, .green-button:focus {
  background: #32A932 !important;
}

.small-drop-menu {
  display: flex;
  flex-direction: column;
  border-radius: 4px;
  border: 1px solid #348fe2;
  background-color: #fff;
  justify-content: space-between;
  transition: opacity .1s ease-in-out;
  z-index: 1000;
}

.small-drop-menu::before {
  content: "";
  background-color: #fff;
  border-top: 1px solid #348fe2;
  border-right: 1px solid #348fe2;
  z-index: 999;
}

.small-drop-menu > * {
  color: #2c3e50;
  z-index: 1000;
}

.small-drop-menu > *:focus, .small-drop-menu > *:hover {
  background: #f2f4f5;
}

.small-drop-menu > button:first-child {
  border-radius: 3px 3px 0 0;
}

.small-drop-menu > button:last-child {
  border-radius: 0 0 3px 3px;
}

.small-drop-menu > button {
  font-size: 14px;
  padding: 5px;
  min-height: 30px;
}

.table-box {
  overflow-y: auto;
  height: 95%;
}

.admin-content-inside-container {
  display: flex;
  flex-direction: column;
  height: 100%;
}


.user-info-column {
  min-width: 140px;
}

.warning {
  position: relative;
  display: flex;
  align-items: center;
  margin-bottom: 10px;
  text-transform: none;
}

.warning > span {
  visibility: hidden;
  position: absolute;
  right: 16px;
  width: 1rem;
  height: 1rem;
  background: url("assets/warning.svg") no-repeat;
  background-size: 1rem;
  transition: opacity 0.5s ease;
  transform-style: preserve-3d;
}

.warning > span::after {
  content: attr(data-validate);
  position: absolute;
  visibility: hidden;
  opacity: 0;
  right: -5px;
  top: 50%;
  padding: 0 28px 0 4px;
  font-size: 0.9rem;
  font-weight: 500;
  color: #FF5B57;
  white-space: nowrap;
  transform: translateY(-50%) translateZ(-1px);
  transition: opacity .2s;
}

.invalid span {
  visibility: visible !important;
  opacity: 1;
  z-index: 2;
}

.invalid .pretty-input, .invalid textarea {
  border-color: #FF5B57;
}


.invalid:hover span::after {
  visibility: visible;
  opacity: 1;
}
</style>