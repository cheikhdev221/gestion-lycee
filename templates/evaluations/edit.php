<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Modifier une évaluation</title>
</head>
<body>
    <h1>Modifier l’évaluation <?= htmlspecialchars($evaluation->libelle, ENT_QUOTES, 'UTF-8') ?></h1>
    <?php if (!empty($error)): ?><p><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
    <form method="post" action="/evaluations/update">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="id" value="<?= (int) $evaluation->id ?>">
        <p><label for="libelle">Libellé</label><br><input id="libelle" name="libelle" value="<?= htmlspecialchars($evaluation->libelle, ENT_QUOTES, 'UTF-8') ?>" required></p>
        <p><label for="type">Type</label><br>
            <select id="type" name="type"><option value="DEVOIR" <?= $evaluation->type === 'DEVOIR' ? 'selected' : '' ?>>Devoir</option><option value="COMPOSITION" <?= $evaluation->type === 'COMPOSITION' ? 'selected' : '' ?>>Composition</option></select>
        </p>
        <p><label for="coefficient">Coefficient</label><br><input id="coefficient" type="number" step="0.01" min="0.01" name="coefficient" value="<?= htmlspecialchars((string) $evaluation->coefficient, ENT_QUOTES, 'UTF-8') ?>" required></p>
        <p><label for="date_evaluation">Date</label><br><input id="date_evaluation" type="date" name="date_evaluation" value="<?= htmlspecialchars((string) $evaluation->dateEvaluation, ENT_QUOTES, 'UTF-8') ?>" required></p>
        <p><label for="description">Description</label><br><textarea id="description" name="description"><?= htmlspecialchars((string) $evaluation->description, ENT_QUOTES, 'UTF-8') ?></textarea></p>
        <button type="submit">Enregistrer</button>
        <a href="/evaluations">Annuler</a>
    </form>
</body>
</html>