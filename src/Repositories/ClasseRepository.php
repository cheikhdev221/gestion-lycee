<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Models\Classe;

final class ClasseRepository
{
    public function findAll(): array
    {
        $statement = Database::getConnection()->prepare(
            'SELECT * FROM classes ORDER BY annee_scolaire_id DESC, libelle ASC'
        );
        $statement->execute();
        $rows = $statement->fetchAll();

        return array_map(static fn (array $row): Classe => Classe::fromArray($row), $rows);
    }

    public function findForTeacher(int $userId): array
    {
        $statement = Database::getConnection()->prepare(
            'SELECT DISTINCT c.*
             FROM classes c
             INNER JOIN affectations_enseignants a ON a.classe_id = c.id AND a.annee_scolaire_id = c.annee_scolaire_id
             INNER JOIN enseignants e ON e.id = a.enseignant_id
             WHERE e.utilisateur_id = :user_id
             ORDER BY c.annee_scolaire_id DESC, c.libelle ASC'
        );
        $statement->execute(['user_id' => $userId]);
        $rows = $statement->fetchAll();

        return array_map(static fn (array $row): Classe => Classe::fromArray($row), $rows);
    }

    public function findAvailableForEnrollment(int $studentId): array
    {
        $statement = Database::getConnection()->prepare(
            "SELECT c.id, c.libelle, c.annee_scolaire_id, a.libelle AS annee_scolaire
             FROM classes c
             INNER JOIN annees_scolaires a ON a.id = c.annee_scolaire_id
             WHERE a.statut IN ('A_VENIR', 'EN_COURS')
               AND NOT EXISTS (
                   SELECT 1 FROM inscriptions i
                   WHERE i.eleve_id = :eleve_id AND i.annee_scolaire_id = c.annee_scolaire_id
               )
             ORDER BY a.date_debut DESC, c.libelle ASC"
        );
        $statement->execute(['eleve_id' => $studentId]);

        return $statement->fetchAll();
    }

    public function findEnrollmentContext(int $classId): ?array
    {
        $statement = Database::getConnection()->prepare(
            'SELECT c.id AS classe_id, c.annee_scolaire_id, a.statut AS annee_statut
             FROM classes c INNER JOIN annees_scolaires a ON a.id = c.annee_scolaire_id
             WHERE c.id = :id'
        );
        $statement->execute(['id' => $classId]);
        $row = $statement->fetch();

        return $row === false ? null : $row;
    }

    public function create(Classe $classe): Classe
    {
        $statement = Database::getConnection()->prepare(
            'INSERT INTO classes (libelle, niveau_id, serie_id, annee_scolaire_id)
             VALUES (:libelle, :niveau_id, :serie_id, :annee_scolaire_id)
             RETURNING *'
        );
        $statement->execute([
            'libelle' => $classe->libelle,
            'niveau_id' => $classe->niveauId,
            'serie_id' => $classe->serieId,
            'annee_scolaire_id' => $classe->anneeScolaireId,
        ]);

        $row = $statement->fetch();
        if ($row === false) {
            throw new \RuntimeException('Impossible de créer la classe.');
        }

        return Classe::fromArray($row);
    }

    public function findById(int $id): ?Classe
    {
        $statement = Database::getConnection()->prepare('SELECT * FROM classes WHERE id = :id');
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();

        return $row === false ? null : Classe::fromArray($row);
    }

    public function hasInscriptionsOrAffectations(int $id): bool
    {
        $statement = Database::getConnection()->prepare(
              'SELECT 1 FROM inscriptions WHERE classe_id = :inscription_class_id
             UNION ALL
               SELECT 1 FROM affectations_enseignants WHERE classe_id = :affectation_class_id
             LIMIT 1'
        );
           $statement->execute(['inscription_class_id' => $id, 'affectation_class_id' => $id]);

        return $statement->fetchColumn() !== false;
    }

    public function update(Classe $classe): Classe
    {
        $statement = Database::getConnection()->prepare(
            'UPDATE classes
             SET libelle = :libelle, niveau_id = :niveau_id, serie_id = :serie_id, annee_scolaire_id = :annee_scolaire_id
             WHERE id = :id
             RETURNING *'
        );
        $statement->execute([
            'id' => $classe->id,
            'libelle' => trim($classe->libelle),
            'niveau_id' => $classe->niveauId,
            'serie_id' => $classe->serieId,
            'annee_scolaire_id' => $classe->anneeScolaireId,
        ]);
        $row = $statement->fetch();

        if ($row === false) {
            throw new \RuntimeException('Classe introuvable.');
        }

        return Classe::fromArray($row);
    }
}
