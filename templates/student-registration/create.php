<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Créer un compte élève · Gestion Lycée</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body class="bg-white">
    <main class="container min-vh-100 d-flex align-items-center justify-content-center py-4">
        <section class="login-card w-100 p-3 p-sm-4 bg-white">
            <a class="small link-primary text-decoration-none" href="/login">← Retour à la connexion</a>
            <h1 class="h3 fw-bold mt-3 mb-1">Créer un compte élève</h1>
            <p class="small text-secondary mb-4">Ton compte sera limité à ton espace élève et à tes propres informations.</p>

            <?php if (!empty($error)): ?><div class="alert alert-danger small" role="alert"><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>

            <form method="post" action="/inscription-eleve" enctype="multipart/form-data">
                <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                <div class="row g-3">
                    <div class="col-12 col-sm-6"><label class="form-label" for="prenom">Prénom</label><input class="form-control" id="prenom" name="prenom" value="<?= htmlspecialchars((string) ($values['prenom'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" autocomplete="given-name" required maxlength="100"></div>
                    <div class="col-12 col-sm-6"><label class="form-label" for="nom">Nom</label><input class="form-control" id="nom" name="nom" value="<?= htmlspecialchars((string) ($values['nom'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" autocomplete="family-name" required maxlength="100"></div>
                    <div class="col-12"><label class="form-label" for="email">Adresse e-mail</label><input class="form-control" id="email" type="email" name="email" value="<?= htmlspecialchars((string) ($values['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" autocomplete="email" required maxlength="255"></div>
                    <div class="col-12"><label class="form-label" for="date_naissance">Date de naissance</label><input class="form-control" id="date_naissance" type="date" name="date_naissance" value="<?= htmlspecialchars((string) ($values['date_naissance'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" autocomplete="bday" required></div>
                    <div class="col-12"><label class="form-label" for="password">Mot de passe</label><input class="form-control" id="password" type="password" name="password" minlength="12" autocomplete="new-password" required><div class="form-text">12 caractères minimum.</div></div>
                    <div class="col-12"><label class="form-label" for="photo">Photo d’identité</label><input class="form-control" id="photo" type="file" name="photo" accept="image/jpeg,image/png,image/webp" required><div class="form-text">JPEG, PNG ou WebP, 2 Mo maximum. Visible uniquement dans ton espace.</div></div>
                </div>
                <button class="btn btn-primary w-100 py-2 mt-4" type="submit">Créer mon compte élève</button>
            </form>
        </section>
    </main>
</body>
</html>