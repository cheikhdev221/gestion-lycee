<?php

declare(strict_types=1);

namespace App\Models;

final class Enseignant
{
    public function __construct(
        public int $id = 0,
        public int $utilisateurId = 0,
        public int $matiereId = 0,
        public ?string $createdAt = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) ($data['id'] ?? 0),
            utilisateurId: (int) ($data['utilisateur_id'] ?? 0),
            matiereId: (int) ($data['matiere_id'] ?? 0),
            createdAt: $data['created_at'] ?? null,
        );
    }
}
