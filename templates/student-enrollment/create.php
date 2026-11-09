<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Demander une inscription</title>
</head>
<body class="student-enrollment-page">
    <main class="container py-4 py-lg-5">
      <a class="small text-decoration-none" href="/mon-espace">← Retour à mon espace</a>
      <section class="student-enrollment-card mx-auto mt-4 p-4 p-lg-5">
        <div class="student-enrollment-icon mb-3" aria-hidden="true">✦</div>
        <p class="text-uppercase text-primary fw-semibold small mb-2">Parcours scolaire</p>
        <h1 class="h2 fw-bold mb-2">Demander une inscription</h1>
        <p class="text-secondary mb-4">Choisis ta classe pour l’année scolaire souhaitée. Le secrétariat étudiera ta demande et te confirmera son statut dans ton espace élève.</p>
        <div class="student-enrollment-fee p-3 mb-4"><span class="small text-secondary d-block">Frais fixes d’inscription pédagogique</span><strong>25 000 XOF</strong><span class="small text-secondary d-block">Le secrétariat te communiquera les modalités de règlement.</span></div>
        <?php if (!empty($error)): ?><div class="alert alert-danger" role="alert"><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
        <?php if (empty($classes)): ?>
            <div class="alert alert-info mb-3" role="status">Tu as déjà une demande pour chaque année ouverte, ou aucune classe n’est encore proposée. Reviens consulter cette page plus tard.</div>
            <a class="btn btn-primary" href="/mon-espace">Retour à mon espace</a>
        <?php else: ?>
            <form method="post" action="/mon-espace/inscription">
                <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                <div class="mb-4"><label class="form-label" for="classe_id">Classe souhaitée</label><select class="form-select" id="classe_id" name="classe_id" required><option value="">Sélectionner une classe</option><?php foreach ($classes as $classe): ?><option value="<?= (int) $classe['id'] ?>"><?= htmlspecialchars($classe['libelle'] . ' · ' . $classe['annee_scolaire'], ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></div>
                <button class="btn btn-primary btn-lg" type="submit">Envoyer ma demande</button>
            </form>
        <?php endif; ?>
      </section>
    </main>
</body>
</html>
