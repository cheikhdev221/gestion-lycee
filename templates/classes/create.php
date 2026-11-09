<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Créer une classe</title>
</head>
<body>
    <h1>Créer une classe</h1>
    <?php if (!empty($error)): ?><div class="alert alert-danger"><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
    <?php if (!empty($success)): ?><div class="alert alert-success"><?= htmlspecialchars((string) $success, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
    <form method="post" action="/classes/store">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
        <div class="mb-3"><label class="form-label" for="libelle">Nom de la classe</label><input class="form-control" id="libelle" type="text" name="libelle" required></div>
        <div class="mb-3"><label class="form-label" for="niveau_id">Niveau</label><select class="form-select" id="niveau_id" name="niveau_id" required><option value="">Choisir un niveau</option><?php foreach ($niveaux as $niveau): ?><option value="<?= (int) $niveau->id ?>"><?= htmlspecialchars($niveau->libelle, ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></div>
        <div class="mb-3"><label class="form-label" for="serie_id">Série</label><select class="form-select" id="serie_id" name="serie_id" required><option value="">Choisir une série</option><?php foreach ($series as $serie): ?><option value="<?= (int) $serie->id ?>"><?= htmlspecialchars($serie->libelle, ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></div>
        <div class="mb-3"><label class="form-label" for="annee_scolaire_id">Année scolaire</label><select class="form-select" id="annee_scolaire_id" name="annee_scolaire_id" required><option value="">Choisir une année</option><?php foreach ($annees as $annee): ?><option value="<?= (int) $annee->id ?>"><?= htmlspecialchars($annee->libelle, ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></div>
        <button class="btn btn-primary" type="submit">Créer la classe</button>
    </form>
</body>
</html>
