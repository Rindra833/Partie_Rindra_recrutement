<script setup>
import { ref, onMounted } from 'vue';

const stats    = ref(null);
const offres   = ref([]);
const candidat = ref(null);
const loading  = ref(true);
const error    = ref(null);

async function charger() {
    loading.value = true;
    error.value   = null;
    try {
        const res = await fetch('/api/candidat/dashboard', {
            headers: { 'Accept': 'application/json' },
            credentials: 'same-origin',
        });
        if (!res.ok) {
            if (res.status === 401) { window.location.href = '/connexion'; return; }
            throw new Error(`HTTP ${res.status}`);
        }
        const data = await res.json();
        stats.value    = data.stats;
        offres.value   = data.offres;
        candidat.value = data.candidat;
    } catch (e) {
        error.value = e.message;
    } finally {
        loading.value = false;
    }
}

onMounted(charger);
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
            <h1>
                Bonjour {{ candidat?.prenom }} {{ candidat?.nom }} 👋
            </h1>

            <div class="grid">
                <div class="kpi">
                    <div class="num">{{ stats.offres_dispo }}</div>
                    <div>Offres disponibles</div>
                </div>
                <div class="kpi">
                    <div class="num">{{ stats.candidatures_envoyees }}</div>
                    <div>Candidatures envoyées</div>
                </div>
                <div class="kpi">
                    <div class="num">{{ stats.candidatures_retenues }}</div>
                    <div>Candidatures retenues</div>
                </div>
                <div class="kpi">
                    <div class="num">{{ stats.notifications_non_lues }}</div>
                    <div>
                        <a href="/candidat/notifications" style="color:inherit;text-decoration:none;">
                            Notifications non lues
                        </a>
                    </div>
                </div>
            </div>

            <div class="card">
                <h2 style="display:flex;align-items:center;gap:12px;">
                    Dernières offres publiées
                    <button class="btn btn-secondary" @click="charger" style="font-size:12px;padding:4px 10px;">
                        🔄 Rafraîchir
                    </button>
                </h2>
                <table>
                    <thead>
                        <tr>
                            <th>Titre</th><th>Poste</th><th>Département</th>
                            <th>Date limite</th><th>Statut</th><th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="o in offres" :key="o.id_offre">
                            <td>{{ o.titre }}</td>
                            <td>{{ o.poste_libelle }}</td>
                            <td>{{ o.departement_nom }}</td>
                            <td>{{ o.date_limite || '—' }}</td>
                            <td>
                                <span class="badge" style="background:#2c7be5;color:#fff;">PUBLIEE</span>
                            </td>
                            <td>
                                <a class="btn btn-primary" href="/candidat/offres">Voir</a>
                            </td>
                        </tr>
                        <tr v-if="offres.length === 0">
                            <td colspan="6" style="text-align:center;color:#6e84a3;">
                                Aucune offre publiée pour le moment.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </template>
    </div>
</template>