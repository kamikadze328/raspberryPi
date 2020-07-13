<template>
    <div class="header-wrapper">
        <div class="header">
            <div id="number-db">Graph</div>
            <div id="error-msg">{{errorMessage}}</div>
            <div id="time">{{time}}</div>
        </div>
        <hr>
    <SettingsHeader @update-date="$emit('update-date')"/>
    </div>
</template>

<script>
    import SettingsHeader from "./SettingsHeader";

    export default {
        name: "Header",
        components:{
          SettingsHeader
        },
        props:{
            errorMessage: String
        },
        data () {
            return {
                time: ''
            }
        },
        methods: {
            updateTime: function () {
                const date = new Date()
                const options = {
                    year: '2-digit',
                    month: '2-digit',
                    day: '2-digit',
                    timezone: 'UTC',
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit'
                }
                this.time = date.toLocaleString("ru", options)
            }
        },
        mounted() {
            setInterval(this.updateTime, 1000)
        }
    }
</script>

<style scoped>
    .header-wrapper{
        box-shadow: 0 0 16px rgba(0,0,0,.15);
    }
    .header{
        box-shadow: 0 0;
        margin-bottom: 5px;
    }
    #error-msg{
        color:#ff5b57;
        font-weight: 700;
        border-bottom: 0;
    }
    .header-wrapper {
        background-color: white;
    }

</style>