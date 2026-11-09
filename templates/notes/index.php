<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Notes</title>
</head>
<body>
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3"><h1 class="h3 mb-0">Notes</h1><?php if ($roleCode === 'ENSEIGNANT'): ?><a class="btn btn-primary" href="/notes/create">Saisir une note</a><?php endif; ?></div>
    <?php if (empty($notes)): ?><div class="alert alert-light border">Aucune note enregistrée.</div><?php else: ?>
        <div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th>Référence</th><th>Note</th><?php if ($roleCode === 'ENSEIGNANT'): ?><th></th><?php endif; ?></tr></thead><tbody>
            <?php foreach ($notes as $note): ?><tr><td>Note #<?= (int) $note->id ?></td><td class="fw-semibold"><?= htmlspecialchars((string) $note->valeur, ENT_QUOTES, 'UTF-8') ?>/20</td><?php if ($roleCode === 'ENSEIGNANT'): ?><td><a class="btn btn-outline-primary btn-sm" href="/notes/edit?id=<?= (int) $note->id ?>">Modifier</a></td><?php endif; ?></tr><?php endforeach; ?>
        </tbody></table></div>
    <?php endif; ?>
</body>
</html>
