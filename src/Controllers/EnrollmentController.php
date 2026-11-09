<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Inscription;
use App\Repositories\AnneeScolaireRepository;
use App\Repositories\ClasseRepository;
use App\Repositories\EleveRepository;
use App\Repositories\InscriptionRepository;

final class EnrollmentController extends Controller
{
    public function __construct(
        private readonly InscriptionRepository $repository = new InscriptionRepository(),
        private readonly EleveRepository $eleveRepository = new EleveRepository(),
        private readonly ClasseRepository $classeRepository = new ClasseRepository(),
        private readonly AnneeScolaireRepository $anneeRepository = new AnneeScolaireRepository(),
    ) {
    }

    public function index(): void
    {
        $this->render('enrollments/index', [
            'inscriptions' => $this->repository->findAll(),
        ]);
    }

    public function create(): void
    {
        $this->render('enrollments/create', [
            'error' => null,
            'success' => null,
        ] + $this->formOptions());
    }

    public function edit(): void
    {
        $inscription = $this->repository->findById((int) ($_GET['id'] ?? 0));
        if ($inscription === null) {
            http_response_code(404);
            echo 'Inscription introuvable.';
            return;
        }
        if ($inscription->statut !== 'PENDING') {
            http_response_code(409);
            echo 'Seules les inscriptions en attente peuvent être modifiées.';
            return;
        }

        $this->render('enrollments/edit', ['inscription' => $inscription, 'error' => null] + $this->formOptions());
    }

    public function update(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        $inscription = $this->repository->findById($id);
        $classeId = (int) ($_POST['classe_id'] ?? 0);
        $anneeScolaireId = (int) ($_POST['annee_scolaire_id'] ?? 0);
        if ($inscription === null) {
            http_response_code(404);
            echo 'Inscription introuvable.';
            return;
        }
        if ($classeId <= 0 || $anneeScolaireId <= 0) {
            $this->render('enrollments/edit', ['inscription' => $inscription, 'error' => 'Classe ou année scolaire invalide.'] + $this->formOptions());
            return;
        }
        if ($this->repository->updatePending($id, $classeId, $anneeScolaireId) === null) {
            http_response_code(409);
            $this->render('enrollments/edit', ['inscription' => $inscription, 'error' => 'Seules les inscriptions en attente peuvent être modifiées.'] + $this->formOptions());
            return;
        }

        header('Location: /inscriptions');
    }

    public function cancel(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        if (!$this->repository->cancelPending($id)) {
            http_response_code(409);
        }
        header('Location: /inscriptions');
    }

    public function store(): void
    {
        $eleveId = (int) ($_POST['eleve_id'] ?? 0);
        $classeId = (int) ($_POST['classe_id'] ?? 0);
        $anneeScolaireId = (int) ($_POST['annee_scolaire_id'] ?? 0);

        if ($eleveId <= 0 || $classeId <= 0 || $anneeScolaireId <= 0) {
            $this->render('enrollments/create', ['error' => 'L’inscription est incomplète.', 'success' => null] + $this->formOptions());
            return;
        }

        try {
            $this->repository->create(new Inscription(
                eleveId: $eleveId,
                classeId: $classeId,
                anneeScolaireId: $anneeScolaireId,
                statut: 'PENDING',
            ));
            $this->render('enrollments/create', ['error' => null, 'success' => 'Inscription enregistrée.'] + $this->formOptions());
        } catch (\Throwable $e) {
            $this->render('enrollments/create', ['error' => 'L’inscription n’a pas pu être créée. Vérifie l’élève, la classe et l’année.', 'success' => null] + $this->formOptions());
        }
    }

    private function formOptions(): array
    {
        return [
            'eleves' => $this->eleveRepository->findAll(),
            'classes' => $this->classeRepository->findAll(),
            'annees' => $this->anneeRepository->findAll(),
        ];
    }
}
