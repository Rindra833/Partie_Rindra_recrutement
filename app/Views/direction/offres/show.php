<?= $this->extend('direction/_layout') ?>
<?= $this->section('content') ?>
<h1><?= esc($offre['titre']) ?></h1>
<p><span class="badge badge-<?= esc($offre['statut']) ?>"><?= esc($offre['statut']) ?></span></p>

<div class="card">
    <h2>Détails</h2>
    <p><strong>Description :</strong><br><?= nl2br(esc($offre['description'])) ?></p>
    <p><strong>Motif :</strong> <?= esc($offre['motif'] ?? '-') ?></p>
    <p><strong>Poste :</strong> <?= esc($offre['poste_libelle'] ?? '-') ?>
       — <strong>Département :</strong> <?= esc($offre['departement_nom'] ?? '-') ?></p>
    <p><strong>Effectif demandé :</strong> <?= (int)($offre['effectif_demande'] ?? 0) ?>
       — <strong>Salaire / personne :</strong> <?= number_format((float)($offre['salaire_par_personne'] ?? 0), 0, ',', ' ') ?></p>
    <p><strong>Date de besoin :</strong> <?= esc($offre['date_besoin'] ?? '-') ?></p>
</div>

<?php if ($offre['statut'] === 'EN_ATTENTE'): ?>
<div class="card">
    <h2>Décision Direction</h2>
    <form method="post" action="/direction/offres/<?= (int)$offre['id_offre'] ?>/valider" style="margin-bottom:16px;">
        <textarea name="commentaire" placeholder="Commentaire (optionnel)" rows="2"></textarea>
        <button class="btn btn-success" type="submit" onclick="return confirm('Valider cette offre ?')">✅ Valider</button>
    </form>
    <form method="post" action="/direction/offres/<?= (int)$offre['id_offre'] ?>/refuser">
        <textarea name="commentaire" placeholder="Motif du refus" rows="2"></textarea>
        <button class="btn btn-danger" type="submit" onclick="return confirm('Refuser cette offre ?')">❌ Refuser</button>
    </form>
</div>
<?php endif; ?>

<div class="card">
    <h2>Historique des statuts</h2>
    <table>
        <thead><tr><th>Date</th><th>Ancien</th><th>Nouveau</th><th>Auteur</th><th>Commentaire</th></tr></thead>
        <tbody>
        <?php foreach ($historiques as $h): ?>
            <tr>
                <td><?= esc($h['date_action']) ?></td>
                <td><?= esc($h['ancien_statut'] ?? '-') ?></td>
                <td><?= esc($h['nouveau_statut']) ?></td>
                <td><?= esc($h['auteur'] ?? '-') ?></td>
                <td><?= esc($h['commentaire'] ?? '') ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($historiques)): ?>
            <tr><td colspan="5" style="text-align:center;color:#6e84a3;">Aucun historique.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="card">
    <h2>Décisions enregistrées</h2>
    <table>
        <thead><tr><th>Date</th><th>Validateur</th><th>Décision</th><th>Commentaire</th></tr></thead>
        <tbody>
        <?php foreach ($validations as $v): ?>
            <tr>
                <td><?= esc($v['date_decision']) ?></td>
                <td><?= esc($v['validateur'] ?? '-') ?></td>
                <td><?= esc($v['decision']) ?></td>
                <td><?= esc($v['commentaire'] ?? '') ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($validations)): ?>
            <tr><td colspan="4" style="text-align:center;color:#6e84a3;">Aucune décision.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<?= $this->endSection() ?>