<template>
    <div class="row" @mouseenter="visibilityRemoveBtn = true" @mouseleave="visibilityRemoveBtn = false">
        <div class="main-info card">
            <div class="name-and-settings">
                <div class="db-name text-main">
                    Graph {{config.id}}
                </div>
                <div :id="'toggleAddedList' + config.id"
                     class="text-main list-added-tag disable-selection-text"
                     @click="toggleVisibilityTags"
                     ref="select-box-btn" v-html="visibilityTags ? htmlSymbols.close : htmlSymbols.openRight " />
            </div>
            <div class="rows-stat select-box" :id="'select-box-' + config.id" v-show="visibilityTags" ref="select-box">
                <input placeholder="Enter tag id or name"
                       @input="filterSelectorItems">
                <div class="select-items" ref="select-items">
                    <div :key="device.id"
                         v-for="device in this.$store.getters.devices">
                        <div class="select-parent"
                             @click.self="toggleVisibilityChild">
                            <span  @click.self="toggleVisibilityChildText" class="disable-selection-text" v-html="htmlSymbols.openRight"/>
                            {{device.id}} {{device.description}}
                        </div>
                        <div class="select-child-box">
                            <label :for="'select-tag-' + config.id + '-' + tag.id"
                                   :key="'select-row-' + config.id + '-' + tag.id"
                                   v-for="tag in device.tags">
                                <input :id="'select-tag-' + config.id + '-' + tag.id"
                                       :ref="'select-tag-' + tag.id"
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
               @mouse-moover="coordinates => $emit('mouse-moover', coordinates)"
               @clicker="$emit('clicker')"
               @db-clicker="$emit('db-clicker')"
               ref="chart"/>
        <div class="remove-tag-btn remove-graph-row-btn"
             v-show="visibilityRemoveBtn"
             @click="$emit('remove-row', config.id)">&#x2716;</div>
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
                visibilityRemoveBtn: false,
                legend: [],
                visibilityTags: false,
                selectedTagsId: [],
                waitedTagsId: [],
                waitedForDrawTagsId: [],
                waitedForRemove: [],
                colorTagWithData: '#2d353c',
                colorTagWithoutData: '#ff5b57',
                htmlSymbols: {
                    openRight: '&#x2BC8;',
                    close: '&#8212;',
                    openDown: '&#x2BC6;',
                },
            }
        },
        computed: {
            ...mapGetters(['tagsData']),
            refChart: function () {
                return this.$refs['chart']
            },
        },
        watch: {
            tagsData: {
                handler: function (val) {
                    let tagsForDelete = []
                    for (let i = 0; i < this.waitedTagsId.length; i++) {
                        console.log(val[this.waitedTagsId[i]])
                        if (val[this.waitedTagsId[i]]) {
                            const tagId = this.waitedTagsId[i]
                            tagsForDelete.push(tagId)
                            this.refChart.addLine(val[tagId])
                        }
                    }
                    for( const tagId of tagsForDelete)
                        this.waitedTagsId.splice(this.waitedTagsId.indexOf(tagId), 1)
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
            setColorSelectedInput: function(tagId, isWithData){
                this.$refs['select-tag-' + tagId][0].labels[0].style.color = isWithData ? this.colorTagWithData : this.colorTagWithoutData
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
                /*if(this.visibilityTags) {
                    let height
                    try {
                        console.log(document.getElementById('row-list').clientHeight)
                        console.log(document.getElementById('row-list').scrollHeight)
                        console.log(this.$refs['select-box-btn'].getBoundingClientRect())
                        console.log(window.screenX)
                        console.log(window.innerHeight)
                        console.log(window.outerHeight)
                        console.log(window.pageYOffset)

                        const pageMax = document.getElementById('row-list').scrollHeight,
                            boxY = this.$refs['select-box-btn'].getBoundingClientRect().y,
                            availableHeight = pageMax - boxY
                        console.log(availableHeight)
                        height = availableHeight > 600 ? 600 : availableHeight
                    } catch {
                        height = 600
                    } finally {
                        this.$refs['select-box'].style.height = '' + height + 'px'
                    }*/
            },
            closeAll: function (elem){
                if(!(elem === this.$refs['select-box'] || this.$refs['select-box'].contains(elem) || elem === this.$refs['select-box-btn']))
                    this.visibilityTags = false
            },
            toggleVisibilityChild: function (e) {
                const wasVisible = this.toggleVisibilityHTMLElem(e.target.nextElementSibling)
                this.toggleVisibilityArrowDown(e.target.firstElementChild, wasVisible)

            },
            toggleVisibilityChildText: function (e) {
                const wasVisible = this.toggleVisibilityHTMLElem(e.target.parentElement.nextElementSibling)
                this.toggleVisibilityArrowDown(e.target, wasVisible)
            },
            toggleVisibilityArrowDown: function (elem, wasVisible) {
                elem.innerHTML = wasVisible ? this.htmlSymbols.openRight : this.htmlSymbols.openDown
            },
            toggleVisibilityHTMLElem: function (elem) {
                const wasVisible = elem.style.display === 'block'
                elem.style.display = wasVisible ? 'none' : 'block'
                return wasVisible
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
            },
            beforeUpdate: function () {
                this.waitedTagsId.concat(this.selectedTagsId)

            }
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
    .row{
        position: relative;
    }
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
        border-top: 1px solid #dadada;
        border-bottom: 1px solid #dadada;
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
    .remove-graph-row-btn{
        position: absolute;
        right: 15px;
        top: 15px;
        font-size: 1rem;
        visibility: visible;
    }

    @keyframes rotation {
        0% {
            transform: rotate(0deg);
        }
        100% {
            transform: rotate(180deg);
        }
    }
</style>