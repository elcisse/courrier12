<?php
$isEdit = isset($courrier['id']);
$actionUrl = $action ?? $helpers::route('courrier', $isEdit ? 'update' : 'store');
$values = [
    'type'              => call_user_func([$helpers, 'old'], 'type', $courrier['type'] ?? 'ENTRANT'),
    'ref'               => call_user_func([$helpers, 'old'], 'ref', $courrier['ref'] ?? ''),
    'objet'             => call_user_func([$helpers, 'old'], 'objet', $courrier['objet'] ?? ''),
    'expediteur'        => call_user_func([$helpers, 'old'], 'expediteur', $courrier['expediteur'] ?? ''),
    'destinataire'      => call_user_func([$helpers, 'old'], 'destinataire', $courrier['destinataire'] ?? ''),
    'date_reception'    => call_user_func([$helpers, 'old'], 'date_reception', $courrier['date_reception'] ?? ''),
    'date_envoi'        => call_user_func([$helpers, 'old'], 'date_envoi', $courrier['date_envoi'] ?? ''),
    'priorite'          => call_user_func([$helpers, 'old'], 'priorite', $courrier['priorite'] ?? 'NORMALE'),
    'confidentialite'   => call_user_func([$helpers, 'old'], 'confidentialite', $courrier['confidentialite'] ?? 'INTERNE'),
    'service_source_id' => call_user_func([$helpers, 'old'], 'service_source_id', $courrier['service_source_id'] ?? ''),
    'service_cible_id'  => call_user_func([$helpers, 'old'], 'service_cible_id', $courrier['service_cible_id'] ?? ''),
    'statut'            => call_user_func([$helpers, 'old'], 'statut', $courrier['statut'] ?? 'ENREGISTRE'),
    'echeance'          => call_user_func([$helpers, 'old'], 'echeance', $courrier['echeance'] ?? ''),
];
$attachments = $attachments ?? [];
$formatSize = static function ($bytes): string {
    $bytes = (int) $bytes;
    if ($bytes < 1024) {
        return $bytes . ' o';
    }

    $units = ['Ko', 'Mo', 'Go'];
    $index = 0;
    $size = $bytes / 1024;

    while ($size >= 1024 && $index < count($units) - 1) {
        $size /= 1024;
        $index++;
    }

    return number_format($size, 1, ',', ' ') . ' ' . $units[$index];
};
?>
<section class="page-header">
    <h2><?= $isEdit ? 'Modifier le courrier' : 'Enregistrer un courrier' ?></h2>
    <p>Les champs marques d'une etoile sont obligatoires.</p>
</section>

<?php if (!empty($errors['base'])): ?>
    <div class="alert alert-error">
        <?= $helpers::sanitize($errors['base']) ?>
    </div>
<?php endif; ?>

<form method="post" action="<?= $actionUrl ?>" class="form-card" enctype="multipart/form-data">
    <input type="hidden" name="_token" value="<?= $helpers::csrfToken() ?>">
    <?php if ($isEdit): ?>
        <input type="hidden" name="id" value="<?= (int) $courrier['id'] ?>">
    <?php endif; ?>

    <div class="form-grid">
        <div class="form-field">
            <label for="type">Type *</label>
            <select name="type" id="type" required>
                <?php foreach ($types as $type): ?>
                    <option value="<?= $helpers::sanitize($type) ?>" <?= $values['type'] === $type ? 'selected' : '' ?>><?= $helpers::sanitize($type) ?></option>
                <?php endforeach; ?>
            </select>
            <?php if (!empty($errors['type'])): ?>
                <p class="form-error"><?= $helpers::sanitize($errors['type']) ?></p>
            <?php endif; ?>
        </div>

        <div class="form-field">
            <label for="ref">Reference *</label>
            <input type="text" name="ref" id="ref" value="<?= $helpers::sanitize((string) $values['ref']) ?>" maxlength="50" required>
            <?php if (!empty($errors['ref'])): ?>
                <p class="form-error"><?= $helpers::sanitize($errors['ref']) ?></p>
            <?php endif; ?>
        </div>

        <div class="form-field wide">
            <label for="objet">Objet *</label>
            <input type="text" name="objet" id="objet" value="<?= $helpers::sanitize((string) $values['objet']) ?>" maxlength="255" required>
            <?php if (!empty($errors['objet'])): ?>
                <p class="form-error"><?= $helpers::sanitize($errors['objet']) ?></p>
            <?php endif; ?>
        </div>

        <div class="form-field">
            <label for="expediteur">Expediteur</label>
            <input type="text" name="expediteur" id="expediteur" value="<?= $helpers::sanitize((string) $values['expediteur']) ?>" maxlength="180">
        </div>

        <div class="form-field">
            <label for="destinataire">Destinataire</label>
            <input type="text" name="destinataire" id="destinataire" value="<?= $helpers::sanitize((string) $values['destinataire']) ?>" maxlength="180">
        </div>

        <div class="form-field" data-field="date_reception">
            <label for="date_reception">Date reception *</label>
            <input type="date" name="date_reception" id="date_reception" value="<?= $helpers::sanitize((string) $values['date_reception']) ?>">
            <?php if (!empty($errors['date_reception'])): ?>
                <p class="form-error"><?= $helpers::sanitize($errors['date_reception']) ?></p>
            <?php endif; ?>
        </div>

        <div class="form-field" data-field="date_envoi">
            <label for="date_envoi">Date envoi *</label>
            <input type="date" name="date_envoi" id="date_envoi" value="<?= $helpers::sanitize((string) $values['date_envoi']) ?>">
            <?php if (!empty($errors['date_envoi'])): ?>
                <p class="form-error"><?= $helpers::sanitize($errors['date_envoi']) ?></p>
            <?php endif; ?>
        </div>

        <div class="form-field">
            <label for="priorite">Priorite *</label>
            <select name="priorite" id="priorite" required>
                <?php foreach ($priorites as $priorite): ?>
                    <option value="<?= $helpers::sanitize($priorite) ?>" <?= $values['priorite'] === $priorite ? 'selected' : '' ?>><?= $helpers::sanitize($priorite) ?></option>
                <?php endforeach; ?>
            </select>
            <?php if (!empty($errors['priorite'])): ?>
                <p class="form-error"><?= $helpers::sanitize($errors['priorite']) ?></p>
            <?php endif; ?>
        </div>

        <div class="form-field">
            <label for="confidentialite">Confidentialite *</label>
            <select name="confidentialite" id="confidentialite" required>
                <?php foreach ($confidentialites as $confidentialite): ?>
                    <option value="<?= $helpers::sanitize($confidentialite) ?>" <?= $values['confidentialite'] === $confidentialite ? 'selected' : '' ?>><?= $helpers::sanitize($confidentialite) ?></option>
                <?php endforeach; ?>
            </select>
            <?php if (!empty($errors['confidentialite'])): ?>
                <p class="form-error"><?= $helpers::sanitize($errors['confidentialite']) ?></p>
            <?php endif; ?>
        </div>

        <div class="form-field">
            <label for="service_source_id">Service source</label>
            <select name="service_source_id" id="service_source_id">
                <option value="">Non defini</option>
                <?php foreach ($services as $service): ?>
                    <option value="<?= (int) $service['id'] ?>" <?= (string) $values['service_source_id'] === (string) $service['id'] ? 'selected' : '' ?>><?= $helpers::sanitize($service['libelle'] ?? '') ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-field">
            <label for="service_cible_id">Service cible</label>
            <select name="service_cible_id" id="service_cible_id">
                <option value="">Non defini</option>
                <?php foreach ($services as $service): ?>
                    <option value="<?= (int) $service['id'] ?>" <?= (string) $values['service_cible_id'] === (string) $service['id'] ? 'selected' : '' ?>><?= $helpers::sanitize($service['libelle'] ?? '') ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-field">
            <label for="statut">Statut *</label>
            <select name="statut" id="statut" required>
                <?php foreach ($statuts as $statut): ?>
                    <option value="<?= $helpers::sanitize($statut) ?>" <?= $values['statut'] === $statut ? 'selected' : '' ?>><?= $helpers::sanitize($statut) ?></option>
                <?php endforeach; ?>
            </select>
            <?php if (!empty($errors['statut'])): ?>
                <p class="form-error"><?= $helpers::sanitize($errors['statut']) ?></p>
            <?php endif; ?>
        </div>

        <div class="form-field">
            <label for="echeance">Echeance</label>
            <input type="date" name="echeance" id="echeance" value="<?= $helpers::sanitize((string) $values['echeance']) ?>">
            <?php if (!empty($errors['echeance'])): ?>
                <p class="form-error"><?= $helpers::sanitize($errors['echeance']) ?></p>
            <?php endif; ?>
        </div>

        <div class="form-field wide">
            <label for="pieces_jointes">Pieces jointes</label>
            <input type="file" name="pieces_jointes[]" id="pieces_jointes" multiple accept="application/pdf,image/*,.doc,.docx,.xls,.xlsx,.odt,.ods,.zip,.rar,.txt">
            <p class="form-hint">Formats acceptes: PDF, images, bureautique. Taille maximale: 10 Mo par fichier.</p>
        </div>
    </div>

    <?php if (!empty($attachments)): ?>
        <section class="attachments-card" aria-labelledby="attachments-title">
            <div class="attachments-header">
                <h3 id="attachments-title">Pieces jointes existantes</h3>
                <p class="attachments-hint">Cochez les fichiers a supprimer lors de la validation.</p>
            </div>
            <ul class="attachments-list">
                <?php foreach ($attachments as $piece): ?>
                    <li class="attachments-item">
                        <div class="attachments-meta">
                            <a class="attachments-link" href="<?= $helpers::sanitize($helpers::route('courrier', 'download', ['id' => $piece['id']])) ?>">
                                <?= $helpers::sanitize($piece['nom_fichier'] ?? 'Document') ?>
                            </a>
                            <span class="attachments-size"><?= $helpers::sanitize($formatSize($piece['taille'] ?? 0)) ?></span>
                        </div>
                        <label class="attachments-remove">
                            <input type="checkbox" name="attachments_to_delete[]" value="<?= (int) $piece['id'] ?>">
                            Supprimer
                        </label>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>
    <?php endif; ?>

    <div class="form-actions">
        <button type="submit" class="button">Enregistrer</button>
        <a class="button button-secondary" href="<?= $helpers::route('courrier') ?>">Annuler</a>
    </div>
</form>
