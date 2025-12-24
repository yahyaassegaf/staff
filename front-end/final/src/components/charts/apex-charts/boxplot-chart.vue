<script setup>
import * as boxplotData from "../../../data/apexcharts/apexchart-boxplot.ts";
import { defineAsyncComponent } from 'vue'
import Pageheader from "../../../shared/components/pageheader/pageheader.vue";

const ChartCards = defineAsyncComponent(() => import('../../../shared/components/@spk/chart-cards.vue'));


const dataToPass = {
    title: "Charts",
    subtitle: "Apex Charts",
    currentpage: "Apex Boxplot Charts",
    activepage: "Apex Boxplot Charts"
}

const ApexBoxplotChart = [{
    id: 1,
    title: "Basic Boxplot Chart",
    type: "boxPlot",
    height: "320",
    chart: {
        options: boxplotData.Boxplotoptions,
        series: boxplotData.Boxplotseries
    },
},
{
    id: 2,
    title: "Boxplot With Scatter Chart",
    type: "boxPlot",
    height: "320",
    chart: {
        options: boxplotData.Boxplotsoptions,
        series: boxplotData.Boxplotsseries
    },
},
]

</script>

<template>
    <Pageheader :propData="dataToPass" />

    <!-- Start::row-1 -->
    <div class="row">
        <div class="col-xl-6" v-for="card in ApexBoxplotChart" :key="card.id">
            <ChartCards :card="card" :title="card.title" />
        </div>
    </div>
    <!--End::row-1 -->

</template>

<style scoped>
/* Add your styles here */
</style>
