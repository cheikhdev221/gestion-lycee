<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Modifier un élève</title>
</head>
<body>
    <h1>Modifier l’élève <?= htmlspecialchars($eleve->matricule, ENT_QUOTES, 'UTF-8') ?></h1>
    <?php if (!empty($error)): ?><p><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
    <form method="post" action="/eleves/update">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="id" value="<?= (int) $eleve->id ?>">
        <p><label for="matricule">Matricule</label><br><input id="matricule" name="matricule" value="<?= htmlspecialchars($eleve->matricule, ENT_QUOTES, 'UTF-8') ?>" pattern="LYC-[0-9]{4}-[0-9]{5}" required></p>
        <p><label for="date_naissance">Date de naissance</label><br><input id="date_naissance" type="date" name="date_naissance" value="<?= htmlspecialchars((string) $eleve->dateNaissance, ENT_QUOTES, 'UTF-8') ?>" required></p>
        <button type="submit">Enregistrer</button>
        <a href="/eleves">Annuler</a>
    </form>
</body>
</html>