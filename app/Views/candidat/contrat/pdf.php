<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 11pt; line-height: 1.4; margin: 30px; }
    h1 { text-align: center; font-size: 16pt; margin-bottom: 4px; }
    h2 { font-size: 13pt; border-bottom: 1px solid #999; padding-bottom: 3px; margin-top: 18px; }
    .ref { text-align: center; color: #666; font-size: 10pt; margin-bottom: 20px; }
    table { width: 100%; border-collapse: collapse; margin-top: 8px; }
    td, th { padding: 4px 6px; border: 1px solid #ccc; vertical-align: top; }
    th { background: #eee; text-align: left; }
    .signature { margin-top: 30px; border-top: 1px dashed #999; padding-top: 10px; }
    .small { font-size: 9pt; color: #666; }
</style>
</head>
<body>

<h1>CONTRAT DE TRAVAIL</h1>
<div class="ref">Référence : <strong><?= esc($contrat['reference']) ?></strong></div>

<h2>Entre les soussignés</h2>
<table>
    <tr><th style="width:30%;">Employeur</th><td><?= esc($employeur) ?></td></tr>
    <tr><th>Salarié</th><td>
        <?= esc(($contrat['prenom'] ?? '') . ' ' . ($contrat['nom'] ?? '')) ?><br>
        <?= esc($contrat['adresse'] ?? '') ?><br>
        Tél : <?= esc($contrat['telephone'] ?? '') ?> — Email : <?= esc($contrat['email'] ?? '') ?>
    </td></tr>
</table>

<h2>Article 1 — Engagement</h2>
<table>
    <tr><th style="width:30%;">Poste</th><td><?= esc($contrat['poste_libelle'] ?? '') ?></td></tr>
    <tr><th>Département</th><td><?= esc($contrat['departement_nom'] ?? '') ?></td></tr>
    <tr><th>Lieu de travail</th><td><?= esc($contrat['lieu_travail'] ?? '') ?></td></tr>
    <tr><th>Type de contrat</th><td><?= esc($contrat['type_contrat']) ?></td></tr>
    <tr><th>Date de début</th><td><?= esc($contrat['date_debut']) ?></td></tr>
    <tr><th>Date de fin</th><td><?= $contrat['date_fin'] ? esc($contrat['date_fin']) : 'Indéterminée' ?></td></tr>
    <tr><th>Rémunération</th><td><?= number_format((float)$contrat['salaire'], 0, ',', ' ') ?> Ar
        <?= $contrat['periodicite_salaire'] ? ' (' . esc(strtolower($contrat['periodicite_salaire'])) . ')' : '' ?></td></tr>
</table>

<h2>Article 2 — Missions</h2>
<p><?= nl2br(esc($contrat['missions'] ?? '')) ?></p>

<h2>Article 3 — Horaires</h2>
<p><?= nl2br(esc($contrat['horaires'] ?? '')) ?></p>

<h2>Article 4 — Congés et absences</h2>
<p><?= nl2br(esc($contrat['conges_absences'] ?? '')) ?></p>

<?php if (! empty($conditions)): ?>
<h2>Article 5 — Conditions particulières</h2>
<table>
    <thead><tr><th style="width:5%;">#</th><th>Libellé</th><th style="width:35%;">Valeur</th></tr></thead>
    <tbody>
    <?php foreach ($conditions as $c): ?>
        <tr>
            <td><?= (int)$c['ordre_affichage'] ?></td>
            <td><?= esc($c['libelle']) ?></td>
            <td><?= esc($c['valeur_finale']) ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>

<div class="signature">
    <p>Fait à Antananarivo, le <?= date('d/m/Y') ?>, en deux exemplaires originaux.</p>
    <table style="border:none;margin-top:20px;">
        <tr>
            <td style="border:none;width:50%;">
                <strong>L'employeur</strong><br><br><br>
                <span class="small">Signature et cachet</span>
            </td>
            <td style="border:none;width:50%;">
                <strong>Le salarié</strong><br>
                <?= esc(($contrat['prenom'] ?? '') . ' ' . ($contrat['nom'] ?? '')) ?><br><br><br>
                <span class="small">Signature précédée de « Lu et approuvé »</span>
            </td>
        </tr>
    </table>
</div>

<p class="small" style="margin-top:30px;text-align:center;">
    Document généré automatiquement par le SIRH — <?= date('d/m/Y H:i') ?>
</p>

</body>
</html>