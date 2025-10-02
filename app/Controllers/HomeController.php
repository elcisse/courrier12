<?php
declare(strict_types=1);

namespace App\Controllers;

use Core\DB;

class HomeController extends BaseController
{
    public function index(): void
    {
        $stats = [
            'services'         => (int) (DB::run('SELECT COUNT(*) FROM services')->fetchColumn() ?? 0),
            'courriers'        => (int) (DB::run('SELECT COUNT(*) FROM courriers')->fetchColumn() ?? 0),
            'courriers_du_jour' => (int) (DB::run('SELECT COUNT(*) FROM courriers WHERE DATE(created_at) = CURRENT_DATE')->fetchColumn() ?? 0),
        ];

        $recentCourriers = DB::run(
            'SELECT c.id, c.ref, c.objet, c.type, c.statut, c.created_at, s.libelle AS service_cible
             FROM courriers c
             LEFT JOIN services s ON s.id = c.service_cible_id
             ORDER BY c.created_at DESC
             LIMIT 5'
        )->fetchAll();

        $this->render('home', [
            'title'            => 'Tableau de bord',
            'stats'            => $stats,
            'recentCourriers'  => $recentCourriers,
        ]);
    }
}