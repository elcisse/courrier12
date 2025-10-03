<?php
$helpers = \Core\Helpers::class;
$serviceMap = [];
foreach ($services as $service) {
    if (!isset($service['id'])) {
        continue;
    }
    $serviceMap[(string) $service['id']] = $service['libelle'] ?? '';
}

$typeFilter = $filters['type'] ?? '';
$statutFilter = $filters['statut'] ?? '';
$serviceFilter = (string) ($filters['service_cible_id'] ?? '');
$searchFilter = trim((string) ($filters['search'] ?? ''));
$dateStartFilter = trim((string) ($filters['date_start'] ?? ''));
$dateEndFilter = trim((string) ($filters['date_end'] ?? ''));

$typeLabel = $typeFilter !== '' ? $helpers::sanitize($typeFilter) : 'Tous';
$statutLabel = $statutFilter !== '' ? $helpers::sanitize($statutFilter) : 'Tous';
$serviceLabel = $serviceFilter !== '' ? $helpers::sanitize($serviceMap[$serviceFilter] ?? 'Inconnu') : 'Tous';
$searchLabel = $searchFilter !== '' ? $helpers::sanitize($searchFilter) : 'Aucun';

$dateStartLabel = 'Aucune';
if ($dateStartFilter !== '') {
    $dateStart = \DateTime::createFromFormat('Y-m-d', $dateStartFilter);
    if ($dateStart instanceof \DateTime) {
        $dateStartLabel = $dateStart->format('d/m/Y');
    } else {
        $dateStartLabel = $dateStartFilter;
    }
}

$dateEndLabel = 'Aucune';
if ($dateEndFilter !== '') {
    $dateEnd = \DateTime::createFromFormat('Y-m-d', $dateEndFilter);
    if ($dateEnd instanceof \DateTime) {
        $dateEndLabel = $dateEnd->format('d/m/Y');
    } else {
        $dateEndLabel = $dateEndFilter;
    }
}
?>
<section class="print-header">
    <div>
        <h1>Etat des courriers</h1>
        <p class="print-generated">G&eacute;n&eacute;r&eacute; le <?= date('d/m/Y H:i') ?></p>
    </div>
    <div class="print-actions no-print">
        <button type="button" class="button button-secondary" onclick="window.print()">Imprimer</button>
        <a class="button button-light" href="<?= $helpers::route('courrier') ?>">Retour &agrave; la liste</a>
    </div>
</section>

<section class="print-summary">
    <h2>Filtres appliqu&eacute;s</h2>
    <ul class="print-summary__list">
        <li><strong>Type :</strong> <?= $typeLabel ?></li>
        <li><strong>Statut :</strong> <?= $statutLabel ?></li>
        <li><strong>Service cible :</strong> <?= $serviceLabel ?></li>
        <li><strong>Recherche :</strong> <?= $searchLabel ?></li>
        <li><strong>Date d&eacute;but :</strong> <?= $helpers::sanitize($dateStartLabel) ?></li>
        <li><strong>Date fin :</strong> <?= $helpers::sanitize($dateEndLabel) ?></li>
        <li><strong>Total courriers :</strong> <?= count($courriers) ?></li>
    </ul>
</section>

<table class="data-table print-table">
    <thead>
        <tr>
            <th>R&eacute;f&eacute;rence</th>
            <th>Objet</th>
            <th>Type</th>
            <th>Statut</th>
            <th>Service source</th>
            <th>Service cible</th>
            <th>Priorit&eacute;</th>
            <th>Ech&eacute;ance</th>
            <th>Cr&eacute;&eacute; le</th>
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
                    <td><?= $helpers::sanitize($courrier['service_source'] ?? 'Non d&eacute;fini') ?></td>
                    <td><?= $helpers::sanitize($courrier['service_cible'] ?? 'Non d&eacute;fini') ?></td>
                    <td><?= $helpers::sanitize($courrier['priorite'] ?? '') ?></td>
                    <td><?= $helpers::sanitize($courrier['echeance'] ?? '') ?></td>
                    <td><?= $helpers::sanitize(substr((string) ($courrier['created_at'] ?? ''), 0, 10)) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="9" class="text-center">Aucun courrier ne correspond aux filtres s&eacute;lectionn&eacute;s.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>
