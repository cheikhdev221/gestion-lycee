<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Repositories\RoleRepository;
use App\Services\AuthService;
use App\Services\UserService;

final class UserController extends Controller
{
    public function __construct(
        private readonly UserService $userService = new UserService(),
        private readonly AuthService $authService = new AuthService(),
    ) {
    }

    public function index(): void
    {
        $users = $this->userService->listUsers();
        $roleNames = [];
        foreach ((new RoleRepository())->findAll() as $role) {
            $roleNames[$role->id] = $role->libelle;
        }
        $this->render('users/index', [
            'users' => $users,
            'roleNames' => $roleNames,
            'currentUserId' => $this->authService->currentUser()?->id,
        ]);
    }

    public function edit(): void
    {
        $user = $this->userService->getUserById((int) ($_GET['id'] ?? 0));
        if ($user === null) {
            http_response_code(404);
            echo 'Utilisateur introuvable.';
            return;
        }

        $this->render('users/edit', [
            'user' => $user,
            'roles' => (new RoleRepository())->findAll(),
            'error' => null,
        ]);
    }

    public function update(): void
    {
        $userId = (int) ($_POST['id'] ?? 0);
        $currentUserId = $this->authService->currentUser()?->id ?? 0;
        $result = $this->userService->updateUser($userId, $_POST, $currentUserId);

        if (!$result['success']) {
            $user = $this->userService->getUserById($userId);
            if ($user === null) {
                http_response_code(404);
                echo 'Utilisateur introuvable.';
                return;
            }
            $this->render('users/edit', [
                'user' => $user,
                'roles' => (new RoleRepository())->findAll(),
                'error' => $result['message'],
            ]);
            return;
        }

        header('Location: /users');
    }

    public function toggleActive(): void
    {
        $userId = (int) ($_POST['id'] ?? 0);
        $active = ($_POST['actif'] ?? '') === '1';
        $currentUserId = $this->authService->currentUser()?->id ?? 0;
        $result = $this->userService->setUserActive($userId, $active, $currentUserId);

        if (!$result['success']) {
            http_response_code(400);
        }
        header('Location: /users');
    }

    public function create(): void
    {
        $this->render('users/create', [
            'error' => null,
            'success' => null,
        ]);
    }

    public function store(): void
    {
        $result = $this->userService->createUser($_POST);

        if (!$result['success']) {
            $this->render('users/create', [
                'error' => $result['message'],
                'success' => null,
            ]);
            return;
        }

        $this->render('users/create', [
            'error' => null,
            'success' => 'Utilisateur créé avec succès.',
        ]);
    }
}
