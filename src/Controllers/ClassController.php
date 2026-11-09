<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Classe;
use App\Repositories\ClasseRepository;
use App\Repositories\AnneeScolaireRepository;
use App\Repositories\NiveauRepository;
use App\Repositories\SerieRepository;
use App\Services\AuthService;

final class ClassController extends Controller
{
    public function __construct(
        private readonly ClasseRepository $repository = new ClasseRepository(),
        private readonly NiveauRepository $niveauRepository = new NiveauRepository(),
        private readonly SerieRepository $serieRepository = new SerieRepository(),
        private readonly AnneeScolaireRepository $anneeRepository = new AnneeScolaireRepository(),
        private readonly AuthService $authService = new AuthService(),
    ) {
    }

    public function index(): void
    {
        $user = $this->authService->currentUser();
        $classes = $this->authService->currentRoleCode() === 'ENSEIGNANT' && $user !== null
            ? $this->repository->findForTeacher($user->id)
            : $this->repository->findAll();

        $this->render('classes/index', [
            'classes' => $classes,
            'roleCode' => $this->authService->currentRoleCode(),
        ]);
    }

    public function create(): void
    {
        $this->render('classes/create', [
            'error' => null,
            'success' => null,
        ] + $this->formOptions());
    }

    public function edit(): void
    {
        $classe = $this->repository->findById((int) ($_GET['id'] ?? 0));
        if ($classe === null) {
            http_response_code(404);
            echo 'Classe introuvable.';
            return;
        }

        $this->render('classes/edit', ['classe' => $classe, 'error' => null] + $this->formOptions());
    }

    public function update(): void
    {
        $classe = $this->repository->findById((int) ($_POST['id'] ?? 0));
        if ($classe === null) {
            http_response_code(404);
            echo 'Classe introuvable.';
            return;
        }

        $libelle = trim((string) ($_POST['libelle'] ?? ''));
        $niveauId = (int) ($_POST['niveau_id'] ?? 0);
        $serieId = (int) ($_POST['serie_id'] ?? 0);
        $anneeScolaireId = (int) ($_POST['annee_scolaire_id'] ?? 0);
        if ($libelle === '' || $niveauId <= 0 || $serieId <= 0 || $anneeScolaireId <= 0) {
            $this->render('classes/edit', ['classe' => $classe, 'error' => 'Les informations de la classe sont incomplètes.'] + $this->formOptions());
            return;
        }
        $structureChanged = $niveauId !== $classe->niveauId || $serieId !== $classe->serieId || $anneeScolaireId !== $classe->anneeScolaireId;
        if ($structureChanged && $this->repository->hasInscriptionsOrAffectations($classe->id)) {
            $this->render('classes/edit', ['classe' => $classe, 'error' => 'Le niveau, la série et l’année sont verrouillés dès que la classe est utilisée.'] + $this->formOptions());
            return;
        }

        $classe->libelle = $libelle;
        $classe->niveauId = $niveauId;
        $classe->serieId = $serieId;
        $classe->anneeScolaireId = $anneeScolaireId;
        try {
            $this->repository->update($classe);
            header('Location: /classes');
        } catch (\Throwable $exception) {
            $this->render('classes/edit', ['classe' => $classe, 'error' => 'La modification a échoué. Vérifie la cohérence du niveau, de la série et de l’année.'] + $this->formOptions());
        }
    }

    public function store(): void
    {
        $libelle = trim((string) ($_POST['libelle'] ?? ''));
        $niveauId = (int) ($_POST['niveau_id'] ?? 0);
        $serieId = (int) ($_POST['serie_id'] ?? 0);
        $anneeScolaireId = (int) ($_POST['annee_scolaire_id'] ?? 0);

        if ($libelle === '' || $niveauId <= 0 || $serieId <= 0 || $anneeScolaireId <= 0) {
            $this->render('classes/create', ['error' => 'Les informations de la classe sont incomplètes.', 'success' => null] + $this->formOptions());
            return;
        }

        try {
            $this->repository->create(new Classe(
                libelle: $libelle,
                niveauId: $niveauId,
                serieId: $serieId,
                anneeScolaireId: $anneeScolaireId,
            ));
            $this->render('classes/create', ['error' => null, 'success' => 'Classe créée.'] + $this->formOptions());
        } catch (\Throwable $e) {
            $this->render('classes/create', ['error' => 'La classe n’a pas pu être créée. Vérifie les choix effectués.', 'success' => null] + $this->formOptions());
        }
    }

    private function formOptions(): array
    {
        return [
            'niveaux' => $this->niveauRepository->findAll(),
            'series' => $this->serieRepository->findAll(),
            'annees' => $this->anneeRepository->findAll(),
        ];
    }
}
