<?php

declare(strict_types=1);

namespace App\Models;

final class Role
{
    public function __construct(
        public int $id = 0,
        public string $code = '',
        public string $libelle = '',
        public ?string $description = null,
        public ?string $createdAt = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) ($data['id'] ?? 0),
            code: (string) ($data['code'] ?? ''),
            libelle: (string) ($data['libelle'] ?? ''),
            description: $data['description'] ?? null,
            createdAt: $data['created_at'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'libelle' => $this->libelle,
            'description' => $this->description,
            'created_at' => $this->createdAt,
        ];
    }
}
