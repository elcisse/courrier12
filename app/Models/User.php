<?php
declare(strict_types=1);

namespace App\Models;

use Core\DB;

class User
{
    public const ROLES = ['ADMIN', 'SECRETAIRE', 'CHEF_SERVICE', 'AGENT', 'LECTEUR'];

    public static function all(): array
    {
        return DB::run(
            'SELECT u.*, s.libelle AS service_libelle
             FROM utilisateurs u
             LEFT JOIN services s ON s.id = u.service_id
             ORDER BY u.prenom_nom ASC, u.id ASC'
        )->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $user = DB::run('SELECT * FROM utilisateurs WHERE id = :id LIMIT 1', ['id' => $id])->fetch();

        return $user ?: null;
    }

    public static function findActiveByLogin(string $login): ?array
    {
        $user = DB::run(
            'SELECT * FROM utilisateurs WHERE login = :login AND actif = 1 LIMIT 1',
            ['login' => $login]
        )->fetch();

        return $user ?: null;
    }

    public static function findByLogin(string $login): ?array
    {
        $user = DB::run('SELECT * FROM utilisateurs WHERE login = :login LIMIT 1', ['login' => $login])->fetch();

        return $user ?: null;
    }

    public static function create(array $data): int
    {
        DB::run(
            'INSERT INTO utilisateurs (prenom_nom, login, mdp_hash, role, actif, service_id)
             VALUES (:prenom_nom, :login, :mdp_hash, :role, :actif, :service_id)',
            [
                'prenom_nom' => $data['prenom_nom'],
                'login'      => $data['login'],
                'mdp_hash'   => $data['mdp_hash'],
                'role'       => $data['role'],
                'actif'      => (int) $data['actif'],
                'service_id' => $data['service_id'] ?? null,
            ]
        );

        return (int) DB::pdo()->lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        $fields = [
            'prenom_nom = :prenom_nom',
            'login = :login',
            'role = :role',
            'actif = :actif',
            'service_id = :service_id',
        ];

        $params = [
            'id'         => $id,
            'prenom_nom' => $data['prenom_nom'],
            'login'      => $data['login'],
            'role'       => $data['role'],
            'actif'      => (int) $data['actif'],
            'service_id' => $data['service_id'] ?? null,
        ];

        if (!empty($data['mdp_hash'])) {
            $fields[] = 'mdp_hash = :mdp_hash';
            $params['mdp_hash'] = $data['mdp_hash'];
        }

        $sql = 'UPDATE utilisateurs SET ' . implode(', ', $fields) . ' WHERE id = :id';
        $statement = DB::run($sql, $params);

        return $statement->rowCount() > 0;
    }

    public static function delete(int $id): bool
    {
        $statement = DB::run('DELETE FROM utilisateurs WHERE id = :id', ['id' => $id]);

        return $statement->rowCount() > 0;
    }
}
