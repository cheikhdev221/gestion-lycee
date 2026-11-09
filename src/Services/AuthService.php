<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Session;
use App\Models\User;
use App\Core\CsrfToken;
use App\Repositories\RoleRepository;
use App\Repositories\UserRepository;

final class AuthService
{
    public function __construct(
        private readonly UserRepository $userRepository = new UserRepository(),
        private readonly RoleRepository $roleRepository = new RoleRepository(),
    ) {
    }

    public function login(string $email, string $password): array
    {
        $normalizedEmail = strtolower(trim($email));

        if ($normalizedEmail === '' || $password === '') {
            return [
                'success' => false,
                'message' => 'Email et mot de passe sont obligatoires.',
            ];
        }

        $user = $this->userRepository->findByEmail($normalizedEmail);

        if ($user === null || !password_verify($password, $user->passwordHash)) {
            return [
                'success' => false,
                'message' => 'Email ou mot de passe incorrect.',
            ];
        }

        if (!$user->actif) {
            return [
                'success' => false,
                'message' => 'Ce compte est désactivé.',
            ];
        }

        Session::regenerate();
        Session::set('auth_user', $user->toArray());
        CsrfToken::rotate();

        return [
            'success' => true,
            'user' => $user,
        ];
    }

    public function logout(): void
    {
        Session::destroy();
    }

    public function isAuthenticated(): bool
    {
        return $this->currentUser() !== null;
    }

    public function currentUser(): ?User
    {
        $payload = Session::get('auth_user');

        if (!is_array($payload)) {
            return null;
        }

        return User::fromArray($payload);
    }

    public function currentRoleCode(): ?string
    {
        $user = $this->currentUser();
        if ($user === null) {
            return null;
        }

        return $this->roleRepository->findById($user->roleId)?->code;
    }
}
