<script setup>
import { defineAsyncComponent, ref } from 'vue';
import Pageheader from '../../../shared/components/pageheader/pageheader.vue';

const Vue3StarRatings = defineAsyncComponent(() =>
    import('vue3-star-ratings').then(m => m.default)
)

const myRating = ref(4.5)
const numberOfStars = ref(5)
const starSize = ref(32)
const starColor = ref('#ff9800')
const inactiveColor = ref('#d3d3d3')

// Data for the page
const dataToPass = {
    title: "Advanced Ui",
    currentpage: "Ratings",
    activepage: "Ratings"
}

// Ensure the star size doesn't exceed 50
const handleStarSizeChange = (value) => {
    starSize.value = Math.min(value, 50);  // Restrict max size to 50px
}
</script>

<template>
    <Pageheader :propData="dataToPass" />
    <div class="row d-flex justify-content-center">
        <div class="col-xxl-6 col-xl-6">
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">
                        Vue3 Star Ratings
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <!-- Number of Stars Input -->
                        <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                            <label for="input-number-stars" class="form-label">Number of Stars:</label>
                            <input type="number" class="form-control" id="input-number-stars"
                                v-model.number="numberOfStars" min="1" max="10" placeholder="Number of Stars">
                        </div>
                        <!-- Star Size Input -->
                        <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                            <label for="input-number-size" class="form-label">Star Size (px):</label>
                            <input type="number" class="form-control" id="input-number-size" v-model.number="starSize"
                                @input="handleStarSizeChange($event.target.value)" min="1" max="50"
                                placeholder="Star Size (max 50px)">
                        </div>
                        <!-- Active Star Color Input -->
                        <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                            <label for="input-label" class="form-label">Active Star Color:</label>
                            <input class="form-control form-input-color" type="color" v-model="starColor">
                        </div>
                        <!-- Inactive Star Color Input -->
                        <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                            <label for="input-label" class="form-label">Inactive Star Color:</label>
                            <input class="form-control form-input-color" type="color" v-model="inactiveColor">
                        </div>
                    </div>

                    <div class="d-flex flex-wrap align-items-center gap-3">
                        <p class="fs-14 mb-0 fw-medium">Show Some <span class="text-danger">&#10084;</span> with rating:</p>
                        <div id="rater-basic">
                            <Vue3StarRatings 
                                v-model="myRating" 
                                :starSize="starSize" 
                                :starColor="starColor" 
                                :inactiveColor="inactiveColor"
                                :numberOfStars="numberOfStars" 
                                :disableClick="false" 
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped></style>
