<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Modifier une note</title>
</head>
<body>
    <h1>Modifier la note #<?= (int) $note->id ?></h1>
    <?php if (!empty($error)): ?><p><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
    <form method="post" action="/notes/update">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="id" value="<?= (int) $note->id ?>">
        <p><label for="valeur">Valeur sur 20</label><br><input id="valeur" type="number" step="0.01" min="0" max="20" name="valeur" value="<?= htmlspecialchars((string) $note->valeur, ENT_QUOTES, 'UTF-8') ?>" required></p>
        <p><label for="commentaire">Commentaire</label><br><textarea id="commentaire" name="commentaire"><?= htmlspecialchars((string) $note->commentaire, ENT_QUOTES, 'UTF-8') ?></textarea></p>
        <button type="submit">Enregistrer</button>
        <a href="/notes">Annuler</a>
    </form>
</body>
</html>