<?php
$isEdit = isset($service['id']);
$actionUrl = $action ?? $helpers::route('service', $isEdit ? 'update' : 'store');
$codeValue = call_user_func([$helpers, 'old'], 'code', $service['code'] ?? '');
$libelleValue = call_user_func([$helpers, 'old'], 'libelle', $service['libelle'] ?? '');
$actifValue = call_user_func([$helpers, 'old'], 'actif', $service['actif'] ?? 1);
?>
<section class="page-header">
    <h2><?= $isEdit ? 'Modifier le service' : 'Nouveau service' ?></h2>
    <p>Saisis les informations du service.</p>
</section>

<?php if (!empty($errors['base'])): ?>
    <div class="alert alert-error">
        <?= $helpers::sanitize($errors['base']) ?>
    </div>
<?php endif; ?>

<form method="post" action="<?= $actionUrl ?>" class="form-card">
    <input type="hidden" name="_token" value="<?= $helpers::csrfToken() ?>">
    <?php if ($isEdit): ?>
        <input type="hidden" name="id" value="<?= (int) $service['id'] ?>">
    <?php endif; ?>

    <div class="form-field">
        <label for="code">Code *</label>
        <input type="text" name="code" id="code" value="<?= $helpers::sanitize((string) $codeValue) ?>" maxlength="30" required>
        <?php if (!empty($errors['code'])): ?>
            <p class="form-error"><?= $helpers::sanitize($errors['code']) ?></p>
        <?php endif; ?>
    </div>

    <div class="form-field">
        <label for="libelle">Libelle *</label>
        <input type="text" name="libelle" id="libelle" value="<?= $helpers::sanitize((string) $libelleValue) ?>" maxlength="150" required>
        <?php if (!empty($errors['libelle'])): ?>
            <p class="form-error"><?= $helpers::sanitize($errors['libelle']) ?></p>
        <?php endif; ?>
    </div>

    <div class="form-field form-checkbox">
        <label>
            <input type="checkbox" name="actif" value="1" <?= !empty($actifValue) ? 'checked' : '' ?>>
            Service actif
        </label>
    </div>

    <div class="form-actions">
        <button type="submit" class="button">Enregistrer</button>
        <a class="button button-secondary" href="<?= $helpers::route('service') ?>">Annuler</a>
    </div>
</form>