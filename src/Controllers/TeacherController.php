<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Enseignant;
use App\Repositories\EnseignantRepository;
use App\Repositories\MatiereRepository;
use App\Repositories\UserRepository;

final class TeacherController extends Controller
{
    public function __construct(
        private readonly EnseignantRepository $repository = new EnseignantRepository(),
        private readonly UserRepository $userRepository = new UserRepository(),
        private readonly MatiereRepository $matiereRepository = new MatiereRepository(),
    ) {
    }

    public function index(): void
    {
        $this->render('teachers/index', [
            'enseignants' => $this->repository->findAll(),
        ]);
    }

    public function create(): void
    {
        $this->render('teachers/create', [
            'error' => null,
            'success' => null,
            'utilisateurs' => $this->userRepository->findTeachersWithoutProfile(),
            'matieres' => $this->matiereRepository->findAll(),
        ]);
    }

    public function edit(): void
    {
        $enseignant = $this->repository->findById((int) ($_GET['id'] ?? 0));
        if ($enseignant === null) {
            http_response_code(404);
            echo 'Enseignant introuvable.';
            return;
        }

        $this->render('teachers/edit', ['enseignant' => $enseignant, 'matieres' => $this->matiereRepository->findAll(), 'error' => null]);
    }

    public function update(): void
    {
        $enseignant = $this->repository->findById((int) ($_POST['id'] ?? 0));
        if ($enseignant === null) {
            http_response_code(404);
            echo 'Enseignant introuvable.';
            return;
        }

        $matiereId = (int) ($_POST['matiere_id'] ?? 0);
        if ($matiereId <= 0) {
            $this->render('teachers/edit', ['enseignant' => $enseignant, 'matieres' => $this->matiereRepository->findAll(), 'error' => 'La matière sélectionnée est invalide.']);
            return;
        }
        if ($matiereId !== $enseignant->matiereId && $this->repository->hasAffectations($enseignant->id)) {
            $this->render('teachers/edit', ['enseignant' => $enseignant, 'matieres' => $this->matiereRepository->findAll(), 'error' => 'La matière ne peut pas être modifiée après la création d’affectations.']);
            return;
        }

        $enseignant->matiereId = $matiereId;
        try {
            $this->repository->update($enseignant);
            header('Location: /enseignants');
        } catch (\Throwable $exception) {
            $this->render('teachers/edit', ['enseignant' => $enseignant, 'matieres' => $this->matiereRepository->findAll(), 'error' => 'La modification a échoué. Vérifie la matière sélectionnée.']);
        }
    }

    public function store(): void
    {
        $utilisateurId = (int) ($_POST['utilisateur_id'] ?? 0);
        $matiereId = (int) ($_POST['matiere_id'] ?? 0);

        if ($utilisateurId <= 0 || $matiereId <= 0) {
            $this->render('teachers/create', ['error' => 'Le profil enseignant est incomplet.', 'success' => null, 'utilisateurs' => $this->userRepository->findTeachersWithoutProfile(), 'matieres' => $this->matiereRepository->findAll()]);
            return;
        }

        try {
            $this->repository->create(new Enseignant(
                utilisateurId: $utilisateurId,
                matiereId: $matiereId,
            ));
            $this->render('teachers/create', ['error' => null, 'success' => 'Enseignant créé.', 'utilisateurs' => $this->userRepository->findTeachersWithoutProfile(), 'matieres' => $this->matiereRepository->findAll()]);
        } catch (\Throwable $e) {
            $this->render('teachers/create', ['error' => 'Le profil enseignant n’a pas pu être créé. Vérifie le compte et la matière.', 'success' => null, 'utilisateurs' => $this->userRepository->findTeachersWithoutProfile(), 'matieres' => $this->matiereRepository->findAll()]);
        }
    }
}
