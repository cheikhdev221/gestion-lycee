<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Enseignants</title>
</head>
<body>
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3"><h1 class="h3 mb-0">Enseignants</h1><a class="btn btn-primary" href="/enseignants/create">Créer un enseignant</a></div>
    <?php if (empty($enseignants)): ?><div class="alert alert-light border">Aucun enseignant enregistré.</div><?php else: ?>
        <div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th>Référence</th><th>Matière</th><th>Action</th></tr></thead><tbody>
            <?php foreach ($enseignants as $enseignant): ?><tr><td class="fw-semibold">Enseignant #<?= (int) $enseignant->id ?></td><td>Matière #<?= (int) $enseignant->matiereId ?></td><td><a class="btn btn-outline-primary btn-sm" href="/enseignants/edit?id=<?= (int) $enseignant->id ?>">Modifier</a></td></tr><?php endforeach; ?>
        </tbody></table></div>
    <?php endif; ?>
</body>
</html>
