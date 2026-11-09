<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Models\Matiere;

final class MatiereRepository
{
    public function findAll(): array
    {
        $statement = Database::getConnection()->prepare('SELECT * FROM matieres ORDER BY libelle ASC');
        $statement->execute();
        $rows = $statement->fetchAll();

        return array_map(static fn (array $row): Matiere => Matiere::fromArray($row), $rows);
    }

    public function create(Matiere $matiere): Matiere
    {
        $statement = Database::getConnection()->prepare(
            'INSERT INTO matieres (code, libelle, description) VALUES (:code, :libelle, :description) RETURNING *'
        );
        $statement->execute([
            'code' => $matiere->code,
            'libelle' => $matiere->libelle,
            'description' => $matiere->description,
        ]);

        $row = $statement->fetch();
        if ($row === false) {
            throw new \RuntimeException('Impossible de créer la matière.');
        }

        return Matiere::fromArray($row);
    }
}
