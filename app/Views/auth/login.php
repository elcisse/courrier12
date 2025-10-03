<section class="auth-card">
    <h2>Connexion</h2>
    <p class="auth-subtitle">Accedez a l'espace de gestion du courrier.</p>

    <?php if (!empty($errors['base'])): ?>
        <div class="alert alert-error">
            <?= $helpers::sanitize($errors['base']) ?>
        </div>
    <?php endif; ?>

    <form method="post" action="<?= $helpers::route('auth', 'authenticate') ?>" class="form-card form-card--narrow">
        <input type="hidden" name="_token" value="<?= $helpers::csrfToken() ?>">

        <div class="form-field">
            <label for="login">Identifiant</label>
            <input type="text" name="login" id="login" value="<?= $helpers::sanitize((string) call_user_func([$helpers, 'old'], 'login', '')) ?>" required autofocus>
            <?php if (!empty($errors['login'])): ?>
                <p class="form-error"><?= $helpers::sanitize($errors['login']) ?></p>
            <?php endif; ?>
        </div>

        <div class="form-field">
            <label for="password">Mot de passe</label>
            <input type="password" name="password" id="password" required>
            <?php if (!empty($errors['password'])): ?>
                <p class="form-error"><?= $helpers::sanitize($errors['password']) ?></p>
            <?php endif; ?>
        </div>

        <div class="form-actions">
            <button type="submit" class="button">Se connecter</button>
            <a class="button button-light" href="<?= $helpers::route('home') ?>">Annuler</a>
        </div>
    </form>
</section>
