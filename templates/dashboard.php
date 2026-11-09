<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars((string) ($titre ?? 'Dashboard'), ENT_QUOTES, 'UTF-8') ?></title>
</head>
<body>
    <h1><?= htmlspecialchars((string) ($titre ?? 'Dashboard'), ENT_QUOTES, 'UTF-8') ?></h1>

    <?php if (!empty($user)): ?>
        <p>Bienvenue, <?= htmlspecialchars($user['prenom'] . ' ' . $user['nom'], ENT_QUOTES, 'UTF-8') ?>.</p>
        <form method="post" action="/logout">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
            <button type="submit">Se déconnecter</button>
        </form>
    <?php else: ?>
        <p>Vous n’êtes pas connecté.</p>
        <a href="/login">Se connecter</a>
    <?php endif; ?>
</body>
</html>
