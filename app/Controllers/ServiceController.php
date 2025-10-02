<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\Service;
use Core\Helpers;
use PDOException;

class ServiceController extends BaseController
{
    public function index(): void
    {
        $services = Service::all();

        $this->render('services/index', [
            'title'    => 'Services',
            'services' => $services,
        ]);
    }

    public function create(): void
    {
        $this->render('services/form', [
            'title'   => 'Nouveau service',
            'service' => null,
            'action'  => Helpers::route('service', 'store'),
        ]);
    }

    public function store(): void
    {
        $this->requirePost();
        $this->validateCsrfOrFail();

        $data = $this->servicePayload();
        $errors = $this->validateService($data);

        if (!empty($errors)) {
            Helpers::storeOld($_POST);
            Helpers::errors($errors);
            $this->redirect('service', 'create');
            return;
        }

        try {
            Service::create($data);
            Helpers::flash('success', 'Service cree avec succes.');
        } catch (PDOException $exception) {
            Helpers::storeOld($_POST);
            Helpers::errors(['base' => 'Impossible de creer le service. Verifie que le code et le libelle sont uniques.']);
            Helpers::flash('error', 'Erreur lors de la creation du service.');
        }

        $this->redirect('service', 'index');
    }

    public function edit(): void
    {
        $id = (int) $this->input('id');
        $service = Service::find($id);

        if (!$service) {
            Helpers::flash('error', 'Service introuvable.');
            $this->redirect('service', 'index');
            return;
        }

        $this->render('services/form', [
            'title'   => 'Modifier le service',
            'service' => $service,
            'action'  => Helpers::route('service', 'update'),
        ]);
    }

    public function update(): void
    {
        $this->requirePost();
        $this->validateCsrfOrFail();

        $id = (int) $this->input('id');
        $data = $this->servicePayload();
        $errors = $this->validateService($data, $id);

        if (!Service::find($id)) {
            Helpers::flash('error', 'Service introuvable.');
            $this->redirect('service', 'index');
            return;
        }

        if (!empty($errors)) {
            Helpers::storeOld($_POST);
            Helpers::errors($errors);
            $this->redirect('service', 'edit', ['id' => $id]);
            return;
        }

        try {
            Service::update($id, $data);
            Helpers::flash('success', 'Service mis a jour.');
        } catch (PDOException $exception) {
            Helpers::storeOld($_POST);
            Helpers::errors(['base' => 'Impossible de mettre a jour le service.']);
            Helpers::flash('error', 'Erreur lors de la mise a jour.');
            $this->redirect('service', 'edit', ['id' => $id]);
            return;
        }

        $this->redirect('service', 'index');
    }

    public function delete(): void
    {
        $this->requirePost();
        $this->validateCsrfOrFail();

        $id = (int) $this->input('id');

        try {
            if (Service::delete($id)) {
                Helpers::flash('success', 'Service supprime.');
            } else {
                Helpers::flash('error', 'Service introuvable ou deja supprime.');
            }
        } catch (PDOException $exception) {
            Helpers::flash('error', 'Impossible de supprimer ce service (utilise ailleurs).');
        }

        $this->redirect('service', 'index');
    }

    private function servicePayload(): array
    {
        return [
            'code'    => strtoupper((string) $this->input('code')),
            'libelle' => (string) $this->input('libelle'),
            'actif'   => $this->input('actif') ? 1 : 0,
        ];
    }

    private function validateService(array $data, ?int $id = null): array
    {
        $errors = [];

        if (empty($data['code'])) {
            $errors['code'] = 'Le code est obligatoire.';
        } elseif (strlen($data['code']) > 30) {
            $errors['code'] = 'Le code ne doit pas depasser 30 caracteres.';
        }

        if (empty($data['libelle'])) {
            $errors['libelle'] = 'Le libelle est obligatoire.';
        } elseif (strlen($data['libelle']) > 150) {
            $errors['libelle'] = 'Le libelle ne doit pas depasser 150 caracteres.';
        }

        return $errors;
    }
}