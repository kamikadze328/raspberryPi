<template>
  <CurrentTable/>
  <TableHistory/>
  <Chart/>
</template>

<script>
import CurrentTable from "@/components/TableCurrent";
import TableHistory from "@/components/TableHistory";
import Chart from "@/components/Chart";

export default {
  name: 'App',
  components: {
    Chart,
    TableHistory,
    CurrentTable,
  }
}
</script>

<style>
#app {
  font-family: Avenir, Helvetica, Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  text-align: center;
  color: #2c3e50;
  margin-top: 60px;
}
</style>
