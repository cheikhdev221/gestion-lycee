<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Créer un élève</title>
</head>
<body>
    <h1>Créer un élève</h1>
    <?php if (!empty($error)): ?><div class="alert alert-danger"><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
    <?php if (!empty($success)): ?><div class="alert alert-success"><?= htmlspecialchars((string) $success, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
    <form method="post" action="/eleves/store">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
        <?php if (empty($utilisateurs)): ?>
            <div class="alert alert-info">Aucun compte actif de rôle Élève sans fiche. Crée d’abord un utilisateur avec le rôle Élève.</div>
        <?php else: ?>
            <div class="mb-3"><label class="form-label" for="utilisateur_id">Compte élève</label><select class="form-select" id="utilisateur_id" name="utilisateur_id" required><option value="">Sélectionner un élève</option><?php foreach ($utilisateurs as $utilisateur): ?><option value="<?= (int) $utilisateur->id ?>"><?= htmlspecialchars($utilisateur->prenom . ' ' . $utilisateur->nom . ' · ' . $utilisateur->email, ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></div>
            <div class="mb-3"><label class="form-label" for="matricule">Matricule</label><input class="form-control" id="matricule" type="text" name="matricule" placeholder="LYC-2026-00001" pattern="LYC-[0-9]{4}-[0-9]{5}" required></div>
            <div class="mb-3"><label class="form-label" for="date_naissance">Date de naissance</label><input class="form-control" id="date_naissance" type="date" name="date_naissance" required></div>
            <button class="btn btn-primary" type="submit">Créer la fiche élève</button>
        <?php endif; ?>
    </form>
</body>
</html>
