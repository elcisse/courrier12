<section class="page-header">
    <h2>Courriers</h2>
    <p>Liste des courriers entrants et sortants.</p>
    <a class="button" href="<?= $helpers::route('courrier', 'create') ?>">Ajouter un courrier</a>
</section>

<form method="get" action="index.php" class="filter-bar">
    <input type="hidden" name="controller" value="courrier">
    <input type="hidden" name="action" value="index">

    <div class="filter-field">
        <label for="filter-type">Type</label>
        <select name="type" id="filter-type">
            <option value="">Tous</option>
            <?php foreach ($types as $type): ?>
                <option value="<?= $helpers::sanitize($type) ?>" <?= ($filters['type'] ?? '') === $type ? 'selected' : '' ?>><?= $helpers::sanitize($type) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="filter-field">
        <label for="filter-statut">Statut</label>
        <select name="statut" id="filter-statut">
            <option value="">Tous</option>
            <?php foreach ($statuts as $statut): ?>
                <option value="<?= $helpers::sanitize($statut) ?>" <?= ($filters['statut'] ?? '') === $statut ? 'selected' : '' ?>><?= $helpers::sanitize($statut) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="filter-field">
        <label for="filter-service">Service cible</label>
        <select name="service_cible_id" id="filter-service">
            <option value="">Tous</option>
            <?php foreach ($services as $service): ?>
                <option value="<?= (int) $service['id'] ?>" <?= (string) ($filters['service_cible_id'] ?? '') === (string) $service['id'] ? 'selected' : '' ?>><?= $helpers::sanitize($service['libelle'] ?? '') ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="filter-field">
        <label for="filter-search">Recherche</label>
        <input type="text" name="search" id="filter-search" value="<?= $helpers::sanitize((string) ($filters['search'] ?? '')) ?>" placeholder="Objet ou reference">
    </div>

    <div class="filter-actions">
        <button type="submit" class="button button-secondary">Filtrer</button>
        <a class="button button-light" href="<?= $helpers::route('courrier') ?>">Reinitialiser</a>
    </div>
</form>

<div class="table-responsive">
    <table class="data-table">
        <thead>
            <tr>
                <th>Reference</th>
                <th>Objet</th>
                <th>Type</th>
                <th>Statut</th>
                <th>Service source</th>
                <th>Service cible</th>
                <th>Priorite</th>
                <th>Echeance</th>
                <th class="actions">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($courriers)): ?>
                <?php foreach ($courriers as $courrier): ?>
                    <tr>
                        <td><?= $helpers::sanitize($courrier['ref'] ?? '') ?></td>
                        <td><?= $helpers::sanitize($courrier['objet'] ?? '') ?></td>
                        <td><?= $helpers::sanitize($courrier['type'] ?? '') ?></td>
                        <td><?= $helpers::sanitize($courrier['statut'] ?? '') ?></td>
                        <td><?= $helpers::sanitize($courrier['service_source'] ?? 'Non defini') ?></td>
                        <td><?= $helpers::sanitize($courrier['service_cible'] ?? 'Non defini') ?></td>
                        <td><?= $helpers::sanitize($courrier['priorite'] ?? '') ?></td>
                        <td><?= $helpers::sanitize($courrier['echeance'] ?? '') ?></td>
                        <td class="actions">
                            <a class="button button-small" href="<?= $helpers::route('courrier', 'edit', ['id' => $courrier['id']]) ?>">Modifier</a>
                            <form method="post" action="<?= $helpers::route('courrier', 'delete') ?>" class="inline-form" onsubmit="return confirm('Supprimer ce courrier ?');">
                                <input type="hidden" name="_token" value="<?= $helpers::csrfToken() ?>">
                                <input type="hidden" name="id" value="<?= (int) ($courrier['id'] ?? 0) ?>">
                                <button type="submit" class="button button-danger button-small">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="9" class="text-center">Aucun courrier trouve.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>