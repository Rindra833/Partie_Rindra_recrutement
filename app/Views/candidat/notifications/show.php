<?= $this->extend('candidat/_layout') ?>
<?= $this->section('content') ?>
<h1><?= esc($notif['titre']) ?></h1>
<p style="color:#6e84a3;">
    Reçue le <?= esc(substr((string)$notif['date_creation'], 0, 16)) ?>
    <?php if ($notif['date_lecture']): ?>
        — lue le <?= esc(substr((string)$notif['date_lecture'], 0, 16)) ?>
    <?php endif; ?>
</p>

<div class="card">
    <p style="font-size:15px;line-height:1.6;"><?= nl2br(esc($notif['message'])) ?></p>
</div>

<?php if (! empty($notif['lien'])): ?>
<div class="card">
    <h2>Action</h2>
    <a class="btn btn-success" href="<?= esc($notif['lien']) ?>">➡️ Continuer</a>
</div>
<?php endif; ?>

<p>
    <a class="btn btn-secondary" href="/candidat/notifications">← Retour aux notifications</a>
</p>
<?= $this->endSection() ?>