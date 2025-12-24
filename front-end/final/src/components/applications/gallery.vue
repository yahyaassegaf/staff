<script setup>
import Pageheader from "../../shared/components/pageheader/pageheader.vue";
import DescriptionGallery from "../../shared/UI/descriptionGallery.vue";
import GalleryList from "../../shared/UI/galleryList.vue";

const dataToPass = {
    title: "Applications",
    currentpage: "Gallery",
    activepage: "Gallery"
}


</script>

<template>
    <Pageheader :propData="dataToPass" />
    <!-- Start::row-1 -->
    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">
                        Simple Image Gallery
                    </div>
                </div>
                <div class="card-body">
                    <GalleryList />
                </div>
            </div>
        </div>
    </div>
    <!--End::row-1 -->

    <!-- Start:: row-2 -->
    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">
                        Images with Description
                    </div>
                </div>
                <div class="card-body">
                    <DescriptionGallery />
                </div>
            </div>
        </div>
    </div>
    <!-- End:: row-2 -->



</template>

<style scoped>
/* Add your styles here */
</style>
