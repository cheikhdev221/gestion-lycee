<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Note;
use App\Repositories\EleveRepository;
use App\Repositories\EvaluationRepository;
use App\Repositories\NoteRepository;
use App\Services\AuthService;

final class NoteController extends Controller
{
    public function __construct(
        private readonly NoteRepository $repository = new NoteRepository(),
        private readonly EvaluationRepository $evaluationRepository = new EvaluationRepository(),
        private readonly EleveRepository $eleveRepository = new EleveRepository(),
        private readonly AuthService $authService = new AuthService(),
    ) {
    }

    public function index(): void
    {
        $user = $this->authService->currentUser();
        $notes = $user !== null && $this->authService->currentRoleCode() === 'ENSEIGNANT'
            ? $this->repository->findForTeacher($user->id)
            : $this->repository->findAll();

        $this->render('notes/index', [
            'notes' => $notes,
            'roleCode' => $this->authService->currentRoleCode(),
        ]);
    }

    public function create(): void
    {
        $this->render('notes/create', [
            'error' => null,
            'success' => null,
        ] + $this->formOptions());
    }

    public function edit(): void
    {
        $note = $this->repository->findById((int) ($_GET['id'] ?? 0));
        $user = $this->authService->currentUser();
        if ($note === null) {
            http_response_code(404);
            echo 'Note introuvable.';
            return;
        }
        if ($user === null || !$this->repository->teacherOwnsEvaluation($user->id, $note->evaluationId)) {
            http_response_code(403);
            echo 'Accès interdit.';
            return;
        }

        $this->render('notes/edit', ['note' => $note, 'error' => null]);
    }

    public function update(): void
    {
        $user = $this->authService->currentUser();
        $noteId = (int) ($_POST['id'] ?? 0);
        $valeur = filter_var($_POST['valeur'] ?? null, FILTER_VALIDATE_FLOAT);
        if ($user === null || $valeur === false || !is_finite((float) $valeur) || $valeur < 0 || $valeur > 20) {
            http_response_code(400);
            $note = $this->repository->findById($noteId);
            if ($note !== null) {
                $this->render('notes/edit', ['note' => $note, 'error' => 'La note doit être comprise entre 0 et 20.']);
            }
            return;
        }

        $commentaire = trim((string) ($_POST['commentaire'] ?? ''));
        $updated = $this->repository->updateByTeacher($noteId, $user->id, (float) $valeur, $commentaire === '' ? null : $commentaire);
        if ($updated === null) {
            http_response_code(403);
            echo 'Cette note n’est pas rattachée à l’une de vos évaluations.';
            return;
        }

        header('Location: /notes');
    }

    public function store(): void
    {
        $user = $this->authService->currentUser();
        if ($user === null) {
            http_response_code(401);
            $this->render('notes/create', ['error' => 'Vous devez être connecté pour saisir une note.', 'success' => null] + $this->formOptions());
            return;
        }

        $evaluationId = (int) ($_POST['evaluation_id'] ?? 0);
        $eleveId = (int) ($_POST['eleve_id'] ?? 0);
        $valeur = filter_var($_POST['valeur'] ?? null, FILTER_VALIDATE_FLOAT);

        if ($evaluationId <= 0 || $eleveId <= 0 || $valeur === false || !is_finite((float) $valeur) || $valeur < 0 || $valeur > 20) {
            $this->render('notes/create', ['error' => 'Choisis une évaluation, un élève et une note comprise entre 0 et 20.', 'success' => null] + $this->formOptions());
            return;
        }

        $evaluationContext = $this->evaluationRepository->findContext($evaluationId);
        if ($evaluationContext === null) {
            $this->render('notes/create', ['error' => 'Cette évaluation est introuvable.', 'success' => null] + $this->formOptions());
            return;
        }

        if ($this->authService->currentRoleCode() === 'ENSEIGNANT'
            && !$this->repository->teacherOwnsEvaluation($user->id, $evaluationId)) {
            http_response_code(403);
            $this->render('notes/create', ['error' => 'Cette évaluation ne vous appartient pas.', 'success' => null] + $this->formOptions());
            return;
        }

        try {
            $this->repository->create(new Note(
                evaluationId: $evaluationId,
                eleveId: $eleveId,
                classeId: (int) $evaluationContext['classe_id'],
                anneeScolaireId: (int) $evaluationContext['annee_scolaire_id'],
                valeur: (float) $valeur,
                commentaire: trim((string) ($_POST['commentaire'] ?? '')),
                saisiPar: $user->id,
            ));
            $this->render('notes/create', ['error' => null, 'success' => 'Note enregistrée.'] + $this->formOptions());
        } catch (\Throwable $e) {
            $this->render('notes/create', ['error' => 'La note n’a pas pu être enregistrée. Vérifie que l’élève est inscrit dans cette classe et cette année.', 'success' => null] + $this->formOptions());
        }
    }

    private function formOptions(): array
    {
        $user = $this->authService->currentUser();
        $teacherId = $this->authService->currentRoleCode() === 'ENSEIGNANT' ? $user?->id : null;

        return [
            'evaluations' => $this->evaluationRepository->findForNoteForm($teacherId),
            'eleves' => $this->eleveRepository->findAllWithUserDetails(),
        ];
    }
}
