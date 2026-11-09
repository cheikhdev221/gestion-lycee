<?php
$pageTitle = 'Vue d’ensemble';
$activeNav = 'overview';
ob_start();
?>
<section class="mb-3">
    <h1 class="school-heading mb-1">Bonjour<?= !empty($user['prenom']) ? ', ' . htmlspecialchars($user['prenom'], ENT_QUOTES, 'UTF-8') : '' ?></h1>
    <p class="small text-secondary mb-0">Vos classes, évaluations et résultats.</p>
</section>
<section class="row row-cols-2 row-cols-xl-3 g-2 g-md-3 mb-3">
    <div class="col"><article class="school-card h-100 p-3"><div class="school-metric-label">Mes classes</div><div class="school-metric-value my-2"><?= count($classes) ?></div><div class="school-metric-note">Affectations actives</div></article></div>
    <div class="col"><article class="school-card h-100 p-3"><div class="school-metric-label">Évaluations</div><div class="school-metric-value my-2"><?= count($evaluations) ?></div><div class="school-metric-note">Mes évaluations</div></article></div>
    <div class="col"><article class="school-card h-100 p-3"><div class="school-metric-label">Notes saisies</div><div class="school-metric-value my-2"><?= count($notes) ?></div><div class="school-metric-note">Dans mes classes</div></article></div>
</section>
<section class="school-priority p-3 mb-3">
    <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
        <div><h2 class="h6 fw-semibold mb-1">Mes classes</h2><p class="school-metric-note mb-0">Classes et années qui vous sont affectées.</p></div>
        <a class="small link-primary" href="/classes">Tout voir</a>
    </div>
    <?php if (empty($classes)): ?>
        <p class="small text-secondary mb-0">Aucune classe ne vous est affectée pour le moment.</p>
    <?php else: ?>
        <div class="row row-cols-1 row-cols-sm-2 g-2">
            <?php foreach ($classes as $classe): ?>
                <div class="col"><div class="border rounded-2 p-3 h-100"><div class="fw-semibold"><?= htmlspecialchars($classe->libelle, ENT_QUOTES, 'UTF-8') ?></div><div class="school-metric-note mt-1">Année scolaire #<?= (int) $classe->anneeScolaireId ?></div></div></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
<div class="row g-2 g-md-3">
    <section class="col-12 col-md-6"><div class="school-priority h-100 p-3"><h2 class="h6 fw-semibold mb-2">Évaluations</h2><p class="school-metric-note">Créer une évaluation pour une de vos affectations.</p><a class="btn btn-primary w-100" href="/evaluations/create">Créer une évaluation</a></div></section>
    <section class="col-12 col-md-6"><div class="school-priority h-100 p-3"><h2 class="h6 fw-semibold mb-2">Saisie des notes</h2><p class="school-metric-note">Consulter vos évaluations et compléter les résultats.</p><a class="btn btn-outline-primary w-100" href="/notes">Voir mes notes</a></div></section>
</div>
<?php
$content = ob_get_clean();
require BASE_PATH . '/templates/layouts/app.php';
