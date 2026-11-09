<?php
$pageTitle = 'Mon espace';
$activeNav = 'student-portal';
ob_start();
?>
<section class="mb-3">
    <h1 class="school-heading mb-1">Mon espace</h1>
    <p class="small text-secondary mb-0">Mes informations scolaires et mes résultats.</p>
</section>
<?php if (!empty($enrollmentSuccess)): ?><div class="alert alert-success" role="status">Ta demande d’inscription et le paiement de 25 000 XOF ont été enregistrés. Le secrétariat confirmera le règlement.</div><?php endif; ?>
<?php if (!empty($paymentInitiated)): ?><div class="alert alert-info" role="status">Le paiement apparaît comme initié. Le paiement Wave en ligne n’est pas encore connecté : règle les frais auprès du secrétariat, qui mettra ensuite ton dossier à jour.</div><?php endif; ?>
<?php if ($portal === null): ?>
    <div class="alert alert-info" role="status">Aucune fiche élève n’est associée à ce compte. Contactez le secrétariat.</div>
<?php else: ?>
    <?php $profile = $portal['profile']; ?>
    <section class="school-priority p-3 mb-3">
        <p class="small text-secondary text-uppercase fw-semibold mb-1">Profil élève</p>
        <?php if (!empty($profile['photo_path'])): ?><img class="rounded-circle border mb-3" src="/mon-espace/photo" alt="Ma photo d’identité" width="88" height="88" style="object-fit: cover"><?php endif; ?>
        <h2 class="h5 fw-bold mb-2"><?= htmlspecialchars($profile['prenom'] . ' ' . $profile['nom'], ENT_QUOTES, 'UTF-8') ?></h2>
        <div class="d-flex flex-wrap gap-3 small text-secondary">
            <span>Matricule : <?= htmlspecialchars($profile['matricule'], ENT_QUOTES, 'UTF-8') ?></span>
            <span><?= htmlspecialchars($profile['email'], ENT_QUOTES, 'UTF-8') ?></span>
        </div>
    </section>

    <section class="school-priority p-3 mb-3">
        <div class="d-flex justify-content-between align-items-center mb-2"><h2 class="h6 fw-semibold mb-0">Mes inscriptions</h2><span class="badge text-bg-primary"><?= count($portal['inscriptions']) ?></span></div>
        <?php if (empty($portal['inscriptions'])): ?>
            <p class="small text-secondary mb-0">Aucune inscription enregistrée.</p>
        <?php else: ?>
            <div class="table-responsive"><table class="table school-table align-middle mb-0">
                <thead><tr><th>Année</th><th>Classe</th><th>Inscription</th><th>Frais</th><th>Paiement</th></tr></thead>
                <tbody><?php foreach ($portal['inscriptions'] as $inscription): ?>
                    <tr>
                        <td><?= htmlspecialchars($inscription['annee_scolaire'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($inscription['classe'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($inscription['statut'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= $inscription['paiement_montant'] !== null ? number_format((float) $inscription['paiement_montant'], 0, ',', ' ') . ' ' . htmlspecialchars((string) $inscription['paiement_devise'], ENT_QUOTES, 'UTF-8') : '25 000 XOF à régler' ?></td>
                        <td><?= !empty($inscription['paiement_statut']) ? htmlspecialchars($inscription['paiement_statut'], ENT_QUOTES, 'UTF-8') : 'À initier' ?></td>
                    </tr>
                <?php endforeach; ?></tbody>
            </table></div>
        <?php endif; ?>
    </section>

    <section class="school-year-banner p-3 mb-3">
        <h2 class="h6 fw-semibold mb-1">Inscription scolaire et pédagogique</h2>
        <p class="small mb-2">Frais fixes : <strong>25 000 XOF</strong>. La demande crée le suivi du paiement dans ton espace; le secrétariat confirme le règlement.</p>
        <?php if (!empty($portal['availableClasses'])): ?>
            <p class="small mb-3">Choisis une classe pour une année ouverte. Le secrétariat confirmera ta demande.</p>
            <a class="btn btn-primary" href="/mon-espace/inscription">Faire une demande d’inscription</a>
        <?php else: ?>
            <p class="small mb-2">Aucune nouvelle classe n’est disponible pour toi actuellement. Les demandes déjà envoyées apparaissent dans « Mes inscriptions ».</p>
            <a class="btn btn-outline-primary" href="/mon-espace/inscription">Consulter les possibilités</a>
        <?php endif; ?>
    </section>

    <section class="school-priority p-3">
        <div class="d-flex justify-content-between align-items-center mb-2"><h2 class="h6 fw-semibold mb-0">Mes notes</h2><span class="badge text-bg-primary"><?= count($portal['notes']) ?></span></div>
        <?php if (empty($portal['notes'])): ?>
            <p class="small text-secondary mb-0">Aucune note publiée.</p>
        <?php else: ?>
            <div class="table-responsive"><table class="table school-table align-middle mb-0">
                <thead><tr><th>Matière</th><th>Évaluation</th><th>Date</th><th>Note</th></tr></thead>
                <tbody><?php foreach ($portal['notes'] as $note): ?>
                    <tr><td><?= htmlspecialchars($note['matiere'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars($note['evaluation'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars((string) $note['date_evaluation'], ENT_QUOTES, 'UTF-8') ?></td><td class="fw-semibold"><?= htmlspecialchars((string) $note['valeur'], ENT_QUOTES, 'UTF-8') ?>/20</td></tr>
                <?php endforeach; ?></tbody>
            </table></div>
        <?php endif; ?>
    </section>
<?php endif; ?>
<?php
$content = ob_get_clean();
require BASE_PATH . '/templates/layouts/app.php';
