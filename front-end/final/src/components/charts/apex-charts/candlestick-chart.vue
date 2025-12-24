<script setup>
import * as candleData from '../../../data/apexcharts/apexchart-candlestick';
import { defineAsyncComponent } from 'vue'
import Pageheader from '../../../shared/components/pageheader/pageheader.vue';

const ChartCards = defineAsyncComponent(() => import('../../../shared/components/@spk/chart-cards.vue'));

const dataToPass = {
    title: "Charts",
    subtitle: "Apex Charts",
    currentpage: "Apex Candlestick Charts",
    activepage: "Apex Candlestick Charts"
}

const ApexCandlestickChart = [{
    id: 1,
    title: "Basic Candlestick Chart",
    type: "candlestick",
    height: "350",
    chart: {
        options: candleData.Candbasicoptions,
        series: candleData.Candbasicseries
    },
},
{
    id: 2,
    title: "Candlestick Synced With Brush Chart",
    type: "",
    height: "168",
    secondaryheight: "20",
    chart: {
        options: candleData.Candsyncoptions,
        series: candleData.Candsyncedseries,
        secondaryOptions: candleData.CandeoptionsBar,
        secondarySeries: candleData.CandseriesBar
    },
},
{
    id: 3,
    title: "Candlestick With Category X-axis",
    type: "candlestick",
    height: "350",
    chart: {
        options: candleData.Candaxisoptions,
        series: candleData.Candaxisseries
    },
},
{
    id: 'candlestick-line',
    title: "Candlestick With Line Chart",
    type: "candlestick",
    height: "350",
    chart: {
        options: candleData.Candlineoptions,
        series: candleData.Candlineseries
    },
}
]

</script>

<template>
    <Pageheader :propData="dataToPass" />

    <!-- Start::row-1 -->
    <div class="row">
        <div class="col-xl-6" v-for="card in ApexCandlestickChart" :key="card.id">
            <ChartCards :card="card" :title="card.title" :id="card.id" />
        </div>
    </div>
    <!--End::row-1 -->

</template>

<style scoped>
/* Add your styles here */
</style>
