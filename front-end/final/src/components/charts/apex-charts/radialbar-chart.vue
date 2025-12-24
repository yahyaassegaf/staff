<script setup>
import * as radialbarData from '../../../data/apexcharts/apexchart-radialbar.ts';
import { defineAsyncComponent } from 'vue'
import Pageheader from '../../../shared/components/pageheader/pageheader.vue';

const ChartCards = defineAsyncComponent(() => import('../../../shared/components/@spk/chart-cards.vue'));

const dataToPass = {
    title: "Charts",
    subtitle: "Apex Charts",
    currentpage: "Apex Radialbar Charts",
    activepage: "Apex Radialbar Charts"
}

const ApexRadialbarChart = [{
    id: 1,
    title: "Basic Pie Chart",
    type: "radialBar",
    height: "285",
    chart: {
        options: radialbarData.Pieoptions,
        series: radialbarData.Pieseries
    },
},
{
    id: 2,
    title: "Multiple Radialbar Chart",
    type: "radialBar",
    height: "285",
    chartId: "radialbar-multiple",
    chart: {
        options: radialbarData.Multiroptions,
        series: radialbarData.Multirseries
    },
    body: "multiple-radialbar-charts"
},
{
    id: 3,
    title: "Circle Chart - Custom Angle",
    type: "radialBar",
    height: "285",
    chart: {
        options: radialbarData.Customoptions,
        series: radialbarData.Customseries
    },
},
{
    id: 4,
    title: "Gradient Circle Chart",
    type: "radialBar",
    height: "285",
    chart: {
        options: radialbarData.Gradientoptions,
        series: radialbarData.Gradientseries
    },
},
{
    id: 5,
    title: "Stroked Circular Gauge",
    customCardClass: "custom-radialbar",
    type: "radialBar",
    height: "285",
    chart: {
        options: radialbarData.Strokeoptions,
        series: radialbarData.Storkeseries
    },
},
{
    id: 6,
    title: "Circle Chart With Image",
    type: "radialBar",
    height: "285",
    chart: {
        options: radialbarData.Circleoptions,
        series: radialbarData.Circleseries
    },
},
{
    id: 7,
    title: "Semi Circular Gauge",
    type: "radialBar",
    height: "285",
    chart: {
        options: radialbarData.Gaugeoptions,
        series: radialbarData.Gaugeseries
    },
},
]

</script>

<template>
    <Pageheader :propData="dataToPass" />

    <!-- Start::row-1 -->
    <div class="row">
        <div class="col-xl-6" v-for="card in ApexRadialbarChart" :key="card.id">
            <ChartCards :customCardClass="card.customCardClass" :card="card" :title="card.title" :chartId="card.chartId"
                :cardBodyClass="card.body" />
        </div>
    </div>
    <!--End::row-1 -->
</template>

<style scoped>
/* Add your styles here */
</style>
