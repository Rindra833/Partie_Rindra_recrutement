<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= esc($title ?? 'Espace Candidat') ?></title>
    <style>
        body { font-family: system-ui, sans-serif; margin: 0; background: #f4f6f9; }
        nav { background:#1f2d3d; color:#fff; padding: 12px 24px; display:flex; gap:20px; align-items:center; }
        nav a { color:#fff; text-decoration:none; }
        nav a:hover { text-decoration: underline; }
        nav .disabled { color:#6e84a3; cursor:not-allowed; }
        main { padding: 24px; max-width: 1100px; margin: 0 auto; }
        .card { background:#fff; border-radius:6px; padding:16px; box-shadow:0 1px 3px rgba(0,0,0,.08); margin-bottom:16px; }
        .grid { display:grid; grid-template-columns: repeat(4, 1fr); gap:16px; margin-bottom:24px; }
        .kpi { background:#fff; padding:16px; border-radius:6px; text-align:center; }
        .kpi .num { font-size: 28px; font-weight: 700; color: #2c7be5; }
        .btn { display:inline-block; padding: 8px 14px; border-radius:4px; color:#fff; text-decoration:none; border:none; cursor:pointer; font-size:14px; }
        .btn-primary { background:#2c7be5; }
        .btn-success { background:#00d97e; }
        .btn-secondary { background:#6e84a3; }
        .btn[disabled] { opacity:.6; cursor:not-allowed; }
        table { width:100%; border-collapse: collapse; }
        th, td { padding: 10px; border-bottom: 1px solid #e9ecef; text-align:left; font-size:14px; }
        .badge { padding: 3px 8px; border-radius: 12px; font-size:12px; color:#fff; }
        .badge-PUBLIEE { background:#2c7be5; }
        .badge-RECUE { background:#6e84a3; }
        .flash { padding:10px; border-radius:4px; margin-bottom:12px; }
        .flash-success { background:#d4edda; color:#155724; }
        .flash-error { background:#f8d7da; color:#721c24; }
    </style>
</head>
<body>
<nav>
    <strong>Espace Candidat</strong>
    <a href="/candidat">Accueil</a>
    <a href="/candidat/offres">Offres</a>
    <a href="/candidat/mes-candidatures">Mes candidatures</a>
    <a href="/candidat/requetes">Requêtes conditions</a>
    <a href="/candidat/contrats">Mes contrats</a>
    <?php
$__idUser = (int) session()->get('id_utilisateur');
$__nbNotifs = $__idUser ? (\Config\Database::connect()
    ->table('notification')
    ->where('id_utilisateur', $__idUser)
    ->where('lue', false)
    ->countAllResults()) : 0;
?>
<a href="/candidat/notifications">
    Notifications
    <?php if ($__nbNotifs > 0): ?>
        <span style="background:#e63757;color:#fff;font-size:11px;padding:1px 7px;border-radius:10px;margin-left:4px;"><?= (int)$__nbNotifs ?></span>
    <?php endif; ?>
</a>
    <a href="/deconnexion" style="margin-left:auto;">Déconnexion</a>
</nav>
<main>
    <?php if (session()->getFlashdata('success')): ?>
        <div class="flash flash-success"><?= esc(session()->getFlashdata('success')) ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="flash flash-error"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>
    <?= $this->renderSection('content') ?>
</main>
</body>
</html>