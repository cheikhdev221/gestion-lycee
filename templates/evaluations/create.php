<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Créer une évaluation</title>
</head>
<body>
    <h1>Créer une évaluation</h1>
    <?php if (!empty($error)): ?><div class="alert alert-danger"><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
    <?php if (!empty($success)): ?><div class="alert alert-success"><?= htmlspecialchars((string) $success, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
    <form method="post" action="/evaluations/store">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
        <?php if (empty($affectations)): ?>
            <div class="alert alert-info">Aucune affectation disponible pour créer une évaluation.</div>
        <?php else: ?>
            <div class="mb-3"><label class="form-label" for="affectation_id">Cours et classe</label><select class="form-select" id="affectation_id" name="affectation_id" required><option value="">Sélectionner un cours</option><?php foreach ($affectations as $affectation): ?><option value="<?= (int) $affectation['id'] ?>" data-year="<?= (int) $affectation['annee_scolaire_id'] ?>"><?= htmlspecialchars($affectation['matiere'] . ' · ' . $affectation['classe'] . ' · ' . $affectation['annee_scolaire'] . (!empty($affectation['enseignant_nom']) ? ' · ' . $affectation['enseignant_prenom'] . ' ' . $affectation['enseignant_nom'] : ''), ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></div>
            <div class="mb-3"><label class="form-label" for="semestre_id">Semestre</label><select class="form-select" id="semestre_id" name="semestre_id" required><option value="">Sélectionner un semestre</option><?php foreach ($semestres as $semestre): ?><option value="<?= (int) $semestre->id ?>" data-year="<?= (int) $semestre->anneeScolaireId ?>"><?= htmlspecialchars($semestre->libelle, ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></div>
            <div class="mb-3"><label class="form-label" for="libelle">Nom de l’évaluation</label><input class="form-control" id="libelle" type="text" name="libelle" required></div>
            <div class="row g-3 mb-3"><div class="col-12 col-sm-6"><label class="form-label" for="type">Type</label><select class="form-select" id="type" name="type"><option value="DEVOIR">Devoir</option><option value="COMPOSITION">Composition</option></select></div><div class="col-12 col-sm-6"><label class="form-label" for="coefficient">Coefficient</label><input class="form-control" id="coefficient" type="number" min="0.01" step="0.01" name="coefficient" value="1" required></div></div>
            <div class="mb-3"><label class="form-label" for="date_evaluation">Date</label><input class="form-control" id="date_evaluation" type="date" name="date_evaluation" required></div>
            <button class="btn btn-primary" type="submit">Créer l’évaluation</button>
        <?php endif; ?>
    </form>
    <script src="/assets/js/evaluation-form.js" defer></script>
</body>
</html>
