<script setup>
import * as treemapData from '../../../data/apexcharts/apexchart-treemap.ts';
import { defineAsyncComponent } from 'vue'
import Pageheader from '../../../shared/components/pageheader/pageheader.vue';

const ChartCards = defineAsyncComponent(() => import('../../../shared/components/@spk/chart-cards.vue'));

const dataToPass = {
    title: "Charts",
    subtitle: "Apex Charts",
    currentpage: "Apex Treemap Charts",
    activepage: "Apex Treemap Charts"
}


const ApexTreemapChart = [{
    id: 1,
    title: "Basic Treemap Chart",
    type: "treemap",
    height: "350",
    chart: {
        options: treemapData.Treebasicoptions,
        series: treemapData.Treebasicseries
    },
},
{
    id: 2,
    title: "Multi Dimensional Treemap Chart",
    type: "treemap",
    height: "350",
    chart: {
        options: treemapData.Treemultioptions,
        series: treemapData.Treemultiseries
    },
},
{
    id: 3,
    title: "Distributed Treemap Chart",
    type: "treemap",
    height: "350",
    chart: {
        options: treemapData.Treedisoptions,
        series: treemapData.Treedisseries
    },
},
{
    id: 4,
    title: "Treemap with color ranges",
    type: "treemap",
    height: "350",
    chart: {
        options: treemapData.Treecoloroptions,
        series: treemapData.Treecolorseries
    },
},
]

</script>

<template>
    <Pageheader :propData="dataToPass" />

    <!-- Start::row-1 -->
    <div class="row">
        <div class="col-xl-6" v-for="card in ApexTreemapChart" :key="card.id">
            <ChartCards :card="card" :title="card.title" />
        </div>
    </div>
    <!--End::row-1 -->
</template>

<style scoped>
/* Add your styles here */
</style>
