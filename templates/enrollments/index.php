<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Inscriptions</title>
</head>
<body>
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3"><h1 class="h3 mb-0">Inscriptions</h1><a class="btn btn-primary" href="/inscriptions/create">Créer une inscription</a></div>
    <?php if (empty($inscriptions)): ?><div class="alert alert-light border">Aucune inscription enregistrée.</div><?php else: ?>
        <div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th>Élève</th><th>Classe</th><th>Année</th><th>Statut</th><th>Actions</th></tr></thead><tbody>
            <?php foreach ($inscriptions as $inscription): ?><tr>
                <td>Élève #<?= (int) $inscription->eleveId ?></td><td>Classe #<?= (int) $inscription->classeId ?></td><td>Année #<?= (int) $inscription->anneeScolaireId ?></td>
                <td><span class="badge <?= $inscription->statut === 'PAYE' ? 'text-bg-success' : ($inscription->statut === 'PENDING' ? 'text-bg-warning' : 'text-bg-secondary') ?>"><?= htmlspecialchars($inscription->statut, ENT_QUOTES, 'UTF-8') ?></span></td>
                <td><?php if ($inscription->statut === 'PENDING'): ?><div class="d-flex gap-2"><a class="btn btn-outline-primary btn-sm" href="/inscriptions/edit?id=<?= (int) $inscription->id ?>">Modifier</a><form method="post" action="/inscriptions/cancel"><input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="id" value="<?= (int) $inscription->id ?>"><button class="btn btn-outline-danger btn-sm" type="submit">Annuler</button></form></div><?php else: ?><span class="text-secondary">Aucune action</span><?php endif; ?></td>
            </tr><?php endforeach; ?>
        </tbody></table></div>
    <?php endif; ?>
</body>
</html>
