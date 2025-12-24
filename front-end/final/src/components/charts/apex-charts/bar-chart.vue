<script setup>
import * as barData from "../../../data/apexcharts/apexchart-bar.ts";
import { defineAsyncComponent } from 'vue'
import Pageheader from "../../../shared/components/pageheader/pageheader.vue";

const ChartCards = defineAsyncComponent(() => import('../../../shared/components/@spk/chart-cards.vue'));


const dataToPass = {
    itle: "Charts",
    subtitle: "Apex Charts",
    currentpage: "Apex Bar Charts",
    activepage: "Apex Bar Charts"
}

const ApexBarChart = [{
    id: 1,
    title: "Basic Bar Chart",
    type: "bar",
    height: "320",
    chart: {
        options: barData.Barbasicoptions,
        series: barData.Barbasicseries
    },
},
{
    id: 2,
    title: "Grouped Bar Chart",
    type: "bar",
    height: "320",
    chart: {
        options: barData.Bargroupoptions,
        series: barData.Bargroupseries
    },
},
{
    id: 3,
    title: "Stacked Bar Chart",
    type: "bar",
    height: "320",
    chart: {
        options: barData.Barstackoptions,
        series: barData.Barstackseries
    },
},
{
    id: 4,
    title: "100% Stacked Bar Chart",
    type: "bar",
    height: "320",
    chart: {
        options: barData.Barstack1options,
        series: barData.Barstck1series
    },
},
{
    id: 5,
    title: "Bar Chart With Negative Values",
    type: "bar",
    height: "320",
    chart: {
        options: barData.Barchartoptions,
        series: barData.Barchartseries
    },
},
{
    id: 6,
    title: "Bar Chart With Markers",
    type: "bar",
    height: "320",
    chart: {
        options: barData.Barmakeroptions,
        series: barData.Barmakerseries
    },
},
{
    id: 7,
    title: "Reversed Bar Chart",
    type: "bar",
    height: "320",
    chart: {
        options: barData.Barreverseoptions,
        series: barData.Barreverseseries
    },
},
{
    id: 8,
    title: "Bar With Categogry DataLabels",
    type: "bar",
    height: "320",
    chart: {
        options: barData.Barlableoptions,
        series: barData.Barlabelseries
    },
},
{
    id: 9,
    title: "Patterned Bar Chart",
    type: "bar",
    height: "320",
    chart: {
        options: barData.Barpatternoptions,
        series: barData.Barpatternseries
    },
},
{
    id: 10,
    title: "Bar With Image Fill",
    type: "bar",
    height: "320",
    chart: {
        options: barData.Barimgoptions,
        series: barData.Barimgseries
    },
},
]

</script>

<template>
    <Pageheader :propData="dataToPass" />
    <!-- Start::row-1 -->
    <div class="row">
        <div class="col-xl-6" v-for="card in ApexBarChart" :key="card.id">
            <ChartCards :card="card" :title="card.title" />
        </div>
    </div>
    <!--End::row-1 -->
</template>

<style scoped>
/* Add your styles here */
</style>
