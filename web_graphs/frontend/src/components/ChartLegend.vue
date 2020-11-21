<template>
    <div class="rows-stat">
        <div :data-tag-id="line.tag.id" :key="line.tag.id"
             class="row-stat flex"
             v-for="line in legend"
             @mouseleave="mouseLeave"
             @mouseover="mouseOver"
             v-show="selectedTags.indexOf(line.tag.id)>=0">
            <div :style="{'background-color': line.color}" class="circle"/>
            <div :style="{color: line.color}"
                 class="line-description">
                {{line.tag.id}}:{{line.tag.description}}
            </div>
            <div :data-tag-id="line.tag.id"
                 :ref="'remove-btn-'+line.tag.id "
                 class="disable-selection-text clickable remove-tag-btn"
                 @click="removeTag">
                &#x2716;
            </div>
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
        },
        methods: {
            removeTag: function (e) {
                this.$emit('remove-tag', Number(e.target.dataset.tagId))
            },
            mouseOver: function (e) {
                const tagId = e.target.dataset.tagId ? e.target.dataset.tagId : e.target.parentElement.dataset.tagId
                this.$refs['remove-btn-' + tagId][0].style.visibility = 'visible '
            },
            mouseLeave: function (e) {
                const tagId = e.target.dataset.tagId ? e.target.dataset.tagId : e.target.parentElement.dataset.tagId
                this.$refs['remove-btn-' + tagId][0].style.visibility = 'hidden'
            },
        }
    }
</script>

<style scoped>
    .flex {
        display: flex;
        align-items: center;
        justify-content: flex-start;
    }

    .rows-stat {
        overflow-y: auto;
        font-weight: 600;
    }

    .line-description {
        white-space: normal;
    }

    .circle {
        min-height: 8px;
        max-height: 8px;
        width: 8px;
        min-width: 8px;
        max-width: 8px;
    }


</style>