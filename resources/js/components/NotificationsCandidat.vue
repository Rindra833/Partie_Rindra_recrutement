<script setup>
import { ref, onMounted } from 'vue';

const notifications = ref([]);
const nonLues       = ref(0);
const filtre        = ref(null);   // null | 'non-lues' | 'lues'
const loading       = ref(true);
const error         = ref(null);
const busy          = ref(false);  // verrouille les boutons pendant une requête

async function charger() {
    loading.value = true;
    error.value   = null;
    try {
        const url = '/api/notifications' + (filtre.value ? '?filtre=' + filtre.value : '');
        const res = await fetch(url, {
            headers: { 'Accept': 'application/json' },
            credentials: 'same-origin',
        });
        if (!res.ok) {
            if (res.status === 401) { window.location.href = '/connexion'; return; }
            throw new Error(`HTTP ${res.status}`);
        }
        const data = await res.json();
        notifications.value = data.notifications;
        nonLues.value       = data.non_lues;
    } catch (e) {
        error.value = e.message;
    } finally {
        loading.value = false;
    }
}

function setFiltre(f) {
    filtre.value = f;
    charger();
}

async function marquerLue(n) {
    if (n.lue || busy.value) return;
    busy.value = true;
    try {
        const res = await fetch(`/api/notifications/${n.id_notification}/marquer-lue`, {
            method: 'POST',
            headers: { 'Accept': 'application/json' },
            credentials: 'same-origin',
        });
        if (res.ok) {
            n.lue = true;
            n.date_lecture = new Date().toISOString();
            nonLues.value = Math.max(0, nonLues.value - 1);
        }
    } finally {
        busy.value = false;
    }
}

async function toutMarquerLu() {
    if (busy.value || nonLues.value === 0) return;
    busy.value = true;
    try {
        const res = await fetch('/api/notifications/tout-marquer-lu', {
            method: 'POST',
            headers: { 'Accept': 'application/json' },
            credentials: 'same-origin',
        });
        if (res.ok) {
            await charger();
        }
    } finally {
        busy.value = false;
    }
}

function ouvrir(n) {
    // Marque comme lue, puis redirige vers le lien (ou reste sur la page si pas de lien)
    marquerLue(n).then(() => {
        if (n.lien) {
            window.location.href = n.lien;
        }
    });
}

function tronquer(texte, max = 160) {
    return texte.length > max ? texte.slice(0, max) + '…' : texte;
}

function formatDate(d) {
    if (! d) return '—';
    return d.slice(0, 16);
}

onMounted(charger);
</script>

<template>
    <div>
        <!-- En-tête avec badge non-lues -->
        <h1 style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
            Mes notifications
            <span v-if="nonLues > 0"
                  style="background:#e63757;color:#fff;padding:2px 10px;border-radius:12px;font-size:14px;font-weight:600;">
                {{ nonLues }} non lue{{ nonLues > 1 ? 's' : '' }}
            </span>
        </h1>

        <!-- Barre de filtres + action globale -->
        <div style="margin-bottom:16px;display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
            <strong style="color:#6e84a3;">Filtrer :</strong>
            <button class="btn"
                    :class="filtre === null ? 'btn-primary' : 'btn-secondary'"
                    @click="setFiltre(null)">Toutes</button>
            <button class="btn"
                    :class="filtre === 'non-lues' ? 'btn-primary' : 'btn-secondary'"
                    @click="setFiltre('non-lues')">Non lues</button>
            <button class="btn"
                    :class="filtre === 'lues' ? 'btn-primary' : 'btn-secondary'"
                    @click="setFiltre('lues')">Lues</button>

            <button v-if="nonLues > 0"
                    class="btn btn-success"
                    style="margin-left:auto;"
                    :disabled="busy"
                    @click="toutMarquerLu">
                ✅ Tout marquer comme lu
            </button>

            <button class="btn btn-secondary"
                    style="font-size:12px;padding:4px 10px;"
                    :disabled="busy"
                    @click="charger">
                🔄 Rafraîchir
            </button>
        </div>

        <!-- États : chargement / erreur -->
        <div v-if="loading" style="text-align:center;padding:24px;color:#6e84a3;">
            ⏳ Chargement…
        </div>

        <div v-else-if="error" class="flash flash-error">
            Erreur : {{ error }}
            <button class="btn btn-primary" @click="charger" style="margin-left:12px;">Réessayer</button>
        </div>

        <!-- Liste des notifications -->
        <div v-else class="card">
            <div v-if="notifications.length === 0" style="color:#6e84a3;text-align:center;padding:24px;">
                Aucune notification{{ filtre ? ' pour ce filtre' : '' }}.
            </div>

            <div v-for="n in notifications"
                 :key="n.id_notification"
                 :style="{ borderBottom: '1px solid #e9ecef', padding: '12px 0',
                          background: n.lue ? 'transparent' : '#eef4fb' }">
                <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                    <span v-if="!n.lue"
                          style="background:#2c7be5;color:#fff;font-size:11px;padding:2px 8px;border-radius:10px;font-weight:600;">
                        NEW
                    </span>
                    <span v-else
                          style="background:#e9ecef;color:#6e84a3;font-size:11px;padding:2px 8px;border-radius:10px;">
                        lue
                    </span>
                    <strong style="flex:1;">{{ n.titre }}</strong>
                    <small style="color:#6e84a3;">{{ formatDate(n.date_creation) }}</small>
                    <button class="btn btn-primary"
                            style="padding:4px 10px;font-size:12px;"
                            :disabled="busy"
                            @click="ouvrir(n)">
                        Ouvrir
                    </button>
                </div>
                <p style="margin:6px 0 0 0;color:#3c4858;font-size:14px;">
                    {{ tronquer(n.message) }}
                </p>
                <p v-if="n.lue && n.date_lecture"
                   style="margin:4px 0 0 0;font-size:11px;color:#6e84a3;">
                    Lue le {{ formatDate(n.date_lecture) }}
                </p>
            </div>
        </div>
    </div>
</template>