<?php

declare(strict_types=1);

use App\Core\Database;
use App\Models\Paiement;
use App\Repositories\PaiementRepository;

require __DIR__ . '/../../src/Core/Database.php';
require __DIR__ . '/../../src/Models/Paiement.php';
require __DIR__ . '/../../src/Repositories/PaiementRepository.php';

define('BASE_PATH', dirname(__DIR__, 2));
$connection = Database::getConnection();
$connection->beginTransaction();

try {
    $suffix = strtoupper(bin2hex(random_bytes(4)));
    $roleStatement = $connection->prepare("SELECT id FROM roles WHERE code = 'ELEVE'");
    $roleStatement->execute();
    $roleId = (int) $roleStatement->fetchColumn();

    $userStatement = $connection->prepare(
        'INSERT INTO utilisateurs (role_id, nom, prenom, email, password_hash)
         VALUES (:role_id, :nom, :prenom, :email, :password_hash)
         RETURNING id'
    );
    $userStatement->execute([
        'role_id' => $roleId,
        'nom' => 'Test',
        'prenom' => 'Integration',
        'email' => "payment-test-$suffix@example.test",
        'password_hash' => password_hash(bin2hex(random_bytes(16)), PASSWORD_ARGON2ID),
    ]);
    $userId = (int) $userStatement->fetchColumn();

    $yearStatement = $connection->prepare(
        "INSERT INTO annees_scolaires (libelle, date_debut, date_fin, statut)
         VALUES ('2997-2998', '2997-09-01', '2998-06-30', 'A_VENIR')
         RETURNING id"
    );
    $yearStatement->execute();
    $yearId = (int) $yearStatement->fetchColumn();

    $levelStatement = $connection->prepare(
        'INSERT INTO niveaux (code, libelle, ordre) VALUES (:code, :libelle, 1) RETURNING id'
    );
    $levelStatement->execute(['code' => "T_$suffix", 'libelle' => "Test $suffix"]);
    $levelId = (int) $levelStatement->fetchColumn();

    $seriesStatement = $connection->prepare(
        'INSERT INTO series (code, libelle) VALUES (:code, :libelle) RETURNING id'
    );
    $seriesStatement->execute(['code' => "T_$suffix", 'libelle' => "Test $suffix"]);
    $seriesId = (int) $seriesStatement->fetchColumn();

    $relationStatement = $connection->prepare(
        'INSERT INTO series_niveaux (serie_id, niveau_id) VALUES (:serie_id, :niveau_id)'
    );
    $relationStatement->execute(['serie_id' => $seriesId, 'niveau_id' => $levelId]);

    $classStatement = $connection->prepare(
        'INSERT INTO classes (libelle, niveau_id, serie_id, annee_scolaire_id)
         VALUES (:libelle, :niveau_id, :serie_id, :annee_id)
         RETURNING id'
    );
    $classStatement->execute([
        'libelle' => "Test $suffix",
        'niveau_id' => $levelId,
        'serie_id' => $seriesId,
        'annee_id' => $yearId,
    ]);
    $classId = (int) $classStatement->fetchColumn();

    $studentStatement = $connection->prepare(
        'INSERT INTO eleves (utilisateur_id, matricule, date_naissance)
         VALUES (:user_id, :matricule, :date_naissance)
         RETURNING id'
    );
    $studentStatement->execute([
        'user_id' => $userId,
        'matricule' => 'LYC-2997-' . random_int(10000, 99999),
        'date_naissance' => '2010-01-01',
    ]);
    $studentId = (int) $studentStatement->fetchColumn();

    $enrollmentStatement = $connection->prepare(
        "INSERT INTO inscriptions (eleve_id, classe_id, annee_scolaire_id, statut)
         VALUES (:student_id, :class_id, :year_id, 'PENDING')
         RETURNING id"
    );
    $enrollmentStatement->execute([
        'student_id' => $studentId,
        'class_id' => $classId,
        'year_id' => $yearId,
    ]);
    $enrollmentId = (int) $enrollmentStatement->fetchColumn();

    $paymentStatement = $connection->prepare(
        "INSERT INTO paiements (inscription_id, transaction_ref, provider, montant)
         VALUES (:enrollment_id, :transaction_ref, 'WAVE', 1000)
         RETURNING id"
    );
    $paymentStatement->execute([
        'enrollment_id' => $enrollmentId,
        'transaction_ref' => "SIM-TEST-$suffix",
    ]);
    $paymentId = (int) $paymentStatement->fetchColumn();

    $connection->exec('SAVEPOINT direct_payment_status');
    try {
        $directUpdate = $connection->prepare("UPDATE paiements SET statut = 'PAYE' WHERE id = :id");
        $directUpdate->execute(['id' => $paymentId]);
        throw new RuntimeException('A direct PAYE transition was accepted.');
    } catch (PDOException $exception) {
        if ($exception->getCode() !== 'P0001') {
            throw $exception;
        }
        $connection->exec('ROLLBACK TO SAVEPOINT direct_payment_status');
    }

    $payment = (new PaiementRepository())->simulateResult($paymentId, 'PAYE');
    $statusStatement = $connection->prepare(
        'SELECT i.statut AS inscription_statut
         FROM inscriptions i WHERE i.id = :id'
    );
    $statusStatement->execute(['id' => $enrollmentId]);
    $enrollmentStatus = $statusStatement->fetchColumn();

    if ($payment->statut !== 'PAYE' || $enrollmentStatus !== 'PAYE') {
        throw new RuntimeException('The payment simulation did not confirm both records.');
    }

    echo "Payment simulation integration test passed\n";
} finally {
    if ($connection->inTransaction()) {
        $connection->rollBack();
    }
}
