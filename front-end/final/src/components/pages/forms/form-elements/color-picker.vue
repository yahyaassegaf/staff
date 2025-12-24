<script setup>
import * as prism from '../../../../data/prismCode/forms/formelements/colorpicker';
import { ColorPicker } from 'vue3-colorpicker';
import Pageheader from '../../../../shared/components/pageheader/pageheader.vue';
import ShowcodeCard from '../../../../shared/UI/showcodeCard.vue';

const dataToPass = {
    title: "Forms",
    subtitle: "Form Elements",
    currentpage: "Color Pickers",
    activepage: "Color Pickers"
}

</script>

<template>
    <Pageheader :propData="dataToPass" />

    <div class="row">
        <div class="col-xl-6 custom-color-picker">
            <ShowcodeCard :code="prism.vue3CircleWithDisabledAlphaColorPicker"
                title="vue3 Circle with Disabled Alpha Color Picker">
                <div class="text-center text-sm-start">
                    <color-picker v-model:pureColor="pureColor" shape="circle" format="rgb" pickerType="chrome"
                        useType="pure" :disableAlpha="true" />
                </div>
            </ShowcodeCard>
        </div>
        <div class="col-xl-6 custom-color-picker square-colorPicker">
            <ShowcodeCard :code="prism.vue3GradientColorPicker" title="vue3 Gradient Color Picker">
                <div class="text-center text-sm-start">
                    <color-picker v-model:gradientColor="gradientColor" useType="both" lang="en" />
                </div>
            </ShowcodeCard>
        </div>
        <div class="col-xl-12">
            <ShowcodeCard :code="prism.vuetifyColorPicker" title="Bootstrap Color Picker"  custom-card-body-class="d-flex justify-content-between custom-color-picker">
                <input type="color" class="form-control form-control-color border-0" id="exampleColorInput" value="#136ad0"  title="Choose your color" />
            </ShowcodeCard>
        </div>
    </div>

</template>

<style scoped>
/* Add your styles here */
</style>
