<script setup>
import * as radarData from '../../../data/apexcharts/apexchart-radar.ts';
import { defineAsyncComponent } from 'vue'
import Pageheader from '../../../shared/components/pageheader/pageheader.vue';

const ChartCards = defineAsyncComponent(() => import('../../../shared/components/@spk/chart-cards.vue'));

const dataToPass = {
    title: "Charts",
    subtitle: "Apex Charts",
    currentpage: "Apex Radar Charts",
    activepage: "Apex Radar Charts"
}

const ApexRadarChart = [{
    id: 1,
    title: "Basic Radar Chart",
    type: "radar",
    height: "320",
    chart: {
        options: radarData.Radaroptions,
        series: radarData.Radarseries
    },
},
{
    id: 2,
    title: "Radar Chart-Multiple Series",
    type: "radar",
    height: "320",
    chart: {
        options: radarData.Multioptions,
        series: radarData.Multiseries
    },
},
{
    id: 3,
    title: "Radar Chart Polygon Fill",
    type: "radar",
    height: "320",
    chart: {
        options: radarData.Polygonoptions,
        series: radarData.Ploygonseries
    },
},
]

</script>

<template>
    <Pageheader :propData="dataToPass" />

    <!-- Start::row-1 -->
    <div class="row">
        <div class="col-xl-6" v-for="card in ApexRadarChart" :key="card.id">
            <ChartCards :card="card" :title="card.title" />
        </div>
    </div>
    <!--End::row-1 -->
</template>

<style scoped>
/* Add your styles here */
</style>
