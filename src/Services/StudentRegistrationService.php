<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Models\Eleve;
use App\Models\User;
use App\Repositories\EleveRepository;
use App\Repositories\RoleRepository;
use App\Repositories\UserRepository;
use PDOException;

final class StudentRegistrationService
{
    public function __construct(
        private readonly UserRepository $userRepository = new UserRepository(),
        private readonly EleveRepository $eleveRepository = new EleveRepository(),
        private readonly RoleRepository $roleRepository = new RoleRepository(),
        private readonly StudentPhotoStorage $photoStorage = new StudentPhotoStorage(),
    ) {
    }

    public function register(array $data, array $photoUpload): array
    {
        $nom = trim((string) ($data['nom'] ?? ''));
        $prenom = trim((string) ($data['prenom'] ?? ''));
        $email = strtolower(trim((string) ($data['email'] ?? '')));
        $password = (string) ($data['password'] ?? '');
        $dateNaissance = trim((string) ($data['date_naissance'] ?? ''));

        if ($nom === '' || $prenom === '' || strlen($nom) > 100 || strlen($prenom) > 100) {
            return ['success' => false, 'message' => 'Saisis un nom et un prénom valides.'];
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 255) {
            return ['success' => false, 'message' => 'Adresse e-mail invalide.'];
        }
        if (strlen($password) < 12) {
            return ['success' => false, 'message' => 'Le mot de passe doit contenir au moins 12 caractères.'];
        }

        $birthDate = \DateTimeImmutable::createFromFormat('!Y-m-d', $dateNaissance);
        if ($birthDate === false || $birthDate->format('Y-m-d') !== $dateNaissance || $birthDate > new \DateTimeImmutable('today')) {
            return ['success' => false, 'message' => 'Date de naissance invalide.'];
        }
        if ($this->userRepository->findByEmail($email) !== null) {
            return ['success' => false, 'message' => 'Cette adresse e-mail est déjà associée à un compte.'];
        }

        $role = $this->roleRepository->findByCode('ELEVE');
        if ($role === null) {
            return ['success' => false, 'message' => 'L’inscription est momentanément indisponible.'];
        }

        try {
            $photoFilename = $this->photoStorage->store($photoUpload);
        } catch (\InvalidArgumentException $exception) {
            return ['success' => false, 'message' => $exception->getMessage()];
        } catch (\Throwable $exception) {
            error_log($exception->getMessage());
            return ['success' => false, 'message' => 'La photo n’a pas pu être enregistrée. Réessaie plus tard.'];
        }

        $connection = Database::getConnection();
        try {
            $connection->beginTransaction();
            $user = $this->userRepository->create(new User(
                roleId: $role->id,
                nom: $nom,
                prenom: $prenom,
                email: $email,
                passwordHash: password_hash($password, PASSWORD_ARGON2ID),
                actif: true,
            ));

            $eleve = null;
            for ($attempt = 0; $attempt < 8; $attempt++) {
                $connection->exec('SAVEPOINT matricule_attempt');
                $matricule = sprintf('LYC-%s-%05d', date('Y'), random_int(0, 99999));
                try {
                    $eleve = $this->eleveRepository->create(new Eleve(
                        utilisateurId: $user->id,
                        matricule: $matricule,
                        dateNaissance: $dateNaissance,
                        photoPath: $photoFilename,
                    ));
                    $connection->exec('RELEASE SAVEPOINT matricule_attempt');
                    break;
                } catch (PDOException $exception) {
                    $connection->exec('ROLLBACK TO SAVEPOINT matricule_attempt');
                    if (!str_contains($exception->getMessage(), 'uq_eleves_matricule') || $attempt === 7) {
                        throw $exception;
                    }
                }
            }

            if ($eleve === null) {
                throw new \RuntimeException('Impossible de générer un matricule unique.');
            }

            $connection->commit();

            return ['success' => true, 'user' => $user, 'eleve' => $eleve];
        } catch (\Throwable $exception) {
            if ($connection->inTransaction()) {
                $connection->rollBack();
            }
            $this->photoStorage->delete($photoFilename);
            if ($exception instanceof PDOException && $exception->getCode() === '23505') {
                return ['success' => false, 'message' => 'Cette adresse e-mail est déjà associée à un compte.'];
            }
            error_log($exception->getMessage());

            return ['success' => false, 'message' => 'L’inscription n’a pas pu être enregistrée. Réessaie plus tard.'];
        }
    }
}