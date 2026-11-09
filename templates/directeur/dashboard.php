<?php
$pageTitle = 'Vue d’ensemble';
$activeNav = 'overview';
$pendingEnrollments = count(array_filter($summary['inscriptions'], static fn ($item): bool => $item->statut === 'PENDING'));
$eligiblePromotions = count(array_filter($summary['inscriptions'], static fn ($item): bool => $item->statut === 'PAYE'));
ob_start();
?>
<section class="mb-3">
    <h1 class="school-heading mb-1">Bonjour<?= !empty($user['prenom']) ? ', ' . htmlspecialchars($user['prenom'], ENT_QUOTES, 'UTF-8') : '' ?></h1>
    <p class="small text-secondary mb-0">Suivi pédagogique et administratif de l’établissement.</p>
</section>
<section class="row row-cols-2 row-cols-xl-4 g-2 g-md-3 mb-3">
    <div class="col"><article class="school-card h-100 p-3"><div class="school-metric-label">Élèves</div><div class="school-metric-value my-2"><?= count($summary['eleves']) ?></div><div class="school-metric-note">Dossiers scolaires</div></article></div>
    <div class="col"><article class="school-card h-100 p-3"><div class="school-metric-label">Enseignants</div><div class="school-metric-value my-2"><?= count($summary['enseignants']) ?></div><div class="school-metric-note">Équipe pédagogique</div></article></div>
    <div class="col"><article class="school-card h-100 p-3"><div class="school-metric-label">Classes</div><div class="school-metric-value my-2"><?= count($summary['classes']) ?></div><div class="school-metric-note">Toutes années</div></article></div>
    <div class="col"><article class="school-card h-100 p-3"><div class="school-metric-label">À traiter</div><div class="school-metric-value my-2"><?= $pendingEnrollments ?></div><div class="school-metric-note">Inscriptions en attente</div></article></div>
</section>
<section class="school-priority p-3 mb-3">
    <h2 class="h6 fw-semibold mb-2">Décisions de fin d’année</h2>
    <p class="small text-secondary mb-3"><?= $eligiblePromotions ?> inscriptions payées pouvant être examinées pour un passage de classe.</p>
    <a class="btn btn-primary w-100" href="/passages">Examiner les passages</a>
</section>
<div class="row g-2 g-md-3">
    <section class="col-12 col-md-6"><div class="school-priority h-100 p-3"><h2 class="h6 fw-semibold mb-3">Équipe et élèves</h2><a class="btn btn-outline-primary w-100 mb-2" href="/eleves">Consulter les élèves</a><a class="btn btn-outline-primary w-100" href="/enseignants">Consulter les enseignants</a></div></section>
    <section class="col-12 col-md-6"><div class="school-priority h-100 p-3"><h2 class="h6 fw-semibold mb-3">Suivi des inscriptions</h2><p class="small text-secondary"><?= count($summary['inscriptions']) ?> inscriptions enregistrées.</p><a class="btn btn-primary w-100" href="/inscriptions">Ouvrir les inscriptions</a></div></section>
</div>
<?php
$content = ob_get_clean();
require BASE_PATH . '/templates/layouts/app.php';
