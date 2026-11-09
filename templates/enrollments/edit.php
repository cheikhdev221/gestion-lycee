<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Modifier une inscription</title>
</head>
<body>
    <h1>Modifier l’inscription #<?= (int) $inscription->id ?></h1>
    <?php if (!empty($error)): ?><p><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
    <form method="post" action="/inscriptions/update">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="id" value="<?= (int) $inscription->id ?>">
        <p class="small text-secondary">Élève #<?= (int) $inscription->eleveId ?></p>
        <div class="mb-3"><label class="form-label" for="classe_id">Classe</label><select class="form-select" id="classe_id" name="classe_id" required><?php foreach ($classes as $classe): ?><option value="<?= (int) $classe->id ?>" <?= $classe->id === $inscription->classeId ? 'selected' : '' ?>><?= htmlspecialchars($classe->libelle . ' · année #' . $classe->anneeScolaireId, ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></div>
        <div class="mb-3"><label class="form-label" for="annee_scolaire_id">Année scolaire</label><select class="form-select" id="annee_scolaire_id" name="annee_scolaire_id" required><?php foreach ($annees as $annee): ?><option value="<?= (int) $annee->id ?>" <?= $annee->id === $inscription->anneeScolaireId ? 'selected' : '' ?>><?= htmlspecialchars($annee->libelle, ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></div>
        <button class="btn btn-primary" type="submit">Enregistrer</button>
        <a href="/inscriptions">Annuler</a>
    </form>
</body>
</html>