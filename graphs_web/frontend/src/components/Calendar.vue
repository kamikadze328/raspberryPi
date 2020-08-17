<template>
    <div class="settings-wrapper">
        <form>
            <flatpickr class="flatpickr"
                       v-model="dateStr"
                       :config="dateConfig"
                       @on-change="onChange"
                       @on-open="onOpen"
                       @on-close="onClose"/>
            <div :class="'confirm-btn animation ' + (isCalendarOpened ? 'opened' : '')">
                <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="40" height="35" viewBox="0 0 50 50" style=" fill:#ffffff;">
                <path d="M 41.9375 8.625 C 41.273438 8.648438 40.664063 9 40.3125 9.5625 L 21.5 38.34375 L 9.3125 27.8125 C 8.789063 27.269531 8.003906 27.066406 7.28125 27.292969 C 6.5625 27.515625 6.027344 28.125 5.902344 28.867188 C 5.777344 29.613281 6.078125 30.363281 6.6875 30.8125 L 20.625 42.875 C 21.0625 43.246094 21.640625 43.410156 22.207031 43.328125 C 22.777344 43.242188 23.28125 42.917969 23.59375 42.4375 L 43.6875 11.75 C 44.117188 11.121094 44.152344 10.308594 43.78125 9.644531 C 43.410156 8.984375 42.695313 8.589844 41.9375 8.625 Z"></path></svg></div>
        </form>
    </div>
</template>

<script>
    import Vueflatpickr from "vue-flatpickr-component";
    import "flatpickr/dist/flatpickr.min.css";
    import flatpickr from "flatpickr";
    import { Russian } from "flatpickr/dist/l10n/ru.js";
    import { mapGetters } from 'vuex';

    flatpickr.localize(Russian);

    export default {
        name: "Calendar",
        components: {
            flatpickr: Vueflatpickr
        },
        data() {
            return {
                dateConfig: {
                    altInput: true,
                    altFormat: "F j, Y H:i",
                    enableTime: true,
                    maxDate: new Date,
                    mode: 'range',
                    defaultDate: "2020-06-19 00:00 — 2020-06-20 00:00",
                    defaultHour: 0,
                },
                isCalendarOpened: false,
            }
        },
        computed: {
            ...mapGetters(['minDate', 'maxDate']),
            dateStr: function (){

                return `${this.beautyDateStr(this.minDate)} — ${this.beautyDateStr(this.maxDate)}`
            }
        },
        methods: {
            beautyDateStr: function (date){
                const YYYY = new Intl.DateTimeFormat('ru', { year: 'numeric' }).format(date)
                const MM = new Intl.DateTimeFormat('ru', { month: '2-digit' }).format(date)
                const DD = new Intl.DateTimeFormat('ru', { day: '2-digit' }).format(date)
                const HH = new Intl.DateTimeFormat('ru', { hour: '2-digit' }).format(date)
                const mm = new Intl.DateTimeFormat('ru', { minute: '2-digit' }).format(date)
                return `${YYYY}-${MM}-${DD} ${HH}:${mm}`
            },
            onChange: function (dates) {
                if (dates.length > 1) {
                    this.$store.commit('updateDate', {min: dates[0], max: dates[1]})
                    this.$emit('update-date')
                }
            },
            updateTime: function(){
                this.dateConfig.maxDate = new Date
            },
            onOpen: function () {
                this.isCalendarOpened = true
            },
            onClose: function () {
                this.isCalendarOpened = false
            }
        },
        created() {
            this.$store.commit('updateDate', {min: new Date(this.dateStr.split(' — ')[0]), max: new Date(this.dateStr.split(' — ')[1])})
            setInterval(this.updateTime, 60000)
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
        z-index: 1;
    }
</style>
<style scoped>
    .settings-wrapper{
        height: 60px;
        display: flex;
        justify-content: center;
        align-items: center;
    }
    form{
        display: flex;
        flex-direction: row;
    }
    .confirm-btn{
        z-index: 0;
        position: relative;
        right: 50px;
        cursor: pointer;
        background-color: #569ff7;
        border-radius: 3px;
    }
    .confirm-btn:hover, .confirm-btn:focus{
      background-color: #3C86D9;
    }
    .opened{
        right: 0;
    }
</style>