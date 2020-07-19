<template>
    <div class="tooltip-line-text-box" :style="styleBox">
        <div class="tooltip-line-text disable-selection-text" v-for="d in data" :key="data.indexOf(d)"
             :style="styleChild(d)">
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
            paddingTop: Number,
            paddingBottom: Number,
            maxX: Number,
            minX: Number,
        },
        computed: {
            styleBox: function () {
                return this.absolutePositionX < this.maxX && this.absolutePositionX > this.minX ?
                    'left:' + this.absolutePositionX + 'px;'
                    : 'display: none'
            },
            styleChild: function () {
                return d => 'transform: translate(0, ' + d.y + 'px);'
                    + ' color:' + d.color + ';'
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