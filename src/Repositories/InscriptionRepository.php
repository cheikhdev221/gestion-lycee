<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Models\Inscription;

final class InscriptionRepository
{
    public function findAll(): array
    {
        $statement = Database::getConnection()->prepare('SELECT * FROM inscriptions ORDER BY id ASC');
        $statement->execute();
        $rows = $statement->fetchAll();

        return array_map(static fn (array $row): Inscription => Inscription::fromArray($row), $rows);
    }

    public function findRecentWithDetails(int $limit = 4): array
    {
        $statement = Database::getConnection()->prepare(
            'SELECT i.id, i.statut, i.date_inscription, u.prenom, u.nom, c.libelle AS classe
             FROM inscriptions i
             INNER JOIN eleves e ON e.id = i.eleve_id
             INNER JOIN utilisateurs u ON u.id = e.utilisateur_id
             INNER JOIN classes c ON c.id = i.classe_id
             ORDER BY i.date_inscription DESC, i.id DESC
             LIMIT :limit'
        );
        $statement->bindValue('limit', max(1, min($limit, 20)), \PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }

    public function findPendingWithDetails(): array
    {
        $statement = Database::getConnection()->prepare(
            "SELECT i.id, i.statut, e.matricule, u.prenom, u.nom, c.libelle AS classe, a.libelle AS annee_scolaire
             FROM inscriptions i
             INNER JOIN eleves e ON e.id = i.eleve_id
             INNER JOIN utilisateurs u ON u.id = e.utilisateur_id
             INNER JOIN classes c ON c.id = i.classe_id
             INNER JOIN annees_scolaires a ON a.id = i.annee_scolaire_id
             WHERE i.statut = 'PENDING'
               AND NOT EXISTS (
                   SELECT 1 FROM paiements p
                   WHERE p.inscription_id = i.id AND p.statut IN ('INITIE', 'EN_ATTENTE', 'PAYE')
               )
             ORDER BY i.date_inscription DESC, i.id DESC"
        );
        $statement->execute();

        return $statement->fetchAll();
    }

    public function hasStudentYear(int $studentId, int $schoolYearId): bool
    {
        $statement = Database::getConnection()->prepare(
            'SELECT 1 FROM inscriptions WHERE eleve_id = :student_id AND annee_scolaire_id = :year_id LIMIT 1'
        );
        $statement->execute(['student_id' => $studentId, 'year_id' => $schoolYearId]);

        return $statement->fetchColumn() !== false;
    }

    public function create(Inscription $inscription): Inscription
    {
        $statement = Database::getConnection()->prepare(
            'INSERT INTO inscriptions (eleve_id, classe_id, annee_scolaire_id, statut, date_inscription)
             VALUES (:eleve_id, :classe_id, :annee_scolaire_id, :statut, :date_inscription)
             RETURNING *'
        );
        $statement->execute([
            'eleve_id' => $inscription->eleveId,
            'classe_id' => $inscription->classeId,
            'annee_scolaire_id' => $inscription->anneeScolaireId,
            'statut' => $inscription->statut,
            'date_inscription' => $inscription->dateInscription ?? date('Y-m-d'),
        ]);

        $row = $statement->fetch();
        if ($row === false) {
            throw new \RuntimeException('Impossible de créer l’inscription.');
        }

        return Inscription::fromArray($row);
    }

    public function findById(int $id): ?Inscription
    {
        $statement = Database::getConnection()->prepare('SELECT * FROM inscriptions WHERE id = :id');
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();

        return $row === false ? null : Inscription::fromArray($row);
    }

    public function updatePending(int $id, int $classeId, int $anneeScolaireId): ?Inscription
    {
        $statement = Database::getConnection()->prepare(
            "UPDATE inscriptions
             SET classe_id = :classe_id, annee_scolaire_id = :annee_scolaire_id, updated_at = CURRENT_TIMESTAMP
             WHERE id = :id AND statut = 'PENDING'
             RETURNING *"
        );
        $statement->execute(['id' => $id, 'classe_id' => $classeId, 'annee_scolaire_id' => $anneeScolaireId]);
        $row = $statement->fetch();

        return $row === false ? null : Inscription::fromArray($row);
    }

    public function cancelPending(int $id): bool
    {
        $statement = Database::getConnection()->prepare(
            "UPDATE inscriptions SET statut = 'ANNULEE', updated_at = CURRENT_TIMESTAMP
             WHERE id = :id AND statut = 'PENDING'"
        );
        $statement->execute(['id' => $id]);

        return $statement->rowCount() === 1;
    }
}
