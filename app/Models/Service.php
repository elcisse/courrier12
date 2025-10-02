<?php
declare(strict_types=1);

namespace App\Models;

use Core\DB;

class Service
{
    public static function all(): array
    {
        return DB::run('SELECT * FROM services ORDER BY libelle')->fetchAll();
    }

    public static function active(): array
    {
        return DB::run('SELECT * FROM services WHERE actif = 1 ORDER BY libelle')->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $service = DB::run('SELECT * FROM services WHERE id = :id', ['id' => $id])->fetch();

        return $service ?: null;
    }

    public static function create(array $data): int
    {
        DB::run(
            'INSERT INTO services (code, libelle, actif) VALUES (:code, :libelle, :actif)',
            [
                'code'    => $data['code'],
                'libelle' => $data['libelle'],
                'actif'   => (int) ($data['actif'] ?? 1),
            ]
        );

        return (int) DB::pdo()->lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        $result = DB::run(
            'UPDATE services SET code = :code, libelle = :libelle, actif = :actif WHERE id = :id',
            [
                'code'    => $data['code'],
                'libelle' => $data['libelle'],
                'actif'   => (int) ($data['actif'] ?? 0),
                'id'      => $id,
            ]
        );

        return $result->rowCount() > 0;
    }

    public static function delete(int $id): bool
    {
        $result = DB::run('DELETE FROM services WHERE id = :id', ['id' => $id]);

        return $result->rowCount() > 0;
    }
}