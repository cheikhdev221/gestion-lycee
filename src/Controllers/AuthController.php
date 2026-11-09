<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Services\AuthService;

final class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService = new AuthService(),
    ) {
    }

    public function showLogin(): void
    {
        if ($this->authService->isAuthenticated()) {
            header('Location: ' . $this->homeForRole());
            exit;
        }

        $this->render('login', [
            'error' => null,
            'success' => ($_GET['inscription'] ?? '') === 'success' ? 'Compte élève créé. Tu peux maintenant te connecter.' : null,
            'email' => '',
        ]);
    }

    public function login(): void
    {
        $email = strtolower(trim((string) ($_POST['email'] ?? '')));
        $password = (string) ($_POST['password'] ?? '');

        $result = $this->authService->login($email, $password);

        if (!$result['success']) {
            $this->render('login', [
                'error' => $result['message'],
                'success' => null,
                'email' => $email,
            ]);
            return;
        }

        header('Location: ' . $this->homeForRole());
        exit;
    }

    private function homeForRole(): string
    {
        return match ($this->authService->currentRoleCode()) {
            'ADMIN' => '/admin',
            'ELEVE' => '/mon-espace',
            default => '/',
        };
    }

    public function logout(): void
    {
        $this->authService->logout();
        header('Location: /login');
        exit;
    }
}
