<?php

declare(strict_types=1);

namespace App\Models;

final class Evaluation
{
    public function __construct(
        public int $id = 0,
        public int $affectationId = 0,
        public int $semestreId = 0,
        public int $classeId = 0,
        public int $anneeScolaireId = 0,
        public string $libelle = '',
        public string $type = 'DEVOIR',
        public float $coefficient = 1.0,
        public ?string $dateEvaluation = null,
        public ?string $description = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) ($data['id'] ?? 0),
            affectationId: (int) ($data['affectation_id'] ?? 0),
            semestreId: (int) ($data['semestre_id'] ?? 0),
            classeId: (int) ($data['classe_id'] ?? 0),
            anneeScolaireId: (int) ($data['annee_scolaire_id'] ?? 0),
            libelle: (string) ($data['libelle'] ?? ''),
            type: (string) ($data['type'] ?? 'DEVOIR'),
            coefficient: (float) ($data['coefficient'] ?? 1.0),
            dateEvaluation: $data['date_evaluation'] ?? null,
            description: $data['description'] ?? null,
        );
    }
}
