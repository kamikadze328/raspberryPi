<template>
    <div class="header">
        <div id="number-db">Graph</div>
        <div id="error-msg"></div>
        <div id="time">{{time}}</div>
    </div>
</template>

<script>
    export default {
        name: "Header",
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

</style>