<script lang="ts" setup>
import PhotoSwipeLightbox from 'photoswipe/lightbox';
import 'photoswipe/style.css';
import media40 from '/images/media/media-40.jpg';
import media41 from '/images/media/media-41.jpg';
import media42 from '/images/media/media-42.jpg';
import media43 from '/images/media/media-43.jpg';
import media44 from '/images/media/media-44.jpg';
import media45 from '/images/media/media-45.jpg';
import media46 from '/images/media/media-46.jpg';
import media60 from '/images/media/media-60.jpg';
import companyLogo2 from '/images/company-logos/2.png';
import companyLogo4 from '/images/company-logos/4.png';
import companyLogo5 from '/images/company-logos/5.png';
import companyLogo6 from '/images/company-logos/6.png';
import companyLogo7 from '/images/company-logos/7.png';
import companyLogo8 from '/images/company-logos/8.png';
import companyLogo9 from '/images/company-logos/9.png';
import { onMounted, onUnmounted } from 'vue';



interface GalleryItem {
    src: string;
    w: number;
    h: number;
    thumbnail: string;
    alt: string;
    title: string;
    subTitle: string;
    companyLogo: string;
}

const items: GalleryItem[] = [
    {
        src: media40,
        w: 600,
        h: 400,
        thumbnail: media40,
        alt: 'alt',
        title: 'Tech Gadgets',
        subTitle: 'Innovative Marvels',
        companyLogo: companyLogo6
    },
    {
        src: media41,
        w: 600,
        h: 400,
        thumbnail: media41,
        alt: 'alt',
        title: 'Healthy Recipes',
        subTitle: 'Nutrient Nourish',
        companyLogo: companyLogo2
    },
    {
        src: media42,
        w: 600,
        h: 400,
        thumbnail: media42,
        alt: 'alt',
        title: 'Travel Explorer',
        subTitle: 'Global Wander',
        companyLogo: companyLogo4
    },
    {
        src: media43,
        w: 600,
        h: 400,
        thumbnail: media43,
        alt: 'alt',
        title: 'Fashion Finds',
        subTitle: 'Chic Styles',
        companyLogo: companyLogo5
    },
    {
        src: media44,
        w: 600,
        h: 400,
        thumbnail: media44,
        alt: 'alt',
        title: 'Nature Photography',
        subTitle: 'Wild Beauty',
        companyLogo: companyLogo6
    },
    {
        src: media45,
        w: 600,
        h: 400,
        thumbnail: media45,
        alt: 'alt',
        title: 'Future Tales',
        subTitle: 'Innovative Marvels',
        companyLogo: companyLogo7
    },
    {
        src: media46,
        w: 600,
        h: 400,
        thumbnail: media46,
        alt: 'alt',
        title: 'Science Fiction Books',
        subTitle: 'Future Tales',
        companyLogo: companyLogo8
    },
    {
        src: media60,
        w: 600,
        h: 400,
        thumbnail: media60,
        alt: 'alt',
        title: 'Fitness Fanatics',
        subTitle: 'Active Vibes',
        companyLogo: companyLogo9
    },
];

let lightbox: PhotoSwipeLightbox | null = null;

onMounted(() => {
    if (!lightbox) {
        lightbox = new PhotoSwipeLightbox({
            gallery: '#RandomUniqueId',
            children: 'a',
            pswpModule: () => import('photoswipe'),
            bgOpacity: 0.8,
            wheelToZoom: true,
            zoomTitle: 'Zoom',
        });
        lightbox.init();
    }
});

onUnmounted(() => {
    if (lightbox) {
        lightbox.destroy();
        lightbox = null;
    }
});
</script>


<template>
    <div class="row" id="RandomUniqueId">
        <div class="col-lg-3 col-md-3 col-sm-6 col-12" v-for="(image, key) in items" :key="key">
            <a :href="image.src" class="glightbox card search-images-card" rel="noreferrer" data-gallery="gallery1"
                target="_blank" :data-pswp-width="image.w" :data-pswp-height="image.h">
                <img :src="image.thumbnail" alt="image" style="width:100%; border-radius:0.25rem;overflow:hidden">
                <div class="p-2">
                    <div class="d-flex align-items-center gap-1">
                        <div class="avatar avatar-xs">
                            <img :src="image.companyLogo" alt="">
                        </div>
                        <div class="figure-caption fs-13 fw-medium text-default">{{ image.title }}</div>
                    </div>
                    <div class="fs-12 text-default">{{ image.subTitle }}</div>
                </div>
            </a>
        </div>
        <div class="col-xl-12 mb-4">
            <button class="btn btn-info-light btn-loader mx-auto">
                <span class="me-2">Loading</span>
                <span class="loading"><i class="ri-loader-4-line fs-16"></i></span>
            </button>
        </div>
    </div>
</template>
