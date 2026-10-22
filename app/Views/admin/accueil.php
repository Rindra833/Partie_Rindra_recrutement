<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= esc($titre ?? 'SIRH') ?></title>
    <style>
        body { font-family: system-ui, sans-serif; max-width: 720px; margin: 2rem auto; padding: 0 1rem; }
        .badge { display: inline-block; background: #e2e8f0; padding: .2rem .6rem; border-radius: 4px; font-size: .85rem; }
        a { color: #2563eb; }
    </style>
</head>
<body>
    <h1><?= esc($titre ?? 'Accueil') ?></h1>
    <p>Rôle session : <span class="badge"><?= esc($role ?? '—') ?></span></p>
    <p>Espace de travail prêt. Ajoutez vos routes et contrôleurs dans ce préfixe.</p>
    <p><a href="<?= site_url('deconnexion') ?>">Déconnexion</a></p>
</body>
</html>
