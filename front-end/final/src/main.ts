// main.ts
import './style.scss';
import { createApp } from 'vue';
import App from './App.vue';
import router from './router/index';
import { createPinia } from 'pinia';

import VueApexCharts from 'vue3-apexcharts';
import jsVectorMap from 'jsvectormap'; // used globally?
import 'jsvectormap/dist/maps/world-merc.js';
import 'jsvectormap/dist/maps/world.js';
import 'jsvectormap/dist/jsvectormap.min.css';

import { PerfectScrollbarPlugin } from 'vue3-perfect-scrollbar';
import 'vue3-perfect-scrollbar/style.css';

import Vue3ColorPicker from 'vue3-colorpicker';
import 'vue3-colorpicker/style.css';

import DropZone from 'dropzone-vue';
import 'dropzone-vue/dist/dropzone-vue.common.css';

import Datepicker from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css';

import VueSweetalert2 from 'vue-sweetalert2';

import Vue3EasyDataTable from 'vue3-easy-data-table';
import 'vue3-easy-data-table/dist/style.css';

import { Countdown } from 'vue3-flip-countdown';

import VueMultiselect from 'vue-multiselect';
import 'vue-multiselect/dist/vue-multiselect.css';

import VueCountdown from '@chenfengyuan/vue-countdown';

import { createVuetify } from 'vuetify';
import * as components from 'vuetify/components';
import * as directives from 'vuetify/directives';

import { VueEditor } from 'vue3-editor';

import Particles from '@tsparticles/vue3';
import { loadFull } from 'tsparticles';

import SmartTable from 'vuejs-smart-table'
import Vue3Tour from 'vue3-tour'
import 'vue3-tour/dist/vue3-tour.css'
import { vMaska } from 'maska/vue';

// Create Vue app
const app = createApp(App);

// Vuetify setup
const vuetify = createVuetify({
  ssr: false,
  components,
  directives,
});

// Pinia store
const pinia = createPinia();
app.use(pinia);

// Vue Router
app.use(router);

// Vuetify UI
app.use(vuetify);

app.use(SmartTable)

// Register global components
app.component('apexchart', VueApexCharts);
app.component('Datepicker', Datepicker);
app.component('EasyDataTable', Vue3EasyDataTable);
app.component('Countdown', Countdown);
app.component('VueMultiselect', VueMultiselect);
app.component(VueCountdown.name, VueCountdown);
app.component('VueEditor', VueEditor);
app.directive('maska', vMaska);

// Third-party plugins
app.use(PerfectScrollbarPlugin);
app.use(Vue3ColorPicker);
app.use(DropZone);
app.use(VueSweetalert2);
app.use(Vue3Tour);

// Particle effects
app.use(Particles, {
  init: async (engine: any) => {
    await loadFull(engine);
  }
});

// Mount app
app.mount('#app');
