<template>
    <div v-on:click="toggleSettingsPanel"
         v-on:close="closeSettings"
         id="settings-button" :class="classButton">
        <div :class="classIcon" id="settings-icon"></div>
    </div>
</template>

<script>
    export default {
        name: "SettingsButton",
        data() {
            return {
                isClosed: true,
                classIcon: 'show-icon',
                classButton: ['settings-animation']
            }
        },
        methods: {
            toggleSettingsPanel: function () {
                if (this.isClosed)
                    this.openSettings()
                 else
                    this.closeSettings()
            },
            closeSettings: function () {
                this.classIcon = 'show-icon'
                this.classButton.splice(this.classButton.indexOf('show-button'), 1)
                document.getElementById("settings-panel").classList.remove("show-settings")
                this.isClosed = true
            },
            openSettings: function(){

                this.classIcon = 'close-icon'
                this.classButton.push('show-button')
                document.getElementById("settings-panel").classList.add("show-settings")
                this.isClosed = false
            }
        }
    }
</script>

<style scoped>

</style>