<script setup lang="ts">
import { onMounted, onUnmounted, ref } from 'vue';
import PhotoSwipeLightbox from 'photoswipe/lightbox';
import 'photoswipe/style.css';

// Import all media
import media40 from '/images/media/media-40.jpg';
import media41 from '/images/media/media-41.jpg';
import media42 from '/images/media/media-42.jpg';
import media43 from '/images/media/media-43.jpg';
import media44 from '/images/media/media-44.jpg';
import media45 from '/images/media/media-45.jpg';
import media46 from '/images/media/media-46.jpg';
import media60 from '/images/media/media-60.jpg';
import media26 from '/images/media/media-26.jpg';
import media32 from '/images/media/media-32.jpg';
import media30 from '/images/media/media-30.jpg';
import media31 from '/images/media/media-31.jpg';
import media59 from '/images/media/media-59.jpg';
import media61 from '/images/media/media-61.jpg';

// Reference to the lightbox instance
const lightbox = ref<PhotoSwipeLightbox | null>(null);

// Gallery items
const items = [
    { src: media40, w: 500, h: 320, thumbnail: media40, alt: 'alt' },
    { src: media41, w: 500, h: 320, thumbnail: media41, alt: 'alt' },
    { src: media42, w: 500, h: 320, thumbnail: media42, alt: 'alt' },
    { src: media43, w: 500, h: 320, thumbnail: media43, alt: 'alt' },
    { src: media44, w: 500, h: 320, thumbnail: media44, alt: 'alt' },
    { src: media45, w: 500, h: 320, thumbnail: media45, alt: 'alt' },
    { src: media46, w: 500, h: 320, thumbnail: media46, alt: 'alt' },
    { src: media60, w: 500, h: 320, thumbnail: media60, alt: 'alt' },
    { src: media26, w: 500, h: 320, thumbnail: media26, alt: 'alt' },
    { src: media32, w: 500, h: 320, thumbnail: media32, alt: 'alt' },
    { src: media30, w: 500, h: 320, thumbnail: media30, alt: 'alt' },
    { src: media31, w: 500, h: 320, thumbnail: media31, alt: 'alt' },
    { src: media46, w: 500, h: 320, thumbnail: media46, alt: 'alt' },
    { src: media59, w: 500, h: 320, thumbnail: media59, alt: 'alt' },
    { src: media61, w: 500, h: 320, thumbnail: media61, alt: 'alt' },
    { src: media42, w: 500, h: 320, thumbnail: media42, alt: 'alt' },
];

// Lifecycle hooks
onMounted(() => {
    if (!lightbox.value) {
        lightbox.value = new PhotoSwipeLightbox({
            gallery: '#RandomUniqueId',
            children: 'a',
            pswpModule: () => import('photoswipe'),
            bgOpacity: 0.8,
            wheelToZoom: true,
        });
        lightbox.value.init();
    }
});

onUnmounted(() => {
    if (lightbox.value) {
        lightbox.value.destroy();
        lightbox.value = null;
    }
});
</script>


<template>
    <div class="row" id="RandomUniqueId">
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-body">
                    <div class="row gy-4">
                        <div class="col-xxl-3 col-xl-3 col-lg-3 col-md-6 col-sm-12" v-for="(image, key) in items"
                            :key="key">
                            <a :href="image.src" class="glightbox" :data-pswp-width="image.w"
                                :data-pswp-height="image.h" rel="noreferrer" data-gallery="gallery1" target="_blank">
                                <img :src="image.thumbnail" alt=""
                                    style="width:100%; border-radius:0.25rem;overflow:hidden" class="img-fluid rounded">
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
