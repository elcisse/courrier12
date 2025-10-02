<?php
declare(strict_types=1);

namespace App\Models;

use Core\DB;

class Courrier
{
    public const TYPES = ['ENTRANT', 'SORTANT'];
    public const PRIORITES = ['BASSE', 'NORMALE', 'HAUTE', 'URGENTE'];
    public const CONFIDENTIALITES = ['PUBLIQUE', 'INTERNE', 'CONFIDENTIEL'];
    public const STATUTS = ['ENREGISTRE', 'AFFECTE', 'EN_COURS', 'REPONDU', 'CLOS', 'ARCHIVE'];

    public static function all(array $filters = []): array
    {
        $sql = 'SELECT c.*,\n               s1.libelle AS service_source,\n               s2.libelle AS service_cible,\n               (SELECT COUNT(*) FROM pieces_jointes pj WHERE pj.courrier_id = c.id) AS attachments_count
                FROM courriers c
                LEFT JOIN services s1 ON s1.id = c.service_source_id
                LEFT JOIN services s2 ON s2.id = c.service_cible_id
                WHERE 1=1';
        $params = [];

        if (!empty($filters['type'])) {
            $sql .= ' AND c.type = :type';
            $params['type'] = $filters['type'];
        }

        if (!empty($filters['statut'])) {
            $sql .= ' AND c.statut = :statut';
            $params['statut'] = $filters['statut'];
        }

        if (!empty($filters['service_cible_id'])) {
            $sql .= ' AND c.service_cible_id = :service_cible_id';
            $params['service_cible_id'] = (int) $filters['service_cible_id'];
        }

        if (!empty($filters['search'])) {
            $sql .= ' AND (c.objet LIKE :search OR c.ref LIKE :search OR c.expediteur LIKE :search OR c.destinataire LIKE :search)';
            $params['search'] = '%' . $filters['search'] . '%';
        }

        $sql .= ' ORDER BY c.created_at DESC';

        return DB::run($sql, $params)->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $sql = 'SELECT c.*,\n               s1.libelle AS service_source,\n               s2.libelle AS service_cible,\n               (SELECT COUNT(*) FROM pieces_jointes pj WHERE pj.courrier_id = c.id) AS attachments_count
                FROM courriers c
                LEFT JOIN services s1 ON s1.id = c.service_source_id
                LEFT JOIN services s2 ON s2.id = c.service_cible_id
                WHERE c.id = :id';

        $courrier = DB::run($sql, ['id' => $id])->fetch();

        return $courrier ?: null;
    }

    public static function create(array $data): int
    {
        DB::run(
            'INSERT INTO courriers (type, ref, objet, expediteur, destinataire, date_reception, date_envoi, priorite, confidentialite, service_source_id, service_cible_id, statut, echeance, created_by)
             VALUES (:type, :ref, :objet, :expediteur, :destinataire, :date_reception, :date_envoi, :priorite, :confidentialite, :service_source_id, :service_cible_id, :statut, :echeance, :created_by)',
            self::prepareParams($data)
        );

        return (int) DB::pdo()->lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        $params = self::prepareParams($data);
        $params['id'] = $id;

        $statement = DB::run(
            'UPDATE courriers
             SET type = :type,
                 ref = :ref,
                 objet = :objet,
                 expediteur = :expediteur,
                 destinataire = :destinataire,
                 date_reception = :date_reception,
                 date_envoi = :date_envoi,
                 priorite = :priorite,
                 confidentialite = :confidentialite,
                 service_source_id = :service_source_id,
                 service_cible_id = :service_cible_id,
                 statut = :statut,
                 echeance = :echeance,
                 created_by = :created_by
             WHERE id = :id',
            $params
        );

        return $statement->rowCount() > 0;
    }

    public static function delete(int $id): bool
    {
        $statement = DB::run('DELETE FROM courriers WHERE id = :id', ['id' => $id]);

        return $statement->rowCount() > 0;
    }

    private static function prepareParams(array $data): array
    {
        $dateReception = $data['date_reception'] ?? null;
        $dateEnvoi = $data['date_envoi'] ?? null;

        if (($data['type'] ?? '') === 'ENTRANT') {
            $dateEnvoi = null;
        } elseif (($data['type'] ?? '') === 'SORTANT') {
            $dateReception = null;
        }

        return [
            'type'              => $data['type'],
            'ref'               => $data['ref'],
            'objet'             => $data['objet'],
            'expediteur'        => $data['expediteur'] ?? null,
            'destinataire'      => $data['destinataire'] ?? null,
            'date_reception'    => $dateReception ?: null,
            'date_envoi'        => $dateEnvoi ?: null,
            'priorite'          => $data['priorite'],
            'confidentialite'   => $data['confidentialite'],
            'service_source_id' => $data['service_source_id'] ? (int) $data['service_source_id'] : null,
            'service_cible_id'  => $data['service_cible_id'] ? (int) $data['service_cible_id'] : null,
            'statut'            => $data['statut'],
            'echeance'          => $data['echeance'] ?: null,
            'created_by'        => $data['created_by'],
        ];
    }
}

