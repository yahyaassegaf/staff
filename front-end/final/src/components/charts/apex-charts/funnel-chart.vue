<script lang="ts" setup>
import * as funnelData from '../../../data/apexcharts/apexchart-funnel';
import { defineAsyncComponent } from 'vue'
import Pageheader from '../../../shared/components/pageheader/pageheader.vue';

const ChartCards = defineAsyncComponent(() => import('../../../shared/components/@spk/chart-cards.vue'));

const dataToPass = {
    title: "Charts",
    subtitle: "Apex Charts",
    currentpage: "Apex Funnel Charts",
    activepage: "Apex Funnel Charts"
}

const ApexFunnelChart = [{
    id: 1,
    title: "Funnel Chart",
    type: "bar",
    height: "350",
    chart: {
        options: funnelData.Funneloptions,
        series: funnelData.Funnelseries
    },
},
{
    id: 2,
    title: "Pyramid Chart",
    type: "bar",
    height: "350",
    chart: {
        options: funnelData.Pyramidoptions,
        series: funnelData.Pyramidseries
    },
}
]

</script>

<template>
    <Pageheader :propData="dataToPass" />

    <!-- Start::row-1 -->
    <div class="row">
        <div class="col-xl-6" v-for="card in ApexFunnelChart" :key="card.id">
            <ChartCards :card="card" :title="card.title" />
        </div>
    </div>
    <!--End::row-1 -->
</template>

<style scoped>
/* Add your styles here */
</style>
