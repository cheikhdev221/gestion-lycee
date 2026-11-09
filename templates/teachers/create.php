<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Créer un enseignant</title>
</head>
<body>
    <h1>Créer un enseignant</h1>
    <?php if (!empty($error)): ?><div class="alert alert-danger"><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
    <?php if (!empty($success)): ?><div class="alert alert-success"><?= htmlspecialchars((string) $success, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
    <form method="post" action="/enseignants/store">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
        <?php if (empty($utilisateurs)): ?><div class="alert alert-info">Aucun compte actif de rôle Enseignant sans fiche.</div><?php else: ?>
            <div class="mb-3"><label class="form-label" for="utilisateur_id">Compte enseignant</label><select class="form-select" id="utilisateur_id" name="utilisateur_id" required><option value="">Sélectionner un enseignant</option><?php foreach ($utilisateurs as $utilisateur): ?><option value="<?= (int) $utilisateur->id ?>"><?= htmlspecialchars($utilisateur->prenom . ' ' . $utilisateur->nom . ' · ' . $utilisateur->email, ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></div>
            <div class="mb-3"><label class="form-label" for="matiere_id">Matière</label><select class="form-select" id="matiere_id" name="matiere_id" required><option value="">Sélectionner une matière</option><?php foreach ($matieres as $matiere): ?><option value="<?= (int) $matiere->id ?>"><?= htmlspecialchars($matiere->libelle, ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></div>
            <button class="btn btn-primary" type="submit">Créer la fiche enseignant</button>
        <?php endif; ?>
    </form>
</body>
</html>
