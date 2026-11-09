<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Utilisateurs</title>
</head>
<body>
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3"><h1 class="h3 mb-0">Utilisateurs</h1><a class="btn btn-primary" href="/users/create">Créer un utilisateur</a></div>
    <?php if (empty($users)): ?><div class="alert alert-light border">Aucun utilisateur trouvé.</div><?php else: ?>
        <div class="table-responsive"><table class="table table-hover align-middle">
            <thead><tr><th>Nom</th><th>Email</th><th>Rôle</th><th>État</th><th>Actions</th></tr></thead>
            <tbody><?php foreach ($users as $user): ?><tr>
                <td class="fw-semibold"><?= htmlspecialchars($user->prenom . ' ' . $user->nom, ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($user->email, ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($roleNames[$user->roleId] ?? 'Rôle inconnu', ENT_QUOTES, 'UTF-8') ?></td>
                <td><span class="badge <?= $user->actif ? 'text-bg-success' : 'text-bg-secondary' ?>"><?= $user->actif ? 'Actif' : 'Désactivé' ?></span></td>
                <td><div class="d-flex flex-wrap gap-2"><a class="btn btn-outline-primary btn-sm" href="/users/edit?id=<?= (int) $user->id ?>">Modifier</a>
                    <?php if ($user->id !== $currentUserId): ?><form method="post" action="/users/active"><input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="id" value="<?= (int) $user->id ?>"><input type="hidden" name="actif" value="<?= $user->actif ? '0' : '1' ?>"><button class="btn btn-outline-secondary btn-sm" type="submit"><?= $user->actif ? 'Désactiver' : 'Réactiver' ?></button></form><?php endif; ?>
                </div></td>
            </tr><?php endforeach; ?></tbody>
        </table></div>
    <?php endif; ?>
</body>
</html>
