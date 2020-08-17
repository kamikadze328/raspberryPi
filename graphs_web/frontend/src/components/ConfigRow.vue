<template>
    <div :ref="'config-row-' + config.id" class="config-row">
        <div :class="'config-header config-header-text ' + (!isChangingMode ? 'clickable' : '')"
             @click="toggleVisibilityConfig">
            <div v-if="!isChangingMode">
                {{ config.name + (isCurrentConfig ? ' (Текущая)' : (isSavedCurrentConfig ? ' (Сохранённая)': '')) }}
            </div>
            <label v-else>
                <input class="my-input config-header-text" v-model="newName"/>
            </label>
            <div class="disable-selection-text">
                {{ isConfigOpened ? '&#x2BC6;' : '&#x2BC8;' }}
            </div>
        </div>
        <hr/>
        <div class="config-body" v-show="isConfigOpened">

            <div :key="chart.id" v-for="chart in config.charts">
                <div class="charts-description">
                    <div v-if="!isChangingMode" class="chart-name">{{ chart.name }}</div>
                    <label v-else class="chart-name">
                        <input class="my-input input-chart-name chart-name"
                               v-model="chart.name"/>
                    </label>

                    <div class="tags-list">
                        <div :key="tagId" v-for="tagId in chart.tags">
                            {{ tagId + ' ' + $store.getters.getTagsDescription(tagId) }}
                        </div>
                    </div>
                </div>
                <hr  />
            </div>
            <div class="config-footer">
                <div class="button clickable remove-btn"
                    @click.stop="removeButton">
                    {{ isChangingMode ? 'Отмена' : (isCurrentConfig ? 'Очистить' : 'Удалить') }}
                </div>
                <div class="button clickable change-btn"
                     @click.stop="changeButton">
                    {{isChangingMode ? 'Применить' : 'Переименовать'}}
                </div>
                <div class="button clickable load-btn"
                     @click.stop="loadButton"
                     v-show="!isCurrentConfig || wasCurrentChanged"
                     :style="'visibility: ' + (isChangingMode ? 'hidden' : 'visible')">
                    {{ (isCurrentConfig) ? 'Сохранить' : 'Загрузить'}}
                </div>
            </div>
           <!-- <hr/>-->
        </div>
    </div>
</template>

<script>
export default {
    name: "ConfigRow",
    props: {
        config: {
            id: Number,
            name: String,
            charts: Array
        },
        isCurrentConfig: Boolean,
        isWrapperOpened: Boolean,
    },
    watch: {
        isWrapperOpened: function (val){
            if(!val){
                if(this.isChangingMode)
                    this.removeButton()
            }
        },
        config: {
            handler: function(val) {
                this.updateInputValues(val)
                this.updateWasCurrentChanged()
            },
            deep: true
        }
    },
    data() {
        return {
            wasCurrentChanged: false,
            isConfigOpened: false,
            isChangingMode: false,
            newName: this.config.name,
            oldCharts: JSON.parse(JSON.stringify(this.config.charts))
        }
    },
    computed: {
        isSavedCurrentConfig: function () {
            return !this.isCurrentConfig && this.config.id === this.$store.getters.currentConfig.id
        },
        /*wasCurrentChanged: function () {
            console.log(this.isCurrentConfig && this.$store.state.EMPTY_CONFIG.id === this.config.id)
            const result = this.isCurrentConfig && this.$store.state.EMPTY_CONFIG.id === this.config.id ?
                !this.$store.getters.compareConfigWithEmpty :
                !this.$store.getters.compareConfigByIdWithCurrent(this.config.id)

            this.$emit('was-current-config-changed', result)

            return result

        }*/
    },
    methods: {
        updateWasCurrentChanged: function (){

            const result = this.isCurrentConfig && this.$store.state.EMPTY_CONFIG.id === this.config.id ?
                !this.$store.getters.compareConfigWithEmpty :
                !this.$store.getters.compareConfigByIdWithCurrent(this.config.id)
            if(result !== this.wasCurrentChanged) {
                this.$emit('was-current-config-changed', result)
                this.wasCurrentChanged = result
            }
        },
        removeButton: function () {
            if (this.isChangingMode) {
                this.isChangingMode = !this.isChangingMode
                this.config.charts = this.oldCharts
                this.updateInputValues(this.config)
            } else if (this.isCurrentConfig)
                this.$store.commit('clearCurrentConfig')
            else
                this.$store.commit('removeConfig', this.config.id)
        },
        changeButton: function (){
            if(this.isChangingMode) {
                this.config.name = this.newName
                this.updateInputValues(this.config)
            }
            this.isChangingMode = !this.isChangingMode
        },
        loadButton: function () {
            if (this.isCurrentConfig)
                this.$store.commit('saveCurrentConfig')
            else
                this.$store.commit('setCurrentConfig', this.config)
        },
        toggleVisibilityConfig: function () {
            if(!this.isChangingMode)
                this.isConfigOpened = !this.isConfigOpened
        },
        updateInputValues: function (newConfig){
            this.newName = newConfig.name
            this.oldCharts= JSON.parse(JSON.stringify(newConfig.charts))
        },
    },
    created() {
        if (this.isCurrentConfig) this.isConfigOpened = true
    },
    mounted() {
    }
}
</script>

<style scoped>

.config-header {
    padding: 5px 2px;
    justify-content: space-between;
    display: flex;
}
.config-header-text{
    text-transform: uppercase;
    font-weight: 600;
    font-size: 1.1rem;
}

.charts-description {
    font-size: 1.1rem;
    display: flex;
    flex-direction: row;
    padding: 2px;
}

.config-row > hr {
    background-color: rgba(52, 143, 226, .6);
    height: 2px;
}

.config-footer {
    display: flex;
    margin: 5px 0;
    justify-content: space-around;
    flex-direction: row;
}

.button {
    border-radius: 4px;
    border: 1px solid #eff0fd;
    padding: 2px 5px;
    font-size: 1.1rem;
    width: 150px;
    max-width: 150px;
    text-align: center;
    background-color: #f5f6fc;
    color: #4665d7;
}

.button:hover, .button:focus {
    font-weight: bold;
    background-color: white;
}

.remove-btn:hover, .remove-btn:focus {
    border-color: #ff5b57;
    color: #ff5b57;
}

.change-btn:hover, .change-btn:focus {
    border-color: #348fe2;
    color: #348fe2;
}

.load-btn:hover, .load-btn:focus {
    border-color: #32a932;
    color: #32a932;
}

.tags-list {
    white-space: normal;
    margin-left: 30px;
    overflow: hidden;
    text-overflow: ellipsis;
}

.chart-name {
    max-width: 150px;
    white-space: pre-line;
    min-width: 150px;
    word-break: break-all;
}
.my-input{
    color: grey;
    border: 1px solid #3C86D9;
    border-radius: 3px;
    padding: 2px;
    line-height: 24px;
}

.input-chart-name{
    font-size: 1rem;
}

</style>