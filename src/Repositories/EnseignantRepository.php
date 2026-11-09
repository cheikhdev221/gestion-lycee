<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Models\Enseignant;

final class EnseignantRepository
{
    public function findAll(): array
    {
        $statement = Database::getConnection()->prepare('SELECT * FROM enseignants ORDER BY id ASC');
        $statement->execute();
        $rows = $statement->fetchAll();

        return array_map(static fn (array $row): Enseignant => Enseignant::fromArray($row), $rows);
    }

    public function create(Enseignant $enseignant): Enseignant
    {
        $statement = Database::getConnection()->prepare(
            'INSERT INTO enseignants (utilisateur_id, matiere_id) VALUES (:utilisateur_id, :matiere_id) RETURNING *'
        );
        $statement->execute([
            'utilisateur_id' => $enseignant->utilisateurId,
            'matiere_id' => $enseignant->matiereId,
        ]);

        $row = $statement->fetch();
        if ($row === false) {
            throw new \RuntimeException('Impossible de créer l’enseignant.');
        }

        return Enseignant::fromArray($row);
    }

    public function findById(int $id): ?Enseignant
    {
        $statement = Database::getConnection()->prepare('SELECT * FROM enseignants WHERE id = :id');
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();

        return $row === false ? null : Enseignant::fromArray($row);
    }

    public function hasAffectations(int $id): bool
    {
        $statement = Database::getConnection()->prepare('SELECT 1 FROM affectations_enseignants WHERE enseignant_id = :id LIMIT 1');
        $statement->execute(['id' => $id]);

        return $statement->fetchColumn() !== false;
    }

    public function update(Enseignant $enseignant): Enseignant
    {
        $statement = Database::getConnection()->prepare(
            'UPDATE enseignants SET matiere_id = :matiere_id WHERE id = :id RETURNING *'
        );
        $statement->execute(['id' => $enseignant->id, 'matiere_id' => $enseignant->matiereId]);
        $row = $statement->fetch();

        if ($row === false) {
            throw new \RuntimeException('Enseignant introuvable.');
        }

        return Enseignant::fromArray($row);
    }
}
