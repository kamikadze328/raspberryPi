<template>
  <div class="button-box">
    <button v-for="v in durations" :key="v.value"
            :class="{'button-focus': currentDuration.value === v.value}" :value="v.value"
            class="pretty-input my-button clickable" @click="onClick">
      {{ v.name }}
    </button>
  </div>
</template>

<script>

export default {
  name: "ButtonsDuration",
  props: {
    durations: {
      required: true,
      type: Object
    },
    currentDuration: {
      validator: (obj) => {
        return 'value' in obj && 'name' in obj
      },
      type: Object,
      required: true
    }
  },
  emits: ['changed-duration'],
  methods: {
    onClick(e){
      if(this.currentDuration.value !== e.target.value)
        this.$emit('changed-duration', e.target.value)
    }
  }
}
</script>

<style scoped>
button{
  min-width: 70px;
}
</style>