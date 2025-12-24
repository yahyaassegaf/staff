<script lang="ts" setup>
import * as bubbleData from '../../../data/apexcharts/apexchart-bubble';
import { defineAsyncComponent } from 'vue'
import Pageheader from '../../../shared/components/pageheader/pageheader.vue';

const ChartCards = defineAsyncComponent(() => import('../../../shared/components/@spk/chart-cards.vue'));


const dataToPass = {
    title: "Charts",
    subtitle: "Apex Charts",
    currentpage: "Apex Bubble Charts",
    activepage: "Apex Bubble Charts"
}

const ApexBubbleChart = [{
    id: 1,
    title: "Simple Bubble Chart",
    type: "",
    height: "350",
    chart: {
        options: bubbleData.Bubbleoptions,
        series: bubbleData.Bubbleseries
    },
},
{
    id: 2,
    title: "3D Bubble Chart",
    type: "",
    height: "350",
    chart: {
        options: bubbleData.Bubble3doptions,
        series: bubbleData.Bubble3dseries
    },
},
]

</script>

<template>
    <Pageheader :propData="dataToPass" />

    <div class="row">
        <div class="col-xl-6" v-for="card in ApexBubbleChart" :key="card.id">
            <ChartCards :card="card" :title="card.title" />
        </div>
    </div>
</template>

<style scoped>
/* Add your styles here */
</style>
