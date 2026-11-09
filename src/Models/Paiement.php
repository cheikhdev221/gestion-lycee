<?php

declare(strict_types=1);

namespace App\Models;

final class Paiement
{
    public function __construct(
        public int $id = 0,
        public int $inscriptionId = 0,
        public string $transactionRef = '',
        public ?string $providerTransactionRef = null,
        public string $provider = 'WAVE',
        public string $statut = 'INITIE',
        public float $montant = 0.0,
        public string $devise = 'XOF',
        public ?string $description = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) ($data['id'] ?? 0),
            inscriptionId: (int) ($data['inscription_id'] ?? 0),
            transactionRef: (string) ($data['transaction_ref'] ?? ''),
            providerTransactionRef: $data['provider_transaction_ref'] ?? null,
            provider: (string) ($data['provider'] ?? 'WAVE'),
            statut: (string) ($data['statut'] ?? 'INITIE'),
            montant: (float) ($data['montant'] ?? 0.0),
            devise: (string) ($data['devise'] ?? 'XOF'),
            description: $data['description'] ?? null,
        );
    }
}
