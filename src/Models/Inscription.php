<?php

declare(strict_types=1);

namespace App\Models;

final class Inscription
{
    public function __construct(
        public int $id = 0,
        public int $eleveId = 0,
        public int $classeId = 0,
        public int $anneeScolaireId = 0,
        public string $statut = 'PENDING',
        public ?string $dateInscription = null,
        public ?string $createdAt = null,
        public ?string $updatedAt = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) ($data['id'] ?? 0),
            eleveId: (int) ($data['eleve_id'] ?? 0),
            classeId: (int) ($data['classe_id'] ?? 0),
            anneeScolaireId: (int) ($data['annee_scolaire_id'] ?? 0),
            statut: (string) ($data['statut'] ?? 'PENDING'),
            dateInscription: $data['date_inscription'] ?? null,
            createdAt: $data['created_at'] ?? null,
            updatedAt: $data['updated_at'] ?? null,
        );
    }
}
