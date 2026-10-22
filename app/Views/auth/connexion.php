<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion — SIRH</title>
    <style>
        body { font-family: system-ui, sans-serif; max-width: 420px; margin: 3rem auto; padding: 0 1rem; }
        .msg { padding: .75rem; margin-bottom: 1rem; border-radius: 4px; }
        .error { background: #fee2e2; color: #991b1b; }
        .success { background: #dcfce7; color: #166534; }
        label { display: block; margin-top: .75rem; font-weight: 600; }
        input { width: 100%; padding: .5rem; margin-top: .25rem; box-sizing: border-box; }
        button { margin-top: 1.25rem; padding: .6rem 1.2rem; cursor: pointer; }
    </style>
</head>
<body>
    <h1>Connexion SIRH</h1>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="msg error"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('success')): ?>
        <div class="msg success"><?= esc(session()->getFlashdata('success')) ?></div>
    <?php endif; ?>
    <form method="post" action="<?= site_url('connexion') ?>">
        <?= csrf_field() ?>
        <label for="email">Email</label>
        <input type="email" id="email" name="email" required autocomplete="username">
        <label for="mot_de_passe">Mot de passe</label>
        <input type="password" id="mot_de_passe" name="mot_de_passe" required autocomplete="current-password">
        <button type="submit">Se connecter</button>
    </form>
    <p style="margin-top:1.5rem"><a href="<?= site_url('inscription') ?>">Créer un compte candidat</a></p>
</body>
</html>
