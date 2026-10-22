import { createApp } from 'vue';
import CandidaturesDirection from './components/CandidaturesDirection.vue';

const el = document.getElementById('app-candidatures-direction');
if (el) {
    createApp(CandidaturesDirection).mount(el);
}