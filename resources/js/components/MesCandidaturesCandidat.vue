<script setup>
import { ref, onMounted } from 'vue';

const candidatures = ref([]);
const comptes      = ref({});
const filtre       = ref(null);
const loading      = ref(true);
const error        = ref(null);

const statutsDispos = [null, 'RECUE', 'PRESELECTIONNEE', 'SELECTIONNEE', 'REFUSEE', 'RETENUE'];

async function charger() {
    loading.value = true;
    error.value   = null;
    try {
        const res = await fetch('/api/candidat/mes-candidatures', {
            headers: { 'Accept': 'application/json' },
            credentials: 'same-origin',
        });
        if (!res.ok) {
            if (res.status === 401) { window.location.href = '/connexion'; return; }
            throw new Error(`HTTP ${res.status}`);
        }
        const data = await res.json();
        candidatures.value = data.candidatures;
        comptes.value      = data.comptes_par_statut;
    } catch (e) {
        error.value = e.message;
    } finally {
        loading.value = false;
    }
}

function setFiltre(s) { filtre.value = s; }

function filtrer(liste) {
    if (filtre.value === null) return liste;
    return liste.filter(c => c.statut === filtre.value);
}

const badgeColor = (statut) => ({
    RECUE:          '#6e84a3',
    PRESELECTIONNEE:'#2c7be5',
    SELECTIONNEE:   '#00d97e',
    REFUSEE:        '#e63757',
    RETENUE:        '#00d97e',
}[statut] || '#6e84a3');

function formatDate(d) { return d ? d.slice(0, 16) : '—'; }

onMounted(charger);
</script>

<template>
    <div>
        <h1>Mes candidatures</h1>

        <div v-if="!loading && !error"
             style="margin-bottom:16px;display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
            <strong style="color:#6e84a3;">Filtrer :</strong>
            <button v-for="s in statutsDispos" :key="s || 'all'"
                    class="btn" :class="filtre === s ? 'btn-primary' : 'btn-secondary'"
                    @click="setFiltre(s)">
                {{ s === null ? 'Toutes' : s }} ({{ s === null ? (comptes.TOUTES || 0) : (comptes[s] || 0) }})
            </button>
            <button class="btn btn-secondary" style="margin-left:auto;font-size:12px;padding:4px 10px;"
                    @click="charger">🔄 Rafraîchir</button>
        </div>

        <div v-if="loading" style="text-align:center;padding:24px;color:#6e84a3;">⏳ Chargement…</div>
        <div v-else-if="error" class="flash flash-error">
            Erreur : {{ error }}
            <button class="btn btn-primary" @click="charger" style="margin-left:12px;">Réessayer</button>
        </div>

        <div v-else class="card">
            <div v-if="filtrer(candidatures).length === 0"
                 style="text-align:center;color:#6e84a3;padding:24px;">
                <p v-if="candidatures.length === 0">
                    Vous n'avez encore postulé à aucune offre.
                    <a href="/candidat/offres">Voir les offres disponibles</a>
                </p>
                <p v-else>Aucune candidature pour ce filtre.</p>
            </div>

            <table v-else>
                <thead><tr>
                    <th>#</th><th>Offre</th><th>Date</th><th>Statut</th><th>Actions</th>
                </tr></thead>
                <tbody>
                    <tr v-for="c in filtrer(candidatures)" :key="c.id_candidature">
                        <td>{{ c.id_candidature }}</td>
                        <td><strong>{{ c.offre_titre }}</strong></td>
                        <td>{{ formatDate(c.date_candidature) }}</td>
                        <td>
                            <span class="badge" :style="{ background: badgeColor(c.statut), color: '#fff' }">
                                {{ c.statut }}
                            </span>
                        </td>
                        <td style="display:flex;gap:6px;">
                            <a v-if="c.peut_requete"
                               class="btn btn-primary" style="padding:4px 10px;font-size:12px;"
                               :href="`/candidat/requetes/${c.id_candidature}`">
                                📝 Requêtes
                            </a>
                            <a class="btn btn-secondary" style="padding:4px 10px;font-size:12px;"
                               :href="`/candidat/requetes/${c.id_candidature}`"
                               v-if="!c.peut_requete">Voir</a>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>