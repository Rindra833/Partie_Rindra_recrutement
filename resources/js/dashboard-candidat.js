import { createApp } from 'vue';
import DashboardCandidat from './components/DashboardCandidat.vue';

const el = document.getElementById('app-dashboard-candidat');
if (el) {
    createApp(DashboardCandidat).mount(el);
}