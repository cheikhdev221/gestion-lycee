<?php

declare(strict_types=1);

namespace App\Models;

final class Semestre
{
    public function __construct(
        public int $id = 0,
        public int $anneeScolaireId = 0,
        public string $code = '',
        public string $libelle = '',
        public ?string $dateDebut = null,
        public ?string $dateFin = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) ($data['id'] ?? 0),
            anneeScolaireId: (int) ($data['annee_scolaire_id'] ?? 0),
            code: (string) ($data['code'] ?? ''),
            libelle: (string) ($data['libelle'] ?? ''),
            dateDebut: $data['date_debut'] ?? null,
            dateFin: $data['date_fin'] ?? null,
        );
    }
}
