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
import media61 from '/images/media/media-61.jpg';

// Optional: define a type for gallery items
interface GalleryItem {
    src: string;
    w: number;
    h: number;
    thumbnail: string;
    alt: string;
}

let lightbox: PhotoSwipeLightbox | null = null;

const items: GalleryItem[] = [
    { src: media40, w: 500, h: 320, thumbnail: media40, alt: 'alt' },
    { src: media41, w: 500, h: 320, thumbnail: media41, alt: 'alt' },
    { src: media42, w: 500, h: 320, thumbnail: media42, alt: 'alt' },
    { src: media43, w: 500, h: 320, thumbnail: media43, alt: 'alt' },
    { src: media44, w: 500, h: 320, thumbnail: media44, alt: 'alt' },
    { src: media45, w: 500, h: 320, thumbnail: media45, alt: 'alt' },
    { src: media46, w: 500, h: 320, thumbnail: media46, alt: 'alt' },
    { src: media60, w: 500, h: 320, thumbnail: media60, alt: 'alt' },
    { src: media61, w: 500, h: 320, thumbnail: media61, alt: 'alt' },
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
    <div class="row" id="RandomUniqueId">
        <div class="col-lg-4 col-md-4 col-sm-6 col-12" v-for="(image, key) in items" :key="key">
            <a :href="image.src" class="glightbox card" :data-pswp-width="image.w" :data-pswp-height="image.h"
                rel="noreferrer" data-gallery="gallery1" target="_blank">
                <img :src="image.thumbnail" alt="" style="width:100%; border-radius:0.25rem;overflow:hidden" />
            </a>
        </div>
    </div>
</template>
