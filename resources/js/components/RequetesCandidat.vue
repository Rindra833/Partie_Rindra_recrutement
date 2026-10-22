<script setup>
import { ref, onMounted } from 'vue';

const candidatures = ref([]);
const loading      = ref(true);
const error        = ref(null);

async function charger() {
    loading.value = true;
    error.value   = null;
    try {
        const res = await fetch('/api/candidat/requetes', {
            headers: { 'Accept': 'application/json' },
            credentials: 'same-origin',
        });
        if (!res.ok) {
            if (res.status === 401) { window.location.href = '/connexion'; return; }
            throw new Error(`HTTP ${res.status}`);
        }
        const data = await res.json();
        candidatures.value = data.candidatures;
    } catch (e) {
        error.value = e.message;
    } finally {
        loading.value = false;
    }
}

const badgeColor = (statut) => ({
    RETENUE:       '#00d97e',
    SELECTIONNEE:  '#00d97e',
}[statut] || '#6e84a3');

onMounted(charger);
</script>

<template>
    <div>
        <h1>Requêtes sur les conditions de contrat</h1>

        <div v-if="loading" style="text-align:center;padding:24px;color:#6e84a3;">⏳ Chargement…</div>
        <div v-else-if="error" class="flash flash-error">
            Erreur : {{ error }}
            <button class="btn btn-primary" @click="charger" style="margin-left:12px;">Réessayer</button>
        </div>

        <div v-else class="card">
            <div v-if="candidatures.length === 0"
                 style="text-align:center;color:#6e84a3;padding:24px;">
                Vous n'avez aucune candidature retenue pour le moment.<br>
                Les requêtes sur les conditions sont disponibles une fois que la Direction vous a retenu.
            </div>

            <table v-else>
                <thead><tr>
                    <th>#</th><th>Offre</th><th>Statut</th>
                    <th>Requêtes</th><th>Actions</th>
                </tr></thead>
                <tbody>
                    <tr v-for="c in candidatures" :key="c.id_candidature">
                        <td>{{ c.id_candidature }}</td>
                        <td><strong>{{ c.offre_titre }}</strong></td>
                        <td>
                            <span class="badge" :style="{ background: badgeColor(c.statut), color: '#fff' }">
                                {{ c.statut }}
                            </span>
                        </td>
                        <td>
                            <span v-if="c.nb_requetes === 0" style="color:#6e84a3;">Aucune</span>
                            <span v-else>
                                <strong>{{ c.nb_requetes }}</strong>
                                <span v-if="c.nb_requetes_actives > 0"> — {{ c.nb_requetes_actives }} en cours</span>
                            </span>
                        </td>
                        <td style="display:flex;gap:6px;">
                            <a class="btn btn-primary" style="padding:4px 10px;font-size:12px;"
                               :href="`/candidat/requetes/${c.id_candidature}`">Détail</a>
                            <a class="btn btn-success" style="padding:4px 10px;font-size:12px;"
                               :href="`/candidat/requetes/${c.id_candidature}/creer`">+ Proposer</a>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>