<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\User;
use Core\Helpers;

class AuthController extends BaseController
{
    protected function requiresAuthentication(): bool
    {
        return false;
    }

    public function login(): void
    {
        if ($this->user()) {
            $this->redirect('home');
            return;
        }

        $this->render('auth/login', [
            'title' => 'Connexion',
        ]);
    }

    public function authenticate(): void
    {
        $this->requirePost();
        $this->validateCsrfOrFail();

        $login = (string) $this->input('login');
        $password = (string) $this->input('password');
        $errors = [];

        if ($login === '') {
            $errors['login'] = 'Le login est obligatoire.';
        }

        if ($password === '') {
            $errors['password'] = 'Le mot de passe est obligatoire.';
        }

        if (!empty($errors)) {
            Helpers::storeOld(['login' => $login]);
            Helpers::errors($errors);
            $this->redirect('auth', 'login');
            return;
        }

        $user = User::findActiveByLogin($login);

        if (!$user || !password_verify($password, $user['mdp_hash'])) {
            Helpers::storeOld(['login' => $login]);
            Helpers::errors(['base' => 'Identifiants invalides.']);
            Helpers::flash('error', 'Login ou mot de passe incorrect.');
            $this->redirect('auth', 'login');
            return;
        }

        $_SESSION['auth_user'] = [
            'id'         => (int) $user['id'],
            'name'       => $user['prenom_nom'],
            'login'      => $user['login'],
            'role'       => $user['role'] ?? null,
            'service_id' => $user['service_id'] ?? null,
        ];

        Helpers::clearOld();
        Helpers::flash('success', 'Bienvenue ' . ($user['prenom_nom'] ?? $user['login']) . ' !');

        $this->redirect('home');
    }

    public function logout(): void
    {
        $this->requirePost();
        $this->validateCsrfOrFail();

        unset($_SESSION['auth_user']);
        Helpers::flash('success', 'Deconnexion reussie.');

        $this->redirect('auth', 'login');
    }
}

