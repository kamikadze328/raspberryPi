<template>
    <div id="select-temperature" class="select-box">
        <h4>Choose Temperature Tags:</h4>
        <input class="select-clickable" placeholder="Enter teg id">
        <div id="select-temperature-items" class="select-items">
            <label for="select-temperature-tag">
                <input type="checkbox" id="select-temperature-tag"/>
                <label v-for="row in rows"
                       v-bind:key="'select-temperature-row-' + row.id"
                       :for="'select-temperature-tag-' + row.id">
                    <input :id="'select-temperature-tag-' + row.id" type="checkbox"  />
                    {{row.id}} {{row.description}}
                </label>
            </label>
        </div>
    </div>
</template>

<script>
    export default {
        name: "InputSelect",
        props: {
            rows:{
                type: Object,
                required: true
            },
            name: {
                type: String,
                required: true
            }
        }
    }
</script>

<style scoped>

</style>