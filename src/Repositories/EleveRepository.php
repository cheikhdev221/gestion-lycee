<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Models\Eleve;

final class EleveRepository
{
    public function findAll(): array
    {
        $statement = Database::getConnection()->prepare('SELECT * FROM eleves ORDER BY id ASC');
        $statement->execute();
        $rows = $statement->fetchAll();

        return array_map(static fn (array $row): Eleve => Eleve::fromArray($row), $rows);
    }

    public function findAllWithUserDetails(): array
    {
        $statement = Database::getConnection()->prepare(
            'SELECT e.id, e.matricule, u.prenom, u.nom
             FROM eleves e INNER JOIN utilisateurs u ON u.id = e.utilisateur_id
             WHERE u.actif = TRUE ORDER BY u.nom, u.prenom'
        );
        $statement->execute();

        return $statement->fetchAll();
    }

    public function findForTeacher(int $userId): array
    {
        $statement = Database::getConnection()->prepare(
            'SELECT DISTINCT e.*
             FROM eleves e
             INNER JOIN inscriptions i ON i.eleve_id = e.id
             INNER JOIN affectations_enseignants a ON a.classe_id = i.classe_id AND a.annee_scolaire_id = i.annee_scolaire_id
             INNER JOIN enseignants t ON t.id = a.enseignant_id
             WHERE t.utilisateur_id = :user_id
             ORDER BY e.id ASC'
        );
        $statement->execute(['user_id' => $userId]);
        $rows = $statement->fetchAll();

        return array_map(static fn (array $row): Eleve => Eleve::fromArray($row), $rows);
    }

    public function create(Eleve $eleve): Eleve
    {
        $statement = Database::getConnection()->prepare(
            'INSERT INTO eleves (utilisateur_id, matricule, date_naissance, photo_path)
             VALUES (:utilisateur_id, :matricule, :date_naissance, :photo_path)
             RETURNING *'
        );
        $statement->execute([
            'utilisateur_id' => $eleve->utilisateurId,
            'matricule' => $eleve->matricule,
            'date_naissance' => $eleve->dateNaissance,
            'photo_path' => $eleve->photoPath,
        ]);

        $row = $statement->fetch();
        if ($row === false) {
            throw new \RuntimeException('Impossible de créer l’élève.');
        }

        return Eleve::fromArray($row);
    }

    public function findById(int $id): ?Eleve
    {
        $statement = Database::getConnection()->prepare('SELECT * FROM eleves WHERE id = :id');
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();

        return $row === false ? null : Eleve::fromArray($row);
    }

    public function findByUserId(int $userId): ?Eleve
    {
        $statement = Database::getConnection()->prepare('SELECT * FROM eleves WHERE utilisateur_id = :user_id');
        $statement->execute(['user_id' => $userId]);
        $row = $statement->fetch();

        return $row === false ? null : Eleve::fromArray($row);
    }

    public function findPhotoPathByUserId(int $userId): ?string
    {
        $statement = Database::getConnection()->prepare(
            'SELECT photo_path FROM eleves WHERE utilisateur_id = :user_id'
        );
        $statement->execute(['user_id' => $userId]);
        $photoPath = $statement->fetchColumn();

        return $photoPath === false || $photoPath === null ? null : (string) $photoPath;
    }

    public function update(Eleve $eleve): Eleve
    {
        $statement = Database::getConnection()->prepare(
            'UPDATE eleves SET matricule = :matricule, date_naissance = :date_naissance WHERE id = :id RETURNING *'
        );
        $statement->execute([
            'id' => $eleve->id,
            'matricule' => strtoupper(trim($eleve->matricule)),
            'date_naissance' => $eleve->dateNaissance,
        ]);
        $row = $statement->fetch();

        if ($row === false) {
            throw new \RuntimeException('Élève introuvable.');
        }

        return Eleve::fromArray($row);
    }
}
