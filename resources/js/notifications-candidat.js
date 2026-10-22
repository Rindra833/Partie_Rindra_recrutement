import { createApp } from 'vue';
import NotificationsCandidat from './components/NotificationsCandidat.vue';

const el = document.getElementById('app-notifications-candidat');
if (el) {
    createApp(NotificationsCandidat).mount(el);
}