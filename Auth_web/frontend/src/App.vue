<template>
  <div id="app">
    <Header ref="header"
            :user-name="userName"
            @toggle-left-admin-panel="toggleLeftAdminPanel"
            @successful-logout="successfulLogout"
            @changing-password="changingPassword"/>
    <router-view @successful-login="successfulLogin"
                 @create-user="createUser"
                 @delete-user="deleteUser"
                 @reset-user-password="resetUserPassword"
                 @@change-user-role="changeUserRole"
                  ref="routerView"/>
    <div class="alert"></div>
    <Auth v-show="isAuthMenuOpened"
          :auth-code="authMenuCode"
          :user="authMenuUser"
          class="over-all"
          @close="isAuthMenuOpened = false"/>
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
    createUser(){
      this.authMenuCode = this.$mydata.LOCAL_AUTH_CODES.CREATE_USER
      this.openAuthMenu()
    },
    deleteUser(user){
      this.authMenuCode = this.$mydata.LOCAL_AUTH_CODES.DELETE_USER
      this.authMenuUser = user
      this.openAuthMenu()
    },
    resetUserPassword(user){
      this.authMenuCode = this.$mydata.LOCAL_AUTH_CODES.RESET_PASSWORD_USER
      this.authMenuUser = user
      this.openAuthMenu()
    },
    changeUserRole(user){
      this.authMenuCode = this.$mydata.LOCAL_AUTH_CODES.CHANGE_USER_ROLE
      this.authMenuUser = user
      this.openAuthMenu()
    },
    openAuthMenu(){
      this.isAuthMenuOpened = true
    },
    toggleLeftAdminPanel(){
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
.my-button{
  border: 2px solid #348fe2;
  transition: all .3s cubic-bezier(.6, 0, .4, 1);
}
.my-button:hover, .my-button:focus, .button-focus{
  background: #348fe2;
  color: white !important;
}
.red-button{
  border-color: #ff5b57 !important;
  color: #ff5b57 !important;
}

.red-button:hover, .red-button:focus {
  background: #ff5b57 !important;
}
.green-button{
  border-color: #32A932 !important;
}

.green-button:hover, .green-button:focus {
  background: #32A932 !important;
}
.small-drop-menu{
  display: flex;
  flex-direction: column;
  border-radius: 4px;
  border: 1px solid #348fe2;
  background-color: #fff;
  justify-content: space-between;
  transition: opacity .1s ease-in-out;
  z-index: 10000;
}
.small-drop-menu::before{
  content: "";
  background-color: #fff;
  border-top: 1px solid #348fe2;
  border-right: 1px solid #348fe2;
  z-index: 9999;
}
.small-drop-menu > *{
  color: #2c3e50;
  z-index: 10000;
}
.small-drop-menu > *:focus, .small-drop-menu > *:hover{
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
</style>