<template>
  <CurrentTable/>
  <Chart/>
</template>

<script>
import CurrentTable from "@/components/TableCurrent";
import Chart from "@/components/Chart";

export default {
  name: 'App',
  components: {
    Chart,
    CurrentTable,
  }
}
</script>

<style>
#app {
  font-family: Avenir, Helvetica, Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  display: flex;
}
.clickable:hover, .clickable:focus {
  cursor: pointer !important;
}
@media (max-width: 1300px) {
  #app{
    display: block;
  }
}
</style>
