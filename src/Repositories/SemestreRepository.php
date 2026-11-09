<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Models\Semestre;

final class SemestreRepository
{
    public function findAll(): array
    {
        $statement = Database::getConnection()->prepare('SELECT * FROM semestres ORDER BY annee_scolaire_id ASC, id ASC');
        $statement->execute();
        $rows = $statement->fetchAll();

        return array_map(static fn (array $row): Semestre => Semestre::fromArray($row), $rows);
    }

    public function findBySchoolYear(int $schoolYearId): array
    {
        $statement = Database::getConnection()->prepare(
            'SELECT * FROM semestres WHERE annee_scolaire_id = :year_id ORDER BY id ASC'
        );
        $statement->execute(['year_id' => $schoolYearId]);
        $rows = $statement->fetchAll();

        return array_map(static fn (array $row): Semestre => Semestre::fromArray($row), $rows);
    }

    public function belongsToSchoolYear(int $semesterId, int $schoolYearId): bool
    {
        $statement = Database::getConnection()->prepare(
            'SELECT 1 FROM semestres WHERE id = :id AND annee_scolaire_id = :year_id'
        );
        $statement->execute(['id' => $semesterId, 'year_id' => $schoolYearId]);

        return $statement->fetchColumn() !== false;
    }

    public function create(Semestre $semestre): Semestre
    {
        $statement = Database::getConnection()->prepare(
            'INSERT INTO semestres (annee_scolaire_id, code, libelle, date_debut, date_fin)
             VALUES (:annee_scolaire_id, :code, :libelle, :date_debut, :date_fin) RETURNING *'
        );
        $statement->execute([
            'annee_scolaire_id' => $semestre->anneeScolaireId,
            'code' => $semestre->code,
            'libelle' => $semestre->libelle,
            'date_debut' => $semestre->dateDebut,
            'date_fin' => $semestre->dateFin,
        ]);

        $row = $statement->fetch();
        if ($row === false) {
            throw new \RuntimeException('Impossible de créer le semestre.');
        }

        return Semestre::fromArray($row);
    }
}
