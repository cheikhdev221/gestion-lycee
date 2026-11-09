<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Models\Serie;

final class SerieRepository
{
    public function findAll(): array
    {
        $statement = Database::getConnection()->prepare('SELECT * FROM series ORDER BY libelle ASC');
        $statement->execute();
        $rows = $statement->fetchAll();

        return array_map(static fn (array $row): Serie => Serie::fromArray($row), $rows);
    }

    public function create(Serie $serie): Serie
    {
        $statement = Database::getConnection()->prepare(
            'INSERT INTO series (code, libelle) VALUES (:code, :libelle) RETURNING *'
        );
        $statement->execute([
            'code' => $serie->code,
            'libelle' => $serie->libelle,
        ]);

        $row = $statement->fetch();
        if ($row === false) {
            throw new \RuntimeException('Impossible de créer la série.');
        }

        return Serie::fromArray($row);
    }
}
