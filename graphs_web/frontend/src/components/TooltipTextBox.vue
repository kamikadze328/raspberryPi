<template>
    <div class="tooltip-line-text-box" :style="style">
        <div class="tooltip-line-text" v-for="d in data" :key="data.indexOf(d)"
             :style="'transform: translate(0, ' + d.y + 'px); color:' + d.color + ';'">
            {{d.value}}
        </div>
    </div>
</template>

<script>
    export default {
        name: "TooltipTextBox",
        props: {
            data: Array,
            absolutePositionX: Number,
            paddingTop:Number,
            paddingBottom: Number
        },
        computed: {
          style: function () {
                return 'left:' + this.absolutePositionX + 'px;'
          }
        },
    }
</script>

<style scoped>
    .tooltip-line-text-box{
        top: 0;
        left: 0;
        position: absolute;
        height: 100%;
    }
    .tooltip-line-text{
        position: absolute;
        width: 100%;
        font-weight: 900;
        text-shadow:
                -1px -1px 0 #fff,
                1px -1px 0 #fff,
                -1px 1px 0 #fff,
                1px 1px 0 #fff;

    }
</style>