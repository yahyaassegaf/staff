<script lang="ts" setup>
import * as culumnData from '../../../data/apexcharts/apexchart-column';
import { defineAsyncComponent } from 'vue'
import Pageheader from '../../../shared/components/pageheader/pageheader.vue';

const ChartCards = defineAsyncComponent(() => import('../../../shared/components/@spk/chart-cards.vue'));

const dataToPass = {
    title: "Charts",
    subtitle: "Apex Charts",
    currentpage: "Apex Column Charts",
    activepage: "Apex Column Charts"
}

const ApexColumnChart = [{
    id: 1,
    title: "Basic Column Chart",
    type: "bar",
    height: "320",
    chart: {
        options: culumnData.Columnbasicoptions,
        series: culumnData.Columnbasicseries
    },
},
{
    id: 2,
    title: "Column Chart With Datalabels",
    type: "bar",
    height: "320",
    chart: {
        options: culumnData.Columnchartoptions,
        series: culumnData.Columnchartseries
    },
},
{
    id: 3,
    title: "Stacked Column Chart",
    type: "bar",
    height: "320",
    chart: {
        options: culumnData.Stackcolumnoptions,
        series: culumnData.Stackcolumnseries
    },
},
{
    id: 4,
    title: "100% Stacked Column Chart",
    type: "bar",
    height: "320",
    chart: {
        options: culumnData.Columnstack1options,
        series: culumnData.Columnstack1series
    },
},
{
    id: 5,
    title: "Column Chart With Markers",
    type: "bar",
    height: "320",
    chart: {
        options: culumnData.Columnmakeroptions,
        series: culumnData.Columnmakerseries
    },
},
{
    id: 6,
    title: "Column Chart With Rotated Labels",
    cardBodyClass: "custom-chart-class",
    type: "bar",
    height: "320",
    chart: {
        options: culumnData.Columnrotateoptions,
        series: culumnData.Columnrotateseries
    },
},
{
    id: 7,
    title: "Column Chart With Negative Values",
    type: "bar",
    height: "320",
    chart: {
        options: culumnData.Columnvalueoptions,
        series: culumnData.Columnvalueseries
    },
},
{
    id: 8,
    title: "Range Column Chart",
    type: "bar",
    height: "320",
    chart: {
        options: culumnData.Columnoptions,
        series: culumnData.Columnseries
    },
},
{
    id: 9,
    title: "Distributed Columns Chart",
    type: "bar",
    height: "320",
    chart: {
        options: culumnData.Column1options,
        series: culumnData.Column1series
    },
},
]

</script>

<template>
    <Pageheader :propData="dataToPass" />

    <!-- Start::row-1 -->
    <div class="row">
        <div class="col-xl-6" v-for="card in ApexColumnChart" :key="card.id">
            <ChartCards :card="card" :title="card.title" :cardBodyClass="card.cardBodyClass" />
        </div>
    </div>
    <!--End::row-1 -->

</template>

<style scoped>
/* Add your styles here */
</style>
