<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Évaluations</title>
</head>
<body>
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3"><h1 class="h3 mb-0">Évaluations</h1><a class="btn btn-primary" href="/evaluations/create">Créer une évaluation</a></div>
    <?php if (empty($evaluations)): ?><div class="alert alert-light border">Aucune évaluation enregistrée.</div><?php else: ?>
        <div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th>Évaluation</th><th>Type</th><th>Coefficient</th><th>Date</th><th></th></tr></thead><tbody>
            <?php foreach ($evaluations as $evaluation): ?><tr><td class="fw-semibold"><?= htmlspecialchars($evaluation->libelle, ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars($evaluation->type, ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars((string) $evaluation->coefficient, ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars((string) $evaluation->dateEvaluation, ENT_QUOTES, 'UTF-8') ?></td><td><a class="btn btn-outline-primary btn-sm" href="/evaluations/edit?id=<?= (int) $evaluation->id ?>">Modifier</a></td></tr><?php endforeach; ?>
        </tbody></table></div>
    <?php endif; ?>
</body>
</html>
