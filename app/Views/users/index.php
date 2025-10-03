<section class="page-header">
    <h2>Utilisateurs</h2>
    <p>Gestion des comptes applicatifs et de leurs droits.</p>
    <a class="button" href="<?= $helpers::route('user', 'create') ?>">Nouvel utilisateur</a>
</section>

<div class="table-responsive">
    <table class="data-table">
        <thead>
            <tr>
                <th>Nom</th>
                <th>Login</th>
                <th>Role</th>
                <th>Service</th>
                <th>Statut</th>
                <th class="actions">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($users)): ?>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= $helpers::sanitize($user['prenom_nom'] ?? '') ?></td>
                        <td><?= $helpers::sanitize($user['login'] ?? '') ?></td>
                        <td><?= $helpers::sanitize($user['role'] ?? '') ?></td>
                        <td><?= $helpers::sanitize($user['service_libelle'] ?? 'Non attribue') ?></td>
                        <td>
                            <span class="status <?= ((int) ($user['actif'] ?? 0) === 1) ? 'status-success' : 'status-muted' ?>">
                                <?= ((int) ($user['actif'] ?? 0) === 1) ? 'Actif' : 'Inactif' ?>
                            </span>
                        </td>
                        <td class="actions">
                            <a class="button button-small" href="<?= $helpers::route('user', 'edit', ['id' => $user['id']]) ?>">Modifier</a>
                            <form method="post" action="<?= $helpers::route('user', 'delete') ?>" class="inline-form" onsubmit="return confirm('Supprimer cet utilisateur ?');">
                                <input type="hidden" name="_token" value="<?= $helpers::csrfToken() ?>">
                                <input type="hidden" name="id" value="<?= (int) $user['id'] ?>">
                                <button type="submit" class="button button-danger button-small">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="text-center">Aucun utilisateur enregistre.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
