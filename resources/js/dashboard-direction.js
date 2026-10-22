import { createApp } from 'vue';
import DashboardDirection from './components/DashboardDirection.vue';

const el = document.getElementById('app-dashboard-direction');
if (el) {
    createApp(DashboardDirection).mount(el);
}