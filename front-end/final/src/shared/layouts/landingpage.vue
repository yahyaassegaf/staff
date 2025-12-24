<template>
<div>
    <customswitcher />
    <router-view />
    <BackToTop />
</div>
</template>

<script setup>
import customswitcher from '../components/customswitcher/customswitcher.vue'
import BackToTop from '../components/backtotop/backtotop.vue'
import { onBeforeMount, onUnmounted } from 'vue'

// Add the body class before the component is mounted
onBeforeMount(() => {
  document.body.classList.add('landing-body')
})

// Remove the body class when the component unmounts
onUnmounted(() => {
  document.body.classList.remove('landing-body')
})
</script>

<style scoped></style>
