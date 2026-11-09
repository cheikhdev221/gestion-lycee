<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Repositories\StudentPortalRepository;
use App\Services\AuthService;

final class StudentPortalController extends Controller
{
    public function __construct(
        private readonly StudentPortalRepository $repository = new StudentPortalRepository(),
        private readonly AuthService $authService = new AuthService(),
    ) {
    }

    public function index(): void
    {
        $user = $this->authService->currentUser();
        $portal = $user === null ? null : $this->repository->findForUser($user->id);

        $this->render('student-portal/index', [
            'portal' => $portal,
            'roleCode' => 'ELEVE',
            'enrollmentSuccess' => ($_GET['inscription'] ?? '') === 'requested',
            'paymentInitiated' => ($_GET['paiement'] ?? '') === 'initie',
            'user' => $user === null ? [] : ['id' => $user->id, 'nom' => $user->nom, 'prenom' => $user->prenom],
        ]);
    }
}
