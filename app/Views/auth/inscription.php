<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription candidat — SIRH</title>
    <style>
        body { font-family: system-ui, sans-serif; max-width: 420px; margin: 3rem auto; padding: 0 1rem; }
        label { display: block; margin-top: .75rem; font-weight: 600; }
        input { width: 100%; padding: .5rem; margin-top: .25rem; box-sizing: border-box; }
        button { margin-top: 1.25rem; padding: .6rem 1.2rem; cursor: pointer; }
    </style>
</head>
<body>
    <h1>Inscription candidat</h1>
    <p>À implémenter par Danie (utilisateur + personne + candidat).</p>
    <form method="post" action="<?= site_url('inscription') ?>">
        <?= csrf_field() ?>
        <label for="email">Email</label>
        <input type="email" id="email" name="email" required>
        <label for="mot_de_passe">Mot de passe</label>
        <input type="password" id="mot_de_passe" name="mot_de_passe" required>
        <label for="nom">Nom</label>
        <input type="text" id="nom" name="nom" required>
        <label for="prenom">Prénom</label>
        <input type="text" id="prenom" name="prenom" required>
        <button type="submit">S'inscrire</button>
    </form>
    <p style="margin-top:1.5rem"><a href="<?= site_url('connexion') ?>">Déjà un compte ? Connexion</a></p>
</body>
</html>
