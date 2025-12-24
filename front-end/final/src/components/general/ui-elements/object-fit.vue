<script setup>
import Pageheader from "../../../shared/components/pageheader/pageheader.vue";
import ShowcodeCard from '../../../shared/UI/showcodeCard.vue';
import * as prism from '../../../data/prismCode/ui-elements/objectfit.ts';

const dataToPass = {
    title: "Ui Elements",
    currentpage: "Object Fit",
    activepage: "Object Fit"
}

</script>

<template>
<Pageheader :propData="dataToPass" />

<!-- Start::row-1 -->
<div class="row">
    <div class="col-xxl-6 col-xl-6 col-lg-6 col-md-6 col-sm-12">
        <ShowcodeCard title="Object Fit Contain" custom-card-body-class="object-fit-container" :code="prism.ObjectFitContain">
            <img src="/images/media/media-28.jpg" class="object-fit-contain border rounded" alt="...">
        </ShowcodeCard>
    </div>
    <div class="col-xxl-6 col-xl-6 col-lg-6 col-md-6 col-sm-12">
        <ShowcodeCard title="Object Fit Cover" custom-card-body-class="object-fit-container" :code="prism.ObjectFitCover">
            <img src="/images/media/media-28.jpg" class="object-fit-cover border rounded" alt="...">
        </ShowcodeCard>
    </div>
    <div class="col-xxl-6 col-xl-6 col-lg-6 col-md-6 col-sm-12">
        <ShowcodeCard title="Object Fit Fill" custom-card-body-class="object-fit-container" :code="prism.ObjectFitFill">
            <img src="/images/media/media-28.jpg" class="object-fit-fill border rounded" alt="...">
        </ShowcodeCard>
    </div>
    <div class="col-xxl-6 col-xl-6 col-lg-6 col-md-6 col-sm-12">
        <ShowcodeCard title="Object Fit Scale Down" custom-card-body-class="object-fit-container" :code="prism.ObjectFitScaleDown">
            <img src="/images/media/media-28.jpg" class="object-fit-scale border rounded" alt="...">
        </ShowcodeCard>
    </div>
    <div class="col-xxl-6 col-xl-6 col-lg-6 col-md-6 col-sm-12">
        <ShowcodeCard title="Object Fit None" custom-card-body-class="object-fit-container" :code="prism.ObjectFitNone">
            <img src="/images/media/media-28.jpg" class="object-fit-none border rounded" alt="...">
        </ShowcodeCard>
    </div>
    <div class="col-xxl-6 col-xl-6 col-lg-6 col-md-6 col-sm-12">
        <ShowcodeCard title="Object Fit Contain (SM - responsive)" custom-card-body-class="object-fit-container" :code="prism.ObjectFitContainSMResponsive">
            <img src="/images/media/media-28.jpg" class="object-fit-sm-contain border rounded" alt="...">
        </ShowcodeCard>
    </div>
    <div class="col-xxl-6 col-xl-6 col-lg-6 col-md-6 col-sm-12">
        <ShowcodeCard title="Object Fit Contain (MD - responsive)" custom-card-body-class="object-fit-container" :code="prism.ObjectFitContainMDResponsive">
            <img src="/images/media/media-28.jpg" class="object-fit-md-contain border rounded" alt="...">
        </ShowcodeCard>
    </div>
    <div class="col-xxl-6 col-xl-6 col-lg-6 col-md-6 col-sm-12">
        <ShowcodeCard title="Object Fit Contain (LG - responsive)" custom-card-body-class="object-fit-container" :code="prism.ObjectFitContainLGResponsive">
            <img src="/images/media/media-28.jpg" class="object-fit-lg-contain border rounded" alt="...">
        </ShowcodeCard>
    </div>
    <div class="col-xxl-6 col-xl-6 col-lg-6 col-md-6 col-sm-12">
        <ShowcodeCard title="Object Fit Contain (XL - responsive)" custom-card-body-class="object-fit-container" :code="prism.ObjectFitContainXLResponsive">
            <img src="/images/media/media-28.jpg" class="object-fit-xl-contain border rounded" alt="...">
        </ShowcodeCard>
    </div>
    <div class="col-xxl-6 col-xl-6 col-lg-6 col-md-6 col-sm-12">
        <ShowcodeCard title="Object Fit Contain (XXL - responsive)" custom-card-body-class="object-fit-container" :code="prism.ObjectFitContainXXLResponsive">
            <img src="/images/media/media-28.jpg" class="object-fit-xxl-contain border rounded" alt="...">
        </ShowcodeCard>
    </div>
    <div class="col-xxl-6 col-xl-6 col-lg-6 col-md-6 col-sm-12">
        <ShowcodeCard title="Object Fit Contain Video" custom-card-body-class="object-fit-container" :code="prism.ObjectFitContainVideo">
            <video src="../../../assets/video/1.mp4" class="object-fit-contain rounded border" autoplay loop muted></video>
        </ShowcodeCard>
    </div>
    <div class="col-xxl-6 col-xl-6 col-lg-6 col-md-6 col-sm-12">
        <ShowcodeCard title="Object Fit Cover Video" custom-card-body-class="object-fit-container" :code="prism.ObjectFitCoverVideo">
            <video src="../../../assets/video/1.mp4" class="object-fit-cover rounded border" autoplay loop muted></video>
        </ShowcodeCard>
    </div>
    <div class="col-xxl-6 col-xl-6 col-lg-6 col-md-6 col-sm-12">
        <ShowcodeCard title="Object Fit Fill Video" custom-card-body-class="object-fit-container" :code="prism.ObjectFitFillVideo">
            <video src="../../../assets/video/1.mp4" class="object-fit-fill rounded border" autoplay loop muted></video>
        </ShowcodeCard>
    </div>
    <div class="col-xxl-6 col-xl-6 col-lg-6 col-md-6 col-sm-12">
        <ShowcodeCard title="Object Fit Scale Video" custom-card-body-class="object-fit-container" :code="prism.ObjectFitScaleVideo">
            <video src="../../../assets/video/1.mp4" class="object-fit-scale rounded border" autoplay loop muted></video>
        </ShowcodeCard>
    </div>
    <div class="col-xxl-6 col-xl-6 col-lg-6 col-md-6 col-sm-12">
        <ShowcodeCard title=" Object Fit None Video" custom-card-body-class="object-fit-container" :code="prism.ObjectFitNoneVideo">
            <video src="../../../assets/video/1.mp4" class="object-fit-none rounded border" autoplay loop muted></video>
        </ShowcodeCard>
    </div>
</div>
<!-- End::row-1 -->

</template>

<style scoped>
/* Add your styles here */
</style>
