<script setup>
import { ref, onMounted } from 'vue';

const stats   = ref(null);
const offres  = ref([]);
const loading = ref(true);
const error   = ref(null);

async function charger() {
    loading.value = true;
    error.value   = null;
    try {
        const res = await fetch('/api/direction/dashboard', {
            headers: { 'Accept': 'application/json' },
            credentials: 'same-origin',
        });
        if (!res.ok) {
            if (res.status === 401) { window.location.href = '/connexion'; return; }
            throw new Error(`HTTP ${res.status}`);
        }
        const data = await res.json();
        stats.value  = data.stats;
        offres.value = data.offres;
    } catch (e) {
        error.value = e.message;
    } finally {
        loading.value = false;
    }
}

onMounted(charger);

const badgeColor = (statut) => ({
    EN_ATTENTE: '#f6c343',
    VALIDE: '#00d97e',
    REFUSEE: '#e63757',
    PUBLIEE: '#2c7be5',
    BROUILLON: '#6e84a3',
    FERMEE: '#333',
}[statut] || '#6e84a3');
</script>

<template>
    <div>
        <div v-if="loading" style="text-align:center;padding:24px;color:#6e84a3;">
            ⏳ Chargement…
        </div>

        <div v-else-if="error" class="flash flash-error">
            Erreur : {{ error }}
            <button class="btn btn-primary" @click="charger" style="margin-left:12px;">Réessayer</button>
        </div>

        <template v-else>
            <div class="grid">
                <div class="kpi">
                    <div class="num">{{ stats.offres_en_attente }}</div>
                    <div>Offres en attente</div>
                </div>
                <div class="kpi">
                    <div class="num">{{ stats.offres_publiees }}</div>
                    <div>Offres publiées</div>
                </div>
                <div class="kpi">
                    <div class="num">{{ stats.candidatures_recues }}</div>
                    <div>Candidatures reçues</div>
                </div>
                <div class="kpi">
                    <div class="num">{{ stats.candidatures_retenues }}</div>
                    <div>Candidatures retenues</div>
                </div>
            </div>

            <div class="card">
                <h2 style="display:flex;align-items:center;gap:12px;">
                    Dernières offres
                    <button class="btn btn-secondary" @click="charger" style="font-size:12px;padding:4px 10px;">
                        🔄 Rafraîchir
                    </button>
                </h2>
                <table>
                    <thead>
                        <tr><th>Titre</th><th>Poste</th><th>Département</th><th>Statut</th><th></th></tr>
                    </thead>
                    <tbody>
                        <tr v-for="o in offres" :key="o.id_offre">
                            <td>{{ o.titre }}</td>
                            <td>{{ o.poste_libelle }}</td>
                            <td>{{ o.departement_nom }}</td>
                            <td>
                                <span class="badge" :style="{ background: badgeColor(o.statut), color: o.statut === 'EN_ATTENTE' ? '#333' : '#fff' }">
                                    {{ o.statut }}
                                </span>
                            </td>
                            <td>
                                <a class="btn btn-primary" :href="`/direction/offres/${o.id_offre}`">Voir</a>
                            </td>
                        </tr>
                        <tr v-if="offres.length === 0">
                            <td colspan="5" style="text-align:center;color:#6e84a3;">Aucune offre.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </template>
    </div>
</template>