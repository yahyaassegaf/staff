<script setup>
import SpkReusebleCouresCard from "../../shared/components/@spk/dashboards/spk-reuseble-couresCard.vue";

defineProps({
    cards: {
        type: Array,
        default: () => []
    }
});
</script>

<template>
    <div class="row row-cols-xxl-5 row-cols-lg-2 row-cols-xl">
        <div class="col flex-fill" v-for="(idx) in cards" :key="idx.id">
            <SpkReusebleCouresCard :card="idx" />
        </div>
    </div>
</template>

<style scoped>
/* Add your styles here */
</style>
