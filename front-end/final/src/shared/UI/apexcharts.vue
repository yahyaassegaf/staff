<template>
    <ClientOnly>
        <VueApexCharts :width="width || '100%' || 'apexchart'" :height="height" :type="type" :options="options"
            :series="series" />
    </ClientOnly>
</template>
<script setup>
import { defineAsyncComponent } from 'vue';
const VueApexCharts = defineAsyncComponent(() => import ('vue3-apexcharts'))

const props = defineProps({
    width: { type: [String, Number], default: '100%' },
    height: { type: [String, Number], default: '350' },
    type: { type: String, required: true },
    options: { type: Object, required: true },
    series: { type: Array, required: true },
});
</script>