<template>
    <div class="row">
        <div class="main-info card">
            <div class="db-name text-main">
                Graph {{config.id}}
            </div>
            <div class="rows-stat">
                <div class="row-stat">
                    <div id="select-temperature" class="select-box">
                        <h4>Choose Temperature Tags:</h4>
                        <input v-on:click="toggleVisibility"
                               v-on:input="filterSelectorItems"
                               class="select-clickable"
                               placeholder="Enter teg id or name">
                        <div :id="'select-temperature-items-' + config.id" class="select-items">
                            <label v-for="tag in tagsTemperature"
                                   v-bind:key="'select-temperature-row-' + config.id + '-' + tag.id"
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
            <Chart
                   v-bind:tagsTemperature="selectedTagsTemperature"
                   v-bind:id="config.id"/>
        </div>
    </div>
</template>

<script>
    import Chart from "@/components/Chart";
    export default {
        name: "GraphRow",
        components: {Chart},
        props: {
            config: {
                type: Object
            }
        },
        data() {
            return {
                tagsTemperature: [
                    {id: 10101, description: 'Топка'},
                    {id: 10102, description: 'Сушилка левая'},
                    {id: 10103, description: 'Дым газы'},
                    {id: 10104, description: 'Сушилка правая'},
                    {id: 10105, description: 'Реактор левый'},
                    {id: 10106, description: 'Выгрузка углерода левая'},
                    {id: 10107, description: 'Реактор правый'},
                    {id: 10108, description: 'Выгрузка углерода правая'},
                ],
                selectedTagsTemperature:[]
            }
        },
        methods: {
            toggleVisibility: function (e) {
                const elemTarget = e.target.nextElementSibling
                document.querySelectorAll('.select-clickable').forEach((elem => {
                    if(elem.nextElementSibling.id !== elemTarget.id) elem.nextElementSibling.style.display = "none"
                }))
                elemTarget.style.display = elemTarget.style.display === "block" ? "none" : "block"
            },
            filterSelectorItems: function (e) {
                document.getElementById('select-temperature-items-' + this.config.id).style.display = "block"

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