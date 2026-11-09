<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Modifier une classe</title>
</head>
<body>
    <h1>Modifier la classe <?= htmlspecialchars($classe->libelle, ENT_QUOTES, 'UTF-8') ?></h1>
    <?php if (!empty($error)): ?><p><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
    <form method="post" action="/classes/update">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="id" value="<?= (int) $classe->id ?>">
        <div class="mb-3"><label class="form-label" for="libelle">Nom de la classe</label><input class="form-control" id="libelle" name="libelle" value="<?= htmlspecialchars($classe->libelle, ENT_QUOTES, 'UTF-8') ?>" required></div>
        <div class="mb-3"><label class="form-label" for="niveau_id">Niveau</label><select class="form-select" id="niveau_id" name="niveau_id" required><?php foreach ($niveaux as $niveau): ?><option value="<?= (int) $niveau->id ?>" <?= $niveau->id === $classe->niveauId ? 'selected' : '' ?>><?= htmlspecialchars($niveau->libelle, ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></div>
        <div class="mb-3"><label class="form-label" for="serie_id">Série</label><select class="form-select" id="serie_id" name="serie_id" required><?php foreach ($series as $serie): ?><option value="<?= (int) $serie->id ?>" <?= $serie->id === $classe->serieId ? 'selected' : '' ?>><?= htmlspecialchars($serie->libelle, ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></div>
        <div class="mb-3"><label class="form-label" for="annee_scolaire_id">Année scolaire</label><select class="form-select" id="annee_scolaire_id" name="annee_scolaire_id" required><?php foreach ($annees as $annee): ?><option value="<?= (int) $annee->id ?>" <?= $annee->id === $classe->anneeScolaireId ? 'selected' : '' ?>><?= htmlspecialchars($annee->libelle, ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></div>
        <button class="btn btn-primary" type="submit">Enregistrer</button>
        <a href="/classes">Annuler</a>
    </form>
</body>
</html>