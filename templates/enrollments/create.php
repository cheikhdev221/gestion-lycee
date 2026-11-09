<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Créer une inscription</title>
</head>
<body>
    <h1>Créer une inscription</h1>
    <?php if (!empty($error)): ?><div class="alert alert-danger"><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
    <?php if (!empty($success)): ?><div class="alert alert-success"><?= htmlspecialchars((string) $success, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
    <form method="post" action="/inscriptions/store">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
        <?php if (empty($eleves) || empty($classes) || empty($annees)): ?>
            <div class="alert alert-info">Il faut au moins un élève, une classe et une année scolaire avant de créer une inscription.</div>
        <?php else: ?>
        <div class="mb-3"><label class="form-label" for="eleve_id">Élève</label><select class="form-select" id="eleve_id" name="eleve_id" required><option value="">Sélectionner un élève</option><?php foreach ($eleves as $eleve): ?><option value="<?= (int) $eleve->id ?>"><?= htmlspecialchars($eleve->matricule, ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></div>
        <div class="mb-3"><label class="form-label" for="classe_id">Classe</label><select class="form-select" id="classe_id" name="classe_id" required><option value="">Sélectionner une classe</option><?php foreach ($classes as $classe): ?><option value="<?= (int) $classe->id ?>"><?= htmlspecialchars($classe->libelle . ' · année #' . $classe->anneeScolaireId, ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></div>
        <div class="mb-3"><label class="form-label" for="annee_scolaire_id">Année scolaire</label><select class="form-select" id="annee_scolaire_id" name="annee_scolaire_id" required><option value="">Sélectionner une année</option><?php foreach ($annees as $annee): ?><option value="<?= (int) $annee->id ?>"><?= htmlspecialchars($annee->libelle, ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></div>
        <button type="submit">Créer</button>
        <?php endif; ?>
    </form>
</body>
</html>
