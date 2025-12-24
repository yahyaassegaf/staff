<template>
<customswitcher />
<router-view />
</template>

<script>
import { defineComponent, onMounted, onUnmounted } from 'vue'
import customswitcher from '../components/customswitcher/customswitcher.vue'

export default defineComponent({
  components: { customswitcher },
  setup() {
    onMounted(() => document.body.classList.add('error-1'))
    onUnmounted(() => document.body.classList.remove('error-1'))
  }
})
</script>

<style scoped></style>
