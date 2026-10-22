import { createApp } from 'vue';
import ContratsCandidat from './components/ContratsCandidat.vue';
const el = document.getElementById('app-contrats-candidat');
if (el) createApp(ContratsCandidat).mount(el);