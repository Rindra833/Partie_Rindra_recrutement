import { createApp } from 'vue';
import MesCandidaturesCandidat from './components/MesCandidaturesCandidat.vue';
const el = document.getElementById('app-mes-candidatures-candidat');
if (el) createApp(MesCandidaturesCandidat).mount(el);