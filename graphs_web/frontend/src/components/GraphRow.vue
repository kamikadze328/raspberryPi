<template>
    <div class="row">
        <div class="main-info card">
            <div class="name-and-settings">
                <div class="db-name text-main">
                    Graph {{config.id}}
                </div>
                <div :id="'toggleAddedList' + config.id"
                     class="text-main list-added-tag"
                     v-on:click="toggleVisibilityTags">&#x2BC8;
                </div>
            </div>
            <div class="rows-stat select-box" v-show="visibilityTags">
                <input class="select-clickable"
                       placeholder="Enter tag id or name"
                       v-on:click="toggleVisibility"
                       v-on:input="filterSelectorItems">
                <div class="select-items">
                    <div :key="device.id"
                         v-for="device in this.$store.getters.devices">
                        <div class="select-parent select-clickable"
                             v-on:click="toggleVisibilityChild"><span>&#x2BC6;</span> {{device.id}}
                            {{device.description}}
                        </div>
                        <div class="select-child-box">
                            <label :for="'select-tag-' + config.id + '-' + tag.id"
                                   :key="'select-row-' + config.id + '-' + tag.id"
                                   v-for="tag in device.tags">
                                <input :id="'select-tag-' + config.id + '-' + tag.id"
                                       :value="tag.id"
                                       type="checkbox"
                                       v-model="selectedTagsId"
                                       v-on:change="changedSelected"/>
                                {{tag.id}} {{tag.description}}
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <ChartLegend :legend="legend" :selectedTags="selectedTagsId" @remove-tag="removeTagFromLegend"/>
        </div>

        <Chart :configId="config.id"
               :selectedTagsId="selectedTagsId"
               @add-color="addColor"
               @remove-color="removeColor"
               @zoomer="$emit('zoomer')"
               @mouse-moover="$emit('mouse-moover')"
               ref="chart"/>
    </div>
</template>

<script>
    import Chart from "./Chart";
    import ChartLegend from "./ChartLegend";
    import {mapGetters} from "vuex";

    export default {
        name: "GraphRow",
        components: {Chart, ChartLegend},
        props: {
            config: Object,
        },
        data() {
            return {
                legend: [],
                visibilityTags: false,
                selectedTagsId: [],
                waitedTagsId: [],
                waitedForDrawTagsId: [],
                waitedForRemove: []
            }
        },
        computed: {
            ...mapGetters(['tagsData']),
            refChart: function () {
                return this.$refs['chart']
            }
        },
        watch: {
            tagsData: {
                handler: function (val) {
                    for (let i = 0; i < this.waitedTagsId.length; i++) {
                        if (val[this.waitedTagsId[i]]) {
                            const tagId = this.waitedTagsId[i]
                            this.waitedTagsId.splice(i, 1)
                            this.refChart.addLine(val[tagId])
                            break
                        }
                    }
                },
                deep: true
            },
        },
        methods: {
            changedSelected: function (e) {
                const tagId = Number(e.target.value)
                console.log(this.selectedTagsId)
                if (this.selectedTagsId.indexOf(tagId) >= 0)
                    this.addTag(tagId)
                else
                    this.removeTag(tagId)
            },

            addTag: function (tagId) {
                if (!this.$store.getters.isTagsLoaded(tagId)) {
                    this.$emit('newtag', tagId)
                    this.waitedTagsId.push(tagId)
                } else this.waitedForDrawTagsId.push(tagId)
            },
            removeTag: function (tagId) {
                let index
                if ((index = this.waitedForDrawTagsId.indexOf(tagId)) > -1) {
                    this.waitedForDrawTagsId.splice(index, 1)
                } else if ((index = this.waitedTagsId.indexOf(tagId)) > -1) {
                    this.waitedTagsId.splice(index, 1)
                } else
                    this.waitedForRemove.push(tagId)
            },
            removeTagFromLegend: function (tagId) {
                this.removeTag(tagId)
                this.selectedTagsId.splice(this.selectedTagsId.indexOf(tagId), 1)
            },
            addColor: function (color, tag) {

                this.legend.push({color, tag})
                console.log(this.legend)
            },
            removeColor: function (tagId) {
                for (let i = 0; i < this.legend.length; i++)
                    if (this.legend[i].tag.id === tagId) {
                        this.legend.splice(i, 1)
                        break
                    }
            },
            toggleVisibilityTags: function () {
                this.visibilityTags = !this.visibilityTags
            },
            toggleVisibility: function (e) {
                const elemTarget = e.target.nextElementSibling
                document.querySelectorAll('.select-clickable').forEach((elem => {
                    if (elem.parentElement.id !== e.target.parentElement.id) elem.nextElementSibling.style.display = 'none'
                }))
                this.toggleVisibilityHTMLElem(elemTarget)
            },
            toggleVisibilityChild: function (e) {
                this.toggleVisibilityHTMLElem(e.target.nextElementSibling)
            },
            toggleVisibilityHTMLElem: function (elem) {
                elem.style.display = elem.style.display === 'block' ? 'none' : 'block'
            },
            filterSelectorItems: function (e) {
                e.target.nextElementSibling.style.display = "block"

                const userText = e.target.value.toLowerCase()
                const items = e.target.nextElementSibling.children
                if (userText && userText.length > 0)
                    for (let i = 0; i < items.length; i++)
                        items[i].style.display = (items[i].outerText.toLocaleLowerCase().indexOf(userText) > -1) ? 'flex' : 'none'
                else
                    for (let i = 0; i < items.length; i++)
                        items[i].style.display = 'flex'
            },
            updateCharts: function () {
                this.refChart.updateCharts()
            }
        },

        mounted() {
        },

        updated() {
            if (this.waitedForDrawTagsId.length)
                this.refChart.addLine(this.$store.getters.tagById(this.waitedForDrawTagsId.shift()))
            if (this.waitedForRemove.length)
                this.refChart.removeLine(this.waitedForRemove.shift())
        },

    }
</script>

<style scoped>
    .name-and-settings {
        display: flex;
        justify-content: space-between;
    }

    .list-added-tag {
        line-height: 20px;
        cursor: pointer;
    }

    .select-box > *, .select-box {
        width: 100%;
    }

    .select-box > input {
        width: calc(100% - 1.5rem);
        padding: .4375rem .75rem;
        background-color: #fff;
        background-clip: padding-box;
        border: 0;
        border-radius: 4px;
        font-size: 0.8rem !important;
    }

    .select-box {
        margin: 0 !important;
        white-space: normal;
        position: absolute;
        left: 102.3%;
        z-index: 1000;
        top: 3%;
        width: 500px;
    }

    .select-items {
        z-index: 1000;
        display: block;
        border-top: 1px #dadada solid;
        background-color: white;
        position: absolute;
        overflow-y: auto;
        max-height: 600px;
    }

    .select-items label, .select-parent {
        border-bottom: 1px #dadada solid;
        margin: 3px 5px 0 5px;
        display: flex;
        align-items: center;
        min-height: 35px;
    }

    .select-items label > input, .select-parent > span {
        margin-right: 5px;
    }

    .select-items label, .select-box > input, .select-parent {
        color: #2d353c;
        font-size: 1rem;
        line-height: 20px;
        font-weight: 600;
        font-family: "Open Sans", sans-serif;
    }

    .select-items label:focus, .select-items label:hover, .select-parent:focus, .select-parent:hover {
        background: #f2f4f5;
    }

    .select-parent {
        cursor: pointer;
    }

    .select-child-box {
        display: none;
    }

    h4 {
        margin-bottom: 3px;
        font-weight: bold;
        font-size: 0.9rem;
    }

    .select-items label {
        padding-left: 30px;
    }

</style>