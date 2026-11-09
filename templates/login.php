<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Connexion · Gestion Lycée</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body class="bg-white">
    <main class="container min-vh-100 d-flex align-items-center justify-content-center py-4">
        <div class="login-card w-100 p-3 p-sm-4 bg-white">
            <header class="mb-3">
                <div class="login-brand">Gestion Lycée</div>
                <div class="login-kicker text-uppercase mt-1">Espace scolaire sécurisé</div>
            </header>

            <img class="login-photo mb-4" src="https://images.unsplash.com/photo-1509062522246-3755977927d7?auto=format&amp;fit=crop&amp;w=1000&amp;q=85" alt="Élèves dans une salle de classe" fetchpriority="high">

            <h1 class="h3 fw-bold mb-1">Bienvenue</h1>
            <p class="small text-secondary mb-4">Connectez-vous à votre espace Gestion Lycée.</p>

            <?php if (!empty($success)): ?><div class="alert alert-success small" role="status"><?= htmlspecialchars((string) $success, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger small" role="alert">
                    <?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <form method="post" action="/login">
                <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                <div class="mb-3">
                    <label class="form-label" for="email">Adresse e-mail</label>
                    <input class="form-control" type="email" id="email" name="email" value="<?= htmlspecialchars((string) ($email ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="prenom.nom@lycee.fr" autocomplete="username" required autofocus>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="password">Mot de passe</label>
                    <div class="input-group">
                        <input class="form-control" type="password" id="password" name="password" autocomplete="current-password" required>
                        <button class="btn btn-outline-secondary" type="button" data-password-toggle aria-label="Afficher le mot de passe">Afficher</button>
                    </div>
                </div>
                <button class="btn btn-primary w-100 py-2 mt-1" type="submit">Se connecter</button>
            </form>

            <p class="small text-secondary mt-3 mb-1">Votre accès est adapté à votre rôle.</p>
            <p class="small mb-1"><a href="/inscription-eleve">Créer un compte élève</a></p>
            <p class="small text-secondary mb-0">Besoin d’aide ? Contactez le secrétariat.</p>
        </div>
    </main>
    <script src="/assets/js/login.js" defer></script>
</body>
</html>
