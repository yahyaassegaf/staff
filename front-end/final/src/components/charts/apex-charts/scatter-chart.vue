<script setup>
import * as scatterData from '../../../data/apexcharts/apexchart-scatter.ts';
import { defineAsyncComponent } from 'vue'
import Pageheader from '../../../shared/components/pageheader/pageheader.vue';

const ChartCards = defineAsyncComponent(() => import('../../../shared/components/@spk/chart-cards.vue'));

const dataToPass = {
    title: "Charts",
    subtitle: "Apex Charts",
    currentpage: "Apex Scatter Charts",
    activepage: "Apex Scatter Charts"
}

const ApexScatterChart = [{
    id: 1,
    title: "Basic Scatter Chart",
    type: "scatter",
    height: "320",
    chart: {
        options: scatterData.Scatterboptions,
        series: scatterData.Scatterbseries
    },
},
{
    id: 2,
    title: "Datetime Scatter Chart",
    type: "scatter",
    height: "320",
    chart: {
        options: scatterData.Scatterdateoptions,
        series: scatterData.Scatterdateseries
    },
},
{
    id: 3,
    title: "Image Fill Scatter Chart",
    type: "scatter",
    height: "320",
    chart: {
        options: scatterData.Scatterfilloptions,
        series: scatterData.Scatterfillseries
    },
},
]

</script>

<template>
    <Pageheader :propData="dataToPass" />
    <!-- Start::row-1 -->
    <div class="row">
        <div class="col-xl-6" v-for="card in ApexScatterChart" :key="card.id">
            <ChartCards :card="card" :title="card.title" />
        </div>
    </div>
    <!--End::row-1 -->
</template>

<style scoped>
/* Add your styles here */
</style>
