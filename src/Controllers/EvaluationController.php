<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Evaluation;
use App\Repositories\EvaluationRepository;
use App\Repositories\SemestreRepository;
use App\Services\AuthService;

final class EvaluationController extends Controller
{
    public function __construct(
        private readonly EvaluationRepository $repository = new EvaluationRepository(),
        private readonly SemestreRepository $semesterRepository = new SemestreRepository(),
        private readonly AuthService $authService = new AuthService(),
    ) {
    }

    public function index(): void
    {
        $user = $this->authService->currentUser();
        $evaluations = $this->authService->currentRoleCode() === 'ENSEIGNANT' && $user !== null
            ? $this->repository->findForTeacher($user->id)
            : $this->repository->findAll();

        $this->render('evaluations/index', [
            'evaluations' => $evaluations,
            'roleCode' => $this->authService->currentRoleCode(),
        ]);
    }

    public function create(): void
    {
        $this->render('evaluations/create', [
            'error' => null,
            'success' => null,
        ] + $this->formOptions());
    }

    public function edit(): void
    {
        $evaluation = $this->repository->findById((int) ($_GET['id'] ?? 0));
        if ($evaluation === null) {
            http_response_code(404);
            echo 'Évaluation introuvable.';
            return;
        }
        $user = $this->authService->currentUser();
        if ($this->authService->currentRoleCode() === 'ENSEIGNANT'
            && ($user === null || !$this->repository->teacherOwnsAffectation($user->id, $evaluation->affectationId))) {
            http_response_code(403);
            echo 'Accès interdit.';
            return;
        }

        $this->render('evaluations/edit', ['evaluation' => $evaluation, 'error' => null] + $this->formOptions());
    }

    public function update(): void
    {
        $evaluation = $this->repository->findById((int) ($_POST['id'] ?? 0));
        if ($evaluation === null) {
            http_response_code(404);
            echo 'Évaluation introuvable.';
            return;
        }
        $user = $this->authService->currentUser();
        if ($this->authService->currentRoleCode() === 'ENSEIGNANT'
            && ($user === null || !$this->repository->teacherOwnsAffectation($user->id, $evaluation->affectationId))) {
            http_response_code(403);
            echo 'Accès interdit.';
            return;
        }

        $libelle = trim((string) ($_POST['libelle'] ?? ''));
        $type = strtoupper(trim((string) ($_POST['type'] ?? '')));
        $coefficient = (float) ($_POST['coefficient'] ?? 0);
        $dateEvaluation = trim((string) ($_POST['date_evaluation'] ?? ''));
        $dateValue = \DateTimeImmutable::createFromFormat('!Y-m-d', $dateEvaluation);
        if ($libelle === '' || !in_array($type, ['DEVOIR', 'COMPOSITION'], true)
            || !is_finite($coefficient) || $coefficient <= 0
            || $dateValue === false || $dateValue->format('Y-m-d') !== $dateEvaluation) {
            $this->render('evaluations/edit', ['evaluation' => $evaluation, 'error' => 'Les données de l’évaluation sont invalides.']);
            return;
        }

        $evaluation->libelle = $libelle;
        $evaluation->type = $type;
        $evaluation->coefficient = $coefficient;
        $evaluation->dateEvaluation = $dateEvaluation;
        $evaluation->description = trim((string) ($_POST['description'] ?? '')) ?: null;
        $this->repository->updateContent($evaluation);
        header('Location: /evaluations');
    }

    public function store(): void
    {
        $affectationId = (int) ($_POST['affectation_id'] ?? 0);
        $semestreId = (int) ($_POST['semestre_id'] ?? 0);
        $libelle = trim((string) ($_POST['libelle'] ?? ''));
        $type = strtoupper(trim((string) ($_POST['type'] ?? 'DEVOIR')));
        $coefficient = (float) ($_POST['coefficient'] ?? 1.0);
        $dateEvaluation = trim((string) ($_POST['date_evaluation'] ?? ''));

        if ($affectationId <= 0 || $semestreId <= 0 || $libelle === '' || $dateEvaluation === ''
            || !in_array($type, ['DEVOIR', 'COMPOSITION'], true) || !is_finite($coefficient) || $coefficient <= 0) {
            $this->render('evaluations/create', ['error' => 'Les informations de l’évaluation sont incomplètes ou invalides.', 'success' => null] + $this->formOptions());
            return;
        }

        $affectation = $this->repository->findAffectationContext($affectationId);
        if ($affectation === null || !$this->semesterRepository->belongsToSchoolYear($semestreId, (int) $affectation['annee_scolaire_id'])) {
            $this->render('evaluations/create', ['error' => 'Choisis une affectation et un semestre de la même année scolaire.', 'success' => null] + $this->formOptions());
            return;
        }

        $user = $this->authService->currentUser();
        if ($user !== null && $this->authService->currentRoleCode() === 'ENSEIGNANT'
            && !$this->repository->teacherOwnsAffectation($user->id, $affectationId)) {
            http_response_code(403);
            $this->render('evaluations/create', ['error' => 'Cette affectation ne vous appartient pas.', 'success' => null] + $this->formOptions());
            return;
        }

        try {
            $this->repository->create(new Evaluation(
                affectationId: $affectationId,
                semestreId: $semestreId,
                classeId: (int) $affectation['classe_id'],
                anneeScolaireId: (int) $affectation['annee_scolaire_id'],
                libelle: $libelle,
                type: $type,
                coefficient: $coefficient,
                dateEvaluation: $dateEvaluation,
            ));
            $this->render('evaluations/create', ['error' => null, 'success' => 'Évaluation créée.'] + $this->formOptions());
        } catch (\Throwable $e) {
            $this->render('evaluations/create', ['error' => 'L’évaluation n’a pas pu être créée. Vérifie les choix effectués.', 'success' => null] + $this->formOptions());
        }
    }

    private function formOptions(): array
    {
        $user = $this->authService->currentUser();
        $teacherId = $this->authService->currentRoleCode() === 'ENSEIGNANT' ? $user?->id : null;

        return [
            'affectations' => $this->repository->findAffectations($teacherId),
            'semestres' => $this->semesterRepository->findAll(),
        ];
    }
}
