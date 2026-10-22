<?= $this->extend('direction/_layout') ?>
<?= $this->section('content') ?>
<h1>Tableau de bord Direction</h1>

<div class="grid">
    <div class="kpi"><div class="num"><?= (int)$stats['offres_en_attente'] ?></div><div>Offres en attente</div></div>
    <div class="kpi"><div class="num"><?= (int)$stats['offres_publiees'] ?></div><div>Offres publiées</div></div>
    <div class="kpi"><div class="num"><?= (int)$stats['candidatures_recues'] ?></div><div>Candidatures reçues</div></div>
    <div class="kpi"><div class="num"><?= (int)$stats['candidatures_retenues'] ?></div><div>Candidatures retenues</div></div>
</div>

<div class="card">
    <h2>Dernières offres</h2>
    <table>
        <thead><tr><th>Titre</th><th>Poste</th><th>Département</th><th>Statut</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($offres as $o): ?>
            <tr>
                <td><?= esc($o['titre']) ?></td>
                <td><?= esc($o['poste_libelle'] ?? '-') ?></td>
                <td><?= esc($o['departement_nom'] ?? '-') ?></td>
                <td><span class="badge badge-<?= esc($o['statut']) ?>"><?= esc($o['statut']) ?></span></td>
                <td><a class="btn btn-primary" href="/direction/offres/<?= (int)$o['id_offre'] ?>">Voir</a></td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($offres)): ?>
            <tr><td colspan="5" style="text-align:center;color:#6e84a3;">Aucune offre.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<?= $this->endSection() ?>