<script lang="ts" setup>
import type { DefineComponent } from 'vue';

defineProps({
  customCardClass: String,
  cardHeaderClass: String,
  title: String,
  cardBodyClass: String,
  type: {
    type: Object as () => DefineComponent<{}, {}, any>,
    required: true
  },
  data: {
    type: [Object, Array],
    required: true
  }
})
</script>

<template>
  <div :class="['card custom-card', customCardClass]">
    <div :class="['card-header', cardHeaderClass]">
      <div class="card-title">{{ title }}</div>
    </div>
    <div :class="['card-body', cardBodyClass]">
      <component :is="type" :chartData="data" />
    </div>
  </div>
</template>

<style scoped></style>
