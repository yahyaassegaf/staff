<script setup>
import Pageheader from '../../shared/components/pageheader/pageheader.vue';
const dataToPass = {
    title: "Pages",
    currentpage: "Empty",
    activepage: "Empty"
}

</script>

<template>
    <Pageheader :propData="dataToPass" />
    <!-- Start::row-1 -->
    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-body">
                    <h6 class="mb-0">Empty Card</h6>
                </div>
            </div>
        </div>
    </div>
    <!--End::row-1 -->
</template>

<style scoped>
/* Add your styles here */
</style>
