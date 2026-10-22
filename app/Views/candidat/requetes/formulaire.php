<?php helper('form'); ?>
<?= $this->extend('candidat/_layout') ?>
<?= $this->section('content') ?>
<h1>Proposer des modifications</h1>

<div class="card">
    <p><strong>Offre :</strong> <?= esc($candidature['offre_titre']) ?></p>
    <p><strong>Poste :</strong> <?= esc($candidature['poste_libelle'] ?? '-') ?>
       — <strong>Département :</strong> <?= esc($candidature['departement_nom'] ?? '-') ?></p>
</div>

<div class="card">
    <h2>Conditions confirmées du poste</h2>
    <p style="color:#6e84a3;font-size:13px;">
        Laissez un champ vide si vous acceptez la condition telle quelle.<br>
        Remplissez uniquement les conditions que vous souhaitez modifier.
    </p>

    <?php if (! empty($errors)): ?>
        <div class="flash flash-error">
            <ul style="margin:8px 0 0 20px;">
                <?php foreach ($errors as $msg): ?><li><?= esc($msg) ?></li><?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?= form_open('/candidat/requetes/' . (int)$candidature['id_candidature'] . '/creer') ?>

    <table>
        <thead><tr>
            <th style="width:25%;">Condition</th>
            <th style="width:15%;">Valeur actuelle</th>
            <th style="width:25%;">Votre proposition</th>
            <th style="width:35%;">Motif (optionnel)</th>
        </tr></thead>
        <tbody>
        <?php foreach ($conditions as $c): ?>
            <?php $idc = (int)$c['id_condition']; ?>
            <tr>
                <td>
                    <strong><?= esc($c['libelle']) ?></strong><br>
                    <small style="color:#6e84a3;">Type : <?= esc($c['type_valeur']) ?></small>
                </td>
                <td>
                    <em><?= esc($c['valeur_condition']) ?></em>
                </td>
                <td>
                    <input type="text"
                           name="proposition[<?= $idc ?>]"
                           value="<?= esc($old['proposition'][$idc] ?? '') ?>"
                           placeholder="Nouvelle valeur (vide = inchangé)"
                           style="width:100%;box-sizing:border-box;padding:6px;">
                </td>
                <td>
                    <input type="text"
                           name="motif[<?= $idc ?>]"
                           value="<?= esc($old['motif'][$idc] ?? '') ?>"
                           placeholder="Pourquoi cette modification ?"
                           style="width:100%;box-sizing:border-box;padding:6px;">
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <p style="margin-top:16px;">
        <button class="btn btn-success" type="submit">📤 Transmettre au RH</button>
        <a class="btn btn-secondary" href="/candidat/requetes/<?= (int)$candidature['id_candidature'] ?>">Annuler</a>
    </p>

    <?= form_close() ?>
</div>
<?= $this->endSection() ?>