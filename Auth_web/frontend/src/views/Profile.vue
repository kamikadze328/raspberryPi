<template>
  <div class="profile-container">
    <div class="main-container">
      <section v-show="isAdmin" class="admin-section">
        <router-link class="href red-button my-button" to="admin">Панель управления</router-link>
      </section>
      <section class="user-section">
        <a class="href my-button" href="/syncdata/">
          <div class="href-text">Syncdata</div>
        </a>
        <a class="href my-button" href="/graphs/">
          <div class="href-text">Графики</div>
        </a>
        <a class="href my-button" href="/unp/">
          <div class="href-text">UNP</div>
        </a>
        <a class="href my-button" href="/unp/info_teg.html">
          <div class="href-text">Информация об устройствах</div>
        </a>
        <a class="href my-button" href="/unp/info_dev_err.html">
          <div class="href-text">Ошибки устройств</div>
        </a>
      </section>
    </div>
  </div>

</template>

<script>

export default {
  name: "Profile",
  date() {
    return {
      isAdmin: false
    }
  },
  computed: {
    isAdmin: function () {
      return this.$cookies.isKey('isAdmin') ? this.$cookies.get('isAdmin') === 'true' : false
    }
  },
  beforeRouteEnter(to, from, next) {
    next(vm => {
      vm.$mydata.isAuth()
    })
  },
}
</script>

<style scoped>
.profile-container {

}

.user-section {
  display: flex;
  justify-content: space-between;
  flex-flow: row wrap;
}

.href {
  min-width: 330px;
  max-width: 330px;
  height: 100px;
  text-align: center;
  border-radius: 5px;
  padding: 20px 5px;
  margin: 10px 10px;
  font-size: 1.6rem;
  display: flex;
  justify-content: center;
  align-items: center;
}


@media (max-width: 750px) {
  .href {
    margin: 10px auto;
    min-width: calc(100% - 25px);
    padding: 5px 10px;
    font-size: 1.2rem;
  }
}
</style>