<script lang="ts" setup>
import type { PropType } from 'vue'

// Define a more explicit shape for `card` prop if possible
interface ChartOptions {
    options: any
    series: any
    secondaryOptions?: any
    secondarySeries?: any
    tertiaryOptions?: any
    tertiarySeries?: any
}

interface CardData {
    chart?: ChartOptions
    height?: number | string
    type?: string
}

defineProps({
    customCardClass: String,
    cardHeaderClass: String,
    cardBodyClass: String,
    cardFooterClass: String,
    chartId: String,
    title: String,
    showCardFooter: {
        type: Boolean,
        default: false,
    },
    showGitImg: {
        type: Boolean,
        default: false,
    },
    btnGroup: {
        type: Boolean,
        default: false,
    },
    card: {
        type: Object as PropType<CardData>,
        required: true,
    },
})
</script>
<template>
<div :class="['card custom-card', customCardClass]">
    <div :class="['card-header justify-content-between', cardHeaderClass]">
        <div class="card-title"> {{ title }}</div>
        <template v-if="btnGroup">
            <div class="btn-group ms-auto">
                <button class="btn btn-primary btn-sm" id="one_month">1M</button>
                <button class="btn btn-primary btn-sm" id="six_months">6M</button>
                <button class="btn btn-primary btn-sm" id="one_year">1Y</button>
                <button class="btn btn-primary btn-sm" id="all">ALL</button>
                <!-- <button class="btn btn-primary btn-sm" id="ytd">ALL</button> -->
            </div>
        </template>
        <slot name="cardHeader"></slot>
    </div>
    <div :class="['card-body', cardBodyClass]">
        <slot name="showData"></slot>
        <div class="content-wrapper" :id="chartId">
            <apexchart v-if="card.chart && card.chart.options" :height="card.height" :type="card.type" :options="card.chart.options" :series="card.chart.series" />
            <template v-if="showGitImg">
                <div class="github-style d-flex align-items-center">
                    <div class="me-2">
                        <img class="userimg rounded" src="/images/faces/1.jpg" data-hovercard-user-id="634573" alt="" width="38" height="38">
                    </div>
                    <div class="userdetails lh-1">
                        <a class="username fw-semibold fs-14">coder</a>
                        <span class="cmeta d-block mt-1">
                            <span class="commits">95</span> commits
                        </span>
                    </div>
                </div>
            </template>
            <apexchart v-if="card.chart.secondaryOptions" :height="card.height" :type1="card.type" :options="card.chart.secondaryOptions" :series="card.chart.secondarySeries" />
            <apexchart v-if="card.chart.tertiaryOptions" :height="card.height" :type="card.type" :options="card.chart.tertiaryOptions" :series="card.chart.tertiarySeries" />
        </div>
        <slot name="ChartCardComponent"></slot>
    </div>
    <template v-if="showCardFooter">
        <div :class="['card-footer', cardFooterClass]">
            <slot name="cardFooter"></slot>
        </div>
    </template>
</div>
</template>

<style scoped></style>
