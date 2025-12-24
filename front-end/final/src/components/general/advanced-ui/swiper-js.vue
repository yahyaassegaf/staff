<script setup>

import {
    Swiper,
    SwiperSlide
} from "swiper/vue";
import "swiper/swiper-bundle.css";
import {
    Autoplay,
    Navigation,
    Pagination,
    Scrollbar,
    Mousewheel,
    Keyboard,
    EffectCube,
    EffectFade,
    EffectCoverflow,
    EffectFlip,
    FreeMode,
    Thumbs
} from 'swiper/modules';
import * as swiperData from "../../../data/swiper.ts"
import {
    ref
} from "vue";
import media_30 from "/images/media/media-30.jpg"
import media_25 from "/images/media/media-25.jpg"
import media_28 from "/images/media/media-28.jpg"
import media_29 from "/images/media/media-29.jpg"
import media_31 from "/images/media/media-31.jpg"
import media_32 from "/images/media/media-31.jpg"
import media_1 from "/images/media/media-1.jpg"
import media_2 from "/images/media/media-2.jpg"
import media_6 from "/images/media/media-6.jpg"
import media_7 from "/images/media/media-7.jpg"
import media_10 from "/images/media/media-10.jpg"
import media_11 from "/images/media/media-11.jpg"
import Pageheader from "../../../shared/components/pageheader/pageheader.vue";
import SimpleCard from "../../../shared/components/@spk/simple-card.vue";


const thumbsSwiper = ref(null);

const setThumbsSwiper = (swiper) => {
    thumbsSwiper.value = swiper;
};

const pagination = {
    clickable: true,
}
const custompagination = {
    clickable: true,
    renderBullet: function (index, className) {
        return '<span class="' + className + '">' + (index + 1) + '</span>';
    },
}
const modules = [Autoplay, Navigation, Pagination, Scrollbar, Mousewheel, Keyboard, EffectCube, EffectFade, EffectCoverflow, EffectFlip, FreeMode, Thumbs]
const dataToPass = {
    title: "Advanced Ui",
    currentpage: "Swiper",
    activepage: "Swiper"
}

</script>

<template>
    <Pageheader :propData="dataToPass" />
    <!-- Start::row-1 -->
    <div class="row">
        <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12">
            <SimpleCard title="Basic Swiper">
                <swiper :autoplay="true" class="">
                    <swiper-slide v-for="(idx, index) in swiperData.Basicdata" :key="index">
                        <img :src="idx.src" alt="">
                    </swiper-slide>
                </swiper>
            </SimpleCard>
        </div>
        <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12">
            <SimpleCard title="Swiper With Navigation">
                <swiper :autoplay="true" :navigation="true" :modules="modules" class="">
                    <swiper-slide v-for="(idx, index) in swiperData.Navigationdata" :key="index">
                        <img :src="idx" alt="">
                    </swiper-slide>
                </swiper>
            </SimpleCard>
        </div>
        <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12">
            <SimpleCard title="Swiper with Pagination">
                <swiper :autoplay="true" class="" :pagination="pagination" :modules="modules">
                    <swiper-slide v-for="(idx, index) in swiperData.Paginationdata" :key="index">
                        <img :src="idx" alt="">
                    </swiper-slide>
                </swiper>
            </SimpleCard>
        </div>
        <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12">
            <SimpleCard title="Dynamic Pagination">
                <swiper :autoplay="true" class="" :pagination="{
                    dynamicBullets: true,
                }" :modules="modules">
                    <swiper-slide v-for="(idx, index) in swiperData.Dynamicdata" :key="index">
                        <img :src="idx" alt="">
                    </swiper-slide>
                </swiper>
            </SimpleCard>
        </div>
        <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12">
            <SimpleCard title="Pagination With Progress">
                <swiper :autoplay="true" class="" :pagination="{
                    type: 'progressbar',
                }" :navigation="true" :modules="modules">
                    <swiper-slide v-for="(idx, index) in swiperData.Progressdata" :key="index">
                        <img :src="idx" alt="">
                    </swiper-slide>
                </swiper>
            </SimpleCard>
        </div>
        <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12">
            <SimpleCard title="Pagination Fraction">
                <swiper :autoplay="true" class="pagination-fraction" :pagination="{
                    type: 'fraction',
                }" :navigation="true" :modules="modules">
                    <swiper-slide v-for="(idx, index) in swiperData.FractionData" :key="index">
                        <img :src="idx" alt="">
                    </swiper-slide>
                </swiper>
            </SimpleCard>
        </div>
        <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12">
            <SimpleCard title="Custom Paginatioin">
                <swiper :autoplay="true" class=" custom-pagination" :pagination="custompagination" :modules="modules">
                    <swiper-slide v-for="(idx, index) in swiperData.CustomPaginatioin" :key="index">
                        <img :src="idx" alt="">
                    </swiper-slide>
                </swiper>
            </SimpleCard>
        </div>
        <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12">
            <SimpleCard title="Scrollbar Swiper">
                <swiper :autoplay="true" class="" :scrollbar="{
                    hide: true,
                }" :modules="modules">
                    <swiper-slide v-for="(idx, index) in swiperData.ScrollbarSwiper" :key="index">
                        <img :src="idx" alt="">
                    </swiper-slide>
                </swiper>
            </SimpleCard>
        </div>
         <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12">
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">
                        Vertical Swiper
                    </div>
                </div>
                <div class="card-body">
                    <swiper :autoplay="true" class="" :pagination="{
                        clickable: true,
                    }" :modules="modules">
                        <swiper-slide>
                            <img src="/images/media/media-32.jpg" alt="">
                        </swiper-slide>
                        <swiper-slide>
                            <img src="/images/media/media-8.jpg" alt="">
                        </swiper-slide>
                        <swiper-slide>
                            <img src="/images/media/media-17.jpg" alt="">
                        </swiper-slide>
                    </swiper>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12">
            <SimpleCard title="Mouse Wheel Control">
                <swiper :autoplay="true" class="" :slidesPerView="1" :spaceBetween="30" :mousewheel="true" :pagination="{
                    clickable: true,
                }" :modules="modules">
                    <swiper-slide v-for="(idx, index) in swiperData.MouseWheelControl" :key="index">
                        <img :src="idx" alt="">
                    </swiper-slide>
                </swiper>
            </SimpleCard>
        </div>
        <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12">
            <SimpleCard title="Keyboard Control">
                <swiper :autoplay="true" class="" :slidesPerView="1" :spaceBetween="30" :keyboard="{
                    enabled: true,
                }" :pagination="{
                        clickable: true,
                    }" :navigation="true" :modules="modules">
                    <swiper-slide v-for="(idx, index) in swiperData.KeyboardControl" :key="index">
                        <img :src="idx" alt="">
                    </swiper-slide>
                </swiper>
            </SimpleCard>
        </div>
        <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12 custom-swiper">
            <SimpleCard title="Nested Swiper">
                <swiper :autoplay="true" class="" :slidesPerView="1" :spaceBetween="30" :keyboard="{ enabled: true, }"
                    :pagination="{ clickable: true, }" :navigation="true" :modules="modules">
                    <swiper-slide>
                        <img :src="media_30" alt="">
                    </swiper-slide>
                    <swiper-slide>
                        <swiper :autoplay="{
                            delay: 2500,
                            disableOnInteraction: false,
                        }" class="mySwiper2 swiper-v swiper vertical" :spaceBetween="50" :pagination="{
                                clickable: true,
                            }" :modules="modules" direction="vertical">
                            <swiper-slide><img :src="media_25" alt=""></swiper-slide>
                            <swiper-slide><img :src="media_31" alt=""></swiper-slide>
                            <swiper-slide><img :src="media_32" alt=""></swiper-slide>
                        </swiper>
                    </swiper-slide>
                    <swiper-slide>
                        <img :src="media_28" alt="">
                    </swiper-slide>
                    <swiper-slide>
                        <img :src="media_29" alt="">
                    </swiper-slide>
                </swiper>
            </SimpleCard>
        </div>
        <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12">
            <SimpleCard title="Effect Cube">
                <swiper :autoplay="true" class="" :effect="'cube'" :grabCursor="true" :cubeEffect="{
                    shadow: true, slideShadows: true, shadowOffset: 20, shadowScale: 0.94
                }" :pagination="pagination" :modules="modules">
                    <swiper-slide v-for="(idx, index) in swiperData.EffectCube" :key="index">
                        <img :src="idx" alt="">
                    </swiper-slide>
                </swiper>
            </SimpleCard>
        </div>
        <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12">
            <SimpleCard title="Effect Fade">
                <swiper :autoplay="true" class="" :spaceBetween="30" :effect="'fade'" :navigation="true" :pagination="{
                    clickable: true,
                }" :modules="modules">
                    <swiper-slide v-for="(idx, index) in swiperData.EffectFade" :key="index">
                        <img :src="idx" alt="">
                    </swiper-slide>
                </swiper>
            </SimpleCard>
        </div>
        <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12">
            <SimpleCard title="Effect Flip">
                <swiper :autoplay="true" class="" :effect="'flip'" :grabCursor="true" :pagination="pagination"
                    :navigation="true" :modules="modules">
                    <swiper-slide v-for="(idx, index) in swiperData.EffectFlip" :key="index">
                        <img :src="idx" alt="">
                    </swiper-slide>
                </swiper>
            </SimpleCard>
        </div>
    </div>
    <!--End::row-1 -->

    <!-- Start:: row-2 -->
    <div class="row">
        <div class="col-xl-12">
            <SimpleCard title="Effect Coverflow">
                <swiper :autoplay="true" class="swiper-overflow" :effect="'coverflow'" :grabCursor="true"
                    :centeredSlides="false" :autoHeight="true" :slidesPerView="4" :coverflowEffect="{
                        rotate: 50,
                        stretch: 0,
                        depth: 100,
                        modifier: 1,
                        slideShadows: true,
                    }" :pagination="pagination" :modules="modules">
                    <swiper-slide height="100px" v-for="(idx, index) in swiperData.coverFlow" :key="index">
                        <img class="img-fluid" :src="idx" alt="">
                    </swiper-slide>
                </swiper>
            </SimpleCard>
        </div>
    </div>
    <!-- End:: row-2 -->

    <!-- Start:: row-3 -->
    <div class="row">
        <div class="col-xl-12">
            <SimpleCard title="Thumbs Gallery">
                <swiper :autoplay="true" :style="{
                    '--swiper-navigation-color': '#fff',
                    '--swiper-pagination-color': '#fff',
                }" :spaceBetween="10" :navigation="true" :thumbs="{ swiper: thumbsSwiper }" :loop="true"
                    :modules="modules" class="mySwiper2">
                    <swiper-slide><img class="img-fluid rounded" :src="media_1" /></swiper-slide>
                    <swiper-slide><img class="img-fluid rounded" :src="media_2" /></swiper-slide>
                    <swiper-slide><img class="img-fluid rounded" :src="media_6" /></swiper-slide>
                    <swiper-slide><img class="img-fluid rounded" :src="media_7" /></swiper-slide>
                    <swiper-slide><img class="img-fluid rounded" :src="media_10" /></swiper-slide>
                    <swiper-slide><img class="img-fluid rounded" :src="media_11" /></swiper-slide>
                </swiper>
                <swiper :autoplay="true" @swiper="setThumbsSwiper" :spaceBetween="10" :loop="true" :slidesPerView="4"
                    :freeMode="true" :watchSlidesProgress="true" :modules="modules" class="mySwiper custom-swiper mt-2">
                    <swiper-slide><img class="img-fluid" :src="media_1" /></swiper-slide>
                    <swiper-slide><img class="img-fluid" :src="media_2" /></swiper-slide>
                    <swiper-slide><img class="img-fluid" :src="media_6" /></swiper-slide>
                    <swiper-slide><img class="img-fluid" :src="media_7" /></swiper-slide>
                    <swiper-slide><img class="img-fluid" :src="media_10" /></swiper-slide>
                    <swiper-slide><img class="img-fluid" :src="media_11" /></swiper-slide>
                </swiper>
            </SimpleCard>
        </div>
    </div>
    <!-- End:: row-3 -->
</template>

<style scoped>
/* Add your styles here */
</style>
