<script lang="ts" setup>
import * as prism from '../../../../data/prismCode/forms/formelements/formselect.ts';
import canada_flag from '/images/flags/canada_flag.jpg';
import germany_flag from '/images/flags/germany_flag.jpg';
import spain_flag from '/images/flags/spain_flag.jpg';
import us_flag from '/images/flags/us_flag.jpg';
import Pageheader from "../../../../shared/components/pageheader/pageheader.vue";
import ShowcodeCard from '../../../../shared/UI/showcodeCard.vue';
import Multiselect from 'vue-multiselect';
import { ref } from 'vue';

// Header info
const dataToPass = {
    title: 'Forms',
    subtitle: 'Form Elements',
    currentpage: 'Form Select',
    activepage: 'Form Select'
}

// Select values
const value = ref<any[]>([])
const options = ref<any[]>([])

const multiSelecteValue = ref(['Choice 1', 'Choice 2', 'Choice 3'])
const singleSelecteValue = ref<string | null>(null)
const singleSelecteOptions = ref(['Choice 1', 'Choice 2', 'Choice 3'])

const groupvalue = ref([])
const multigroupvalue = ref([])

const groupOptions = ref([
    {
        language: 'UK',
        libs: [
            { name: 'London', category: 'city' },
            { name: 'Manchester', category: 'city' },
            { name: 'Liverpool', category: 'city' }
        ]
    },
    {
        language: 'FR',
        libs: [
            { name: 'Paris', category: 'city' },
            { name: 'Lyon', category: 'city' },
            { name: 'Marseille', category: 'city' }
        ]
    },
    {
        language: 'DE',
        libs: [
            { name: 'Hamburg', category: 'city' },
            { name: 'Munich', category: 'city' },
            { name: 'Berlin', category: 'city' }
        ]
    },
    {
        language: 'US',
        libs: [
            { name: 'York', category: 'city' },
            { name: 'Washington', category: 'city' },
            { name: 'Michigan', category: 'city' }
        ]
    },
    {
        language: 'SP',
        libs: [
            { name: 'Madrid', category: 'city' },
            { name: 'Barcelona', category: 'city' },
            { name: 'Malaga', category: 'city' }
        ]
    },
    {
        language: 'CA',
        libs: [
            { name: 'Montreal', category: 'city' },
            { name: 'Toronto', category: 'city' },
            { name: 'Vancouver', category: 'city' }
        ]
    }
])

const searchSelectedValue = ref({
    name: 'Vue.js',
    language: 'JavaScript'
})

const searchSelectedOptions = ref([
    { name: 'Vue.js', language: 'JavaScript' },
    { name: 'Rails', language: 'Ruby' },
    { name: 'Sinatra', language: 'Ruby' },
    { name: 'Laravel', language: 'PHP' },
    { name: 'Phoenix', language: 'Elixir' }
])

const tagValue = ref([
    { name: 'Javascript', code: 'js' }
])

const tagOptions = ref([
    { name: 'Vue.js', code: 'vu' },
    { name: 'Javascript', code: 'js' },
    { name: 'Open Source', code: 'os' }
])

const customValue = ref({
    title: 'Explorer',
    desc: 'Discovering new species!',
    img: canada_flag
})

const customOptions = ref([
    { title: 'Space Pirate', desc: 'More space battles!', img: germany_flag },
    { title: 'Merchant', desc: 'PROFIT!', img: us_flag },
    { title: 'Explorer', desc: 'Discovering new species!', img: canada_flag },
    { title: 'Miner', desc: 'We need to go deeper!', img: spain_flag }
])

const edvalue = ref(null)
const disabled = ref(true)

// Custom methods
const nameWithLang = (opt: { name: string; language: string }) => {
    return `${opt.name} — [${opt.language}]`
}

const addTag = (newTag: string) => {
    const tag = {
        name: newTag,
        code: newTag.substring(0, 2) + Math.floor(Math.random() * 10000000)
    }
    options.value.push(tag)
    value.value.push(tag)
}

const customLabel = (opt: { title: string; desc: string }) => {
    return `${opt.title} – ${opt.desc}`
}
</script>

<template>
<Pageheader :propData="dataToPass" />

<!-- Start:: row-4 -->
<h6 class="fw-medium mb-2">Choices:</h6>
<div class="row">
    <div class="col-xl-12">
        <div class="row">
            <div class="col-xl-12">
                <div class="card custom-card">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h6 class="card-title">Multiple Select</h6>
                    </div>
                    <div class="card-body">
                        <p class="fw-medium mb-2">Default</p>
                        <VueMultiselect :searchable="false" :show-labels="false" :multiple="true" v-model="multiSelecteValue" :options="singleSelecteOptions" :taggable="true" />
                        <p class="fw-medium mb-2 mt-2">Option groups</p>
                        <VueMultiselect :searchable="false" :show-labels="false" v-model="multigroupvalue" :options="groupOptions" :multiple="true" group-values="libs" group-label="language" :group-select="true" placeholder="Type to search" track-by="name" label="name"><span slot="noResult">Oops! No
                                elements found. Consider
                                changing the search query.</span></VueMultiselect>
                    </div>
                </div>
            </div>
            <div class="col-xl-12">
                <div class="card custom-card">
                    <div class="card-header">
                        <div class="card-title">
                            Passing Through Options
                        </div>
                    </div>
                    <div class="card-body">
                        <Multiselect id="tagging" v-model="value" tag-placeholder="Add this as new tag" placeholder="Search or add a tag" label="name" track-by="code" :options="options" :multiple="true" :taggable="true" @tag="addTag"></multiselect>

                    </div>
                </div>
            </div>
            <div class="col-xl-12">
                <div class="card custom-card">
                    <div class="card-header">
                        <div class="card-title">
                            Select with search
                        </div>
                    </div>
                    <div class="card-body">
                        <p class="fw-semibold mb-2">Custom Label</p>
                        <VueMultiselect :searchable="true" :show-labels="false" v-model="searchSelectedValue" :options="searchSelectedOptions" :custom-label="nameWithLang" placeholder="Select one" label="name" track-by="name">
                        </VueMultiselect>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-12">
        <div class="row">
            <div class="col-xl-12">
                <div class="card custom-card">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h6 class="card-title">Single Select</h6>
                    </div>
                    <div class="card-body">
                        <p class="fw-medium mb-2">Default</p>
                        <VueMultiselect :searchable="false" :show-labels="false" v-model="singleSelecteValue" :options="singleSelecteOptions"></VueMultiselect>
                        <p class="fw-medium mb-2 mt-3">Option groups</p>
                        <VueMultiselect :searchable="false" :show-labels="false" v-model="groupvalue" :options="groupOptions" :multiple="false" group-values="libs" group-label="language" :group-select="true" placeholder="Type to search" track-by="name" label="name"><span slot="noResult">Oops! No
                                elements found. Consider
                                changing the search query.</span></VueMultiselect>
                    </div>
                </div>
            </div>
            <div class="col-xl-12">
                <div class="card custom-card">
                    <div class="card-header">
                        <div class="card-title">
                            Custom option template
                        </div>
                    </div>
                    <div class="card-body custom-form-select">
                        <VueMultiselect :searchable="false" :show-labels="false" v-model="customValue" placeholder="Fav No Man’s Sky path" label="title" track-by="title" :options="customOptions" :option-height="104" :custom-label="customLabel">
                            <template v-slot:singleLabel="props">
                                <img class="option__image" :src="props.option.img" alt="No Man’s Sky">
                                <span class="option__desc">
                                    <span class="option__title">{{ props.option.title }}</span>
                                </span>
                            </template>

                            <template v-slot:option="props">
                                <img class="option__image" :src="props.option.img" alt="No Man’s Sky">
                                <div class="option__desc">
                                    <span class="option__title">{{ props.option.title }}</span>
                                    <span class="option__small">{{ props.option.desc }}</span>
                                </div>
                            </template>

                        </VueMultiselect>
                    </div>
                </div>
            </div>
            <div class="col-xl-12">
                <div class="card custom-card">
                    <div class="card-header">
                        <div class="card-title">
                            Enable-Disable
                        </div>
                    </div>
                    <div class="card-body">
                        <VueMultiselect :show-labels="false" v-model="edvalue" :options="singleSelecteOptions" :searchable="false" :group-select="true" :close-on-select="false" :disabled="disabled" placeholder="Pick a value">
                        </VueMultiselect>
                        <div class="text-end mt-2">
                            <button @click="disabled = false" class="btn btn-primary">Enable</button>
                            <button @click="disabled = true" class="btn btn-danger ms-1">Disable</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- End:: row-4 -->

<!-- Start::row-1 -->
<div class="row">
    <div class="col-xl-4">
        <showcodeCard :code="prism.DefaultSelect" title="Default Select">
            <select class="form-select" aria-label="Default select example">
                <option selected>Open this select menu
                </option>
                <option value="1">One</option>
                <option value="2">Two</option>
                <option value="3">Three</option>
            </select>
        </showcodeCard>
    </div>
    <div class="col-xl-4">
        <showcodeCard :code="prism.DisabledSelect" title="Disabled Select">
            <select class="form-select" aria-label="Disabled select example" disabled>
                <option selected>Open this select menu</option>
                <option value="1">One</option>
                <option value="2">Two</option>
                <option value="3">Three</option>
            </select>
        </showcodeCard>
    </div>
    <div class="col-xl-4">
        <showcodeCard :code="prism.RoundedSelect" title="Rounded Select">
            <select class="form-select rounded-pill" aria-label="Default select example">
                <option selected>Open this select menu
                </option>
                <option value="1">One</option>
                <option value="2">Two</option>
                <option value="3">Three</option>
            </select>
        </showcodeCard>
    </div>
</div>
<!--End::row-1 -->

<!-- Start:: row-2 -->
<div class="row custom-form-select">
    <div class="col-xl-6">
        <showcodeCard :code="prism.MultipleAttributeSelect" title="Multiple Attribute Select">
            <select class="form-select" multiple aria-label="multiple select example">
                <option selected>Open this select menu</option>
                <option value="1">One</option>
                <option value="2">Two</option>
                <option value="3">Three</option>
            </select>
        </showcodeCard>
    </div>
    <div class="col-xl-6">
        <showcodeCard :code="prism.UsingSizeAttribute" title="Using Size Attribute">
            <select class="form-select" size="4" aria-label="size 3 select example">
                <option selected>Open this select menu</option>
                <option value="1">One</option>
                <option value="2">Two</option>
                <option value="3">Three</option>
                <option value="4">Four</option>
                <option value="5">Five</option>
            </select>
        </showcodeCard>
    </div>
</div>
<!-- End:: row-2 -->

<!-- Start:: row-3 -->
<div class="row">
    <div class="col-xl-12">
        <showcodeCard :code="prism.SelectSizes" title="Select Sizes">
            <select class="form-select form-select-sm mb-3" aria-label=".form-select-sm example">
                <option selected>Open this select menu</option>
                <option value="1">One</option>
                <option value="2">Two</option>
                <option value="3">Three</option>
            </select><select class="form-select mb-3" aria-label="Default select">
                <option selected>Open this select menu
                </option>
                <option value="1">One</option>
                <option value="2">Two</option>
                <option value="3">Three</option>
            </select>
            <select class="form-select form-select-lg" aria-label=".form-select-lg example">
                <option selected>Open this select menu</option>
                <option value="1">One</option>
                <option value="2">Two</option>
                <option value="3">Three</option>
            </select>
        </showcodeCard>
    </div>
</div>
<!-- End:: row-3 -->
</template>

<style scoped>
/* Add your styles here */
</style>
