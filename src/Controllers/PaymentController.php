<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Paiement;
use App\Repositories\InscriptionRepository;
use App\Repositories\PaiementRepository;
use App\Services\AuthService;
use App\Services\SchoolFeeService;

final class PaymentController extends Controller
{
    public function __construct(
        private readonly PaiementRepository $repository = new PaiementRepository(),
        private readonly InscriptionRepository $inscriptionRepository = new InscriptionRepository(),
        private readonly AuthService $authService = new AuthService(),
    ) {
    }

    public function index(): void
    {
        $this->render('payments/index', [
            'payments' => $this->repository->findAll(),
            'canSimulate' => $this->authService->currentRoleCode() === 'ADMIN',
        ]);
    }

    public function create(): void
    {
        $this->render('payments/create', [
            'error' => null,
            'success' => null,
            'inscriptions' => $this->inscriptionRepository->findPendingWithDetails(),
            'feeAmount' => SchoolFeeService::PEDAGOGICAL_ENROLLMENT_XOF,
        ]);
    }

    public function store(): void
    {
        $inscriptionId = (int) ($_POST['inscription_id'] ?? 0);
        $provider = strtoupper(trim((string) ($_POST['provider'] ?? 'WAVE')));
        // Le tarif est fixé côté serveur : une valeur envoyée par le navigateur ne peut pas le modifier.
        $montant = SchoolFeeService::PEDAGOGICAL_ENROLLMENT_XOF;

        if ($inscriptionId <= 0 || !in_array($provider, ['WAVE', 'CARTE'], true)) {
            $this->render('payments/create', ['error' => 'Sélectionne une inscription en attente et un canal de paiement valide.', 'success' => null, 'inscriptions' => $this->inscriptionRepository->findPendingWithDetails(), 'feeAmount' => SchoolFeeService::PEDAGOGICAL_ENROLLMENT_XOF]);
            return;
        }

        try {
            $this->repository->create(new Paiement(
                inscriptionId: $inscriptionId,
                transactionRef: 'SIM-' . strtoupper(bin2hex(random_bytes(6))),
                provider: $provider,
                statut: 'INITIE',
                montant: $montant,
                description: 'Frais d’inscription pédagogique',
            ));
            $this->render('payments/create', ['error' => null, 'success' => 'Paiement simulé créé pour 25 000 XOF.', 'inscriptions' => $this->inscriptionRepository->findPendingWithDetails(), 'feeAmount' => SchoolFeeService::PEDAGOGICAL_ENROLLMENT_XOF]);
        } catch (\Throwable $e) {
            $this->render('payments/create', ['error' => 'Le paiement simulé n’a pas pu être créé.', 'success' => null, 'inscriptions' => $this->inscriptionRepository->findPendingWithDetails(), 'feeAmount' => SchoolFeeService::PEDAGOGICAL_ENROLLMENT_XOF]);
        }
    }

    public function simulate(): void
    {
        $paiementId = (int) ($_POST['paiement_id'] ?? 0);
        $resultat = strtoupper(trim((string) ($_POST['resultat'] ?? '')));

        if ($paiementId <= 0 || !in_array($resultat, ['PAYE', 'ECHEC'], true)) {
            http_response_code(400);
            $this->render('payments/index', [
                'payments' => $this->repository->findAll(),
                'canSimulate' => true,
                'error' => 'Résultat de simulation invalide.',
            ]);
            return;
        }

        try {
            $this->repository->simulateResult($paiementId, $resultat);
            $this->render('payments/index', [
                'payments' => $this->repository->findAll(),
                'canSimulate' => true,
                'error' => null,
                'success' => 'Résultat de paiement simulé enregistré.',
            ]);
        } catch (\Throwable $e) {
            $this->render('payments/index', [
                'payments' => $this->repository->findAll(),
                'canSimulate' => true,
                'error' => 'La simulation du paiement a échoué.',
                'success' => null,
            ]);
        }
    }
}
