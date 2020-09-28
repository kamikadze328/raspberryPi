<template>
<div id="app">
  <Header @successful-logout="successfulLogout"
          @changing-password="changingPassword"
          :user-name="userName"/>
  <router-view @successful-login="successfulLogin"/>
  <div class="alert"></div>
  <Auth v-show="isChangingPassword"
        :auth-code="$mydata.LOCAL_AUTH_CODES.CHANGING_PASSWORD"
        @close="isChangingPassword = false"
        class="over-all"/>
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
          isChangingPassword: false,
          userName: 'carbon-dv.ru',
          defaultUserName: 'carbon-dv.ru'
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
          this.isChangingPassword = true
        }
      },

      mounted() {
        this.userName = this.$mydata.isAuth() ? this.$mydata.currentName() : this.defaultUserName
      }
    }
</script>

<style>
.svg-img{
  background-repeat: no-repeat;
  background-position: center center;
}
  .over-all{
    background-color: rgba(0, 0, 0, 0.7);
  }
  .main-container{
    max-width: 1264px;
    padding: 0 15px;
    margin: auto;
  }
    @import "assets/RobotoFonts.css";
    :focus {outline: none;}
    .disable-selection-text {
      -moz-user-select: none;
      -ms-user-select: none;
      -khtml-user-select: none;
      -webkit-user-select: none;
      -webkit-touch-callout: none;
    }
    .clickable:hover, .clickable:focus{
      cursor: pointer!important;
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
</style>
