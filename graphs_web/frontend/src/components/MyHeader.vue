<template>
    <div class="header-wrapper">
        <div class="header">
            <Configs/>
            <Calendar ref="calendar" @update-date="$emit('update-date')"/>
            <div id="time">{{time}}</div>
        </div>
        <div class="error-msg-box" v-show="showErrorMessage"><div class="error-msg">{{errorInfo.tags.join(', ') + ': ' + errorInfo.message}}</div></div>
    </div>
</template>

<script>
    import Calendar from "./Calendar";
    import Configs from "./Configs";

    export default {
        name: "Header",
        components:{
            Configs,
          Calendar
        },
        props:{
            errorInfo:{
                message: String,
                tags: Array
            }
        },
        data () {
            return {
                time: 'Самое время',
                errorMessageTimeout: null,
                showErrorMessage: false
            }
        },
        watch: {

            errorInfo: {
                handler: function (val) {
                    if (val.message) {
                        this.showErrorMessage = true
                        clearTimeout(this.errorMessageTimeout)
                        this.errorMessageTimeout = setTimeout(this.clearMessage, 3000)
                    } else this.showErrorMessage = false
                },
                deep: true
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
            },
            clearMessage: function () {
                this.showErrorMessage = false
                this.$emit('clear-error-msg')
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
        background-color: white;
    }
    .header{
        box-shadow: 0 0;
        margin-bottom: 5px;
    }
    .error-msg{
        display: inline-block;
        color:#ffffff;
        font-weight: 700;
        border-bottom: 0;
        background-color: #ff5b57;
        font-size: 1rem;
        margin: auto;
        padding: 4px 7px;
        border-radius: 4px;
    }
    .error-msg-box{
        display: flex;

        position: fixed;
        margin-left: auto;
        margin-right: auto;
        left: 0;
        right: 0;
        z-index: 100000;
    }
    .header-wrapper {

    }
    .header{
        align-items: center;
        height: 60px;
        width: 100%;
        font-size: 1.3rem;
        font-weight: 500;
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        box-shadow: 0 0 16px rgba(0,0,0,.15);
    }
    .header>:first-child{
        margin-left: 23px;

    }
    .header>:last-child{
        text-align: right;
        margin-right: 8px;

    }
</style>