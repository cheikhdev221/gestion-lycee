<?php

declare(strict_types=1);

namespace App\Models;

final class User
{
    public function __construct(
        public int $id = 0,
        public int $roleId = 0,
        public string $nom = '',
        public string $prenom = '',
        public string $email = '',
        public string $passwordHash = '',
        public bool $actif = true,
        public ?string $createdAt = null,
        public ?string $updatedAt = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) ($data['id'] ?? 0),
            roleId: (int) ($data['role_id'] ?? 0),
            nom: (string) ($data['nom'] ?? ''),
            prenom: (string) ($data['prenom'] ?? ''),
            email: (string) ($data['email'] ?? ''),
            passwordHash: (string) ($data['password_hash'] ?? ''),
            actif: (bool) ($data['actif'] ?? true),
            createdAt: $data['created_at'] ?? null,
            updatedAt: $data['updated_at'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'role_id' => $this->roleId,
            'nom' => $this->nom,
            'prenom' => $this->prenom,
            'email' => $this->email,
            'password_hash' => $this->passwordHash,
            'actif' => $this->actif,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
