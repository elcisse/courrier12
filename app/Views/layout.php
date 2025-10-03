<?php
$currentController = strtolower($_GET['controller'] ?? 'home');
$currentAction = strtolower($_GET['action'] ?? 'index');
$csrfToken = $helpers::csrfToken();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $helpers::sanitize($pageTitle ?? 'Gestion du courrier') ?></title>
    <link rel="stylesheet" href="assets/css/app.css">
</head>
<body>
    <header class="topbar">
        <div class="container">
            <h1 class="brand">Gestion du courrier</h1>
            <nav class="main-nav">
                <a href="<?= $helpers::route('home') ?>" class="<?= $currentController === 'home' ? 'active' : '' ?>">Accueil</a>
                <a href="<?= $helpers::route('courrier') ?>" class="<?= $currentController === 'courrier' ? 'active' : '' ?>">Courriers</a>
                <?php if (!empty($authUser) && ($authUser['role'] ?? '') === 'ADMIN'): ?>
                    <a href="<?= $helpers::route('service') ?>" class="<?= $currentController === 'service' ? 'active' : '' ?>">Services</a>
                    <a href="<?= $helpers::route('user') ?>" class="<?= $currentController === 'user' ? 'active' : '' ?>">Utilisateurs</a>
                <?php endif; ?>
            </nav>
            <div class="user-nav">
                <?php if (!empty($authUser)): ?>
                    <span class="user-nav__name"><?= $helpers::sanitize($authUser['name'] ?? $authUser['login'] ?? 'Utilisateur') ?></span>
                    <form method="post" action="<?= $helpers::route('auth', 'logout') ?>" class="inline-form">
                        <input type="hidden" name="_token" value="<?= $csrfToken ?>">
                        <button type="submit" class="button button-light button-small">Se deconnecter</button>
                    </form>
                <?php else: ?>
                    <a class="button button-light" href="<?= $helpers::route('auth', 'login') ?>">Se connecter</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <main class="container">
        <?php foreach (['success', 'warning', 'error'] as $flashKey): ?>
            <?php if ($message = $helpers::flash($flashKey)): ?>
                <div class="alert alert-<?= $flashKey ?>">
                    <?= $helpers::sanitize($message) ?>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>

        <?= $content ?>
    </main>

    <script>
        window.__CSRF_TOKEN__ = '<?= $csrfToken ?>';
    </script>
    <script src="assets/js/app.js"></script>
</body>
</html>
