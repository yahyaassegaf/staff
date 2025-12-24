<script setup>
import { TeamMembers } from "../../data/pages/teamdata";
import Pageheader from "../../shared/components/pageheader/pageheader.vue";
import SpkReusableTeamCard from "../../shared/components/@spk/pages/spk-reusable-teamCard.vue";

const dataToPass = {
    title: "Pages",
    currentpage: "Team",
    activepage: "Team"
}
</script>

<template>
<Pageheader :propData="dataToPass" />
<!-- Start:: row-1 -->
<div class="row row-cols-xxl-5 row-cols-xl-4 row-cols-lg-3 row-cols-md-3 row-cols-sm-2 row-cols-1">
    <div class="col" v-for="(idx) in TeamMembers" :key="idx.id">
        <SpkReusableTeamCard :card="idx" />
    </div>
</div>
<!-- End:: row-1 -->
</template>

<style scoped>
/* Add your styles here */
</style>
