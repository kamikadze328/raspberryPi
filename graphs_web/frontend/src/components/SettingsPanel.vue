<template>
    <div id="settings-panel" class="settings-animation">
        <div class="settings-block border-bottom">
            <h3 class="border-bottom">Duration</h3>
            <div v-on:click="updateDuration" class="duration-item">Hour</div>
            <div v-on:click="updateDuration" class="duration-item">Day</div>
            <div v-on:click="updateDuration" class="duration-item">Week</div>

        </div>
        <div class="settings-block">
            <div id="refresh-button" class="btn">Refresh charts</div>
        </div>

        <div class="settings-divider-footer"></div>
        <div id="settings-footer" class="settings-block">
            <div id="to-main-btn" class="btn" v-on:click="redirectToMain">To Main</div>
        </div>
    </div>
</template>

<script>
    export default {
        name: "SettingsPanel",
        methods: {
            updateDuration: function (e) {
                this.$emit('changeduration')
                const duration = e.target.textContent.toLowerCase()
                this.$root.store.setDuration(duration)
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
            redirectToMain: function() {
                window.location.replace('http://' + location.hostname)
            }
        },
        mounted() {
            this.$el.querySelectorAll(".duration-item")
                .forEach(elem =>
                    this.setChoiceDuration(elem, this.$root.store.getDuration()))
        }
    }
</script>

<style scoped>

</style>