<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Services\AuthService;
use App\Services\NavigationService;
use App\Services\StudentRegistrationService;

final class StudentRegistrationController extends Controller
{
    public function __construct(
        private readonly StudentRegistrationService $registrationService = new StudentRegistrationService(),
        private readonly AuthService $authService = new AuthService(),
        private readonly NavigationService $navigation = new NavigationService(),
    ) {
    }

    public function create(): void
    {
        $roleCode = $this->authService->currentRoleCode();
        if ($roleCode !== null) {
            header('Location: ' . $this->navigation->homePathFor($roleCode));
            return;
        }

        $this->render('student-registration/create', [
            'error' => null,
            'values' => [],
        ]);
    }

    public function store(): void
    {
        $roleCode = $this->authService->currentRoleCode();
        if ($roleCode !== null) {
            header('Location: ' . $this->navigation->homePathFor($roleCode));
            return;
        }

        $result = $this->registrationService->register($_POST, $_FILES['photo'] ?? []);
        if (!$result['success']) {
            $this->render('student-registration/create', [
                'error' => $result['message'],
                'values' => [
                    'nom' => trim((string) ($_POST['nom'] ?? '')),
                    'prenom' => trim((string) ($_POST['prenom'] ?? '')),
                    'email' => strtolower(trim((string) ($_POST['email'] ?? ''))),
                    'date_naissance' => trim((string) ($_POST['date_naissance'] ?? '')),
                ],
            ]);
            return;
        }

        header('Location: /login?inscription=success');
    }
}