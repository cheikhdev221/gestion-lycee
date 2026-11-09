<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Paiements</title>
</head>
<body>
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3"><h1 class="h3 mb-0">Paiements simulés</h1><a class="btn btn-primary" href="/payments/create">Créer un paiement</a></div>
    <?php if (!empty($error)): ?><div class="alert alert-danger"><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
    <?php if (!empty($success)): ?><div class="alert alert-success"><?= htmlspecialchars((string) $success, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
    <?php if (empty($payments)): ?><div class="alert alert-light border">Aucun paiement.</div><?php else: ?>
        <div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th>Référence</th><th>Canal</th><th>Montant</th><th>Statut</th><?php if (!empty($canSimulate)): ?><th>Simulation</th><?php endif; ?></tr></thead><tbody>
            <?php foreach ($payments as $payment): ?><tr>
                <td class="fw-semibold"><?= htmlspecialchars($payment->transactionRef, ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars($payment->provider, ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars((string) $payment->montant, ENT_QUOTES, 'UTF-8') ?> <?= htmlspecialchars($payment->devise, ENT_QUOTES, 'UTF-8') ?></td>
                <td><span class="badge <?= $payment->statut === 'PAYE' ? 'text-bg-success' : (in_array($payment->statut, ['INITIE', 'EN_ATTENTE'], true) ? 'text-bg-warning' : 'text-bg-secondary') ?>"><?= htmlspecialchars($payment->statut, ENT_QUOTES, 'UTF-8') ?></span></td>
                <?php if (!empty($canSimulate)): ?><td><?php if (in_array($payment->statut, ['INITIE', 'EN_ATTENTE'], true)): ?><div class="d-flex flex-wrap gap-2"><form method="post" action="/payments/simulate"><input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="paiement_id" value="<?= (int) $payment->id ?>"><button class="btn btn-outline-success btn-sm" type="submit" name="resultat" value="PAYE">Simuler succès</button></form><form method="post" action="/payments/simulate"><input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="paiement_id" value="<?= (int) $payment->id ?>"><button class="btn btn-outline-danger btn-sm" type="submit" name="resultat" value="ECHEC">Simuler échec</button></form></div><?php else: ?><span class="text-secondary">Terminé</span><?php endif; ?></td><?php endif; ?>
            </tr><?php endforeach; ?>
        </tbody></table></div>
    <?php endif; ?>
</body>
</html>
