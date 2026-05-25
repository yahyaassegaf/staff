<script lang="ts" setup>
import { ref, onMounted, onBeforeUnmount, computed } from 'vue'
import Pageheader from '../../shared/components/pageheader/pageheader.vue'
import SimpleCard from '../../shared/components/@spk/simple-card.vue'
declare const jsVectorMap: any



// Header info
const dataToPass = {
  title: 'Maps',
  currentpage: 'Vector Maps',
  activepage: 'Vector Maps'
}

// List of maps to render
const data = [
  { id: 1, name: 'Basic Vector Map', chartid: 'vector-map', bodyclass: 'text-center mx-auto', cardclass: 'overflow-hidden' },
  { id: 2, name: 'Map With Markers', chartid: 'marker-map', bodyclass: 'text-center mx-auto', cardclass: 'overflow-hidden' },
  { id: 3, name: 'Map With Image Markers', chartid: 'marker-image-map', bodyclass: 'text-center mx-auto', cardclass: 'overflow-hidden' },
  { id: 4, name: 'Map With Lines', chartid: 'lines-map', bodyclass: 'text-center mx-auto', cardclass: 'overflow-hidden' },
  { id: 5, name: 'World Vector Map', chartid: 'world-map', bodyclass: 'text-center mx-auto', cardclass: 'overflow-hidden' },
]

// Window width for responsive map sizing
const windowWidth = ref(window.innerWidth)

// Resize event handler
const onResize = () => {
  windowWidth.value = window.innerWidth
}

// Dynamically computed style
const mapStyle = computed(() => {
  return windowWidth.value <= 600
    ? { width: '300px', height: '150px' }
    : { width: '512px', height: '350px' }
})

// Setup all vector maps on mount
onMounted(() => {
  window.addEventListener('resize', onResize)

  // Basic Map
  new jsVectorMap({
    selector: '#vector-map',
    map: 'world_merc',
  })

  // Map with Markers
  const markers = [
    { name: 'Russia', coords: [61, 105], style: { fill: '#5c5cff' } },
    { name: 'Greenland', coords: [72, -42], style: { fill: '#ff9251' } },
    { name: 'Canada', coords: [56, -106], style: { fill: '#56de80' } },
    { name: 'Palestine', coords: [31.5, 34.8], style: { fill: 'yellow' } },
    { name: 'Brazil', coords: [-14.235, -51.9253], style: { fill: '#000' } },
  ]

  new jsVectorMap({
    map: 'world_merc',
    selector: '#marker-map',
    markersSelectable: true,
    onMarkerSelected(index: number, isSelected: boolean, selectedMarkers: any[]) {
</script>


<template>
  <Pageheader :propData="dataToPass" />

  <!-- Start::row-1 -->
  <div class="row">
    <div v-for="(map, index) in data" :key="index" :class="map.id === 5 ? 'col-xl-12' : 'col-xl-6'">
      <SimpleCard :title="map.name" :customCardClass="map.cardclass" :cardClassBody="map.bodyclass">
        <div :id="map.chartid" :style="mapStyle"></div>
      </SimpleCard>
    </div>
  </div>
  <!-- End::row-1 -->

</template>

<style scoped>
/* Add your styles here */
</style>
