<template>
    <div class="settings-animation" id="settings-panel">
        <div class="settings-block border-bottom">
            <h3 class="border-bottom">Duration</h3>
            <div class="duration-item" v-on:click="updateDuration">Hour</div>
            <div class="duration-item" v-on:click="updateDuration">Day</div>
            <div class="duration-item" v-on:click="updateDuration">Week</div>

        </div>
        <div class="settings-block border-bottom">
            <h3 class="border-bottom">Date Range</h3>
            <input class="form-control" name="start" placeholder="Start Date" type="text"
                   id="dateStart"
                   :value="this.computedDate.min"
                    v-on:input="useUserDateInput(true)">
            <span>to</span>
            <input class="form-control" name="end" placeholder="End Date" type="text"
                   id="dateEnd"
                   :value="this.computedDate.max"
                   v-on:input="useUserDateInput(true)">
            <span  class="text-error" id="error-date">Wrong Format Date</span>
        </div>
        <div class="settings-block">
            <div class="btn" id="refresh-button"
                 v-on:click="this.updateCharts">Refresh charts</div>
        </div>

        <div class="settings-divider-footer"></div>
        <div class="settings-block" id="settings-footer">
            <div class="btn" id="to-main-btn"
                 v-on:click="redirectToMain">To Main</div>
        </div>
    </div>
</template>

<script>
    import { mapGetters } from 'vuex';
    export default {
        name: "SettingsPanel",

        computed: {
            ...mapGetters(['date']),
            computedDate: function () {
                const options = {
                    year: 'numeric',
                    month: '2-digit',
                    day: '2-digit',
                    timezone: 'UTC',
                }
                return {
                    min: new Date(this.date.min).toLocaleString("ru", options),
                    max: new Date(this.date.max).toLocaleString("ru", options),
                }
            },
        },
        methods: {
            updateDuration: function (e) {
                this.useUserDateInput(false)
                this.$store.commit('setIsDuration', {isDuration: true})
                this.$emit('changeduration')
                const duration = e.target.textContent.toLowerCase()
                this.$store.commit('setDuration', {duration})
                let durationItems = this.$el.querySelectorAll(".duration-item")
                durationItems.forEach(elem =>
                    this.setChoiceDuration(elem, duration))
                document.getElementById("settings-button").click()
            },
            setChoiceDuration: function (htmlElem, duration) {
                if (htmlElem.textContent.toLowerCase() === duration)
                    htmlElem.classList.add("duration-choice")
                else htmlElem.classList.remove("duration-choice")
            },
            redirectToMain: function () {
                window.location.replace('http://' + location.hostname)
            },
            updateCharts: function () {
                this.updateDate()
                this.$emit('updatecharts')
            },
            updateDate: function () {
                const min = this.parseDate(document.getElementById('dateStart').value)
                const max = this.parseDate(document.getElementById('dateEnd').value)
                this.$store.commit('updateDate', {min, max})
            },
            parseDate: function (date) {
                date = date.split('.')
                return new Date(date[2] + '-' + date[1] + '-' + date[0])
            },
            useUserDateInput: function (isUserInput) {
                this.$store.commit('useUserDateInput', {isUserInput})
            }
        },
        mounted() {
            this.$el.querySelectorAll(".duration-item")
                .forEach(elem =>
                    this.setChoiceDuration(elem, this.$store.getters.duration))
        }
    }
</script>

<style scoped>
    .form-control {
        border: 1px solid #d5dbe0;
        border-radius: 4px;
        font-size: 1rem;
        font-weight: 600;
        margin: 5px 0;
        line-height: 1.5;
        text-align: center;
        color: #2d353c;
        background-color: #fff;
        width: 100%;
    }
    #error-date{
        display: none;
    }

</style>