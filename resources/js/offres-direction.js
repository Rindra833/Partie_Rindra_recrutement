import { createApp } from 'vue';
import OffresDirection from './components/OffresDirection.vue';

const el = document.getElementById('app-offres-direction');
if (el) {
    createApp(OffresDirection).mount(el);
}