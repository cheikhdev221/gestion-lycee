<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Models\Niveau;

final class NiveauRepository
{
    public function findAll(): array
    {
        $statement = Database::getConnection()->prepare('SELECT * FROM niveaux ORDER BY ordre ASC');
        $statement->execute();
        $rows = $statement->fetchAll();

        return array_map(static fn (array $row): Niveau => Niveau::fromArray($row), $rows);
    }

    public function create(Niveau $niveau): Niveau
    {
        $statement = Database::getConnection()->prepare(
            'INSERT INTO niveaux (code, libelle, ordre) VALUES (:code, :libelle, :ordre) RETURNING *'
        );
        $statement->execute([
            'code' => $niveau->code,
            'libelle' => $niveau->libelle,
            'ordre' => $niveau->ordre,
        ]);

        $row = $statement->fetch();
        if ($row === false) {
            throw new \RuntimeException('Impossible de créer le niveau.');
        }

        return Niveau::fromArray($row);
    }
}
