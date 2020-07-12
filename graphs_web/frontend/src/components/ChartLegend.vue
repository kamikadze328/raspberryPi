<template>
    <div class="rows-stat">
        <div :key="line.tag.id" class="row-stat flex"
             v-for="line in legend"
             v-show="selectedTags.indexOf(line.tag.id)>=0">
            <div :style="'background-color: ' + line.color" class="circle"/>
            <div class="line-description" :style="'color: ' + line.color">{{line.tag.id}}:{{line.tag.description}}</div>
        </div>
    </div>
</template>

<script>
    export default {
        name: "ChartLegend",
        props: {
            legend: {
                type: Array,
                required: true
            },
            selectedTags: {
                type: Array,
                required: true
            },
        }
    }
</script>

<style scoped>
   .flex{
       display: flex;
       align-items: center;
       justify-content: flex-start;
   }
    .rows-stat{
        overflow-y: auto;
    }
    .row-stat>:last-child{
        margin-left: 0;
    }
    .line-description{
        white-space: normal;
    }
   .circle{
       min-height: 8px;
       max-height: 8px;
       width: 8px;
       min-width: 8px;
       max-width: 8px;
   }
</style>