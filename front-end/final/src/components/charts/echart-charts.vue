<script lang="ts" setup>

import { defineAsyncComponent } from 'vue'

import {
    ref
} from 'vue';

const VChart = defineAsyncComponent(() => import('vue-echarts'))
import * as echartData from "../../data/echarts/bar.ts"
import Pageheader from '../../shared/components/pageheader/pageheader.vue';

const dataToPass = {
    title: "Charts",
    currentpage: "Echart Charts",
    activepage: "Echart Charts"
}

const chartOptions = ref([{
        title: "Basic Line Chart",
        option: echartData.chartOption
    },
    {
        title: "Smoothed Line Chart",
        option: echartData.smoothedOpction
    },
    {
        title: "Basic Area Chart",
        option: echartData.basicAreaOption
    },
    {
        title: "Stacked Line Chart",
        option: echartData.stackedOption
    },
    {
        title: "Stacked Area Chart",
        option: echartData.stackedAreaOption
    },
    {
        title: "Step Line Chart",
        option: echartData.stepLineOption
    },
    {
        title: "Basic Bar Chart",
        option: echartData.basicBarOption
    },
    {
        title: "Bar with Background Chart",
        option: echartData.barWithBackGroundOption
    },
    {
        title: "Single Bar Chart",
        option: echartData.singleBarOption
    },
    {
        title: "Waterfall Chart",
        option: echartData.waterFallOption
    },
    {
        title: "Bar With Negative Values Chart",
        option: echartData.negativeValueOption
    },
    {
        title: "Bar With Labels Chart",
        option: echartData.withLableOption
    },
    {
        title: "Horizontal Bar Chart",
        option: echartData.horizontalBarOption
    },
    {
        title: "Horizontal Stacked Bar Chart",
        option: echartData.horizontalStackedBar
    },
    {
        title: "Pie Chart",
        option: echartData.pieChartoption
    },
    {
        title: "Doughnut Chart",
        option: echartData.doughnutOption
    },
    {
        title: "Basic Scatter Chart",
        option: echartData.basicScatterOption
    },
    {
        title: "Bubble Chart",
        option: echartData.bubbleOption.option
    },
    {
        title: "Candlestick Chart",
        option: echartData.candlestickOption
    },
    {
        title: "Basic Radar Chart",
        option: echartData.basicRadarOption
    },
    {
        title: "Heatmap Charts",
        option: echartData.heatmapOption.option
    },
    {
        title: "Treemap Charts",
        option: echartData.treemapOption
    },
    {
        title: "Funnel Chart",
        option: echartData.funnelOption
    },
    {
        title: "Basic Gauge Chart",
        option: echartData.gaugeOption
    },
    {
        title: "Simple Graph Chart",
        option: echartData.graphOption
    },
    {
        title: "Pictorial Bar Chart",
        option: echartData.pictorialOption.makeOption('pictorialBar')
    }
]);
</script>

<template>
<Pageheader :propData="dataToPass" />

<div class="row">
    <div v-for="(chart, index) in chartOptions" :key="index" class="col-xl-6">
        <div class="card custom-card">
            <div class="card-header">
                <div class="card-title">{{ chart.title }}</div>
            </div>
            <div class="card-body">
                <v-chart class="echart-charts" :option="chart.option" autoresize />
            </div>
        </div>
    </div>
</div>
</template>

<style scoped>
/* Add your styles here */
</style>
