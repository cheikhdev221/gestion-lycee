<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Models\Evaluation;

final class EvaluationRepository
{
    public function findAll(): array
    {
        $statement = Database::getConnection()->prepare('SELECT * FROM evaluations ORDER BY date_evaluation DESC');
        $statement->execute();
        $rows = $statement->fetchAll();

        return array_map(static fn (array $row): Evaluation => Evaluation::fromArray($row), $rows);
    }

    public function findForTeacher(int $userId): array
    {
        $statement = Database::getConnection()->prepare(
            'SELECT ev.*
             FROM evaluations ev
             INNER JOIN affectations_enseignants a ON a.id = ev.affectation_id
             INNER JOIN enseignants e ON e.id = a.enseignant_id
             WHERE e.utilisateur_id = :user_id
             ORDER BY ev.date_evaluation DESC'
        );
        $statement->execute(['user_id' => $userId]);
        $rows = $statement->fetchAll();

        return array_map(static fn (array $row): Evaluation => Evaluation::fromArray($row), $rows);
    }

    public function findAffectations(?int $teacherUserId = null): array
    {
        $sql = 'SELECT a.id, a.classe_id, a.annee_scolaire_id, m.libelle AS matiere,
                       c.libelle AS classe, an.libelle AS annee_scolaire,
                       u.prenom AS enseignant_prenom, u.nom AS enseignant_nom
                FROM affectations_enseignants a
                INNER JOIN enseignants t ON t.id = a.enseignant_id
                INNER JOIN utilisateurs u ON u.id = t.utilisateur_id
                INNER JOIN matieres m ON m.id = a.matiere_id
                INNER JOIN classes c ON c.id = a.classe_id
                INNER JOIN annees_scolaires an ON an.id = a.annee_scolaire_id';
        if ($teacherUserId !== null) {
            $sql .= ' WHERE t.utilisateur_id = :user_id';
        }
        $sql .= ' ORDER BY an.date_debut DESC, c.libelle ASC, m.libelle ASC';
        $statement = Database::getConnection()->prepare($sql);
        if ($teacherUserId !== null) {
            $statement->execute(['user_id' => $teacherUserId]);
        } else {
            $statement->execute();
        }

        return $statement->fetchAll();
    }

    public function findAffectationContext(int $affectationId): ?array
    {
        $statement = Database::getConnection()->prepare(
            'SELECT classe_id, annee_scolaire_id FROM affectations_enseignants WHERE id = :id'
        );
        $statement->execute(['id' => $affectationId]);
        $row = $statement->fetch();

        return $row === false ? null : $row;
    }

    public function findForNoteForm(?int $teacherUserId = null): array
    {
        $sql = 'SELECT ev.id, ev.libelle, ev.date_evaluation, ev.classe_id, ev.annee_scolaire_id,
                       m.libelle AS matiere, c.libelle AS classe, an.libelle AS annee_scolaire
                FROM evaluations ev
                INNER JOIN affectations_enseignants af ON af.id = ev.affectation_id
                INNER JOIN enseignants t ON t.id = af.enseignant_id
                INNER JOIN matieres m ON m.id = af.matiere_id
                INNER JOIN classes c ON c.id = ev.classe_id
                INNER JOIN annees_scolaires an ON an.id = ev.annee_scolaire_id';
        if ($teacherUserId !== null) {
            $sql .= ' WHERE t.utilisateur_id = :user_id';
        }
        $sql .= ' ORDER BY ev.date_evaluation DESC, ev.libelle ASC';
        $statement = Database::getConnection()->prepare($sql);
        if ($teacherUserId !== null) {
            $statement->execute(['user_id' => $teacherUserId]);
        } else {
            $statement->execute();
        }

        return $statement->fetchAll();
    }

    public function findContext(int $evaluationId): ?array
    {
        $statement = Database::getConnection()->prepare(
            'SELECT classe_id, annee_scolaire_id FROM evaluations WHERE id = :id'
        );
        $statement->execute(['id' => $evaluationId]);
        $row = $statement->fetch();

        return $row === false ? null : $row;
    }

    public function teacherOwnsAffectation(int $userId, int $affectationId): bool
    {
        $statement = Database::getConnection()->prepare(
            'SELECT 1
             FROM affectations_enseignants a
             INNER JOIN enseignants e ON e.id = a.enseignant_id
             WHERE e.utilisateur_id = :user_id AND a.id = :affectation_id'
        );
        $statement->execute(['user_id' => $userId, 'affectation_id' => $affectationId]);

        return $statement->fetchColumn() !== false;
    }

    public function create(Evaluation $evaluation): Evaluation
    {
        $statement = Database::getConnection()->prepare(
            'INSERT INTO evaluations (affectation_id, semestre_id, classe_id, annee_scolaire_id, libelle, type, coefficient, date_evaluation, description)
             VALUES (:affectation_id, :semestre_id, :classe_id, :annee_scolaire_id, :libelle, :type, :coefficient, :date_evaluation, :description)
             RETURNING *'
        );
        $statement->execute([
            'affectation_id' => $evaluation->affectationId,
            'semestre_id' => $evaluation->semestreId,
            'classe_id' => $evaluation->classeId,
            'annee_scolaire_id' => $evaluation->anneeScolaireId,
            'libelle' => $evaluation->libelle,
            'type' => $evaluation->type,
            'coefficient' => $evaluation->coefficient,
            'date_evaluation' => $evaluation->dateEvaluation,
            'description' => $evaluation->description,
        ]);

        $row = $statement->fetch();
        if ($row === false) {
            throw new \RuntimeException('Impossible de créer l’évaluation.');
        }

        return Evaluation::fromArray($row);
    }

    public function findById(int $id): ?Evaluation
    {
        $statement = Database::getConnection()->prepare('SELECT * FROM evaluations WHERE id = :id');
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();

        return $row === false ? null : Evaluation::fromArray($row);
    }

    public function updateContent(Evaluation $evaluation): ?Evaluation
    {
        $statement = Database::getConnection()->prepare(
            'UPDATE evaluations
             SET libelle = :libelle, type = :type, coefficient = :coefficient,
                 date_evaluation = :date_evaluation, description = :description,
                 updated_at = CURRENT_TIMESTAMP
             WHERE id = :id
             RETURNING *'
        );
        $statement->execute([
            'id' => $evaluation->id,
            'libelle' => $evaluation->libelle,
            'type' => $evaluation->type,
            'coefficient' => $evaluation->coefficient,
            'date_evaluation' => $evaluation->dateEvaluation,
            'description' => $evaluation->description,
        ]);
        $row = $statement->fetch();

        return $row === false ? null : Evaluation::fromArray($row);
    }
}
