<section class="page-header">
    <h2>Services</h2>
    <p>Gestion des services et directions.</p>
    <a class="button" href="<?= $helpers::route('service', 'create') ?>">Ajouter un service</a>
</section>

<div class="table-responsive">
    <table class="data-table">
        <thead>
            <tr>
                <th>Code</th>
                <th>Libelle</th>
                <th>Actif</th>
                <th class="actions">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($services)): ?>
                <?php foreach ($services as $service): ?>
                    <tr>
                        <td><?= $helpers::sanitize($service['code'] ?? '') ?></td>
                        <td><?= $helpers::sanitize($service['libelle'] ?? '') ?></td>
                        <td>
                            <span class="status <?= !empty($service['actif']) ? 'status-success' : 'status-muted' ?>">
                                <?= !empty($service['actif']) ? 'Oui' : 'Non' ?>
                            </span>
                        </td>
                        <td class="actions">
                            <a class="button button-small" href="<?= $helpers::route('service', 'edit', ['id' => $service['id']]) ?>">Modifier</a>
                            <form method="post" action="<?= $helpers::route('service', 'delete') ?>" class="inline-form" onsubmit="return confirm('Supprimer ce service ?');">
                                <input type="hidden" name="_token" value="<?= $helpers::csrfToken() ?>">
                                <input type="hidden" name="id" value="<?= (int) ($service['id'] ?? 0) ?>">
                                <button type="submit" class="button button-danger button-small">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" class="text-center">Aucun service enregistre.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
