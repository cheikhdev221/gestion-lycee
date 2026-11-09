<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Models\AnneeScolaire;

final class AnneeScolaireRepository
{
    public function findAll(): array
    {
        $statement = Database::getConnection()->prepare(
            'SELECT * FROM annees_scolaires ORDER BY date_debut DESC'
        );
        $statement->execute();

        $rows = $statement->fetchAll();
        return array_map(static fn (array $row): AnneeScolaire => AnneeScolaire::fromArray($row), $rows);
    }

    public function create(AnneeScolaire $annee): AnneeScolaire
    {
        $statement = Database::getConnection()->prepare(
            'INSERT INTO annees_scolaires (libelle, date_debut, date_fin, statut)
             VALUES (:libelle, :date_debut, :date_fin, :statut)
             RETURNING *'
        );
        $statement->execute([
            'libelle' => $annee->libelle,
            'date_debut' => $annee->dateDebut,
            'date_fin' => $annee->dateFin,
            'statut' => $annee->statut,
        ]);

        $row = $statement->fetch();
        if ($row === false) {
            throw new \RuntimeException('Impossible de créer l’année scolaire.');
        }

        return AnneeScolaire::fromArray($row);
    }

    public function findById(int $id): ?AnneeScolaire
    {
        $statement = Database::getConnection()->prepare('SELECT * FROM annees_scolaires WHERE id = :id');
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();

        return $row === false ? null : AnneeScolaire::fromArray($row);
    }

    public function hasClasses(int $id): bool
    {
        $statement = Database::getConnection()->prepare('SELECT 1 FROM classes WHERE annee_scolaire_id = :id LIMIT 1');
        $statement->execute(['id' => $id]);

        return $statement->fetchColumn() !== false;
    }

    public function update(AnneeScolaire $annee): AnneeScolaire
    {
        $statement = Database::getConnection()->prepare(
            'UPDATE annees_scolaires
             SET libelle = :libelle, date_debut = :date_debut, date_fin = :date_fin, statut = :statut
             WHERE id = :id
             RETURNING *'
        );
        $statement->execute([
            'id' => $annee->id,
            'libelle' => $annee->libelle,
            'date_debut' => $annee->dateDebut,
            'date_fin' => $annee->dateFin,
            'statut' => $annee->statut,
        ]);
        $row = $statement->fetch();

        if ($row === false) {
            throw new \RuntimeException('Année scolaire introuvable.');
        }

        return AnneeScolaire::fromArray($row);
    }
}
