<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Élèves</title>
</head>
<body>
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3"><h1 class="h3 mb-0">Élèves</h1><?php if ($roleCode !== 'ENSEIGNANT'): ?><a class="btn btn-primary" href="/eleves/create">Créer un élève</a><?php endif; ?></div>
    <?php if (empty($eleves)): ?><div class="alert alert-light border">Aucun élève enregistré.</div><?php else: ?>
        <div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th>Matricule</th><th>Compte élève</th><?php if ($roleCode !== 'ENSEIGNANT'): ?><th>Action</th><?php endif; ?></tr></thead><tbody>
            <?php foreach ($eleves as $eleve): ?><tr><td class="fw-semibold"><?= htmlspecialchars($eleve->matricule, ENT_QUOTES, 'UTF-8') ?></td><td>Profil scolaire</td><?php if ($roleCode !== 'ENSEIGNANT'): ?><td><a class="btn btn-outline-primary btn-sm" href="/eleves/edit?id=<?= (int) $eleve->id ?>">Modifier</a></td><?php endif; ?></tr><?php endforeach; ?>
        </tbody></table></div>
    <?php endif; ?>
</body>
</html>
