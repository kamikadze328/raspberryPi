<template>
  <div class="buttons-wrapper">
    <button class="clickable" @click="changePassword">Изменить пароль</button>
    <button class="clickable" @click="logout">Выйти из аккаунта</button>
  </div>
</template>

<script>
export default {
  name: "ProfileButtonsHeader",
  methods: {
    changePassword: function () {
      this.$emit('changing-password')
    },
    logout: function () {
      try {
        this.$axios({
          timeout: 5000,
          method: 'post',
          url: this.$mydata.URL.auth,
          data: {
            purpose: 'logout',
          }
        }).catch(()=>{}).then(this.localLogout)

        // eslint-disable-next-line no-empty
      }catch {}

    },
    localLogout: function () {
      this.$cookies.remove(this.$mydata.cookieName)
      this.$cookies.remove(this.$mydata.cookieName, '/', '.' + window.location.hostname)
      this.$emit('successful-logout')
      this.$router.push({name: 'auth'})

    }
  }
}
</script>

<style scoped>
.buttons-wrapper {
  position: absolute;
  top: calc(100% + 25px);
  right: 0;
  display: flex;
  flex-direction: column;
  border-radius: 4px;
  border: 1px solid #348fe2;
  background-color: white;
  justify-content: space-between;
  transition: opacity .1s ease-in-out;
  z-index: 10000;
}

.buttons-wrapper::before {
  content: "";
  background-color: #fff;
  border-top: 1px solid #348fe2;
  border-right: 1px solid #348fe2;
  transform: rotate(-45deg);
  position: absolute;
  top: -9px;
  right: 7px;
  height: 15px;
  width: 15px;
  z-index: 9999;
}

button:first-child {
  border-radius: 3px 3px 0 0;

}

button:last-child {
  border-radius: 0 0 3px 3px;

}

button {
  font-size: 14px;
  color: #2c3e50;
  padding: 5px;
  z-index: 10000;
  min-height: 30px;
}

button:hover, button:focus {
  background: #f2f4f5;
}
</style>