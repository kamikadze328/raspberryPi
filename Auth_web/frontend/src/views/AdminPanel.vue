<template>
  <div class="admin-container">
    <div ref="leftPanel" class="admin-left-panel">
      <div class="left-panel-top">
        <router-link :to="{name:'admin-panel-stats'}">
          Статистика
        </router-link>
        <router-link :to="{name: 'admin-panel-users'}">
          Пользователи
        </router-link>
        <router-link :to="{name: 'admin-panel-roles'}">
          Роли
        </router-link>
      </div>
      <div class="left-panel-bottom">
        <router-link :to="{name: 'profile'}">К ссылкам</router-link>
      </div>
    </div>
    <div ref="adminContent" class="admin-content">
      <router-view ref="routerView"
                   @create-user="$emit('create-user')"
                   @delete-user="user => $emit('delete-user', user)"
                   @reset-user-password="user => $emit('reset-user-password', user)"
                   @update-user="user => $emit('update-user', user)"/>
    </div>
  </div>
</template>

<script>
export default {
  name: "AdminPanel",
  data(){
    return {
    width: 0
    }
  },
  computed: {
    isMobile: function () {
      return this.width <= 1200
    },
  },
  watch: {
    isMobile: function (val){
      if(!val)
        this.$refs['leftPanel'].style.setProperty('left', '', '');
    }
  },
  methods: {
    toggleLeftPanel() {
      const left = this.$refs['leftPanel'].style.left
      if (left === '') this.openMenu()
      else left === '0px' ? this.closeMenu() : this.openMenu()
    },
    openMenu() {
      this.$refs['leftPanel'].style.setProperty('left', '0', 'important');
    },
    closeMenu() {
      this.$refs['leftPanel'].style.setProperty('left', '-190px', 'important');
    },
    closeAll(e) {
      if (this.$refs['leftPanel'] && this.$refs['leftPanel'] !== e.target && this.$refs['leftPanel'].style && this.$refs['leftPanel'].style.left === '0px')
        this.closeMenu()
    },
    onResize() {
      this.width = document.body.getBoundingClientRect().width
    },
  },
  mounted() {
    this.$refs['leftPanel'].classList.add('opened')
    document.addEventListener('click', this.closeAll)
    if (this.$route.name === 'admin-panel')
      this.$router.push({name: 'admin-panel-users'})

    this.onResize()
    window.removeEventListener('resize', this.onResize)
    window.addEventListener('resize', this.onResize)
  },
}
</script>

<style scoped>


.component-fade-enter-active, .component-fade-leave-active {
  transition: opacity .2s ease;
}

.admin-container {
  display: flex;
  position: fixed;
  top: 50px;
  height: calc(100% - 50px);
  width: 100%;
}

.admin-left-panel {
  z-index: 10000;
  text-transform: uppercase;
  font-weight: 500;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  width: 190px;
  position: relative;
  left: -170px;
  background-color: #d0e3f3;
  height: 100%;
  -webkit-transition: left .2s linear;
  -moz-transition: left .2s linear;
  -ms-transition: left .2s linear;
  -o-transition: left .2s linear;
  transition: left .2s linear;
}

.opened {
  left: 0 !important;
}

.admin-left-panel a {
  color: #115593;
  padding: 5px 0 5px 12px;
  border-left: 3px solid transparent;
}

.router-link-exact-active {
  border-left: 4px solid #348fe2 !important;
  font-weight: bold;

}

.router-link-exact-active, .admin-left-panel a:focus, .admin-left-panel a:hover {
  color: #348fe2 !important;
}

.admin-content {
  width: 100% !important;
  box-sizing: border-box;
  padding: 15px 20px;
  overflow: auto;
  position: relative;
  -webkit-transition: left .2s linear;
  transition: left .2s linear;
}

.admin-content >>> table {
  box-sizing: border-box;
  border: solid #e0e0dc;
  border-width: 1px 0 0 1px;
}

.admin-content >>> table thead {
  text-transform: uppercase;
  font-weight: 500;
}

.admin-content >>> table td, .admin-content >>> table th {
  border-width: 0 1px 1px 0;
  border: solid #e0e0dc;
  padding: 6px 5px;
}

.left-panel-top, .left-panel-bottom {
  display: flex;
  flex-direction: column;
}

.left-panel-top {
  margin-top: 10px
}

.left-panel-bottom {
  margin-bottom: 10px
}


@media (max-width: 1200px) {
  .admin-left-panel {
    left: -190px !important;
    position: absolute;
    font-size: 1.2rem;
  }

}

</style>