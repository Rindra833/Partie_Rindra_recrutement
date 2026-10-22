<?php helper('form'); ?>
<?= $this->extend('candidat/_layout') ?>
<?= $this->section('content') ?>
<h1>Postuler — <?= esc($offre['titre']) ?></h1>

<div class="card">
    <h2>Récapitulatif de l'offre</h2>
    <p><strong>Poste :</strong> <?= esc($offre['poste_libelle'] ?? '-') ?>
       — <strong>Département :</strong> <?= esc($offre['departement_nom'] ?? '-') ?></p>
    <p><strong>Date limite :</strong> <?= esc($offre['date_limite'] ?? '—') ?></p>
    <p><strong>Description :</strong><br><?= nl2br(esc($offre['description'])) ?></p>
</div>

<div class="card">
    <h2>Formulaire de candidature</h2>

    <?php if (! empty($errors)): ?>
        <div class="flash flash-error">
            <strong>Corrigez les erreurs suivantes :</strong>
            <ul style="margin:8px 0 0 20px;">
                <?php foreach ($errors as $msg): ?>
                    <li><?= esc($msg) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?= form_open_multipart('/candidat/offres/' . (int)$offre['id_offre'] . '/postuler') ?>

    <p>
        <label><strong>CV (PDF obligatoire, ≤ 5 Mo)</strong><br>
            <input type="file" name="cv" accept="application/pdf" required>
        </label>
    </p>
    <p>
        <label><strong>Lettre de motivation (PDF obligatoire, ≤ 5 Mo)</strong><br>
            <input type="file" name="lettre" accept="application/pdf" required>
        </label>
    </p>
    <p>
        <label><strong>CIN (PDF ou image JPEG/PNG, ≤ 5 Mo)</strong><br>
            <input type="file" name="cin" accept="application/pdf,image/jpeg,image/png" required>
        </label>
    </p>
    <p>
        <label><strong>Motivation (optionnel)</strong><br>
            <textarea name="motivation" rows="4"><?= esc($old['motivation'] ?? '') ?></textarea>
        </label>
    </p>

    <p>
        <button class="btn btn-success" type="submit">📩 Envoyer ma candidature</button>
        <a class="btn btn-secondary" href="/candidat/offres">Annuler</a>
    </p>

    <?= form_close() ?>
</div>

<p style="color:#6e84a3;font-size:13px;">
    💡 Le serveur vérifie le type réel du fichier (pas seulement l'extension) et la taille.
    Une candidature par offre maximum.
</p>
<?= $this->endSection() ?>