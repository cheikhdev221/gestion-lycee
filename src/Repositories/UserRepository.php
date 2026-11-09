<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Models\User;

final class UserRepository
{
    public function findByEmail(string $email): ?User
    {
        $statement = Database::getConnection()->prepare(
            'SELECT * FROM utilisateurs WHERE email = :email LIMIT 1'
        );
        $statement->execute(['email' => strtolower(trim($email))]);

        $row = $statement->fetch();

        if ($row === false) {
            return null;
        }

        return User::fromArray($row);
    }

    public function findByEmailExcept(string $email, int $userId): ?User
    {
        $statement = Database::getConnection()->prepare(
            'SELECT * FROM utilisateurs WHERE email = :email AND id <> :id LIMIT 1'
        );
        $statement->execute(['email' => strtolower(trim($email)), 'id' => $userId]);
        $row = $statement->fetch();

        return $row === false ? null : User::fromArray($row);
    }

    public function findStudentsWithoutProfile(): array
    {
        $statement = Database::getConnection()->prepare(
            "SELECT u.* FROM utilisateurs u
             INNER JOIN roles r ON r.id = u.role_id
             LEFT JOIN eleves e ON e.utilisateur_id = u.id
             WHERE r.code = 'ELEVE' AND u.actif = TRUE AND e.id IS NULL
             ORDER BY u.nom, u.prenom"
        );
        $statement->execute();

        return array_map(static fn (array $row): User => User::fromArray($row), $statement->fetchAll());
    }

    public function findTeachersWithoutProfile(): array
    {
        $statement = Database::getConnection()->prepare(
            "SELECT u.* FROM utilisateurs u
             INNER JOIN roles r ON r.id = u.role_id
             LEFT JOIN enseignants e ON e.utilisateur_id = u.id
             WHERE r.code = 'ENSEIGNANT' AND u.actif = TRUE AND e.id IS NULL
             ORDER BY u.nom, u.prenom"
        );
        $statement->execute();

        return array_map(static fn (array $row): User => User::fromArray($row), $statement->fetchAll());
    }

    public function findById(int $id): ?User
    {
        $statement = Database::getConnection()->prepare(
            'SELECT * FROM utilisateurs WHERE id = :id LIMIT 1'
        );
        $statement->execute(['id' => $id]);

        $row = $statement->fetch();

        if ($row === false) {
            return null;
        }

        return User::fromArray($row);
    }

    public function findAll(): array
    {
        $statement = Database::getConnection()->prepare(
            'SELECT * FROM utilisateurs ORDER BY id ASC'
        );
        $statement->execute();

        $rows = $statement->fetchAll();

        return array_map(static fn (array $row): User => User::fromArray($row), $rows);
    }

    public function create(User $user): User
    {
        $sql = 'INSERT INTO utilisateurs (role_id, nom, prenom, email, password_hash, actif)
                VALUES (:role_id, :nom, :prenom, :email, :password_hash, :actif)
                RETURNING *';

        $statement = Database::getConnection()->prepare($sql);
        $statement->execute([
            'role_id' => $user->roleId,
            'nom' => $user->nom,
            'prenom' => $user->prenom,
            'email' => strtolower(trim($user->email)),
            'password_hash' => $user->passwordHash,
            'actif' => $user->actif ? 1 : 0,
        ]);

        $row = $statement->fetch();

        if ($row === false) {
            throw new \RuntimeException('Impossible de créer l’utilisateur.');
        }

        return User::fromArray($row);
    }

    public function update(User $user, ?string $passwordHash = null): User
    {
        if ($passwordHash === null) {
            $statement = Database::getConnection()->prepare(
                'UPDATE utilisateurs
                 SET role_id = :role_id, nom = :nom, prenom = :prenom, email = :email, actif = :actif,
                     updated_at = CURRENT_TIMESTAMP
                 WHERE id = :id
                 RETURNING *'
            );
        } else {
            $statement = Database::getConnection()->prepare(
                'UPDATE utilisateurs
                 SET role_id = :role_id, nom = :nom, prenom = :prenom, email = :email,
                     password_hash = :password_hash, actif = :actif, updated_at = CURRENT_TIMESTAMP
                 WHERE id = :id
                 RETURNING *'
            );
            $statement->bindValue('password_hash', $passwordHash);
        }

        $statement->bindValue('role_id', $user->roleId, \PDO::PARAM_INT);
        $statement->bindValue('nom', $user->nom);
        $statement->bindValue('prenom', $user->prenom);
        $statement->bindValue('email', strtolower(trim($user->email)));
        $statement->bindValue('actif', $user->actif, \PDO::PARAM_BOOL);
        $statement->bindValue('id', $user->id, \PDO::PARAM_INT);
        $statement->execute();

        $row = $statement->fetch();
        if ($row === false) {
            throw new \RuntimeException('Utilisateur introuvable.');
        }

        return User::fromArray($row);
    }

    public function countOtherActiveAdmins(int $userId): int
    {
        $statement = Database::getConnection()->prepare(
            "SELECT COUNT(*)
             FROM utilisateurs u
             INNER JOIN roles r ON r.id = u.role_id
             WHERE r.code = 'ADMIN' AND u.actif = TRUE AND u.id <> :id"
        );
        $statement->execute(['id' => $userId]);

        return (int) $statement->fetchColumn();
    }

    public function setActive(int $id, bool $active): ?User
    {
        $statement = Database::getConnection()->prepare(
            'UPDATE utilisateurs
             SET actif = :actif, updated_at = CURRENT_TIMESTAMP
             WHERE id = :id
             RETURNING *'
        );
        $statement->bindValue('actif', $active, \PDO::PARAM_BOOL);
        $statement->bindValue('id', $id, \PDO::PARAM_INT);
        $statement->execute();
        $row = $statement->fetch();

        return $row === false ? null : User::fromArray($row);
    }
}
