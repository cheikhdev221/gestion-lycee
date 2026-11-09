<?php
$pageTitle = 'Vue d’ensemble';
$activeNav = 'overview';
$pendingEnrollments = count(array_filter($summary['inscriptions'], static fn ($item): bool => $item->statut === 'PENDING'));
$activeYear = null;
$activeYearId = null;
foreach ($summary['annees'] as $annee) {
    if ($annee->statut === 'EN_COURS') {
        $activeYear = $annee->libelle;
        $activeYearId = $annee->id;
        break;
    }
}
$activeClasses = count(array_filter($summary['classes'], static fn ($classe): bool => ($classe->anneeScolaireId ?? null) === $activeYearId));

ob_start();
?>
<section class="mb-3">
    <h1 class="school-heading mb-1">Vue d’ensemble</h1>
    <p class="small text-secondary mb-3">Les indicateurs essentiels pour votre lycée.</p>
</section>

<section class="row row-cols-2 row-cols-xl-4 g-2 g-md-3 mb-3" aria-label="Indicateurs de l’établissement">
    <article class="col">
        <div class="school-card h-100 p-3">
            <div class="school-metric-label">Élèves</div>
            <p class="school-metric-value my-2"><?= count($summary['eleves']) ?></p>
            <p class="school-metric-note mb-0"><?= $activeClasses ?> classes</p>
        </div>
    </article>
    <article class="col">
        <div class="school-card h-100 p-3">
            <div class="school-metric-label">Enseignants</div>
            <p class="school-metric-value my-2"><?= count($summary['enseignants']) ?></p>
            <p class="school-metric-note mb-0">Équipe pédagogique</p>
        </div>
    </article>
    <article class="col">
        <div class="school-card h-100 p-3">
            <div class="school-metric-label">Classes</div>
            <p class="school-metric-value my-2"><?= $activeClasses ?></p>
            <p class="school-metric-note mb-0">Année en cours</p>
        </div>
    </article>
    <article class="col">
        <div class="school-card h-100 p-3">
            <div class="school-metric-label">En attente</div>
            <p class="school-metric-value my-2"><?= $pendingEnrollments ?></p>
            <p class="school-metric-note mb-0">Inscriptions</p>
        </div>
    </article>
</section>

<section class="school-year-banner p-3 mb-3">
    <div class="small fw-semibold">Année scolaire <?= htmlspecialchars($activeYear ?? 'Non définie', ENT_QUOTES, 'UTF-8') ?></div>
    <div class="small mt-1">Année en cours</div>
</section>

<section class="school-priority p-3 mb-3">
    <h2 class="h6 fw-semibold mb-2">À traiter en priorité</h2>
    <p class="small mb-1"><strong><?= $pendingEnrollments ?> inscriptions en attente</strong></p>
    <p class="school-metric-note mb-3">Les dossiers peuvent être consultés et traités depuis les inscriptions.</p>
    <a class="btn btn-primary w-100" href="/inscriptions">Examiner les dossiers</a>
</section>

<section class="school-priority p-3 mb-3">
    <h2 class="h6 fw-semibold mb-3">Dernières inscriptions</h2>
    <?php if (empty($summary['recentInscriptions'])): ?>
        <p class="small text-secondary mb-0">Aucune inscription enregistrée.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table school-table align-middle mb-0">
                <thead><tr><th>Élève</th><th>Classe</th><th>Statut</th></tr></thead>
                <tbody>
                    <?php foreach ($summary['recentInscriptions'] as $inscription): ?>
                        <?php $status = ['PENDING' => 'En attente', 'PAYE' => 'Validée', 'ANNULEE' => 'Annulée'][$inscription['statut']] ?? $inscription['statut']; ?>
                        <tr>
                            <td><?= htmlspecialchars($inscription['prenom'] . ' ' . $inscription['nom'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($inscription['classe'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><span class="badge <?= $inscription['statut'] === 'PAYE' ? 'school-status-paid' : 'school-status-pending' ?>"><?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8') ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
    <a class="small link-primary d-inline-block mt-2" href="/inscriptions">Voir toutes les inscriptions →</a>
</section>

<section class="school-priority p-3">
    <h2 class="h6 fw-semibold mb-3">Accès rapides</h2>
    <a class="btn btn-primary w-100 mb-2" href="/inscriptions/create">Inscrire un élève</a>
    <a class="small link-primary" href="/eleves">Voir les élèves →</a>
</section>
<?php
$content = ob_get_clean();
require BASE_PATH . '/templates/layouts/app.php';
