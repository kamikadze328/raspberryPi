<template>
    <div class="settings-wrapper">
        <form>
            <flatpickr class="flatpickr"
                       v-model="dateStr"
                       :config="dateConfig"
                       @on-change="onChange"/>
        </form>
    </div>
</template>

<script>
    import Vueflatpickr from "vue-flatpickr-component";
    import "flatpickr/dist/flatpickr.min.css";
    import flatpickr from "flatpickr";
    import { Russian } from "flatpickr/dist/l10n/ru.js"

    flatpickr.localize(Russian);

    export default {
        name: "SettingsHeader",
        components: {
            flatpickr: Vueflatpickr
        },
        data() {
            return {
                dateStr: "2020-06-19 00:00 — 2020-06-20 00:00",
                dateConfig: {
                    altInput: true,
                    altFormat: "F j, Y H:i",
                    enableTime: true,
                    maxDate: new Date,
                    mode: 'range',
                    defaultDate: "2020-06-19 00:00 — 2020-06-20 00:00",
                    defaultHour: 0,
                },
            }
        },
        computed: {

        },
        methods: {
            onChange: function (dates) {
                if (dates.length > 1) {
                    this.$store.commit('updateDate', {min: dates[0], max: dates[1]})
                    this.$emit('update-date')
                }
            }
        },
        created() {
            console.log(new Date(this.dateStr.split(' — ')[0]))
            this.$store.commit('updateDate', {min: new Date(this.dateStr.split(' — ')[0]), max: new Date(this.dateStr.split(' — ')[1])})
        }
    }
</script>

<style>
    .flatpickr{
        width: 470px;
        text-align: center;
        font-size: 1.1rem;
        font-weight: 600;
        border: 1px solid #c3c3c3;
        line-height: 25px;
        padding: 8px;
        border-radius: 3px;
        background: #fff;
    }
</style>
<style scoped>
    .settings-wrapper{
        height: 60px;
        display: flex;
        justify-content: center;
        align-items: center;
    }

</style>