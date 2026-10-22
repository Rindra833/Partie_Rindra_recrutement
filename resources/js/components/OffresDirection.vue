<script setup>
import { ref, onMounted } from 'vue';

const offres  = ref([]);
const comptes = ref({});
const filtre  = ref(null);
const loading = ref(true);
const error   = ref(null);

const statutsDispos = [null, 'BROUILLON', 'EN_ATTENTE', 'VALIDE', 'PUBLIEE', 'FERMEE', 'REFUSEE'];

async function charger() {
    loading.value = true;
    error.value   = null;
    try {
        const url = '/api/direction/offres' + (filtre.value ? '?statut=' + filtre.value : '');
        const res = await fetch(url, {
            headers: { 'Accept': 'application/json' },
            credentials: 'same-origin',
        });
        if (!res.ok) {
            if (res.status === 401) { window.location.href = '/connexion'; return; }
            throw new Error(`HTTP ${res.status}`);
        }
        const data = await res.json();
        offres.value  = data.offres;
        comptes.value = data.comptes_par_statut;
    } catch (e) {
        error.value = e.message;
    } finally {
        loading.value = false;
    }
}

function setFiltre(s) {
    filtre.value = s;
    charger();
}

function libelleFiltre(s) {
    return s === null ? 'Toutes' : s;
}

const badgeColor = (statut) => ({
    BROUILLON:  '#6e84a3',
    EN_ATTENTE: '#f6c343',
    VALIDE:     '#00d97e',
    PUBLIEE:    '#2c7be5',
    FERMEE:     '#333',
    REFUSEE:    '#e63757',
}[statut] || '#6e84a3');

const texteBlanc = (statut) => statut !== 'EN_ATTENTE';

onMounted(charger);
</script>

<template>
    <div>
        <h1>Offres d'emploi</h1>

        <div v-if="!loading && !error"
             style="margin-bottom:16px;display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
            <strong style="color:#6e84a3;">Filtrer :</strong>
            <button v-for="s in statutsDispos"
                    :key="s || 'all'"
                    class="btn"
                    :class="filtre === s ? 'btn-primary' : 'btn-secondary'"
                    @click="setFiltre(s)">
                {{ libelleFiltre(s) }} ({{ s === null ? (comptes.TOUTES || 0) : (comptes[s] || 0) }})
            </button>
            <button class="btn btn-secondary"
                    style="margin-left:auto;font-size:12px;padding:4px 10px;"
                    @click="charger">
                🔄 Rafraîchir
            </button>
        </div>

        <div v-if="loading" style="text-align:center;padding:24px;color:#6e84a3;">
            ⏳ Chargement…
        </div>

        <div v-else-if="error" class="flash flash-error">
            Erreur : {{ error }}
            <button class="btn btn-primary" @click="charger" style="margin-left:12px;">Réessayer</button>
        </div>

        <div v-else class="card">
            <div v-if="offres.length === 0"
                 style="text-align:center;color:#6e84a3;padding:24px;">
                Aucune offre{{ filtre ? ' pour ce statut' : '' }}.
            </div>

            <table v-else>
                <thead>
                    <tr>
                        <th>Titre</th>
                        <th>Poste</th>
                        <th>Département</th>
                        <th>Effectif</th>
                        <th>Publication</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="o in offres" :key="o.id_offre">
                        <td><strong>{{ o.titre }}</strong></td>
                        <td>{{ o.poste_libelle }}</td>
                        <td>{{ o.departement_nom }}</td>
                        <td>{{ o.effectif_demande }}</td>
                        <td>{{ o.date_publication || '—' }}</td>
                        <td>
                            <span class="badge"
                                  :style="{ background: badgeColor(o.statut), color: texteBlanc(o.statut) ? '#fff' : '#333' }">
                                {{ o.statut }}
                            </span>
                        </td>
                        <td>
                            <a class="btn btn-primary"
                               :href="`/direction/offres/${o.id_offre}`">
                                Détail
                            </a>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>