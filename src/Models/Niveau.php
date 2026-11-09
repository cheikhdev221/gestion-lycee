<?php

declare(strict_types=1);

namespace App\Models;

final class Niveau
{
    public function __construct(
        public int $id = 0,
        public string $code = '',
        public string $libelle = '',
        public int $ordre = 0,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) ($data['id'] ?? 0),
            code: (string) ($data['code'] ?? ''),
            libelle: (string) ($data['libelle'] ?? ''),
            ordre: (int) ($data['ordre'] ?? 0),
        );
    }
}
