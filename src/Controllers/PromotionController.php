<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Repositories\PromotionRepository;
use App\Services\AuthService;

final class PromotionController extends Controller
{
    public function __construct(
        private readonly PromotionRepository $repository = new PromotionRepository(),
        private readonly AuthService $authService = new AuthService(),
    ) {
    }

    public function index(): void
    {
        $this->render('promotions/index', [
            'candidates' => $this->repository->findCandidates(),
            'error' => null,
            'success' => null,
        ]);
    }

    public function approve(): void
    {
        $user = $this->authService->currentUser();
        $eleveId = (int) ($_POST['eleve_id'] ?? 0);
        $classeId = (int) ($_POST['classe_id'] ?? 0);

        if ($user === null) {
            http_response_code(401);
            return;
        }

        if ($eleveId <= 0 || $classeId <= 0) {
            http_response_code(400);
            $this->render('promotions/index', [
                'candidates' => $this->repository->findCandidates(),
                'error' => 'Élève ou classe invalide.',
                'success' => null,
            ]);
            return;
        }

        try {
            $this->repository->approve($eleveId, $classeId, $user->id);
            $this->render('promotions/index', [
                'candidates' => $this->repository->findCandidates(),
                'error' => null,
                'success' => 'Passage validé. Une inscription en attente de paiement a été créée.',
            ]);
        } catch (\Throwable $exception) {
            $this->render('promotions/index', [
                'candidates' => $this->repository->findCandidates(),
                'error' => $exception->getMessage(),
                'success' => null,
            ]);
        }
    }
}