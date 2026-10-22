import { createApp } from 'vue';
import OffresCandidat from './components/OffresCandidat.vue';

const el = document.getElementById('app-offres-candidat');
if (el) {
    createApp(OffresCandidat).mount(el);
}