<script setup>
import * as polarareaData from '../../../data/apexcharts/apexchart-polararea.ts';
import { defineAsyncComponent } from 'vue'
import Pageheader from '../../../shared/components/pageheader/pageheader.vue';

const ChartCards = defineAsyncComponent(() => import('../../../shared/components/@spk/chart-cards.vue'));

const dataToPass = {
    title: "Charts",
    subtitle: "Apex Charts",
    currentpage: "Apex Polar Area Charts",
    activepage: "Apex Polar Area Charts"
}

const ApexPolarChart = [{
    id: 1,
    title: "Basic Polar Area Chart",
    type: "polarArea",
    height: "300",
    chartId: "polararea-basic",
    chart: {
        options: polarareaData.Areaoptions,
        series: polarareaData.Areaseries
    },
    body: "polar-areacharts01 mx-auto mx-sm-0"
},
{
    id: 2,
    title: "Polar Area Monochrome Chart",
    type: "polarArea",
    height: "300",
    chart: {
        options: polarareaData.Monooptions,
        series: polarareaData.Monoseries
    },
},
]
</script>

<template>
    <Pageheader :propData="dataToPass" />

    <!-- Start::row-1 -->
    <div class="row">
        <div class="col-xl-6" v-for="card in ApexPolarChart" :key="card.id">
            <ChartCards :card="card" :title="card.title" :chartId="card.chartId" :cardBodyClass="card.body" />
        </div>
    </div>
    <!--End::row-1 -->
</template>

<style scoped>
/* Add your styles here */
</style>
