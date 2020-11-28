<template>
  <div :class="{'button-hide-content-closed': isOpened}" class="button-hide-content svg-box svg-img clickable"
       v-show="isVisible" @click="$emit('toggle')">
  </div>

</template>

<script>
export default {
  name: "ButtonHideContent",
  props:{
    isVisible: Boolean,
    isOpened: Boolean
  },
}
</script>

<style scoped>
.button-hide-content {
  height: 25px;
  width: 25px;
  padding: 10px 0;
  transform: rotate(0);
  -webkit-animation: .3s linear forwards;
  animation: .3s linear forwards;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' x='0px' y='0px' viewBox='0 0 451.847 451.847'%3E%3Cg%3E%3Cpath d='M225.923,354.706c-8.098,0-16.195-3.092-22.369-9.263L9.27,151.157c-12.359-12.359-12.359-32.397,0-44.751 c12.354-12.354,32.388-12.354,44.748,0l171.905,171.915l171.906-171.909c12.359-12.354,32.391-12.354,44.744,0 c12.365,12.354,12.365,32.392,0,44.751L248.292,345.449C242.115,351.621,234.018,354.706,225.923,354.706z'/%3E%3C/g%3E%3C/svg%3E%0A");
}
.button-hide-content-closed {
  -webkit-transform: rotate(180deg);
  transform: rotate(180deg);
}


</style>