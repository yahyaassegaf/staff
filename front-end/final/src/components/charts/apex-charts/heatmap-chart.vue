<script lang="ts" setup>
import * as heatmapData from '../../../data/apexcharts/apexchart-heatmap';
import { defineAsyncComponent } from 'vue'
import Pageheader from '../../../shared/components/pageheader/pageheader.vue';

const ChartCards = defineAsyncComponent(() => import('../../../shared/components/@spk/chart-cards.vue'));

const dataToPass = {
    title: "Charts",
    subtitle: "Apex Charts",
    currentpage: "Apex Heatmap Charts",
    activepage: "Apex Heatmap Charts"
}

const ApexHeatmapChart = [{
    id: 1,
    title: "Basic Heatmap Chart",
    type: "heatmap",
    height: "350",
    chart: {
        options: heatmapData.Heatboptions,
        series: heatmapData.Heatbseries
    },
},
{
    id: 2,
    title: "Multi Series Heatmap Chart",
    type: "heatmap",
    height: "350",
    chart: {
        options: heatmapData.Heatmultioptions,
        series: heatmapData.Heatmultiseries
    },
},
{
    id: 3,
    title: "Color Range Heatmap Chart",
    type: "heatmap",
    height: "350",
    chart: {
        options: heatmapData.Heatrangeoptions,
        series: heatmapData.Heatrangeseries
    },
},
{
    id: 4,
    title: "Heatmap Range Without Shades",
    type: "heatmap",
    height: "350",
    chart: {
        options: heatmapData.Heatshadeoptions,
        series: heatmapData.Heatshadeseries
    },
},
]

</script>

<template>
    <Pageheader :propData="dataToPass" />

    <!-- Start::row-1 -->
    <div class="row">
        <div class="col-xl-6" v-for="card in ApexHeatmapChart" :key="card.id">
            <ChartCards :card="card" :title="card.title" />
        </div>
    </div>
    <!--End::row-1 -->
</template>

<style scoped>
/* Add your styles here */
</style>
