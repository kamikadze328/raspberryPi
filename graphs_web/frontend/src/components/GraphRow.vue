<template>
    <div class="row">
        <div class="main-info card">
            <div class="db-name text-main">
                Graph {{config.id}}
            </div>
            <div class="rows-stat">
                <div class="row-stat">
                    <div :id="'select-di-' + config.id" class="select-box">
                        <h4>Choose Digital Input Tags:</h4>
                        <input v-on:click="toggleVisibility"
                               v-on:input="filterSelectorItems"
                               class="select-clickable"
                               placeholder="Enter tag id or name">
                        <div class="select-items">
                            <label v-for="tag in this.$store.getters.tagsDigitalInput"
                                   :key="'select-di-row-' + config.id + '-' + tag.id"
                                   :for="'select-di-tag-' + config.id + '-' + tag.id">
                                <input type="checkbox"
                                       v-model="selectedTagsDigitalInputs"
                                       :value="tag.id"
                                       :id="'select-di-tag-' + config.id + '-' + tag.id"/>
                                {{tag.id}} {{tag.description}}
                            </label>
                        </div>
                    </div>
                </div>
                <div class="row-stat">
                    <div :id="'select-temperature-' + config.id" class="select-box">
                        <h4>Choose Temperature Tags:</h4>
                        <input v-on:click="toggleVisibility"
                               v-on:input="filterSelectorItems"
                               class="select-clickable"
                               placeholder="Enter tag id or name">
                        <div class="select-items">
                            <label v-for="tag in this.$store.getters.tagsTemperature"
                                   :key="'select-temperature-row-' + config.id + '-' + tag.id"
                                   :for="'select-temperature-tag-' + config.id + '-' + tag.id">
                                <input type="checkbox"
                                       v-model="selectedTagsTemperature"
                                       :value="tag.id"
                                       :id="'select-temperature-tag-' + config.id + '-' + tag.id"/>
                                {{tag.id}} {{tag.description}}
                            </label>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <div :id="'chart-' + config.id" class="extended-info card">
            <Chart :selectedTagsTemperature="selectedTagsTemperature"
                   :selectedTagsDigitalInputs="selectedTagsDigitalInputs"
                   :id="config.id"/>
        </div>
    </div>
</template>

<script>
    import Chart from "./Chart";
    export default {
        name: "GraphRow",
        components: {Chart},
        props: {
            config: Object,
        },
        data() {
            return {
                selectedTagsTemperature:[],
                selectedTagsDigitalInputs:[],
            }
        },
        methods: {
            toggleVisibility: function (e) {
                const elemTarget = e.target.nextElementSibling
                document.querySelectorAll('.select-clickable').forEach((elem => {
                    if(elem.parentElement.id !== e.target.parentElement.id) elem.nextElementSibling.style.display = 'none'
                }))
                elemTarget.style.display = elemTarget.style.display === 'block' ? 'none' : 'block'
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
        },
        mounted() {
        }
    }
</script>

<style scoped>

</style>