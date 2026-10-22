<?= $this->extend('candidat/_layout') ?>
<?= $this->section('content') ?>
<h1>Requêtes — <?= esc($candidature['offre_titre']) ?></h1>

<?php if ($peut_creer): ?>
    <p><a class="btn btn-success" href="/candidat/requetes/<?= (int)$candidature['id_candidature'] ?>/creer">+ Proposer une nouvelle modification</a></p>
<?php endif; ?>

<div class="card">
    <h2>Conditions du poste</h2>
    <table>
        <thead><tr><th>#</th><th>Libellé</th><th>Type</th><th>Valeur actuelle</th></tr></thead>
        <tbody>
        <?php foreach ($conditions as $c): ?>
            <tr>
                <td><?= (int)$c['ordre_affichage'] ?></td>
                <td><?= esc($c['libelle']) ?></td>
                <td><?= esc($c['type_valeur']) ?></td>
                <td><strong><?= esc($c['valeur_condition']) ?></strong></td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($conditions)): ?>
            <tr><td colspan="4" style="text-align:center;color:#6e84a3;">Aucune condition confirmée.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="card">
    <h2>Mes propositions</h2>
    <table>
        <thead><tr>
            <th>Date</th><th>Condition</th><th>Proposition</th><th>Motif</th><th>Statut</th>
        </tr></thead>
        <tbody>
        <?php foreach ($requetes as $r): ?>
            <tr>
                <td><?= esc(substr((string)$r['date_creation'], 0, 16)) ?></td>
                <td><?= esc($r['libelle'] ?? '-') ?></td>
                <td><strong><?= esc($r['valeur_proposee']) ?></strong></td>
                <td><?= esc($r['commentaire_candidat'] ?? '—') ?></td>
                <td>
                    <?php
                    $labels = [
                        'SOUMISE'             => ['Soumise',              '#f6c343'],
                        'ACCEPTEE_RH'         => ['Acceptée RH',          '#2c7be5'],
                        'REFUSEE_RH'          => ['Refusée RH',           '#e63757'],
                        'ACCEPTEE_DIRECTION'  => ['Acceptée Direction ✅', '#00d97e'],
                        'REFUSEE_DIRECTION'   => ['Refusée Direction ❌',  '#e63757'],
                        'BROUILLON'           => ['Brouillon',            '#6e84a3'],
                    ];
                    [$label, $color] = $labels[$r['statut']] ?? [$r['statut'], '#6e84a3'];
                    ?>
                    <span style="background:<?= $color ?>;color:#fff;padding:3px 8px;border-radius:12px;font-size:12px;">
                        <?= esc($label) ?>
                    </span>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($requetes)): ?>
            <tr><td colspan="5" style="text-align:center;color:#6e84a3;padding:24px;">
                Vous n'avez encore fait aucune proposition.
            </td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<p><a class="btn btn-secondary" href="/candidat/requetes">← Retour à mes candidatures retenues</a></p>
<?= $this->endSection() ?>