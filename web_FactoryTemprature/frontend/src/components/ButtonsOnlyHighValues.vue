<template>
  <div class="button-box-wrapper">
    <label>Температуры:</label>
    <div class="button-box">
    <button :class="{'button-focus': isOnlyHighValues}" @click="updateTypeValues"
            class="pretty-input my-button clickable" :value="TYPES_VALUES.HIGH.value">
      {{TYPES_VALUES.HIGH.name}}
    </button>
    <button :class="{'button-focus': !isOnlyHighValues}" @click="updateTypeValues"
            class="pretty-input my-button clickable" :value="TYPES_VALUES.LOW.value">
      {{TYPES_VALUES.LOW.name}}
    </button>
    </div>
  </div>
</template>

<script>
import {mapMutations} from 'vuex';
import {mapState} from 'vuex';


export default {
  name: "ButtonsOnlyHighValues",
  computed: {
    ...mapState(['isOnlyHighValues'])
  },
  data(){
    return{
      TYPES_VALUES: {
        HIGH: {
          name: 'Высокие',
          value: 1
        },
        LOW: {
          name: 'Низкие',
          value: 0
        }
      }
    }
  },
  methods:{
    ...mapMutations(['updateIsOnlyHighValues']),
    updateTypeValues(e){
      const newVal = !!Number(e.target.value)
      this.updateIsOnlyHighValues({newVal})
    }
  }
}
</script>

<style scoped>
.button-box{
  align-items: center;
}
button{
  min-width: 80px;
}
.button-box-wrapper{
  display: flex;
  flex-direction: row;
  align-items: center;
}
.button-box-wrapper > label{
  margin-right: 5px;
  font-weight: bold;
}
</style>