<script setup>
import * as  WalletData from "../../../data/dashboards/nft/wallet-integrationdata.ts";
import Pageheader from "../../../shared/components/pageheader/pageheader.vue";

const dataToPass = {
    activepage: "Wallet Integration",
    title: "Dashboards",
    subtitle: "NFT",
    currentpage: "Wallet Integration",
}
</script>

<template>
<Pageheader :propData="dataToPass" />
<!-- Start::row-1 -->
<div class="row">
    <div class="col-xl-3">
        <div class="card custom-card overflow-hidden">
            <div class="card-header">
                <div class="card-title">
                    Choose Network
                </div>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush nft-list">
                    <li class="list-group-item" v-for="(idx) in WalletData.WalletList" :key="idx.id">
                        <a href="javascript:void(0);" class="stretched-link"></a>
                        <div class="d-flex align-items-center gap-2">
                            <div>
                                <span class="avatar avatar-rounded avatar-sm bg-light p-1">
                                    <img :src="idx.image" alt="">
                                </span>
                            </div>
                            <div class="fs-14 fw-medium">{{ idx.name }}</div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <div class="col-xl-9">
        <div class="card custom-card">
            <div class="card-header justify-content-between">
                <div class="card-title">
                    Choose Wallet
                </div>
                <div>
                    <button class="btn btn-secondary btn-wave">Add New Wallet</button>
                </div>
            </div>
            <div class="card-body p-4">
                <div class="row justify-content-center">
                    <div class="col-xxl-3" v-for="(idx) in WalletData.WalletCards" :key="idx.id">
                        <div :class="`card custom-card nft-wallet ${idx.active ? 'active' : ''} `">
                            <div class="card-body p-3">
                                <a href="javascript:void(0);" class="stretched-link"></a>
                                <div class="d-flex align-items-center justify-content-center gap-3">
                                    <div class="lh-1">
                                        <span :class="`avatar avatar-rounded ${idx.avatarClass} bg-light`">
                                            <img :src="idx.image" alt="">
                                        </span>
                                    </div>
                                    <div>
                                        <h6 class="fw-medium mb-0">{{ idx.name }}</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <div>
                    <button class="btn btn-success float-end btn-wave">Connect</button>
                </div>
            </div>
        </div>
    </div>
</div>
<!--End::row-1 -->
</template>

<style scoped>
/* Add your styles here */
</style>
