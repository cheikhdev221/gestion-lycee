<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Repositories\RoleRepository;
use App\Repositories\UserRepository;

final class UserService
{
    public function __construct(
        private readonly UserRepository $userRepository = new UserRepository(),
        private readonly RoleRepository $roleRepository = new RoleRepository(),
    ) {
    }

    public function createUser(array $data): array
    {
        $nom = trim((string) ($data['nom'] ?? ''));
        $prenom = trim((string) ($data['prenom'] ?? ''));
        $email = strtolower(trim((string) ($data['email'] ?? '')));
        $password = (string) ($data['password'] ?? '');
        $roleCode = strtoupper(trim((string) ($data['role_code'] ?? '')));

        if ($nom === '' || $prenom === '' || $email === '' || $password === '') {
            return ['success' => false, 'message' => 'Tous les champs obligatoires doivent être remplis.'];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Adresse email invalide.'];
        }

        if (strlen($password) < 8) {
            return ['success' => false, 'message' => 'Le mot de passe doit faire au moins 8 caractères.'];
        }

        $existingUser = $this->userRepository->findByEmail($email);
        if ($existingUser !== null) {
            return ['success' => false, 'message' => 'Cette adresse email est déjà utilisée.'];
        }

        $role = $this->roleRepository->findByCode($roleCode);
        if ($role === null) {
            return ['success' => false, 'message' => 'Le rôle sélectionné est invalide.'];
        }

        $user = new User(
            roleId: $role->id,
            nom: $nom,
            prenom: $prenom,
            email: $email,
            passwordHash: password_hash($password, PASSWORD_ARGON2ID),
            actif: true,
        );

        $created = $this->userRepository->create($user);

        return ['success' => true, 'user' => $created];
    }

    public function listUsers(): array
    {
        return $this->userRepository->findAll();
    }

    public function getUserById(int $id): ?User
    {
        return $this->userRepository->findById($id);
    }

    public function updateUser(int $id, array $data, int $currentUserId): array
    {
        $user = $this->userRepository->findById($id);
        if ($user === null) {
            return ['success' => false, 'message' => 'Utilisateur introuvable.'];
        }

        $nom = trim((string) ($data['nom'] ?? ''));
        $prenom = trim((string) ($data['prenom'] ?? ''));
        $email = strtolower(trim((string) ($data['email'] ?? '')));
        $roleCode = strtoupper(trim((string) ($data['role_code'] ?? '')));
        $password = (string) ($data['password'] ?? '');
        $active = isset($data['actif']) && $data['actif'] === '1';

        if ($nom === '' || $prenom === '' || $email === '') {
            return ['success' => false, 'message' => 'Le nom, le prénom et l’email sont obligatoires.'];
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Adresse email invalide.'];
        }
        if ($password !== '' && strlen($password) < 8) {
            return ['success' => false, 'message' => 'Le nouveau mot de passe doit faire au moins 8 caractères.'];
        }
        if ($id === $currentUserId && !$active) {
            return ['success' => false, 'message' => 'Vous ne pouvez pas désactiver votre propre compte.'];
        }

        $role = $this->roleRepository->findByCode($roleCode);
        if ($role === null) {
            return ['success' => false, 'message' => 'Le rôle sélectionné est invalide.'];
        }
        if ($this->userRepository->findByEmailExcept($email, $id) !== null) {
            return ['success' => false, 'message' => 'Cette adresse email est déjà utilisée.'];
        }

        if ($user->actif && $user->roleId === $this->roleRepository->findByCode('ADMIN')?->id
            && (!$active || $role->code !== 'ADMIN')
            && $this->userRepository->countOtherActiveAdmins($id) === 0) {
            return ['success' => false, 'message' => 'Impossible de retirer le dernier administrateur actif.'];
        }

        $user->nom = $nom;
        $user->prenom = $prenom;
        $user->email = $email;
        $user->roleId = $role->id;
        $user->actif = $active;
        $passwordHash = $password === '' ? null : password_hash($password, PASSWORD_ARGON2ID);

        return ['success' => true, 'user' => $this->userRepository->update($user, $passwordHash)];
    }

    public function setUserActive(int $id, bool $active, int $currentUserId): array
    {
        $user = $this->userRepository->findById($id);
        if ($user === null) {
            return ['success' => false, 'message' => 'Utilisateur introuvable.'];
        }
        if ($id === $currentUserId && !$active) {
            return ['success' => false, 'message' => 'Vous ne pouvez pas désactiver votre propre compte.'];
        }

        $adminRole = $this->roleRepository->findByCode('ADMIN');
        if (!$active && $user->actif && $adminRole !== null && $user->roleId === $adminRole->id
            && $this->userRepository->countOtherActiveAdmins($id) === 0) {
            return ['success' => false, 'message' => 'Impossible de désactiver le dernier administrateur actif.'];
        }

        $updated = $this->userRepository->setActive($id, $active);

        return $updated === null
            ? ['success' => false, 'message' => 'Utilisateur introuvable.']
            : ['success' => true, 'user' => $updated];
    }
}
