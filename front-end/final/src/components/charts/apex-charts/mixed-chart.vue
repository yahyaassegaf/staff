<script setup>
import * as mixedData from '../../../data/apexcharts/apexchart-mixed.ts';
import { defineAsyncComponent } from 'vue'
import Pageheader from '../../../shared/components/pageheader/pageheader.vue';

const ChartCards = defineAsyncComponent(() => import('../../../shared/components/@spk/chart-cards.vue'));

const dataToPass = {
    title: "Charts",
    subtitle: "Apex Charts",
    currentpage: "Apex Mixed Charts",
    activepage: "Apex Mixed Charts"
}

const ApexMixedChart = [{
    id: 1,
    title: "Line & Column Chart",
    type: "line",
    height: "320",
    chart: {
        options: mixedData.Mixedlineoptions,
        series: mixedData.Mixedlineseries
    },
},
{
    id: 2,
    title: "Multiple Y-Axis Chart",
    type: "line",
    height: "320",
    chart: {
        options: mixedData.Mixedaxisoptions,
        series: mixedData.Mixedaxisseries
    },
},
{
    id: 3,
    title: "Line & Area Chart",
    type: "line",
    height: "320",
    chart: {
        options: mixedData.Mixedareaoptions,
        series: mixedData.Mixedareaseries
    },
},
{
    id: 4,
    title: "Line,Column & Area Chart",
    type: "line",
    height: "320",
    chart: {
        options: mixedData.Mixedcolumnoptions,
        series: mixedData.Mixedcolumnseries
    },
},
]
</script>

<template>
    <Pageheader :propData="dataToPass" />
    <!-- Start::row-1 -->
    <div class="row">
        <div class="col-xl-6" v-for="card in ApexMixedChart" :key="card.id">
            <ChartCards :card="card" :title="card.title" />
        </div>
    </div>
    <!--End::row-1 -->
</template>

<style scoped>
/* Add your styles here */
</style>
