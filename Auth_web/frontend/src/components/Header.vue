<template>
  <div class="header">
    <div class="header-inner">
      <div>Ecodom</div>
      <a class="header-logo" href="https://se.ifmo.ru/courses/web">
        <img crossorigin="anonymous" src="../assets/itmo_logo.png" alt="itmo logo">
      </a>
      <div class="buttons-container" ref="clickable"
           :class="{'clickable disable-selection-text': isAuthorized()}"
           @click="toggleProfileButtons">
        {{ userName }}
        <ProfileButtonsHeader @successful-logout="$emit('successful-logout')"
                              @changing-password="$emit('changing-password')"
                              :style="{'opacity': Number(isProfileButtonsOpened), 'visibility' : buttonsContainerVisibility}"/>
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
    isAuthorized(){
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
    updateIsAuth(val){
      this.isAuth = val
    },
    closeAll(elem){
      if(this.$refs['clickable'] !== elem && this.isProfileButtonsOpened)
        this.toggleProfileButtons()
    }
  },

}
</script>

<style scoped>
.buttons-container{
  position: relative;
}
.header {
  width: 100%;
  margin-bottom: 20px;
  background-color: #fafafb;
  font-size: 1rem;
  box-shadow: 0 1px 0 rgba(12,13,14,0.1), 0 1px 6px rgba(59,64,69,0.1);
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
@media (max-width: 500px) {
  .header-inner > * {
    width: calc(100% / 2);
  }

  .header-logo {
    display: none;
  }
}
</style>