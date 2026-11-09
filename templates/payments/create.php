<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Frais d’inscription pédagogique</title>
</head>
<body>
    <h1 class="h3 mb-2">Frais d’inscription pédagogique</h1>
    <p class="text-secondary">Le montant fixe est de <strong>25 000 XOF</strong> par inscription.</p>
    <?php if (!empty($error)): ?><div class="alert alert-danger"><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
    <?php if (!empty($success)): ?><div class="alert alert-success"><?= htmlspecialchars((string) $success, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
    <form method="post" action="/payments/store">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
        <?php if (empty($inscriptions)): ?>
            <div class="alert alert-info">Aucune inscription en attente ne peut recevoir un paiement simulé.</div>
        <?php else: ?>
            <div class="mb-3"><label class="form-label" for="inscription_id">Inscription</label><select class="form-select" id="inscription_id" name="inscription_id" required><option value="">Sélectionner un dossier</option><?php foreach ($inscriptions as $inscription): ?><option value="<?= (int) $inscription['id'] ?>"><?= htmlspecialchars($inscription['prenom'] . ' ' . $inscription['nom'] . ' · ' . $inscription['classe'] . ' · ' . $inscription['annee_scolaire'], ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></div>
            <div class="mb-3"><label class="form-label" for="provider">Canal simulé</label><select class="form-select" id="provider" name="provider"><option value="WAVE">Wave</option><option value="CARTE">Carte</option></select></div>
            <div class="mb-3"><span class="form-label d-block">Montant à régler</span><div class="form-control bg-light" aria-label="Montant fixe des frais d’inscription"><?= number_format((int) ($feeAmount ?? 25000), 0, ',', ' ') ?> XOF</div></div>
            <button class="btn btn-primary" type="submit">Créer le paiement simulé</button>
        <?php endif; ?>
    </form>
</body>
</html>
