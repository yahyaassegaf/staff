<script lang="ts" setup>
import { onMounted, onUnmounted } from 'vue';
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

interface GalleryItem {
    src: string;
    w: number;
    h: number;
    thumbnail: string;
    alt: string;
}

let lightbox: PhotoSwipeLightbox | null = null;

const items: GalleryItem[] = [
    { src: media40, w: 500, h: 320, thumbnail: media40, alt: 'Image 1' },
    { src: media41, w: 500, h: 320, thumbnail: media41, alt: 'Image 2' },
    { src: media42, w: 500, h: 320, thumbnail: media42, alt: 'Image 3' },
    { src: media43, w: 500, h: 320, thumbnail: media43, alt: 'Image 4' },
    { src: media44, w: 500, h: 320, thumbnail: media44, alt: 'Image 5' },
    { src: media45, w: 500, h: 320, thumbnail: media45, alt: 'Image 6' },
    { src: media46, w: 500, h: 320, thumbnail: media46, alt: 'Image 7' },
    { src: media60, w: 500, h: 320, thumbnail: media60, alt: 'Image 8' },
];

onMounted(() => {
    if (!lightbox) {
        lightbox = new PhotoSwipeLightbox({
            gallery: '#RandomUniqueId',
            children: 'a',
            pswpModule: () => import('photoswipe'),
            bgOpacity: 0.8,
            wheelToZoom: true,
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
    <div class="row gy-4" id="RandomUniqueId">
        <div class="col-lg-3 col-md-4 col-sm-6 col-12" v-for="(image, key) in items" :key="key">
            <a :href="image.src" class="glightbox" :data-pswp-width="image.w" :data-pswp-height="image.h"
                rel="noreferrer" data-gallery="gallery1" target="_blank">
                <img :src="image.thumbnail" alt="" style="width:100%; border-radius:0.25rem;overflow:hidden" />
            </a>
        </div>
    </div>
</template>
