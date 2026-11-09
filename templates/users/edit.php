<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Modifier un utilisateur</title>
</head>
<body>
    <h1>Modifier un utilisateur</h1>
    <?php if (!empty($error)): ?><p><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
    <form method="post" action="/users/update">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="id" value="<?= (int) $user->id ?>">
        <p><label for="nom">Nom</label><br><input id="nom" name="nom" value="<?= htmlspecialchars($user->nom, ENT_QUOTES, 'UTF-8') ?>" required></p>
        <p><label for="prenom">Prénom</label><br><input id="prenom" name="prenom" value="<?= htmlspecialchars($user->prenom, ENT_QUOTES, 'UTF-8') ?>" required></p>
        <p><label for="email">Email</label><br><input id="email" type="email" name="email" value="<?= htmlspecialchars($user->email, ENT_QUOTES, 'UTF-8') ?>" required></p>
        <p><label for="role_code">Rôle</label><br>
            <select id="role_code" name="role_code" required>
                <?php foreach ($roles as $role): ?>
                    <option value="<?= htmlspecialchars($role->code, ENT_QUOTES, 'UTF-8') ?>" <?= $role->id === $user->roleId ? 'selected' : '' ?>><?= htmlspecialchars($role->libelle, ENT_QUOTES, 'UTF-8') ?></option>
                <?php endforeach; ?>
            </select>
        </p>
        <p><label for="password">Nouveau mot de passe (facultatif)</label><br><input id="password" type="password" name="password" minlength="8" autocomplete="new-password"></p>
        <p><label for="actif">État</label><br>
            <select id="actif" name="actif">
                <option value="1" <?= $user->actif ? 'selected' : '' ?>>Actif</option>
                <option value="0" <?= !$user->actif ? 'selected' : '' ?>>Désactivé</option>
            </select>
        </p>
        <button type="submit">Enregistrer</button>
        <a href="/users">Annuler</a>
    </form>
</body>
</html>