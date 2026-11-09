<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Eleve;
use App\Repositories\EleveRepository;
use App\Repositories\UserRepository;
use App\Services\AuthService;

final class StudentController extends Controller
{
    public function __construct(
        private readonly EleveRepository $repository = new EleveRepository(),
        private readonly UserRepository $userRepository = new UserRepository(),
        private readonly AuthService $authService = new AuthService(),
    ) {
    }

    public function index(): void
    {
        $user = $this->authService->currentUser();
        $eleves = $this->authService->currentRoleCode() === 'ENSEIGNANT' && $user !== null
            ? $this->repository->findForTeacher($user->id)
            : $this->repository->findAll();

        $this->render('students/index', [
            'eleves' => $eleves,
            'roleCode' => $this->authService->currentRoleCode(),
        ]);
    }

    public function create(): void
    {
        $this->render('students/create', [
            'error' => null,
            'success' => null,
            'utilisateurs' => $this->userRepository->findStudentsWithoutProfile(),
        ]);
    }

    public function edit(): void
    {
        $eleve = $this->repository->findById((int) ($_GET['id'] ?? 0));
        if ($eleve === null) {
            http_response_code(404);
            echo 'Élève introuvable.';
            return;
        }

        $this->render('students/edit', ['eleve' => $eleve, 'error' => null]);
    }

    public function update(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        $eleve = $this->repository->findById($id);
        if ($eleve === null) {
            http_response_code(404);
            echo 'Élève introuvable.';
            return;
        }

        $matricule = strtoupper(trim((string) ($_POST['matricule'] ?? '')));
        $dateNaissance = trim((string) ($_POST['date_naissance'] ?? ''));
        $dateValue = \DateTimeImmutable::createFromFormat('!Y-m-d', $dateNaissance);
        if (!preg_match('/^LYC-[0-9]{4}-[0-9]{5}$/', $matricule)
            || $dateValue === false || $dateValue->format('Y-m-d') !== $dateNaissance) {
            $this->render('students/edit', ['eleve' => $eleve, 'error' => 'Matricule ou date de naissance invalide.']);
            return;
        }

        $eleve->matricule = $matricule;
        $eleve->dateNaissance = $dateNaissance;
        try {
            $this->repository->update($eleve);
            header('Location: /eleves');
        } catch (\Throwable $exception) {
            $this->render('students/edit', ['eleve' => $eleve, 'error' => 'La modification a échoué. Vérifie que le matricule n’est pas déjà utilisé.']);
        }
    }

    public function store(): void
    {
        $utilisateurId = (int) ($_POST['utilisateur_id'] ?? 0);
        $matricule = trim((string) ($_POST['matricule'] ?? ''));
        $dateNaissance = trim((string) ($_POST['date_naissance'] ?? ''));

        if ($utilisateurId <= 0 || $matricule === '' || $dateNaissance === '') {
            $this->render('students/create', ['error' => 'Les informations de l’élève sont incomplètes.', 'success' => null, 'utilisateurs' => $this->userRepository->findStudentsWithoutProfile()]);
            return;
        }

        try {
            $this->repository->create(new Eleve(
                utilisateurId: $utilisateurId,
                matricule: $matricule,
                dateNaissance: $dateNaissance,
            ));
            $this->render('students/create', ['error' => null, 'success' => 'Élève créé.', 'utilisateurs' => $this->userRepository->findStudentsWithoutProfile()]);
        } catch (\Throwable $e) {
            $this->render('students/create', ['error' => 'La fiche élève n’a pas pu être créée. Vérifie le compte et le matricule.', 'success' => null, 'utilisateurs' => $this->userRepository->findStudentsWithoutProfile()]);
        }
    }
}
