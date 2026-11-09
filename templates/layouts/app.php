<?php
$roleCode = strtoupper($roleCode ?? 'ADMIN');
$navigationService = new \App\Services\NavigationService();
$navigation = $navigationService->itemsFor($roleCode);
$roleLabel = $navigationService->labelFor($roleCode);
$homeHref = $navigationService->homePathFor($roleCode);
$fullName = trim(($user['prenom'] ?? '') . ' ' . ($user['nom'] ?? ''));
$initials = strtoupper(substr((string) ($user['prenom'] ?? 'A'), 0, 1) . substr((string) ($user['nom'] ?? 'D'), 0, 1));
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($pageTitle . ' · Gestion Lycée', ENT_QUOTES, 'UTF-8') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body>
    <nav class="navbar navbar-expand-xxl navbar-dark bg-dark px-3 px-xl-4">
        <div class="container-fluid">
            <button class="navbar-toggler me-2 order-first d-xxl-none" type="button" data-bs-toggle="collapse" data-bs-target="#primaryNavigation" aria-controls="primaryNavigation" aria-expanded="false" aria-label="Ouvrir la navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <a class="navbar-brand me-auto me-xxl-3" href="<?= htmlspecialchars($homeHref, ENT_QUOTES, 'UTF-8') ?>">Gestion Lycée</a>
            <div class="collapse navbar-collapse" id="primaryNavigation">
                <ul class="navbar-nav me-auto mb-2 mb-xxl-0 gap-xxl-1">
                    <?php foreach ($navigation as $item): ?>
                        <li class="nav-item"><a class="nav-link <?= $activeNav === $item['key'] ? 'active' : '' ?>" href="<?= htmlspecialchars($item['href'], ENT_QUOTES, 'UTF-8') ?>" <?= $activeNav === $item['key'] ? 'aria-current="page"' : '' ?>><?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') ?></a></li>
                    <?php endforeach; ?>
                </ul>
                <div class="d-flex align-items-center gap-2 pb-2 pb-xxl-0">
                    <span class="badge rounded-pill text-bg-light"><?= htmlspecialchars($initials, ENT_QUOTES, 'UTF-8') ?></span>
                    <span class="small text-white text-nowrap"><?= htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8') ?></span>
                    <form method="post" action="/logout">
                        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                        <button class="btn btn-outline-light btn-sm text-nowrap" type="submit">Quitter</button>
                    </form>
                </div>
            </div>
        </div>
    </nav>
    <main class="container-fluid dashboard-content px-3 px-xl-4 py-4 py-xl-5">
        <div class="school-meta mb-3"><?= htmlspecialchars($roleLabel, ENT_QUOTES, 'UTF-8') ?> · <?= date('d/m/Y') ?></div>
        <?= $content ?>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
