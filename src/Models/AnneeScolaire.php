<?php

declare(strict_types=1);

namespace App\Models;

final class AnneeScolaire
{
    public function __construct(
        public int $id = 0,
        public string $libelle = '',
        public ?string $dateDebut = null,
        public ?string $dateFin = null,
        public string $statut = 'A_VENIR',
        public ?string $createdAt = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) ($data['id'] ?? 0),
            libelle: (string) ($data['libelle'] ?? ''),
            dateDebut: $data['date_debut'] ?? null,
            dateFin: $data['date_fin'] ?? null,
            statut: (string) ($data['statut'] ?? 'A_VENIR'),
            createdAt: $data['created_at'] ?? null,
        );
    }
}
