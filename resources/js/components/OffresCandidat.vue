<script setup>
import { ref, computed, onMounted } from 'vue';

const offres   = ref([]);
const stats    = ref(null);
const loading  = ref(true);
const error    = ref(null);
const filtre   = ref('toutes');    // 'toutes' | 'dispo' | 'deja'

const offresFiltrees = computed(() => {
    if (filtre.value === 'dispo') return offres.value.filter(o => !o.deja_postule);
    if (filtre.value === 'deja')  return offres.value.filter(o => o.deja_postule);
    return offres.value;
});

async function charger() {
    loading.value = true;
    error.value   = null;
    try {
        const res = await fetch('/api/candidat/offres', {
            headers: { 'Accept': 'application/json' },
            credentials: 'same-origin',
        });
        if (!res.ok) {
            if (res.status === 401) { window.location.href = '/connexion'; return; }
            throw new Error(`HTTP ${res.status}`);
        }
        const data = await res.json();
        offres.value = data.offres;
        stats.value  = data.stats;
    } catch (e) {
        error.value = e.message;
    } finally {
        loading.value = false;
    }
}

function postuler(o) {
    window.location.href = `/candidat/offres/${o.id_offre}/postuler`;
}

function formatMontant(v) {
    if (v === null || v === undefined) return '—';
    return new Intl.NumberFormat('fr-MG').format(v) + ' Ar';
}

onMounted(charger);
</script>

<template>
    <div>
        <h1>Offres disponibles</h1>

        <!-- Résumé + filtres -->
        <div v-if="!loading && !error"
             style="margin-bottom:16px;display:flex;gap:12px;align-items:center;flex-wrap:wrap;">
            <strong style="color:#6e84a3;">Filtrer :</strong>
            <button class="btn"
                    :class="filtre === 'toutes' ? 'btn-primary' : 'btn-secondary'"
                    @click="filtre = 'toutes'">
                Toutes ({{ stats?.total || 0 }})
            </button>
            <button class="btn"
                    :class="filtre === 'dispo' ? 'btn-primary' : 'btn-secondary'"
                    @click="filtre = 'dispo'">
                Non postulées ({{ (stats?.total || 0) - (stats?.deja_postule || 0) }})
            </button>
            <button class="btn"
                    :class="filtre === 'deja' ? 'btn-primary' : 'btn-secondary'"
                    @click="filtre = 'deja'">
                Déjà postulées ({{ stats?.deja_postule || 0 }})
            </button>
            <button class="btn btn-secondary" style="margin-left:auto;font-size:12px;padding:4px 10px;"
                    @click="charger">
                🔄 Rafraîchir
            </button>
        </div>

        <!-- États -->
        <div v-if="loading" style="text-align:center;padding:24px;color:#6e84a3;">
            ⏳ Chargement…
        </div>

        <div v-else-if="error" class="flash flash-error">
            Erreur : {{ error }}
            <button class="btn btn-primary" @click="charger" style="margin-left:12px;">Réessayer</button>
        </div>

        <!-- Liste -->
        <div v-else class="card">
            <div v-if="offresFiltrees.length === 0" style="text-align:center;color:#6e84a3;padding:24px;">
                Aucune offre{{ filtre !== 'toutes' ? ' pour ce filtre' : '' }}.
            </div>

            <table v-else>
                <thead>
                    <tr>
                        <th>Titre</th>
                        <th>Poste</th>
                        <th>Département</th>
                        <th>Publiée le</th>
                        <th>Date limite</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="o in offresFiltrees" :key="o.id_offre">
                        <td>
                            <strong>{{ o.titre }}</strong>
                            <div v-if="o.salaire"
                                 style="font-size:12px;color:#6e84a3;margin-top:2px;">
                                💰 {{ formatMontant(o.salaire) }}
                            </div>
                        </td>
                        <td>{{ o.poste_libelle }}</td>
                        <td>{{ o.departement_nom }}</td>
                        <td>{{ o.date_publication || '—' }}</td>
                        <td>{{ o.date_limite || '—' }}</td>
                        <td>
                            <button v-if="o.deja_postule"
                                    class="btn btn-secondary"
                                    disabled
                                    title="Vous avez déjà postulé">
                                ✅ Déjà postulé
                            </button>
                            <button v-else
                                    class="btn btn-primary"
                                    @click="postuler(o)">
                                📩 Postuler
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>