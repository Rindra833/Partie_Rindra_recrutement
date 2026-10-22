<?= $this->extend('candidat/_layout') ?>
<?= $this->section('content') ?>
<h1>Contrat <?= esc($contrat['reference']) ?></h1>
<p>
    <strong>Statut :</strong>
    <?php
    $colors = ['BROUILLON'=>'#6e84a3','A_SIGNER'=>'#f6c343','SIGNE'=>'#00d97e','ANNULE'=>'#e63757'];
    $col = $colors[$contrat['statut']] ?? '#6e84a3';
    ?>
    <span style="background:<?= $col ?>;color:<?= $contrat['statut']==='A_SIGNER'?'#333':'#fff' ?>;padding:3px 10px;border-radius:12px;font-size:13px;">
        <?= esc($contrat['statut']) ?>
    </span>
</p>

<div class="card">
    <h2>Parties</h2>
    <p><strong>Employeur :</strong> SIRH SARL — Antananarivo, Madagascar</p>
    <p><strong>Salarié :</strong> <?= esc(($contrat['prenom'] ?? '') . ' ' . ($contrat['nom'] ?? '')) ?></p>
    <p><strong>Email :</strong> <?= esc($contrat['email'] ?? '-') ?>
       — <strong>Téléphone :</strong> <?= esc($contrat['telephone'] ?? '-') ?></p>
    <p><strong>Adresse :</strong> <?= esc($contrat['adresse'] ?? '-') ?></p>
</div>

<div class="card">
    <h2>Conditions du poste</h2>
    <p><strong>Poste :</strong> <?= esc($contrat['poste_libelle'] ?? '-') ?>
       — <strong>Département :</strong> <?= esc($contrat['departement_nom'] ?? '-') ?></p>
    <p><strong>Type :</strong> <?= esc($contrat['type_contrat']) ?>
       — <strong>Durée :</strong> <?= $contrat['date_fin'] ? 'du ' . esc($contrat['date_debut']) . ' au ' . esc($contrat['date_fin']) : 'Indéterminée (CDI)' ?></p>
    <p><strong>Date de début :</strong> <?= esc($contrat['date_debut']) ?></p>
    <p><strong>Salaire :</strong> <?= number_format((float)$contrat['salaire'], 0, ',', ' ') ?> Ar
       <?= $contrat['periodicite_salaire'] ? ' / ' . esc(strtolower($contrat['periodicite_salaire'])) : '' ?></p>
    <p><strong>Lieu de travail :</strong> <?= esc($contrat['lieu_travail'] ?? '-') ?></p>
</div>

<?php if (! empty($conditions)): ?>
<div class="card">
    <h2>Conditions figées au contrat</h2>
    <table>
        <thead><tr><th>#</th><th>Libellé</th><th>Valeur</th></tr></thead>
        <tbody>
        <?php foreach ($conditions as $c): ?>
            <tr>
                <td><?= (int)$c['ordre_affichage'] ?></td>
                <td><?= esc($c['libelle']) ?></td>
                <td><strong><?= esc($c['valeur_finale']) ?></strong></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<div class="card">
    <h2>Missions et organisation</h2>
    <p><strong>Missions :</strong><br><?= nl2br(esc($contrat['missions'] ?? '-')) ?></p>
    <p><strong>Horaires :</strong><br><?= nl2br(esc($contrat['horaires'] ?? '-')) ?></p>
    <p><strong>Congés et absences :</strong><br><?= nl2br(esc($contrat['conges_absences'] ?? '-')) ?></p>
</div>

<?php if ($peut_agir): ?>
<div class="card">
    <h2>Votre décision</h2>
    <p>En acceptant, vous figerez les conditions du poste et générerez le PDF du contrat à signer.</p>
    <form method="post" action="/candidat/contrats/<?= (int)$contrat['id_contrat'] ?>/accepter" style="display:inline">
        <button class="btn btn-success" type="submit" onclick="return confirm('Accepter cette proposition de contrat ?')">
            ✅ Accepter la proposition
        </button>
    </form>
    <form method="post" action="/candidat/contrats/<?= (int)$contrat['id_contrat'] ?>/refuser" style="display:inline;margin-left:8px">
        <input type="text" name="commentaire" placeholder="Motif (optionnel)" style="padding:6px;margin-right:6px;">
        <button class="btn btn-danger" type="submit" onclick="return confirm('Refuser cette proposition ?')">
            ❌ Refuser
        </button>
    </form>
</div>
<?php endif; ?>

<?php if ($peut_voir_pdf): ?>
<div class="card">
    <h2>Document PDF</h2>
    <p>
        <a class="btn btn-primary" href="/candidat/contrats/<?= (int)$contrat['id_contrat'] ?>/pdf" target="_blank">
            📄 Télécharger / voir le PDF
        </a>
    </p>
    <?php if ($contrat['statut'] === 'A_SIGNER'): ?>
        <p style="color:#6e84a3;font-size:13px;margin-top:12px;">
            ⚠️ La signature électronique sera disponible à l'étape suivante (D.4.2).
        </p>
    <?php elseif ($contrat['statut'] === 'SIGNE'): ?>
        <p style="color:#00d97e;font-size:13px;margin-top:12px;">
            ✅ Signé le <?= esc(substr((string)$contrat['date_signature'], 0, 16)) ?>
        </p>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php if (! empty($historique)): ?>
<div class="card">
    <h2>Historique</h2>
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
        </tbody>
    </table>
</div>
<?php endif; ?>

<p><a class="btn btn-secondary" href="/candidat/contrats">← Retour à mes contrats</a></p>

<?php if ($contrat['statut'] === 'A_SIGNER'): ?>
<div class="card">
    <h2>Signature électronique</h2>
    <p style="font-size:14px;">
        En signant, vous acceptez définitivement les termes du contrat.
        Votre compte employé sera créé automatiquement avec un matricule unique.
    </p>

    <?php if (! empty($errors = session()->getFlashdata('errors'))): ?>
        <div class="flash flash-error">
            <ul style="margin:8px 0 0 20px;">
                <?php foreach ($errors as $msg): ?><li><?= esc($msg) ?></li><?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" action="/candidat/contrats/<?= (int)$contrat['id_contrat'] ?>/signer">
        <p>
            <label><strong>Nom complet du salarié (tel qu'il apparaît sur le contrat)</strong><br>
                <input type="text" name="nom_complet" required minlength="3" style="width:100%;padding:8px;box-sizing:border-box;"
                       placeholder="<?= esc(($contrat['prenom_salarie'] ?? '') . ' ' . ($contrat['nom_salarie'] ?? '')) ?>"
                       value="<?= esc($contrat['prenom_salarie'] ?? '') ?> <?= esc($contrat['nom_salarie'] ?? '') ?>">
            </label>
        </p>
        <p>
            <label style="display:flex;gap:8px;align-items:center;">
                <input type="checkbox" name="accepte" value="1" required>
                <span>J'ai lu et j'approuve le contrat. Je reconnais que cette signature électronique a valeur juridique.</span>
            </label>
        </p>
        <p>
            <button class="btn btn-success" type="submit"
                    onclick="return confirm('Signer définitivement le contrat ? Cette action est irréversible.')">
                ✍️ Signer le contrat
            </button>
        </p>
    </form>
</div>
<?php endif; ?>

<?php if ($contrat['statut'] === 'SIGNE'): ?>
<div class="card" style="background:#e9f7f1;">
    <h2>✅ Contrat signé</h2>
    <p><strong>Signé le :</strong> <?= esc(substr((string)$contrat['date_signature'], 0, 16)) ?></p>
    <?php
    $employe = \Config\Database::connect()->table('employe')
        ->where('id_contrat', (int)$contrat['id_contrat'])->get()->getRowArray();
    ?>
    <?php if ($employe): ?>
        <p><strong>Matricule employé :</strong> <span style="font-family:monospace;font-size:16px;background:#fff;padding:4px 10px;border-radius:4px;"><?= esc($employe['matricule']) ?></span></p>
        <p><strong>Date d'embauche :</strong> <?= esc($employe['date_embauche']) ?></p>
        <p><strong>Statut employé :</strong> <?= esc($employe['statut']) ?></p>
    <?php endif; ?>
</div>
<?php endif; ?>

<?= $this->endSection() ?>