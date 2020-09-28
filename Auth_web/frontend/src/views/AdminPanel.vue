<template>
  <div class="admin-container">
    <div ref="leftPanel" class="admin-left-panel">
      <div class="left-panel-top" >
        <router-link :to="{name:'admin-panel-stats'}">
          Стаистика
        </router-link>
        <router-link :to="{name: 'admin-panel-list-users'}">
          Пользователи
        </router-link>
      </div>
      <div class="left-panel-bottom">
        <router-link :to="{name: 'profile'}">К ссылкам</router-link>
      </div>
    </div>
    <div class="admin-content">
      <router-view/>
    </div>
  </div>
</template>

<script>
export default {
  name: "AdminPanel",
  mounted() {
    this.$refs['leftPanel'].classList.add('opened')
  }
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
  text-transform: uppercase;
  font-weight: 500;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  flex: 0 0 170px;
  position: relative;
  left: -170px;
  background-color: rgba(52, 143, 226, .2);
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
  border-left: 3px solid #348fe2 !important;
  font-weight: bold;

}

.router-link-exact-active, .admin-left-panel a:focus, .admin-left-panel a:hover {
  color: #348fe2 !important;
}

.admin-content {
  width: 100%;
  box-sizing: border-box;
  padding: 15px 20px;
}
.admin-content >>> table{
  box-sizing: border-box;
  border: solid #e0e0dc;
  border-width: 1px 0 0 1px;
}
.admin-content >>> table thead{
 text-transform: uppercase;
  font-weight: 500;
}
.admin-content >>> table td, .admin-content >>> table th{
  border-width: 0 1px 1px 0;
  border: solid #e0e0dc;
  padding: 3px 5px;
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

</style>