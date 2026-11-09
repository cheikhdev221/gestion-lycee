<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\Inscription;
use App\Models\Paiement;
use App\Repositories\ClasseRepository;
use App\Repositories\EleveRepository;
use App\Repositories\InscriptionRepository;
use App\Repositories\PaiementRepository;
use App\Services\AuthService;
use App\Services\SchoolFeeService;

final class StudentEnrollmentController extends Controller
{
    public function __construct(
        private readonly AuthService $authService = new AuthService(),
        private readonly EleveRepository $eleveRepository = new EleveRepository(),
        private readonly ClasseRepository $classeRepository = new ClasseRepository(),
        private readonly InscriptionRepository $inscriptionRepository = new InscriptionRepository(),
        private readonly PaiementRepository $paiementRepository = new PaiementRepository(),
    ) {
    }

    public function create(): void
    {
        $user = $this->authService->currentUser();
        $eleve = $user === null ? null : $this->eleveRepository->findByUserId($user->id);
        if ($eleve === null) {
            http_response_code(403);
            echo 'Aucune fiche élève n’est associée à ce compte.';
            return;
        }

        $this->render('student-enrollment/create', [
            'classes' => $this->classeRepository->findAvailableForEnrollment($eleve->id),
            'error' => null,
        ]);
    }

    public function store(): void
    {
        $user = $this->authService->currentUser();
        $eleve = $user === null ? null : $this->eleveRepository->findByUserId($user->id);
        if ($eleve === null) {
            http_response_code(403);
            echo 'Aucune fiche élève n’est associée à ce compte.';
            return;
        }

        $classeId = (int) ($_POST['classe_id'] ?? 0);
        $context = $classeId > 0 ? $this->classeRepository->findEnrollmentContext($classeId) : null;
        if ($context === null || !in_array($context['annee_statut'], ['A_VENIR', 'EN_COURS'], true)) {
            $this->render('student-enrollment/create', [
                'classes' => $this->classeRepository->findAvailableForEnrollment($eleve->id),
                'error' => 'Sélectionne une classe d’une année scolaire ouverte.',
            ]);
            return;
        }

        $yearId = (int) $context['annee_scolaire_id'];
        if ($this->inscriptionRepository->hasStudentYear($eleve->id, $yearId)) {
            $this->render('student-enrollment/create', [
                'classes' => $this->classeRepository->findAvailableForEnrollment($eleve->id),
                'error' => 'Une demande existe déjà pour cette année scolaire.',
            ]);
            return;
        }

        try {
            $connection = Database::getConnection();
            $connection->beginTransaction();
            $inscription = $this->inscriptionRepository->create(new Inscription(
                eleveId: $eleve->id,
                classeId: (int) $context['classe_id'],
                anneeScolaireId: $yearId,
                statut: 'PENDING',
            ));
            $this->paiementRepository->create(new Paiement(
                inscriptionId: $inscription->id,
                transactionRef: 'INS-' . strtoupper(bin2hex(random_bytes(8))),
                provider: 'WAVE',
                statut: 'INITIE',
                montant: SchoolFeeService::PEDAGOGICAL_ENROLLMENT_XOF,
                devise: 'XOF',
                description: 'Frais d’inscription pédagogique',
            ));
            $connection->commit();
            header('Location: /mon-espace?inscription=requested&paiement=initie');
        } catch (\Throwable $exception) {
            if (isset($connection) && $connection->inTransaction()) {
                $connection->rollBack();
            }
            error_log($exception->getMessage());
            $this->render('student-enrollment/create', [
                'classes' => $this->classeRepository->findAvailableForEnrollment($eleve->id),
                'error' => 'La demande n’a pas pu être enregistrée. Vérifie que cette année ne possède pas déjà une inscription.',
            ]);
        }
    }
}
