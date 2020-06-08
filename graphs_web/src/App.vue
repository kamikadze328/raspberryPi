<template>
  <div id="app">
    <Header/>
    <div v-on:click="closeAll" class="wrapper">
      <div id="row-list">
        <GraphRow v-for="row in graphConfigs"
                  v-bind:config="row"
                  v-bind:key="row.id"/>
      </div>
    </div>
  </div>
</template>

<script>
import Header from "@/components/MyHeader";
import GraphRow from "@/components/GraphRow";

export default {
  name: 'App',

  components: {
    GraphRow,
    Header,
  },
  data() {
    return {
      graphConfigs: [
        {
          id: 1,
          config: {}
        },
        {
          id: 2,
          config: {}
        }
      ],

    };
  },
  methods: {
    closeAll: function (e) {
      document.querySelectorAll(".select-items").forEach(elem => {
        if (!e.target.classList.contains('select-clickable') && e.target.parentNode.tagName !== 'LABEL' && e.target.tagName !== 'LABEL')
          elem.style.display = "none"
      })
    }
  },
}
</script>

<style>
  @import "assets/css/style.css";
  @import "assets/css/new_style.css";
  @import "assets/css/style.css";
  .line {
    fill: none;
    stroke: steelblue;
  }

  .axisWhite >>> line {
    shape-rendering: optimizeSpeed;
    stroke: white;
  }

  .axisWhite >>> path {
    stroke: white;
    stroke-width: 1px;
    shape-rendering: optimizeSpeed;

  }

  .axisWhite >>> text {
    fill: white;
    font-size: 0.8rem;
  }
</style>
