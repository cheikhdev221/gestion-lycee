<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Passages en classe supérieure</title>
</head>
<body>
    <h1 class="h3 mb-3">Passages en classe supérieure</h1>
    <?php if (!empty($error)): ?><div class="alert alert-danger"><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
    <?php if (!empty($success)): ?><div class="alert alert-success"><?= htmlspecialchars((string) $success, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
    <?php if (empty($candidates)): ?>
        <div class="alert alert-light border">Aucun passage à valider pour le moment.</div>
    <?php else: ?>
        <div class="table-responsive"><table class="table table-hover align-middle">
            <thead class="table-light">
                <tr><th>Élève</th><th>Classe actuelle</th><th>Année actuelle</th><th>Classe suivante</th><th>Année suivante</th><th></th></tr>
            </thead>
            <tbody>
                <?php foreach ($candidates as $candidate): ?>
                    <tr>
                        <td><?= htmlspecialchars($candidate['prenom'] . ' ' . $candidate['nom'], ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars($candidate['matricule'], ENT_QUOTES, 'UTF-8') ?>)</td>
                        <td><?= htmlspecialchars($candidate['ancienne_classe'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($candidate['ancienne_annee'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($candidate['nouvelle_classe'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($candidate['nouvelle_annee'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td>
                            <form method="post" action="/passages/valider">
                                <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                <input type="hidden" name="eleve_id" value="<?= (int) $candidate['eleve_id'] ?>">
                                <input type="hidden" name="classe_id" value="<?= (int) $candidate['nouvelle_classe_id'] ?>">
                                <button class="btn btn-primary btn-sm text-nowrap" type="submit">Valider le passage</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table></div>
    <?php endif; ?>
</body>
</html>