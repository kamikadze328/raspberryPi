<template>
  <div class="buttons-wrapper small-drop-menu">
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
          url: this.$mydata.server.URL.auth,
          data: {
            purpose: 'logout',
          }
        }).catch(() => {
        }).then(this.localLogout)

        // eslint-disable-next-line no-empty
      } catch {
        this.localLogout()
      }

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
}

.buttons-wrapper::before {
  transform: rotate(-45deg);
  position: absolute;
  top: -9px;
  right: 7px;
  height: 15px;
  width: 15px;
}
</style>