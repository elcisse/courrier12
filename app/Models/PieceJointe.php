<?php
declare(strict_types=1);

namespace App\Models;

use Core\DB;

class PieceJointe
{
    public static function forCourrier(int $courrierId): array
    {
        return DB::run(
            'SELECT * FROM pieces_jointes WHERE courrier_id = :courrier_id ORDER BY uploaded_at DESC, id DESC',
            ['courrier_id' => $courrierId]
        )->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $piece = DB::run('SELECT * FROM pieces_jointes WHERE id = :id', ['id' => $id])->fetch();

        return $piece ?: null;
    }

    public static function create(int $courrierId, array $data): int
    {
        DB::run(
            'INSERT INTO pieces_jointes (courrier_id, nom_fichier, chemin, taille, mime) VALUES (:courrier_id, :nom_fichier, :chemin, :taille, :mime)',
            [
                'courrier_id' => $courrierId,
                'nom_fichier' => $data['nom_fichier'],
                'chemin'      => $data['chemin'],
                'taille'      => $data['taille'],
                'mime'        => $data['mime'],
            ]
        );

        return (int) DB::pdo()->lastInsertId();
    }

    public static function delete(int $id): bool
    {
        $statement = DB::run('DELETE FROM pieces_jointes WHERE id = :id', ['id' => $id]);

        return $statement->rowCount() > 0;
    }
}
