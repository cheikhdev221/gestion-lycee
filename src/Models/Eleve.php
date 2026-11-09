<?php

declare(strict_types=1);

namespace App\Models;

final class Eleve
{
    public function __construct(
        public int $id = 0,
        public int $utilisateurId = 0,
        public string $matricule = '',
        public ?string $dateNaissance = null,
        public ?string $createdAt = null,
        public ?string $photoPath = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) ($data['id'] ?? 0),
            utilisateurId: (int) ($data['utilisateur_id'] ?? 0),
            matricule: (string) ($data['matricule'] ?? ''),
            dateNaissance: $data['date_naissance'] ?? null,
            createdAt: $data['created_at'] ?? null,
            photoPath: $data['photo_path'] ?? null,
        );
    }
}
