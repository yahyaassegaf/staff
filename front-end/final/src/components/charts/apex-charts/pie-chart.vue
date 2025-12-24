<script setup>
import * as pieData from '../../../data/apexcharts/apexchart-pie.ts';
import { defineAsyncComponent } from 'vue'
import Pageheader from '../../../shared/components/pageheader/pageheader.vue';

const ChartCards = defineAsyncComponent(() => import('../../../shared/components/@spk/chart-cards.vue'));

const dataToPass = {
    title: "Charts",
    subtitle: "Apex Charts",
    currentpage: "Apex Pie Charts",
    activepage: "Apex Pie Charts"
}

const ApexPieChart = [{
    id: 1,
    title: "Basic Pie Chart",
    type: "pie",
    height: "285",
    chart: {
        options: pieData.Basicpieoptions,
        series: pieData.Basicpieseries
    },
},
{
    id: 2,
    title: "Simple Donut Chart",
    type: "donut",
    height: "285",
    chart: {
        options: pieData.Donutoptions,
        series: pieData.Donutseries
    },
},
{
    id: 3,
    title: "Updating Donut Chart",
    type: "pie",
    height: "285",
    chart: {
        options: pieData.Updatedoptions,
        series: pieData.Updatedseries
    },
},
{
    id: 4,
    title: "Monochrome Pie Chart",
    type: "pie",
    height: "285",
    chart: {
        options: pieData.Piemonooptions,
        series: pieData.Piemonoseries
    },
},
{
    id: 5,
    title: "Gradient Donut Chart",
    type: "donut",
    height: "285",
    chart: {
        options: pieData.Piegardientoptions,
        series: pieData.Piegradientseries
    },
},
{
    id: 6,
    title: "Image Filled Pie Chart",
    customCardClass: "custom-chart1",
    type: "pie",
    height: "285",
    chart: {
        options: pieData.Pieimgoptions,
        series: pieData.Pieimgseries
    },
},
{
    id: 7,
    title: "Donut Chart With Patterns",
    type: "donut",
    height: "260",
    chartId: "donut-pattern",
    chart: {
        options: pieData.Piechartoptions,
        series: pieData.Piechartseries
    },
    body: "donut-pattern-chart mx-auto"
},

]

</script>

<template>
    <Pageheader :propData="dataToPass" />
    <!-- Start::row-1 -->
    <div class="row">
        <div class="col-xl-6" v-for="card in ApexPieChart" :key="card.id">
            <ChartCards :customCardClass="card.customCardClass" :card="card" :title="card.title" :chartId="card.chartId"
                :cardBodyClass="card.body" />
        </div>
    </div>
    <!--End::row-1 -->
</template>

<style scoped>
/* Add your styles here */
</style>
