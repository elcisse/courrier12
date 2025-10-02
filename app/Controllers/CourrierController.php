<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\Courrier;
use App\Models\Service;
use Core\Helpers;
use PDOException;

class CourrierController extends BaseController
{
    public function index(): void
    {
        $filters = [
            'type'             => $this->input('type'),
            'statut'           => $this->input('statut'),
            'service_cible_id' => $this->input('service_cible_id'),
            'search'           => $this->input('search'),
        ];

        $courriers = Courrier::all($filters);
        $services = Service::all();

        $this->render('courriers/index', [
            'title'     => 'Courriers',
            'courriers' => $courriers,
            'services'  => $services,
            'filters'   => $filters,
            'types'     => Courrier::TYPES,
            'statuts'   => Courrier::STATUTS,
        ]);
    }

    public function create(): void
    {
        $services = Service::active();

        $this->render('courriers/form', [
            'title'            => 'Enregistrer un courrier',
            'services'         => $services,
            'courrier'         => null,
            'types'            => Courrier::TYPES,
            'priorites'        => Courrier::PRIORITES,
            'confidentialites' => Courrier::CONFIDENTIALITES,
            'statuts'          => Courrier::STATUTS,
            'action'           => Helpers::route('courrier', 'store'),
        ]);
    }

    public function store(): void
    {
        $this->requirePost();
        $this->validateCsrfOrFail();

        $data = $this->courrierPayload();
        $errors = $this->validateCourrier($data);

        if (!empty($errors)) {
            Helpers::storeOld($_POST);
            Helpers::errors($errors);
            $this->redirect('courrier', 'create');
            return;
        }

        try {
            Courrier::create($data);
            Helpers::flash('success', 'Courrier enregistre avec succes.');
        } catch (PDOException $exception) {
            Helpers::storeOld($_POST);
            Helpers::errors(['base' => 'Reference deja utilisee ou donnees invalides.']);
            Helpers::flash('error', 'Erreur lors de la creation du courrier.');
            $this->redirect('courrier', 'create');
            return;
        }

        $this->redirect('courrier', 'index');
    }

    public function edit(): void
    {
        $id = (int) $this->input('id');
        $courrier = Courrier::find($id);

        if (!$courrier) {
            Helpers::flash('error', 'Courrier introuvable.');
            $this->redirect('courrier', 'index');
            return;
        }

        $services = Service::all();

        $this->render('courriers/form', [
            'title'            => 'Modifier le courrier',
            'courrier'         => $courrier,
            'services'         => $services,
            'types'            => Courrier::TYPES,
            'priorites'        => Courrier::PRIORITES,
            'confidentialites' => Courrier::CONFIDENTIALITES,
            'statuts'          => Courrier::STATUTS,
            'action'           => Helpers::route('courrier', 'update'),
        ]);
    }

    public function update(): void
    {
        $this->requirePost();
        $this->validateCsrfOrFail();

        $id = (int) $this->input('id');
        $courrier = Courrier::find($id);

        if (!$courrier) {
            Helpers::flash('error', 'Courrier introuvable.');
            $this->redirect('courrier', 'index');
            return;
        }

        $data = $this->courrierPayload();
        $errors = $this->validateCourrier($data, true);

        if (!empty($errors)) {
            Helpers::storeOld($_POST);
            Helpers::errors($errors);
            $this->redirect('courrier', 'edit', ['id' => $id]);
            return;
        }

        try {
            Courrier::update($id, $data);
            Helpers::flash('success', 'Courrier mis a jour.');
        } catch (PDOException $exception) {
            Helpers::storeOld($_POST);
            Helpers::errors(['base' => 'Impossible de mettre a jour ce courrier.']);
            Helpers::flash('error', 'Erreur lors de la mise a jour.');
            $this->redirect('courrier', 'edit', ['id' => $id]);
            return;
        }

        $this->redirect('courrier', 'index');
    }

    public function delete(): void
    {
        $this->requirePost();
        $this->validateCsrfOrFail();

        $id = (int) $this->input('id');

        try {
            if (Courrier::delete($id)) {
                Helpers::flash('success', 'Courrier supprime.');
            } else {
                Helpers::flash('error', 'Courrier introuvable.');
            }
        } catch (PDOException $exception) {
            Helpers::flash('error', 'Suppression impossible: des elements lies existent.');
        }

        $this->redirect('courrier', 'index');
    }

    private function courrierPayload(): array
    {
        return [
            'type'              => (string) $this->input('type'),
            'ref'               => (string) $this->input('ref'),
            'objet'             => (string) $this->input('objet'),
            'expediteur'        => $this->input('expediteur') ?: null,
            'destinataire'      => $this->input('destinataire') ?: null,
            'date_reception'    => $this->input('date_reception') ?: null,
            'date_envoi'        => $this->input('date_envoi') ?: null,
            'priorite'          => (string) $this->input('priorite', 'NORMALE'),
            'confidentialite'   => (string) $this->input('confidentialite', 'INTERNE'),
            'service_source_id' => $this->input('service_source_id') ? (int) $this->input('service_source_id') : null,
            'service_cible_id'  => $this->input('service_cible_id') ? (int) $this->input('service_cible_id') : null,
            'statut'            => (string) $this->input('statut', 'ENREGISTRE'),
            'echeance'          => $this->input('echeance') ?: null,
            'created_by'        => $this->input('created_by') ? (int) $this->input('created_by') : 1,
        ];
    }

    private function validateCourrier(array $data, bool $isUpdate = false): array
    {
        $errors = [];

        if (!in_array($data['type'], Courrier::TYPES, true)) {
            $errors['type'] = 'Type invalide.';
        }

        if (empty($data['ref'])) {
            $errors['ref'] = 'La reference est obligatoire.';
        } elseif (strlen($data['ref']) > 50) {
            $errors['ref'] = 'La reference ne doit pas depasser 50 caracteres.';
        }

        if (empty($data['objet'])) {
            $errors['objet'] = 'L\'objet est obligatoire.';
        }

        if (!in_array($data['priorite'], Courrier::PRIORITES, true)) {
            $errors['priorite'] = 'Priorite invalide.';
        }

        if (!in_array($data['confidentialite'], Courrier::CONFIDENTIALITES, true)) {
            $errors['confidentialite'] = 'Confidentialite invalide.';
        }

        if (!in_array($data['statut'], Courrier::STATUTS, true)) {
            $errors['statut'] = 'Statut invalide.';
        }

        if ($data['type'] === 'ENTRANT' && empty($data['date_reception'])) {
            $errors['date_reception'] = 'La date de reception est obligatoire pour un courrier entrant.';
        }

        if ($data['type'] === 'SORTANT' && empty($data['date_envoi'])) {
            $errors['date_envoi'] = 'La date d\'envoi est obligatoire pour un courrier sortant.';
        }

        if (!empty($data['echeance']) && !preg_match('/^\\d{4}-\\d{2}-\\d{2}$/', (string) $data['echeance'])) {
            $errors['echeance'] = 'Format de date d\'echeance invalide (AAAA-MM-JJ).';
        }

        if (!empty($data['date_reception']) && !preg_match('/^\\d{4}-\\d{2}-\\d{2}$/', (string) $data['date_reception'])) {
            $errors['date_reception'] = 'Format de date de reception invalide (AAAA-MM-JJ).';
        }

        if (!empty($data['date_envoi']) && !preg_match('/^\\d{4}-\\d{2}-\\d{2}$/', (string) $data['date_envoi'])) {
            $errors['date_envoi'] = 'Format de date d\'envoi invalide (AAAA-MM-JJ).';
        }

        if (empty($data['created_by']) || !is_int($data['created_by']) || $data['created_by'] < 1) {
            $errors['created_by'] = 'Utilisateur createur invalide.';
        }

        return $errors;
    }
}