<?php
$bootstrapCss = 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css';
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gestion Lycée · Bienvenue</title>
    <link href="<?= htmlspecialchars($bootstrapCss, ENT_QUOTES, 'UTF-8') ?>" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body class="public-home">
    <nav class="navbar navbar-expand-lg bg-white border-bottom sticky-top">
        <div class="container">
            <a class="navbar-brand text-primary fw-bold" href="/">Gestion Lycée</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#publicNav" aria-controls="publicNav" aria-expanded="false" aria-label="Ouvrir la navigation"><span class="navbar-toggler-icon"></span></button>
            <div class="collapse navbar-collapse" id="publicNav">
                <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                    <li class="nav-item"><a class="nav-link" href="#accueil">Accueil</a></li>
                    <li class="nav-item"><a class="nav-link" href="#etablissement">Établissement</a></li>
                    <li class="nav-item"><a class="nav-link" href="#formations">Formations</a></li>
                    <li class="nav-item"><a class="nav-link" href="#contact">Contact</a></li>
                    <li class="nav-item"><a class="btn btn-outline-primary btn-sm ms-lg-2" href="/login">Se connecter</a></li>
                    <li class="nav-item"><a class="btn btn-primary btn-sm" href="/inscription-eleve">Inscription élève</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <main>
        <section class="public-hero" id="accueil">
            <img class="public-hero-image" src="https://images.unsplash.com/photo-1509062522246-3755977927d7?auto=format&amp;fit=crop&amp;w=2200&amp;q=88" alt="Élèves réunis dans une salle de classe lumineuse" fetchpriority="high">
            <div class="public-hero-shade"></div>
            <div class="container public-hero-content">
                <div class="public-hero-copy">
                    <p class="text-uppercase fw-semibold small mb-3">Apprendre · grandir · réussir</p>
                    <h1 class="display-4 fw-bold mb-3">L’avenir commence ici.</h1>
                    <p class="lead mb-4">Un lycée engagé pour la réussite de chaque élève, avec un accompagnement attentif et une communauté tournée vers l’avenir.</p>
                    <div class="d-flex flex-wrap gap-2">
                        <a class="btn btn-primary btn-lg" href="/inscription-eleve">Créer mon compte élève</a>
                        <a class="btn btn-light btn-lg" href="#etablissement">Découvrir le lycée</a>
                    </div>
                </div>
            </div>
            <a class="public-scroll-cue" href="#etablissement" aria-label="Découvrir la suite">↓</a>
        </section>

        <section class="container py-5 py-lg-6" id="etablissement">
            <div class="row g-4 align-items-center">
                <div class="col-12 col-lg-5">
                    <p class="text-uppercase text-primary fw-semibold small mb-2">Notre engagement</p>
                    <h2 class="display-6 fw-bold mb-3">Un cadre qui donne envie d’aller plus loin</h2>
                </div>
                <div class="col-12 col-lg-7">
                    <p class="text-secondary mb-0">Nous accompagnons les élèves dans leurs apprentissages et dans la construction de leur projet. L’équipe éducative veille à offrir un environnement accueillant, exigeant et propice à l’épanouissement.</p>
                </div>
            </div>
        </section>

        <section class="public-programs py-5 py-lg-6" id="formations">
            <div class="container">
                <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
                    <div><p class="text-uppercase text-primary fw-semibold small mb-2">Apprendre et se construire</p><h2 class="h1 fw-bold mb-0">Des parcours pour chaque ambition</h2></div>
                    <p class="text-secondary mb-0">Un accompagnement adapté à chaque étape du parcours scolaire.</p>
                </div>
                <div class="row row-cols-1 row-cols-md-3 g-3">
                    <article class="col"><div class="public-program h-100 p-4"><span class="public-program-number">01</span><h3 class="h5 fw-bold mt-4">Culture générale</h3><p class="text-secondary mb-0">Des bases solides pour comprendre le monde et poursuivre ses études avec confiance.</p></div></article>
                    <article class="col"><div class="public-program h-100 p-4"><span class="public-program-number">02</span><h3 class="h5 fw-bold mt-4">Sciences et esprit critique</h3><p class="text-secondary mb-0">Observer, expérimenter et développer les compétences qui préparent aux défis de demain.</p></div></article>
                    <article class="col"><div class="public-program h-100 p-4"><span class="public-program-number">03</span><h3 class="h5 fw-bold mt-4">Langues et ouverture</h3><p class="text-secondary mb-0">Cultiver l’expression, la curiosité et le dialogue avec des horizons variés.</p></div></article>
                </div>
            </div>
        </section>

        <section class="container py-5 py-lg-6" id="contact">
            <div class="public-contact p-4 p-lg-5 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div><p class="text-uppercase fw-semibold small mb-2">Admissions</p><h2 class="h3 fw-bold mb-2">Fais le premier pas vers ton avenir</h2><p class="mb-0">Crée ton compte élève pour commencer ta démarche d’admission.</p></div>
                <a class="btn btn-light" href="/inscription-eleve">Commencer mon inscription</a>
            </div>
        </section>
    </main>

    <footer class="border-top py-4">
        <div class="container d-flex flex-wrap justify-content-between gap-2 small text-secondary"><span>© <?= date('Y') ?> Gestion Lycée</span><a class="link-secondary" href="/login">Espace de connexion</a></div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
