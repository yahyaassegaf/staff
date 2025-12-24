// main.ts
import './style.scss';
import { createApp } from 'vue';
import App from './App.vue';
import router from './router/index';
import { createPinia } from 'pinia';
import VueApexCharts from 'vue3-apexcharts';


// Create Vue app
const app = createApp(App);

app.component('apexchart', VueApexCharts);

// Vue Router
app.use(router);

// Pinia store
const pinia = createPinia();
app.use(pinia);

// Mount app
app.mount('#app');
