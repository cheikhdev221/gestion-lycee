<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Saisir une note</title>
</head>
<body>
    <h1>Saisir une note</h1>
    <?php if (!empty($error)): ?><div class="alert alert-danger"><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
    <?php if (!empty($success)): ?><div class="alert alert-success"><?= htmlspecialchars((string) $success, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
    <form method="post" action="/notes/store">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
        <?php if (empty($evaluations) || empty($eleves)): ?>
            <div class="alert alert-info">Il faut au moins une évaluation et un élève pour saisir une note.</div>
        <?php else: ?>
            <div class="mb-3"><label class="form-label" for="evaluation_id">Évaluation</label><select class="form-select" id="evaluation_id" name="evaluation_id" required><option value="">Sélectionner une évaluation</option><?php foreach ($evaluations as $evaluation): ?><option value="<?= (int) $evaluation['id'] ?>"><?= htmlspecialchars($evaluation['matiere'] . ' · ' . $evaluation['libelle'] . ' · ' . $evaluation['classe'] . ' · ' . $evaluation['annee_scolaire'], ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></div>
            <div class="mb-3"><label class="form-label" for="eleve_id">Élève</label><select class="form-select" id="eleve_id" name="eleve_id" required><option value="">Sélectionner un élève</option><?php foreach ($eleves as $eleve): ?><option value="<?= (int) $eleve['id'] ?>"><?= htmlspecialchars($eleve['prenom'] . ' ' . $eleve['nom'] . ' · ' . $eleve['matricule'], ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></div>
            <div class="mb-3"><label class="form-label" for="valeur">Note sur 20</label><input class="form-control" id="valeur" type="number" step="0.01" name="valeur" min="0" max="20" required></div>
            <div class="mb-3"><label class="form-label" for="commentaire">Commentaire</label><textarea class="form-control" id="commentaire" name="commentaire" rows="3"></textarea></div>
            <button class="btn btn-primary" type="submit">Enregistrer la note</button>
        <?php endif; ?>
    </form>
</body>
</html>
