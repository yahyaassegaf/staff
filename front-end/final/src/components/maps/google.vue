<script lang="ts" setup>
import CustomControl from '../../shared/components/@spk/google-maps/customControl.vue';
import Custommarker from '../../shared/components/@spk/google-maps/custommarker.vue';
import GoogleCircle from '../../shared/components/@spk/google-maps/googleCircle.vue';
import GoogleMarker from '../../shared/components/@spk/google-maps/googleMarker.vue';
import GooglePolygon from '../../shared/components/@spk/google-maps/googlePolygon.vue';
import GooglePolyline from '../../shared/components/@spk/google-maps/googlePolyline.vue';
import GoogleRectangle from '../../shared/components/@spk/google-maps/googleRectangle.vue';
import MarkerCluster from '../../shared/components/@spk/google-maps/markerCluster.vue';




const dataToPass = {
    title: "Maps",
    currentpage: "Google Maps",
    activepage: "Goole Maps"
}

</script>

<template>
    <Pageheader :propData="dataToPass" />

    <div class="row">
        <!-- Map With Marker -->
        <div class="col-xl-6">
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">Map With Marker</div>
                </div>
                <div class="card-body">
                    <GoogleMarker />
                </div>
            </div>
        </div>

        <!-- Map With Rectangle -->
        <div class="col-xl-6">
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">Map with Rectangle</div>
                </div>
                <div class="card-body">
                    <GoogleRectangle />
                </div>
            </div>
        </div>

        <!-- Map with Polygon -->
        <div class="col-xl-6">
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">Map with Polygon</div>
                </div>
                <div class="card-body">
                    <GooglePolygon />
                </div>
            </div>
        </div>

        <!-- Map With Polyline -->
        <div class="col-xl-6">
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">Map With Polyline</div>
                </div>
                <div class="card-body">
                    <GooglePolyline />
                </div>
            </div>
        </div>

        <!-- Map With MarkerCluster -->
        <div class="col-xl-6">
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">Map With MarkerCluster</div>
                </div>
                <div class="card-body">
                    <MarkerCluster />
                </div>
            </div>
        </div>

        <!-- Map With Custom Marker -->
        <div class="col-xl-6">
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">Map With Custom Marker</div>
                </div>
                <div class="card-body">
                    <Custommarker />
                </div>
            </div>
        </div>

        <!-- Map With Circle -->
        <div class="col-xl-6">
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">Map With Circle</div>
                </div>
                <div class="card-body">
                    <GoogleCircle />
                </div>
            </div>
        </div>

        <!-- Map With Custom Control -->
        <div class="col-xl-6">
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">Map With Custom Control</div>
                </div>
                <div class="card-body">
                    <CustomControl />
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped></style>
