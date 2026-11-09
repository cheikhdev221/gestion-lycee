<?php

declare(strict_types=1);

namespace App\Models;

final class Classe
{
    public function __construct(
        public int $id = 0,
        public string $libelle = '',
        public int $niveauId = 0,
        public int $serieId = 0,
        public int $anneeScolaireId = 0,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) ($data['id'] ?? 0),
            libelle: (string) ($data['libelle'] ?? ''),
            niveauId: (int) ($data['niveau_id'] ?? 0),
            serieId: (int) ($data['serie_id'] ?? 0),
            anneeScolaireId: (int) ($data['annee_scolaire_id'] ?? 0),
        );
    }
}
