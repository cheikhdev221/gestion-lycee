<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Créer une année scolaire</title>
</head>
<body>
    <h1>Créer une année scolaire</h1>
    <?php if (!empty($error)): ?><p style="color: #b00020; font-weight: bold;"><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
    <?php if (!empty($success)): ?><p style="color: #006400; font-weight: bold;"><?= htmlspecialchars((string) $success, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
    <form method="post" action="/annees/store">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
        <p><label>Libellé</label><br><input type="text" name="libelle" required></p>
        <p><label>Date début</label><br><input type="date" name="date_debut" required></p>
        <p><label>Date fin</label><br><input type="date" name="date_fin" required></p>
        <p><label>Statut</label><br>
            <select name="statut">
                <option value="A_VENIR">A_VENIR</option>
                <option value="EN_COURS">EN_COURS</option>
                <option value="TERMINEE">TERMINEE</option>
            </select>
        </p>
        <button type="submit">Créer</button>
    </form>
</body>
</html>
