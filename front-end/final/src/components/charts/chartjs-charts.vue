<script lang="ts" setup>
import { DoughnutChart, LineChart, BarChart, ScatterChart, BubbleChart, RadarChart, PolarAreaChart, PieChart } from 'vue-chart-3';
import { Chart as ChartJS, registerables } from 'chart.js';
import * as chartjsData from "../../data/chartjs/chartjs";
import { ref } from 'vue';
import Pageheader from '../../shared/components/pageheader/pageheader.vue';
import ChartjsCards from '../../shared/components/@spk/charts/chartjs-cards.vue';

ChartJS.register(...registerables);

const dataToPass = {
    current: "ChartJs Charts",
    list: ['Charts', 'ChartJs Charts']
}
const chartjs = ref([
    { type: LineChart, title: 'Chartjs Line Chart', data: chartjsData.linedata },
    { type: BarChart, title: 'Chartjs Bar Chart', data: chartjsData.bardata },
    { type: PieChart, title: 'Chartjs Pie Chart', data: chartjsData.singledata },
    { type: DoughnutChart, title: 'Chartjs Doughnut Chart', data: chartjsData.singledata },
    { type: PolarAreaChart, title: 'Chartjs Polar Chart', data: chartjsData.singledata2 },
    { type: RadarChart, title: 'Chartjs Radial Chart', data: chartjsData.radialdata },
    { type: ScatterChart, title: 'Chartjs Scatter Chart', data: chartjsData.scatterdata },
    { type: BubbleChart, title: 'Chartjs Bubble Chart', data: chartjsData.Bubbledata },
])

</script>

<template>
    <Pageheader :propData="dataToPass" />

    <!-- Start::row-1 -->
    <div class="row">
        <div v-for="(card, index) in chartjs" :key="index" class="col-xl-6">
            <ChartjsCards :title="card.title" :type="card.type" :data="card.data" />
        </div>
    </div>
</template>
