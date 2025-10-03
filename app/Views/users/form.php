<?php
$isEdit = isset($user['id']);
$actionUrl = $action ?? $helpers::route('user', $isEdit ? 'update' : 'store');
$values = [
    'prenom_nom'  => call_user_func([$helpers, 'old'], 'prenom_nom', $user['prenom_nom'] ?? ''),
    'login'       => call_user_func([$helpers, 'old'], 'login', $user['login'] ?? ''),
    'role'        => call_user_func([$helpers, 'old'], 'role', $user['role'] ?? 'AGENT'),
    'service_id'  => call_user_func([$helpers, 'old'], 'service_id', $user['service_id'] ?? ''),
    'actif'       => (int) call_user_func([$helpers, 'old'], 'actif', (int) ($user['actif'] ?? 1)),
];
?>
<section class="page-header">
    <h2><?= $isEdit ? 'Modifier l\'utilisateur' : 'Nouvel utilisateur' ?></h2>
    <p>Creation et mise a jour des comptes d\'acces.</p>
</section>

<?php if (!empty($errors['base'])): ?>
    <div class="alert alert-error">
        <?= $helpers::sanitize($errors['base']) ?>
    </div>
<?php endif; ?>

<form method="post" action="<?= $actionUrl ?>" class="form-card form-card--narrow">
    <input type="hidden" name="_token" value="<?= $helpers::csrfToken() ?>">
    <?php if ($isEdit): ?>
        <input type="hidden" name="id" value="<?= (int) $user['id'] ?>">
    <?php endif; ?>

    <div class="form-grid">
        <div class="form-field">
            <label for="prenom_nom">Nom complet *</label>
            <input type="text" name="prenom_nom" id="prenom_nom" value="<?= $helpers::sanitize((string) $values['prenom_nom']) ?>" maxlength="150" required>
            <?php if (!empty($errors['prenom_nom'])): ?>
                <p class="form-error"><?= $helpers::sanitize($errors['prenom_nom']) ?></p>
            <?php endif; ?>
        </div>

        <div class="form-field">
            <label for="login">Login *</label>
            <input type="text" name="login" id="login" value="<?= $helpers::sanitize((string) $values['login']) ?>" maxlength="80" required>
            <?php if (!empty($errors['login'])): ?>
                <p class="form-error"><?= $helpers::sanitize($errors['login']) ?></p>
            <?php endif; ?>
        </div>

        <div class="form-field">
            <label for="role">Role *</label>
            <select name="role" id="role" required>
                <?php foreach ($roles as $role): ?>
                    <option value="<?= $helpers::sanitize($role) ?>" <?= $values['role'] === $role ? 'selected' : '' ?>><?= $helpers::sanitize($role) ?></option>
                <?php endforeach; ?>
            </select>
            <?php if (!empty($errors['role'])): ?>
                <p class="form-error"><?= $helpers::sanitize($errors['role']) ?></p>
            <?php endif; ?>
        </div>

        <div class="form-field">
            <label for="service_id">Service</label>
            <select name="service_id" id="service_id">
                <option value="">Non attribue</option>
                <?php foreach ($services as $service): ?>
                    <option value="<?= (int) $service['id'] ?>" <?= (string) $values['service_id'] === (string) $service['id'] ? 'selected' : '' ?>><?= $helpers::sanitize($service['libelle'] ?? '') ?></option>
                <?php endforeach; ?>
            </select>
            <?php if (!empty($errors['service_id'])): ?>
                <p class="form-error"><?= $helpers::sanitize($errors['service_id']) ?></p>
            <?php endif; ?>
        </div>

        <div class="form-field">
            <label for="password">Mot de passe <?= $isEdit ? '(laisser vide pour conserver)' : '*' ?></label>
            <input type="password" name="password" id="password" <?= $isEdit ? '' : 'required' ?> minlength="6">
            <?php if (!empty($errors['password'])): ?>
                <p class="form-error"><?= $helpers::sanitize($errors['password']) ?></p>
            <?php elseif ($isEdit): ?>
                <p class="form-hint">Laissez vide pour garder le mot de passe actuel.</p>
            <?php endif; ?>
        </div>

        <div class="form-field">
            <label for="password_confirmation">Confirmation</label>
            <input type="password" name="password_confirmation" id="password_confirmation" <?= $isEdit ? '' : 'required' ?> minlength="6">
            <?php if (!empty($errors['password_confirmation'])): ?>
                <p class="form-error"><?= $helpers::sanitize($errors['password_confirmation']) ?></p>
            <?php endif; ?>
        </div>

        <div class="form-field form-checkbox">
            <label>
                <input type="checkbox" name="actif" value="1" <?= (int) $values['actif'] === 1 ? 'checked' : '' ?>>
                Compte actif
            </label>
        </div>
    </div>

    <div class="form-actions">
        <button type="submit" class="button">Enregistrer</button>
        <a class="button button-secondary" href="<?= $helpers::route('user') ?>">Annuler</a>
    </div>
</form>
