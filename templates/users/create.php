<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Créer un utilisateur</title>
</head>
<body>
    <h1>Créer un utilisateur</h1>

    <?php if (!empty($error)): ?>
        <p style="color: #b00020; font-weight: bold;">
            <?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?>
        </p>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <p style="color: #006400; font-weight: bold;">
            <?= htmlspecialchars((string) $success, ENT_QUOTES, 'UTF-8') ?>
        </p>
    <?php endif; ?>

    <form method="post" action="/users/store">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
        <p>
            <label for="nom">Nom</label><br>
            <input type="text" id="nom" name="nom" required>
        </p>
        <p>
            <label for="prenom">Prénom</label><br>
            <input type="text" id="prenom" name="prenom" required>
        </p>
        <p>
            <label for="email">Email</label><br>
            <input type="email" id="email" name="email" required>
        </p>
        <p>
            <label for="password">Mot de passe</label><br>
            <input type="password" id="password" name="password" required>
        </p>
        <p>
            <label for="role_code">Rôle</label><br>
            <select id="role_code" name="role_code" required>
                <option value="ADMIN">ADMIN</option>
                <option value="DIRECTEUR">DIRECTEUR</option>
                <option value="ENSEIGNANT">ENSEIGNANT</option>
                <option value="ELEVE">ELEVE</option>
            </select>
        </p>
        <button type="submit">Créer</button>
    </form>
</body>
</html>
