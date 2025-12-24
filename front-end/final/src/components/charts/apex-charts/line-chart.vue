<script lang="ts" setup>
import * as lineData from '../../../data/apexcharts/apexchart-linemap';
import { defineAsyncComponent, onMounted, onUnmounted } from 'vue'
import Pageheader from '../../../shared/components/pageheader/pageheader.vue';

const ChartCards = defineAsyncComponent(() => import('../../../shared/components/@spk/chart-cards.vue'));

const dataToPass = {
    title: "Charts",
    subtitle: "Apex Charts",
    currentpage: "Apex Line Charts",
    activepage: "Apex Line Charts"
}

const ApexLineChart = [{
    id: 1,
    title: "Basic Line Chart",
    type: "line",
    height: "300",
    chart: {
        options: lineData.Linebasicoptions,
        series: lineData.Linebasicseries
    }
},
{
    id: 2,
    title: "Line Chart With Data Labels",
    type: "line",
    height: "300",
    chart: {
        options: lineData.Linedataoptions,
        series: lineData.Linedataseries
    }
},
{
    id: 3,
    title: "Zoomable Time Series",
    type: "area",
    height: "300",
    chart: {
        options: lineData.Linezoomoptions,
        series: lineData.Linezoomseries
    }
},
{
    id: 4,
    title: "Line With Annotations",
    type: "line",
    height: "300",
    chart: {
        options: lineData.Lineannotationoptions,
        series: lineData.Lineannotationseries
    }
},
{
    id: 5,
    title: "Brush Chart",
    type1: "area",
    type: "line",
    height: "143",
    secondaryHeight: '20',
    chart: {
        options: lineData.Linebrushoptions,
        series: lineData.Linebrushseries,
        secondaryOptions: lineData.optionsLine,
        secondarySeries: lineData.seriesLine
    }
},
{
    id: 6,
    title: "StepLine Chart",
    type: "line",
    height: "300",
    chart: {
        options: lineData.Linestepoptions,
        series: lineData.Linestepseries
    }
},
{
    id: 7,
    title: "Gradient Line Chart",
    type: "line",
    height: "300",
    chart: {
        options: lineData.Linegardientoptions,
        series: lineData.Linegardientseries
    }
},
{
    id: 8,
    title: "Missing/Null Values Chart",
    type: "line",
    height: "300",
    chart: {
        options: lineData.Linenulloptions,
        series: lineData.Linenullseries
    }
},
{
    id: 9,
    title: "Real Time Chart",
    type: "line",
    height: "300",
    chart: {
        options: lineData.Linerealoptions,
        series: lineData.Linerealseries
    }
},
{
    id: 10,
    title: "Dashed Line Chart",
    type: "line",
    height: "300",
    chart: {
        options: lineData.Linedashoptions,
        series: lineData.Linedashseries
    }
},
{
    id: 11,
    title: "Syncing Charts",
    height: "166",
    chart: {
        options: lineData.Linesyncoptions,
        series: lineData.Linesyncseries,
        secondaryOptions: lineData.optionsLine2,
        secondarySeries: lineData.seriesLine2,
        tertiaryOptions: lineData.optionsArea,
        tertiarySeries: lineData.seriesArea
    }
}
]

const setDataLineChart = () => {
    const intervalId = setInterval(() => { }, 3000);
    onUnmounted(() => {
        clearInterval(intervalId);
    });
}

onMounted(() => {
    setDataLineChart();
}) 
</script>

<template>
    <Pageheader :propData="dataToPass" />
    <!-- Start::row-1 -->
    <div class="row">
        <div class="col-xl-6" v-for="card in ApexLineChart" :key="card.id">
            <ChartCards :card="card" :title="card.title" />
        </div>
    </div>
    <!--End::row-1 -->
</template>

<style scoped>
/* Add your styles here */
</style>
