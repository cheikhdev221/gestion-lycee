<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Models\Note;

final class NoteRepository
{
    public function findAll(): array
    {
        $statement = Database::getConnection()->prepare('SELECT * FROM notes ORDER BY id ASC');
        $statement->execute();
        $rows = $statement->fetchAll();

        return array_map(static fn (array $row): Note => Note::fromArray($row), $rows);
    }

    public function findForTeacher(int $userId): array
    {
        $statement = Database::getConnection()->prepare(
            'SELECT n.*
             FROM notes n
             INNER JOIN evaluations ev ON ev.id = n.evaluation_id
             INNER JOIN affectations_enseignants a ON a.id = ev.affectation_id
             INNER JOIN enseignants e ON e.id = a.enseignant_id
             WHERE e.utilisateur_id = :user_id
             ORDER BY n.id ASC'
        );
        $statement->execute(['user_id' => $userId]);
        $rows = $statement->fetchAll();

        return array_map(static fn (array $row): Note => Note::fromArray($row), $rows);
    }

    public function teacherOwnsEvaluation(int $userId, int $evaluationId): bool
    {
        $statement = Database::getConnection()->prepare(
            'SELECT 1
             FROM evaluations ev
             INNER JOIN affectations_enseignants a ON a.id = ev.affectation_id
             INNER JOIN enseignants e ON e.id = a.enseignant_id
             WHERE e.utilisateur_id = :user_id AND ev.id = :evaluation_id'
        );
        $statement->execute(['user_id' => $userId, 'evaluation_id' => $evaluationId]);

        return $statement->fetchColumn() !== false;
    }

    public function create(Note $note): Note
    {
        $statement = Database::getConnection()->prepare(
            'INSERT INTO notes (evaluation_id, eleve_id, classe_id, annee_scolaire_id, valeur, commentaire, saisi_par)
             VALUES (:evaluation_id, :eleve_id, :classe_id, :annee_scolaire_id, :valeur, :commentaire, :saisi_par)
             RETURNING *'
        );
        $statement->execute([
            'evaluation_id' => $note->evaluationId,
            'eleve_id' => $note->eleveId,
            'classe_id' => $note->classeId,
            'annee_scolaire_id' => $note->anneeScolaireId,
            'valeur' => $note->valeur,
            'commentaire' => $note->commentaire,
            'saisi_par' => $note->saisiPar,
        ]);

        $row = $statement->fetch();
        if ($row === false) {
            throw new \RuntimeException('Impossible de créer la note.');
        }

        return Note::fromArray($row);
    }

    public function findById(int $id): ?Note
    {
        $statement = Database::getConnection()->prepare('SELECT * FROM notes WHERE id = :id');
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();

        return $row === false ? null : Note::fromArray($row);
    }

    public function updateByTeacher(int $id, int $userId, float $valeur, ?string $commentaire): ?Note
    {
        $statement = Database::getConnection()->prepare(
            'UPDATE notes n
             SET valeur = :valeur, commentaire = :commentaire, saisi_par = :entered_by,
                 updated_at = CURRENT_TIMESTAMP
             FROM evaluations ev
             INNER JOIN affectations_enseignants a ON a.id = ev.affectation_id
             INNER JOIN enseignants e ON e.id = a.enseignant_id
             WHERE n.id = :id AND ev.id = n.evaluation_id AND e.utilisateur_id = :user_id
             RETURNING n.*'
        );
        $statement->execute([
            'id' => $id,
            'user_id' => $userId,
            'entered_by' => $userId,
            'valeur' => $valeur,
            'commentaire' => $commentaire,
        ]);
        $row = $statement->fetch();

        return $row === false ? null : Note::fromArray($row);
    }
}
