<section class="page-header">
    <h2>Tableau de bord</h2>
    <p>Suivi rapide des indicateurs clefs.</p>
</section>

<section class="stats-grid">
    <article class="stat-card">
        <span class="stat-label">Services actifs</span>
        <span class="stat-value"><?= (int) ($stats['services'] ?? 0) ?></span>
    </article>
    <article class="stat-card">
        <span class="stat-label">Courriers totaux</span>
        <span class="stat-value"><?= (int) ($stats['courriers'] ?? 0) ?></span>
    </article>
    <article class="stat-card">
        <span class="stat-label">Courriers du jour</span>
        <span class="stat-value"><?= (int) ($stats['courriers_du_jour'] ?? 0) ?></span>
    </article>
</section>

<section class="panel">
    <div class="panel-header">
        <h3>Courriers recents</h3>
        <a class="button" href="<?= $helpers::route('courrier', 'create') ?>">Nouveau courrier</a>
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Reference</th>
                    <th>Objet</th>
                    <th>Type</th>
                    <th>Statut</th>
                    <th>Service cible</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($recentCourriers)): ?>
                    <?php foreach ($recentCourriers as $row): ?>
                        <tr>
                            <td><?= $helpers::sanitize($row['ref'] ?? '') ?></td>
                            <td><?= $helpers::sanitize($row['objet'] ?? '') ?></td>
                            <td><?= $helpers::sanitize($row['type'] ?? '') ?></td>
                            <td><?= $helpers::sanitize($row['statut'] ?? '') ?></td>
                            <td><?= $helpers::sanitize($row['service_cible'] ?? 'Non defini') ?></td>
                            <td><?= $helpers::sanitize(substr((string) ($row['created_at'] ?? ''), 0, 10)) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center">Aucun courrier recemment enregistre.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>