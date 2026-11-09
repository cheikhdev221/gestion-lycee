<?php

declare(strict_types=1);

namespace App\Models;

final class Note
{
    public function __construct(
        public int $id = 0,
        public int $evaluationId = 0,
        public int $eleveId = 0,
        public int $classeId = 0,
        public int $anneeScolaireId = 0,
        public float $valeur = 0.0,
        public ?string $commentaire = null,
        public int $saisiPar = 0,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) ($data['id'] ?? 0),
            evaluationId: (int) ($data['evaluation_id'] ?? 0),
            eleveId: (int) ($data['eleve_id'] ?? 0),
            classeId: (int) ($data['classe_id'] ?? 0),
            anneeScolaireId: (int) ($data['annee_scolaire_id'] ?? 0),
            valeur: (float) ($data['valeur'] ?? 0.0),
            commentaire: $data['commentaire'] ?? null,
            saisiPar: (int) ($data['saisi_par'] ?? 0),
        );
    }
}
