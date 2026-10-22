<script setup>
import { ref, onMounted } from 'vue';

const contrats = ref([]);
const loading  = ref(true);
const error    = ref(null);

async function charger() {
    loading.value = true;
    error.value   = null;
    try {
        const res = await fetch('/api/candidat/contrats', {
            headers: { 'Accept': 'application/json' },
            credentials: 'same-origin',
        });
        if (!res.ok) {
            if (res.status === 401) { window.location.href = '/connexion'; return; }
            throw new Error(`HTTP ${res.status}`);
        }
        const data = await res.json();
        contrats.value = data.contrats;
    } catch (e) {
        error.value = e.message;
    } finally {
        loading.value = false;
    }
}

const badgeColor = (statut) => ({
    BROUILLON:  '#6e84a3',
    A_SIGNER:   '#f6c343',
    SIGNE:      '#00d97e',
    ANNULE:     '#e63757',
}[statut] || '#6e84a3');

const texteBlanc = (statut) => statut !== 'A_SIGNER';

function formatMontant(v) {
    return new Intl.NumberFormat('fr-MG').format(v) + ' Ar';
}

onMounted(charger);
</script>

<template>
    <div>
        <h1>Mes contrats</h1>

        <div v-if="loading" style="text-align:center;padding:24px;color:#6e84a3;">⏳ Chargement…</div>
        <div v-else-if="error" class="flash flash-error">
            Erreur : {{ error }}
            <button class="btn btn-primary" @click="charger" style="margin-left:12px;">Réessayer</button>
        </div>

        <div v-else class="card">
            <div v-if="contrats.length === 0"
                 style="text-align:center;color:#6e84a3;padding:24px;">
                Aucun contrat pour le moment.<br>
                Un contrat est généré après que la Direction a retenu votre candidature.
            </div>

            <table v-else>
                <thead><tr>
                    <th>Référence</th><th>Offre</th><th>Type</th>
                    <th>Salaire</th><th>Statut</th><th>Actions</th>
                </tr></thead>
                <tbody>
                    <tr v-for="c in contrats" :key="c.id_contrat">
                        <td><strong>{{ c.reference }}</strong></td>
                        <td>{{ c.offre_titre }}</td>
                        <td>{{ c.type_contrat }}</td>
                        <td>{{ formatMontant(c.salaire) }}</td>
                        <td>
                            <span class="badge"
                                  :style="{ background: badgeColor(c.statut), color: texteBlanc(c.statut) ? '#fff' : '#333' }">
                                {{ c.statut }}
                            </span>
                        </td>
                        <td>
                            <a class="btn btn-primary" style="padding:4px 10px;font-size:12px;"
                               :href="`/candidat/contrats/${c.id_contrat}`">Détail</a>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>