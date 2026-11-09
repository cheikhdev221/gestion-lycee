<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Modifier un enseignant</title>
</head>
<body>
    <h1>Modifier l’enseignant #<?= (int) $enseignant->id ?></h1>
    <?php if (!empty($error)): ?><p><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
    <form method="post" action="/enseignants/update">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="id" value="<?= (int) $enseignant->id ?>">
        <div class="mb-3"><label class="form-label" for="matiere_id">Matière</label><select class="form-select" id="matiere_id" name="matiere_id" required><?php foreach ($matieres as $matiere): ?><option value="<?= (int) $matiere->id ?>" <?= $matiere->id === $enseignant->matiereId ? 'selected' : '' ?>><?= htmlspecialchars($matiere->libelle, ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></div>
        <button class="btn btn-primary" type="submit">Enregistrer</button>
        <a href="/enseignants">Annuler</a>
    </form>
</body>
</html>