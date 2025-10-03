<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\Service;
use App\Models\User;
use Core\Helpers;
use PDOException;

class UserController extends BaseController
{
    public function index(): void
    {
        $users = User::all();

        $this->render('users/index', [
            'title' => 'Utilisateurs',
            'users' => $users,
            'roles' => User::ROLES,
        ]);
    }

    public function create(): void
    {
        $this->render('users/form', [
            'title'    => 'Nouvel utilisateur',
            'user'     => null,
            'services' => Service::all(),
            'roles'    => User::ROLES,
            'action'   => Helpers::route('user', 'store'),
        ]);
    }

    public function store(): void
    {
        $this->requirePost();
        $this->validateCsrfOrFail();

        $data = $this->userPayload();
        $errors = $this->validateUser($data);

        if (!empty($errors)) {
            Helpers::storeOld($_POST);
            Helpers::errors($errors);
            $this->redirect('user', 'create');
            return;
        }

        $payload = $this->persistenceData($data);

        try {
            User::create($payload);
            Helpers::flash('success', 'Utilisateur cree avec succes.');
        } catch (PDOException $exception) {
            Helpers::storeOld($_POST);
            Helpers::errors(['base' => 'Impossible de creer cet utilisateur (login peut-etre deja utilise).']);
            Helpers::flash('error', 'Erreur lors de la creation de l\'utilisateur.');
            $this->redirect('user', 'create');
            return;
        }

        $this->redirect('user', 'index');
    }

    public function edit(): void
    {
        $id = (int) $this->input('id');
        $user = User::find($id);

        if (!$user) {
            Helpers::flash('error', 'Utilisateur introuvable.');
            $this->redirect('user', 'index');
            return;
        }

        $this->render('users/form', [
            'title'    => 'Modifier l\'utilisateur',
            'user'     => $user,
            'services' => Service::all(),
            'roles'    => User::ROLES,
            'action'   => Helpers::route('user', 'update'),
        ]);
    }

    public function update(): void
    {
        $this->requirePost();
        $this->validateCsrfOrFail();

        $id = (int) $this->input('id');
        $user = User::find($id);

        if (!$user) {
            Helpers::flash('error', 'Utilisateur introuvable.');
            $this->redirect('user', 'index');
            return;
        }

        $data = $this->userPayload();
        $errors = $this->validateUser($data, true, $id);

        if (!empty($errors)) {
            Helpers::storeOld($_POST);
            Helpers::errors($errors);
            $this->redirect('user', 'edit', ['id' => $id]);
            return;
        }

        $payload = $this->persistenceData($data, true);

        try {
            User::update($id, $payload);
            Helpers::flash('success', 'Utilisateur mis a jour.');
        } catch (PDOException $exception) {
            Helpers::storeOld($_POST);
            Helpers::errors(['base' => 'Impossible de mettre a jour cet utilisateur.']);
            Helpers::flash('error', 'Erreur lors de la mise a jour.');
            $this->redirect('user', 'edit', ['id' => $id]);
            return;
        }

        $this->redirect('user', 'index');
    }

    public function delete(): void
    {
        $this->requirePost();
        $this->validateCsrfOrFail();

        $id = (int) $this->input('id');

        if ($this->userId() === $id) {
            Helpers::flash('error', 'Vous ne pouvez pas supprimer votre propre compte.');
            $this->redirect('user', 'index');
            return;
        }

        try {
            if (User::delete($id)) {
                Helpers::flash('success', 'Utilisateur supprime.');
            } else {
                Helpers::flash('error', 'Utilisateur introuvable.');
            }
        } catch (PDOException $exception) {
            Helpers::flash('error', 'Suppression impossible: dependances existantes.');
        }

        $this->redirect('user', 'index');
    }

    private function userPayload(): array
    {
        $serviceInput = $this->input('service_id');
        $actifInput = $this->input('actif');

        return [
            'prenom_nom'            => (string) $this->input('prenom_nom'),
            'login'                 => (string) $this->input('login'),
            'password'              => (string) $this->input('password'),
            'password_confirmation' => (string) $this->input('password_confirmation'),
            'role'                  => (string) $this->input('role'),
            'service_id'            => ($serviceInput !== null && $serviceInput !== '') ? (int) $serviceInput : null,
            'actif'                 => $actifInput ? 1 : 0,
        ];
    }

    private function validateUser(array $data, bool $isUpdate = false, ?int $userId = null): array
    {
        $errors = [];

        if ($data['prenom_nom'] === '') {
            $errors['prenom_nom'] = 'Le nom est obligatoire.';
        } elseif (mb_strlen($data['prenom_nom']) > 150) {
            $errors['prenom_nom'] = 'Le nom ne doit pas depasser 150 caracteres.';
        }

        if ($data['login'] === '') {
            $errors['login'] = 'Le login est obligatoire.';
        } elseif (mb_strlen($data['login']) > 80) {
            $errors['login'] = 'Le login ne doit pas depasser 80 caracteres.';
        } else {
            $existing = User::findByLogin($data['login']);
            if ($existing && (int) $existing['id'] !== (int) ($userId ?? 0)) {
                $errors['login'] = 'Ce login est deja utilise.';
            }
        }

        if (!$isUpdate || $data['password'] !== '') {
            if ($data['password'] === '') {
                $errors['password'] = 'Le mot de passe est obligatoire.';
            } elseif (strlen($data['password']) < 6) {
                $errors['password'] = 'Le mot de passe doit contenir au moins 6 caracteres.';
            }

            if (($data['password'] ?? '') !== ($data['password_confirmation'] ?? '')) {
                $errors['password_confirmation'] = 'La confirmation ne correspond pas.';
            }
        }

        if (!in_array($data['role'], User::ROLES, true)) {
            $errors['role'] = 'Role invalide.';
        }

        if ($data['service_id'] !== null) {
            if ($data['service_id'] < 1 || !Service::find($data['service_id'])) {
                $errors['service_id'] = 'Service invalide.';
            }
        }

        return $errors;
    }

    private function persistenceData(array $data, bool $isUpdate = false): array
    {
        $payload = [
            'prenom_nom' => $data['prenom_nom'],
            'login'      => $data['login'],
            'role'       => $data['role'],
            'actif'      => $data['actif'] ? 1 : 0,
            'service_id' => $data['service_id'],
        ];

        if (!$isUpdate || $data['password'] !== '') {
            $payload['mdp_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
        } else {
            $payload['mdp_hash'] = null;
        }

        return $payload;
    }
}





