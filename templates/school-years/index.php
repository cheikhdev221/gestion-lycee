<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Années scolaires</title>
</head>
<body>
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3"><h1 class="h3 mb-0">Années scolaires</h1><a class="btn btn-primary" href="/annees/create">Créer une année</a></div>
    <?php if (empty($annees)): ?><div class="alert alert-light border">Aucune année scolaire enregistrée.</div><?php else: ?>
        <div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th>Année</th><th>Début</th><th>Fin</th><th>Statut</th><th></th></tr></thead><tbody>
            <?php foreach ($annees as $annee): ?><tr><td class="fw-semibold"><?= htmlspecialchars($annee->libelle, ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars((string) $annee->dateDebut, ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars((string) $annee->dateFin, ENT_QUOTES, 'UTF-8') ?></td><td><span class="badge <?= $annee->statut === 'EN_COURS' ? 'text-bg-primary' : 'text-bg-secondary' ?>"><?= htmlspecialchars($annee->statut, ENT_QUOTES, 'UTF-8') ?></span></td><td><a class="btn btn-outline-primary btn-sm" href="/annees/edit?id=<?= (int) $annee->id ?>">Modifier</a></td></tr><?php endforeach; ?>
        </tbody></table></div>
    <?php endif; ?>
</body>
</html>
