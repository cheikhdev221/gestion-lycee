<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Services\AuthService;
use App\Services\SchoolCatalogService;

final class AdminController extends Controller
{
    public function __construct(
        private readonly SchoolCatalogService $catalog = new SchoolCatalogService(),
        private readonly AuthService $authService = new AuthService(),
    ) {
    }

    public function dashboard(): void
    {
        $summary = $this->catalog->getSummary();

        $this->render('admin/dashboard', [
            'summary' => $summary,
            'user' => [
                'id' => $this->authService->currentUser()?->id,
                'nom' => $this->authService->currentUser()?->nom ?? '',
                'prenom' => $this->authService->currentUser()?->prenom ?? '',
            ],
            'roleCode' => 'ADMIN',
        ]);
    }
}
