<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Repositories\EleveRepository;
use App\Services\AuthService;
use App\Services\StudentPhotoStorage;

final class StudentPhotoController extends Controller
{
    public function __construct(
        private readonly AuthService $authService = new AuthService(),
        private readonly EleveRepository $eleveRepository = new EleveRepository(),
        private readonly StudentPhotoStorage $photoStorage = new StudentPhotoStorage(),
    ) {
    }

    public function showMine(): void
    {
        $user = $this->authService->currentUser();
        if ($user === null || $this->authService->currentRoleCode() !== 'ELEVE') {
            http_response_code(403);
            return;
        }

        $filename = $this->eleveRepository->findPhotoPathByUserId($user->id);
        $path = $this->photoStorage->resolve($filename);
        if ($path === null) {
            http_response_code(404);
            return;
        }

        header('Content-Type: ' . $this->photoStorage->mimeType((string) $filename));
        header('Content-Length: ' . (string) filesize($path));
        header('Content-Disposition: inline; filename="photo-identite.' . pathinfo((string) $filename, PATHINFO_EXTENSION) . '"');
        header('Cache-Control: private, no-store');
        header('X-Content-Type-Options: nosniff');
        readfile($path);
    }
}