<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Models\Paiement;

final class PaiementRepository
{
    public function findAll(): array
    {
        $statement = Database::getConnection()->prepare('SELECT * FROM paiements ORDER BY created_at DESC');
        $statement->execute();
        $rows = $statement->fetchAll();

        return array_map(static fn (array $row): Paiement => Paiement::fromArray($row), $rows);
    }

    public function create(Paiement $paiement): Paiement
    {
        $statement = Database::getConnection()->prepare(
            'INSERT INTO paiements (inscription_id, transaction_ref, provider_transaction_ref, provider, statut, montant, devise, description)
             VALUES (:inscription_id, :transaction_ref, :provider_transaction_ref, :provider, :statut, :montant, :devise, :description)
             RETURNING *'
        );
        $statement->execute([
            'inscription_id' => $paiement->inscriptionId,
            'transaction_ref' => $paiement->transactionRef,
            'provider_transaction_ref' => $paiement->providerTransactionRef,
            'provider' => $paiement->provider,
            'statut' => $paiement->statut,
            'montant' => $paiement->montant,
            'devise' => $paiement->devise,
            'description' => $paiement->description,
        ]);

        $row = $statement->fetch();
        if ($row === false) {
            throw new \RuntimeException('Impossible de créer le paiement.');
        }

        return Paiement::fromArray($row);
    }

    public function simulateResult(int $paiementId, string $resultat): Paiement
    {
        if ($resultat === 'PAYE') {
            $statement = Database::getConnection()->prepare('SELECT public.confirmer_paiement(:paiement_id)');
            $statement->execute(['paiement_id' => $paiementId]);

            $paiement = $this->findById($paiementId);
            if ($paiement === null) {
                throw new \RuntimeException('Paiement introuvable.');
            }

            return $paiement;
        }

        if ($resultat !== 'ECHEC') {
            throw new \InvalidArgumentException('Résultat de simulation invalide.');
        }

        $statement = Database::getConnection()->prepare(
            "UPDATE paiements
             SET statut = 'ECHEC', updated_at = CURRENT_TIMESTAMP
             WHERE id = :paiement_id AND statut IN ('INITIE', 'EN_ATTENTE')
             RETURNING *"
        );
        $statement->execute(['paiement_id' => $paiementId]);
        $row = $statement->fetch();

        if ($row !== false) {
            return Paiement::fromArray($row);
        }

        $paiement = $this->findById($paiementId);
        if ($paiement?->statut === 'ECHEC') {
            return $paiement;
        }

        throw new \RuntimeException('Le paiement ne peut pas recevoir ce résultat.');
    }

    private function findById(int $id): ?Paiement
    {
        $statement = Database::getConnection()->prepare('SELECT * FROM paiements WHERE id = :id');
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();

        return $row === false ? null : Paiement::fromArray($row);
    }
}
