import { createApp } from 'vue';
import RequetesCandidat from './components/RequetesCandidat.vue';
const el = document.getElementById('app-requetes-candidat');
if (el) createApp(RequetesCandidat).mount(el);