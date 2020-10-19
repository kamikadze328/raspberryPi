<template>
  <div class="header">
    <div class="header-inner">
      <div>
        <div class="left-header-box">
          <div v-show="$route.name.startsWith('admin-panel')" class="sidebar-button svg-box svg-img clickable"
               @click.stop="toggleLeftMenu"></div>
          <div class="organization-name">Ecodom</div>
        </div>
      </div>
      <a class="header-logo" href="https://se.ifmo.ru/courses/web">
        <img alt="itmo logo" crossorigin="anonymous" src="../assets/itmo_logo.png">
      </a>
      <div ref="clickable" :class="{'clickable disable-selection-text': isAuthorized()}"
           class="buttons-container"
           @click="toggleProfileButtons">
        {{ userName }}
        <ProfileButtonsHeader
            :style="{'opacity': Number(isProfileButtonsOpened), 'visibility' : buttonsContainerVisibility}"
            @successful-logout="logout"
            @changing-password="$emit('changing-password')"/>
      </div>

    </div>

  </div>
</template>

<script>
import ProfileButtonsHeader from "@/components/ProfileButtonsHeader";

export default {
  name: "Header",
  components: {ProfileButtonsHeader},
  props: {
    userName: String
  },
  watch: {
    username: function () {
      this.isAuthorized()
    }
  },
  data() {
    return {
      isProfileButtonsOpened: false,
      buttonsContainerVisibility: 'hidden',
      isAuth: false
    }
  },
  methods: {
    isAuthorized() {
      const isAuth = this.$mydata.isAuth()
      this.updateIsAuth(isAuth)
      return isAuth
    },
    toggleProfileButtons() {
      if (this.isProfileButtonsOpened === true)
        setTimeout(() => {
          this.buttonsContainerVisibility = 'hidden'
        }, 110)
      this.isProfileButtonsOpened = this.isAuthorized() ? !this.isProfileButtonsOpened : false
      if (this.isProfileButtonsOpened === true)
        this.buttonsContainerVisibility = 'visible'
    },
    updateIsAuth(val) {
      this.isAuth = val
    },
    closeAll(e) {
      if (this.$refs['clickable'] !== e.target && this.isProfileButtonsOpened)
        this.toggleProfileButtons()
    },
    logout() {
      this.toggleProfileButtons()
      this.$emit('successful-logout')
    },
    toggleLeftMenu() {
      if (this.$route.name.startsWith('admin-panel'))
        this.$emit('toggle-left-admin-panel')
    }
  },
  mounted() {
    document.addEventListener('click', this.closeAll)
  }

}
</script>

<style scoped>
.buttons-container {
  position: relative;
}

.header {
  width: 100%;
  margin-bottom: 20px;
  background-color: #fafafb;
  font-size: 1rem;
  box-shadow: 0 1px 0 rgba(12, 13, 14, 0.1), 0 1px 6px rgba(59, 64, 69, 0.1);
  color: #212223;
  border-top: 3px solid #348fe2;
}

.header-inner {
  display: flex;
  max-width: 1264px;
  height: 46px;
  margin: 0 auto;
  padding: 0 15px;
  align-items: center;
  font-size: 15px;
  font-weight: 300;
}

.header-inner > * {
  width: calc(100% / 3);
}

.header-inner > *:first-child {
  text-align: left;
}

.header-inner > *:last-child {
  text-align: right;
}

.header-logo {
  padding: 5px 0;
  margin: 0 15px;
  min-width: 204px;
}

.header-logo img {
  display: block;
  height: 36px;
  margin: 0 auto;
}

.sidebar-button {
  display: none;
  width: 20px;
  height: 20px;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' aria-hidden='true' role='img' viewBox='0 0 448 512' class='icon'%3E%3Cpath fill='currentColor' d='M436 124H12c-6.627 0-12-5.373-12-12V80c0-6.627 5.373-12 12-12h424c6.627 0 12 5.373 12 12v32c0 6.627-5.373 12-12 12zm0 160H12c-6.627 0-12-5.373-12-12v-32c0-6.627 5.373-12 12-12h424c6.627 0 12 5.373 12 12v32c0 6.627-5.373 12-12 12zm0 160H12c-6.627 0-12-5.373-12-12v-32c0-6.627 5.373-12 12-12h424c6.627 0 12 5.373 12 12v32c0 6.627-5.373 12-12 12z'%3E%3C/path%3E%3C/svg%3E");
}
.left-header-box{
  display: flex;
  align-items: center;
}
.left-header-box>*:last-child{
  padding: 0 15px;
}
.organization-name{
  font-weight: 500;
  font-size: 1.2rem;
  color: #2c3e50;
}

@media (max-width: 1200px) {
  .sidebar-button {
    display: flex;
  }
}

@media (max-width: 500px) {
  .header-inner > * {
    width: calc(100% / 2);
  }

  .header-logo {
    display: none;
  }
}
</style>