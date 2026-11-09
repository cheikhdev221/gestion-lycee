<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Classes</title>
</head>
<body>
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3"><h1 class="h3 mb-0">Classes</h1><?php if ($roleCode !== 'ENSEIGNANT'): ?><a class="btn btn-primary" href="/classes/create">Créer une classe</a><?php endif; ?></div>
    <?php if (empty($classes)): ?><div class="alert alert-light border">Aucune classe enregistrée.</div><?php else: ?>
        <div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th>Classe</th><th>Année scolaire</th><?php if ($roleCode !== 'ENSEIGNANT'): ?><th>Action</th><?php endif; ?></tr></thead><tbody>
            <?php foreach ($classes as $classe): ?><tr><td class="fw-semibold"><?= htmlspecialchars($classe->libelle, ENT_QUOTES, 'UTF-8') ?></td><td>Année #<?= (int) $classe->anneeScolaireId ?></td><?php if ($roleCode !== 'ENSEIGNANT'): ?><td><a class="btn btn-outline-primary btn-sm" href="/classes/edit?id=<?= (int) $classe->id ?>">Modifier</a></td><?php endif; ?></tr><?php endforeach; ?>
        </tbody></table></div>
    <?php endif; ?>
</body>
</html>
