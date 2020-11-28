<template>
  <div class="graph-container">
    <GraphConfig/>
    <Graph/>
  </div>
</template>

<script>
import GraphConfig from './GraphConfig';
import Graph from "./Graph";

export default {
  name: "GraphContainer",
  components: {GraphConfig, Graph},
  data(){
    return {
    }
  },
}
</script>

<style scoped>
.graph-container {
  display: flex;
  flex-direction: column;
}
</style>