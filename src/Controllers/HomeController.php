<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Repositories\ClasseRepository;
use App\Repositories\EvaluationRepository;
use App\Repositories\NoteRepository;
use App\Services\AuthService;
use App\Services\SchoolCatalogService;

final class HomeController extends Controller
{
    public function __construct(
        private readonly AuthService $authService = new AuthService(),
        private readonly SchoolCatalogService $catalog = new SchoolCatalogService(),
        private readonly ClasseRepository $classeRepository = new ClasseRepository(),
        private readonly EvaluationRepository $evaluationRepository = new EvaluationRepository(),
        private readonly NoteRepository $noteRepository = new NoteRepository(),
    ) {
    }

    public function index(): void
    {
        $user = $this->authService->currentUser();
        if ($user === null) {
            $this->render('public/home', [
                'roleCode' => 'PUBLIC',
                'user' => [],
            ]);
            return;
        }

        $userData = [
            'id' => $user->id,
            'nom' => $user->nom,
            'prenom' => $user->prenom,
        ];
        $roleCode = $this->authService->currentRoleCode();

        if ($roleCode === 'ADMIN') {
            header('Location: /admin');
            return;
        }
        if ($roleCode === 'ELEVE') {
            header('Location: /mon-espace');
            return;
        }
        if ($roleCode === 'ELEVE') {
            header('Location: /mon-espace');
            return;
        }

        if ($roleCode === 'DIRECTEUR') {
            $this->render('directeur/dashboard', [
                'summary' => $this->catalog->getSummary(),
                'user' => $userData,
                'roleCode' => $roleCode,
            ]);
            return;
        }

        if ($roleCode === 'ENSEIGNANT') {
            $this->render('enseignant/dashboard', [
                'classes' => $this->classeRepository->findForTeacher($user->id),
                'evaluations' => $this->evaluationRepository->findForTeacher($user->id),
                'notes' => $this->noteRepository->findForTeacher($user->id),
                'user' => $userData,
                'roleCode' => $roleCode,
            ]);
            return;
        }

        header('Location: /login');
    }
}
