<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Models\Role;

final class RoleRepository
{
    public function findAll(): array
    {
        $statement = Database::getConnection()->prepare(
            'SELECT * FROM roles ORDER BY id ASC'
        );
        $statement->execute();

        $rows = $statement->fetchAll();

        return array_map(static fn (array $row): Role => Role::fromArray($row), $rows);
    }

    public function findById(int $id): ?Role
    {
        $statement = Database::getConnection()->prepare(
            'SELECT * FROM roles WHERE id = :id LIMIT 1'
        );
        $statement->execute(['id' => $id]);

        $row = $statement->fetch();

        if ($row === false) {
            return null;
        }

        return Role::fromArray($row);
    }

    public function findByCode(string $code): ?Role
    {
        $statement = Database::getConnection()->prepare(
            'SELECT * FROM roles WHERE code = :code LIMIT 1'
        );
        $statement->execute(['code' => strtoupper(trim($code))]);

        $row = $statement->fetch();

        if ($row === false) {
            return null;
        }

        return Role::fromArray($row);
    }

    public function ensureDefaultRoles(): void
    {
        $defaults = [
            ['ADMIN', 'ADMIN'],
            ['DIRECTEUR', 'DIRECTEUR'],
            ['ENSEIGNANT', 'ENSEIGNANT'],
            ['ELEVE', 'ELEVE'],
        ];

        foreach ($defaults as [$code, $libelle]) {
            $statement = Database::getConnection()->prepare(
                'INSERT INTO roles (code, libelle, description)
                 VALUES (:code, :libelle, :description)
                 ON CONFLICT (code) DO NOTHING'
            );

            $statement->execute([
                'code' => $code,
                'libelle' => $libelle,
                'description' => sprintf('Rôle %s du système.', $libelle),
            ]);
        }
    }
}
