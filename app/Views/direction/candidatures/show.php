<?= $this->extend('direction/_layout') ?>
<?= $this->section('content') ?>
<h1>Candidature #<?= (int)$candidature['id_candidature'] ?></h1>
<p><span class="badge badge-<?= esc($candidature['statut']) ?>"><?= esc($candidature['statut']) ?></span></p>

<div class="card">
    <h2>Candidat</h2>
    <p><strong>Nom :</strong> <?= esc(($candidature['nom'] ?? '') . ' ' . ($candidature['prenom'] ?? '')) ?></p>
    <p><strong>Email :</strong> <?= esc($candidature['email_candidat'] ?? '-') ?></p>
    <p><strong>Téléphone :</strong> <?= esc($candidature['telephone'] ?? '-') ?></p>
    <p><strong>Adresse :</strong> <?= esc($candidature['adresse'] ?? '-') ?></p>
    <p><strong>Niveau d'étude :</strong> <?= esc($candidature['niveau_etude'] ?? '-') ?></p>
    <p><strong>Expérience :</strong> <?= esc($candidature['experience'] ?? '-') ?></p>
    <p><strong>Motivation :</strong><br><?= nl2br(esc($candidature['motivation'] ?? '-')) ?></p>
</div>

<div class="card">
    <h2>Offre</h2>
    <p><strong>Titre :</strong> <?= esc($candidature['offre_titre'] ?? '-') ?></p>
    <p><strong>Poste :</strong> <?= esc($candidature['poste_libelle'] ?? '-') ?>
       — <strong>Département :</strong> <?= esc($candidature['departement_nom'] ?? '-') ?></p>
</div>

<div class="card">
    <h2>Documents (accès protégé)</h2>
    <p>
        <a class="btn btn-primary" href="/direction/candidatures/<?= (int)$candidature['id_candidature'] ?>/document/cv"     target="_blank">📄 CV</a>
        <a class="btn btn-primary" href="/direction/candidatures/<?= (int)$candidature['id_candidature'] ?>/document/lettre" target="_blank">✉️ Lettre de motivation</a>
        <a class="btn btn-primary" href="/direction/candidatures/<?= (int)$candidature['id_candidature'] ?>/document/cin"    target="_blank">🪪 CIN</a>
    </p>
</div>

<div class="card">
    <h2>Évaluations RH</h2>
    <table>
        <thead><tr><th>Type</th><th>Note</th><th>Évaluateur</th><th>Date</th><th>Contexte</th><th>Commentaire</th></tr></thead>
        <tbody>
        <?php foreach ($evaluations as $ev): ?>
            <tr>
                <td><?= esc($ev['type_evaluation']) ?></td>
                <td><?= $ev['note'] !== null ? esc((string)$ev['note']) . '/10' : '-' ?></td>
                <td><?= esc($ev['evaluateur_email'] ?? '-') ?></td>
                <td><?= esc(substr((string)$ev['date_evaluation'], 0, 16)) ?></td>
                <td>
                    <?php if ($ev['type_evaluation'] === 'ENTRETIEN'): ?>
                        Entretien du <?= esc(substr((string)$ev['date_entretien'], 0, 16)) ?>
                    <?php elseif ($ev['type_evaluation'] === 'TEST_TECHNIQUE'): ?>
                        <?= esc($ev['test_intitule'] ?? '-') ?>
                    <?php else: ?>—<?php endif; ?>
                </td>
                <td><?= esc($ev['commentaire'] ?? '') ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($evaluations)): ?>
            <tr><td colspan="6" style="text-align:center;color:#6e84a3;">Aucune évaluation.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if (in_array($candidature['statut'], ['PRESELECTIONNEE','SELECTIONNEE'], true)): ?>
<div class="card">
    <h2>Décision Direction</h2>
    <form method="post" action="/direction/candidatures/<?= (int)$candidature['id_candidature'] ?>/retenir">
        <textarea name="commentaire" placeholder="Commentaire (optionnel)" rows="2"></textarea>
        <button class="btn btn-success" type="submit"
                onclick="return confirm('Retenir cette candidature et notifier le candidat ?')">
            ✅ Retenir et notifier le candidat
        </button>
    </form>
</div>
<?php endif; ?>

<div class="card">
    <h2>Historique des statuts</h2>
    <table>
        <thead><tr><th>Date</th><th>Ancien</th><th>Nouveau</th><th>Auteur</th><th>Commentaire</th></tr></thead>
        <tbody>
        <?php foreach ($historique as $h): ?>
            <tr>
                <td><?= esc(substr((string)$h['date_action'], 0, 16)) ?></td>
                <td><?= esc($h['ancien_statut'] ?? '-') ?></td>
                <td><?= esc($h['nouveau_statut']) ?></td>
                <td><?= esc($h['auteur'] ?? '-') ?></td>
                <td><?= esc($h['commentaire'] ?? '') ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($historique)): ?>
            <tr><td colspan="5" style="text-align:center;color:#6e84a3;">Aucun historique.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<?= $this->endSection() ?>