<?php
declare(strict_types=1);

namespace App\Core;

use App\Services\AuthService;
use App\Services\NavigationService;

abstract class Controller
{
    protected function render(string $template, array $data = []): void
    {
        $data['csrfToken'] = CsrfToken::get();
        $authService = new AuthService();
        $authenticatedUser = $authService->currentUser();
        $data['roleCode'] = $data['roleCode'] ?? $authService->currentRoleCode() ?? 'ADMIN';
        $data['user'] = $data['user'] ?? ($authenticatedUser === null ? [] : [
            'id' => $authenticatedUser->id,
            'nom' => $authenticatedUser->nom,
            'prenom' => $authenticatedUser->prenom,
        ]);
        extract($data, EXTR_SKIP);

        ob_start();
        require BASE_PATH . '/templates/' . $template . '.php';
        $html = (string) ob_get_clean();

        if (in_array($template, ['login', 'public/home', 'student-registration/create', 'admin/dashboard', 'directeur/dashboard', 'enseignant/dashboard', 'student-portal/index'], true)) {
            echo $html;
            return;
        }

        echo $this->wrapLegacyTemplate($html, (string) $roleCode, (array) $user);
    }

    private function wrapLegacyTemplate(string $html, string $roleCode, array $user): string
    {
        if (!str_contains(strtolower($html), '</head>') || !str_contains(strtolower($html), '<body')) {
            return $html;
        }

        $styles = '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">'
            . '<link rel="stylesheet" href="/assets/css/app.css">';
        $html = preg_replace('~</head>~i', $styles . '</head>', $html, 1) ?? $html;

        $navigationService = new NavigationService();
        $roleCode = strtoupper($roleCode);
        $items = $navigationService->itemsFor($roleCode);
        $currentPath = (string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $fullName = trim(($user['prenom'] ?? '') . ' ' . ($user['nom'] ?? ''));
        $initials = strtoupper(substr((string) ($user['prenom'] ?? 'A'), 0, 1) . substr((string) ($user['nom'] ?? 'D'), 0, 1));
        $links = '';

        foreach ($items as $item) {
            $active = $currentPath === $item['href'] || ($item['href'] !== '/' && str_starts_with($currentPath, $item['href'] . '/'));
            $links .= '<li class="nav-item"><a class="nav-link ' . ($active ? 'active' : '') . '" href="'
                . htmlspecialchars($item['href'], ENT_QUOTES, 'UTF-8') . '"' . ($active ? ' aria-current="page"' : '') . '>'
                . htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') . '</a></li>';
        }

        $homePath = htmlspecialchars($navigationService->homePathFor($roleCode), ENT_QUOTES, 'UTF-8');
        $nav = '<nav class="navbar navbar-expand-xxl navbar-dark bg-dark px-3 px-xl-4"><div class="container-fluid">'
            . '<button class="navbar-toggler me-2 order-first d-xxl-none" type="button" data-bs-toggle="collapse" data-bs-target="#primaryNavigation" aria-controls="primaryNavigation" aria-expanded="false" aria-label="Ouvrir la navigation"><span class="navbar-toggler-icon"></span></button>'
            . '<a class="navbar-brand me-auto me-xxl-3" href="' . $homePath . '">Gestion Lycée</a>'
            . '<div class="collapse navbar-collapse" id="primaryNavigation"><ul class="navbar-nav me-auto mb-2 mb-xxl-0 gap-xxl-1">' . $links . '</ul>'
            . '<div class="d-flex align-items-center gap-2 pb-2 pb-xxl-0"><span class="badge rounded-pill text-bg-light">'
            . htmlspecialchars($initials, ENT_QUOTES, 'UTF-8') . '</span><span class="small text-white text-nowrap">'
            . htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8') . '</span><form method="post" action="/logout">'
            . '<input type="hidden" name="_csrf" value="' . htmlspecialchars(CsrfToken::get(), ENT_QUOTES, 'UTF-8') . '">'
            . '<button class="btn btn-outline-light btn-sm" type="submit">Quitter</button></form></div></div></div></nav>';

        $html = preg_replace_callback('~<body([^>]*)>~i', static fn (array $matches): string => $matches[0] . $nav . '<main class="container-fluid dashboard-content legacy-page px-3 px-xl-4 py-4 py-xl-5">', $html, 1) ?? $html;
        $footerScripts = '</main><script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>';

        return preg_replace('~</body>~i', $footerScripts . '</body>', $html, 1) ?? $html;
    }
}
