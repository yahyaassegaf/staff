<script setup>
import * as prism from "../../../data/prismCode/ui-elements/breadcrumb.ts";
import Pageheader from "../../../shared/components/pageheader/pageheader.vue";
import ShowcodeCard from "../../../shared/UI/showcodeCard.vue";

const dataToPass = {
    title: "Ui Elements",
    currentpage: "Breadcrumb",
    activepage: "Breadcrumb"
}

</script>

<template>
<Pageheader :propData="dataToPass" />

<!--ROW-START-->
<div class="row">
    <div class="col-xl-12">
        <ShowcodeCard title="Example" :code="prism.example">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item active" aria-current="page">Home</li>
                </ol>
            </nav>

            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="javascript:void(0);">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Library</li>
                </ol>
            </nav>

            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="javascript:void(0);">Home</a></li>
                    <li class="breadcrumb-item"><a href="javascript:void(0);">Library</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Data</li>
                </ol>
            </nav>
        </ShowcodeCard>
    </div>
    <div class="col-xl-12">
        <ShowcodeCard title="Example1" :code="prism.example1">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-example1">
                    <li class="breadcrumb-item active" aria-current="page">Home</li>
                </ol>
            </nav>

            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-example1">
                    <li class="breadcrumb-item"><a href="javascript:void(0);">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Library</li>
                </ol>
            </nav>

            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-example1 mb-0">
                    <li class="breadcrumb-item"><a href="javascript:void(0);">Home</a></li>
                    <li class="breadcrumb-item"><a href="javascript:void(0);">Library</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Data</li>
                </ol>
            </nav>
        </ShowcodeCard>
    </div>
    <div class="col-xl-12">
        <ShowcodeCard title="Dividers" :code="prism.dividers">
            <nav style="--bs-breadcrumb-divider: '~';" aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="javascript:void(0);">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Library</li>
                </ol>
            </nav>
        </ShowcodeCard>
    </div>
    <div class="col-xl-12">
        <ShowcodeCard title="Embedded SVG icon" :code="prism.embeddedSVGIcon">
            <nav style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='currentColor'/%3E%3C/svg%3E&#34;);" aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="javascript:void(0);">Home</a></li>
                    <li class="breadcrumb-item active embedded-breadcrumb" aria-current="page">Library</li>
                </ol>
            </nav>
        </ShowcodeCard>
    </div>
    <div class="col-xl-12">
        <ShowcodeCard title="Breadcrumb Style-1" :code="prism.breadcrumbStyle1">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1 mb-0">
                    <li class="breadcrumb-item"><a href="javascript:void(0);">Home</a></li>
                    <li class="breadcrumb-item"><a href="javascript:void(0);">Library</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Data</li>
                </ol>
            </nav>
        </ShowcodeCard>
    </div>
    <div class="col-xl-12">
        <ShowcodeCard title="Breadcrumb Style-2" :code="prism.breadcrumbStyle2">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style2 mb-0">
                    <li class="breadcrumb-item"><a href="javascript:void(0);"><i class="ti ti-home-2 me-1 fs-15 d-inline-block"></i>Home</a></li>
                    <li class="breadcrumb-item"><a href="javascript:void(0);"><i class="ti ti-apps me-1 fs-15 d-inline-block"></i>About</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Services</li>
                </ol>
            </nav>
        </ShowcodeCard>
    </div>
</div>
<!--ROW-END-->
</template>

<style scoped>
/* Add your styles here */
</style>
