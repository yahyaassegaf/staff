<script setup>
import Pageheader from '../../../shared/components/pageheader/pageheader.vue';


const dataToPass = {
    title: "Advanced Ui",
    currentpage: "Media Player",
    activepage: "Media Player"
}
</script>

<template>
    <Pageheader :propData="dataToPass" />
    <!-- Start::row-1 -->
    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">
                        VIDEO PLAYER
                    </div>
                </div>
                <div class="card-body">
                    <video id="player" playsinline controls
                        data-poster="https://cdn.plyr.io/static/demo/View_From_A_Blue_Moon_Trailer-HD.jpg">
                        <source src="https://cdn.plyr.io/static/demo/View_From_A_Blue_Moon_Trailer-576p.mp4"
                            type="video/mp4">
                        <source src="https://cdn.plyr.io/static/demo/View_From_A_Blue_Moon_Trailer-720p.mp4"
                            type="video/mp4">
                        <source src="https://cdn.plyr.io/static/demo/View_From_A_Blue_Moon_Trailer-1080p.mp4"
                            type="video/mp4">
                    </video>
                </div>
            </div>
        </div>
        <div class="col-xl-12">
            <div class="card custom-card overflow-hidden">
                <div class="card-header">
                    <div class="card-title">YOUTUBE VIDEO</div>
                </div>
                <div class="card-body">
                    <div class="plyr__video-embed" id="player1">
                        <iframe width="560" height="700"
                            src="https://www.youtube.com/embed/-lDlbQ7DiCI?si=EerYCepJFDTb5xOl"
                            title="YouTube video player"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                            allowfullscreen></iframe>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">
                        AUDIO PLAYER
                    </div>
                </div>
                <div class="card-body">
                    <audio id="player2" controls class="w-100">
                        <source src="/src/assets/audio/perfect-beauty.mp3" type="audio/mp3">
                    </audio>
                </div>
            </div>
        </div>
    </div>
    <!--End::row-1 -->
</template>

<style scoped>
/* Add your styles here */
</style>
