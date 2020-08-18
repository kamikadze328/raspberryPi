<template>
    <div class="container" ref="container">
        <div :class="'configs-wrapper ' + (isWrapperOpened ? 'open-wrapper' : '')"
             ref="wrapper">
            <div :style="'overflow-y: auto; max-height: ' + style.wrapperMaxHeight + ';' +
                         'width: ' + style.wrapperMaxWidth + 'px;'">
                <div @click.self="toggleVisibilityAll" class="closed-header-config clickable" v-show="!isWrapperOpened">
                    <div @click="toggleVisibilityAll">{{ currentConfig.name }}</div>
                    <img @click="saveCurrent"
                         alt="Сохранить изменения"
                         class="save-img"
                         height="30px"
                         src="@/assets/save-48px.png" title="Сохранить изменения" v-show="wasCurrentConfigChanged"
                         width="27px"/>
                    <img :style="'opacity: ' + style.opacityImg"
                         alt="Успешно"
                         class="save-img"
                         height="30px" ref="successfulSaveImg" src="@/assets/successful-save-48px.png"
                         title="Успешно"
                         v-show="!wasCurrentConfigChanged && this.style.opacityImg > 0"
                         width="27px"
                    />
                    <div @click="toggleVisibilityAll" class="disable-selection-text open-button">&#x2BC8;</div>
                </div>
                <ConfigRow :config="currentConfig"
                           :is-current-config="true" :is-wrapper-opened="isWrapperOpened" :max-width="style.wrapperMaxWidth"
                           @was-current-config-changed="watchWasCurrentConfigChanged"
                           ref="currConf"
                           v-show="isWrapperOpened"/>
                <ConfigRow :config="config"
                           :is-current-config="false" :is-wrapper-opened="isWrapperOpened" :max-width="style.wrapperMaxWidth"
                           :key="config.id"
                           v-for="config in configurations"
                           v-show="isWrapperOpened"/>
            </div>
            <div @click.stop="isWrapperOpened = false"
                 class="close-button clickable"
                 v-show="isWrapperOpened">
                &#8212;
            </div>
        </div>
    </div>
</template>

<script>
import {mapGetters} from 'vuex';
import ConfigRow from "@/components/ConfigRow";

export default {
    name: "Configs",
    components: {
        ConfigRow
    },
    data() {
        return {
            isWrapperOpened: false,
            isMounted: false,
            wasCurrentConfigChanged: false,
            style: {
                wrapperMaxWidth: 750,
                opacityImg: 0,
                wrapperMaxHeight: 'auto'
            }
        }
    },
    watch: {
        wasCurrentConfigChanged: function (val) {
            if (!val && !this.isWrapperOpened) {
                this.style.opacityImg = 1
                setTimeout(() => this.increaseOpacity(), 1)
            } else this.style.opacityImg = 0
        }
    },
    computed: {
        ...mapGetters(['configurations', 'currentConfig']),
    },
    methods: {
        closeAll: function (elem) {
            if ((elem !== this.$refs['wrapper'] && !this.$refs['wrapper'].contains(elem)))
                this.isWrapperOpened = false
        },
        toggleVisibilityAll: function () {
            this.$emit('update-current-config')
            this.isWrapperOpened = !this.isWrapperOpened
        },
        saveCurrent: function () {
            this.$refs['currConf'].loadButton()
        },
        watchWasCurrentConfigChanged: function (val) {
            this.wasCurrentConfigChanged = val
        },
        increaseOpacity: function () {
            if (this.style.opacityImg > 0) {
                this.style.opacityImg -= 0.002
                setTimeout(this.increaseOpacity, 1)
            } else this.style.opacityImg = 0
        }
    },

    mounted() {
        this.isMounted = true
        const wrapperBoundingClientRect = document.getElementById('row-list').getBoundingClientRect()
        const maxAvailable = wrapperBoundingClientRect.y + wrapperBoundingClientRect.height - 30
        this.style.wrapperMaxHeight = maxAvailable + 'px'

    },

}
</script>

<style scoped>
.closed-header-config {
    display: flex;
    flex-direction: row;
}

.container {
    padding: 10px 0;
    display: inline-block;
    align-self: flex-start;
    margin-right: 35px;
}

.configs-wrapper {
    position: absolute;
    padding: 5px;
    border-radius: 3px;
    border: 2px solid white;
    line-height: 30px;
    background-color: transparent;
    z-index: 1000;
}

.configs-header {
    display: inline-flex;
    flex-direction: row;
}

.open-wrapper {
    box-shadow: 0 0 8px rgba(0, 0, 0, .15);
    border-color: #348fe2;
    background-color: white;

}

.close-button {
    position: absolute;
    background-color: white;
    right: -51px;
    width: 46px;
    top: -2px;
    border-radius: 3px;
    text-align: center;
    border: 2px solid #348fe2;
    font-weight: bolder;
    line-height: 46px;
}


.open-button {
    margin-left: 5px;
    line-height: 35px;
}

.save-img {
    margin: auto auto auto 5px;
}


</style>