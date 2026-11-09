<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Modifier une année scolaire</title>
</head>
<body>
    <h1>Modifier l’année scolaire <?= htmlspecialchars($annee->libelle, ENT_QUOTES, 'UTF-8') ?></h1>
    <?php if (!empty($error)): ?><p><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
    <form method="post" action="/annees/update">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="id" value="<?= (int) $annee->id ?>">
        <p><label for="libelle">Libellé</label><br><input id="libelle" name="libelle" value="<?= htmlspecialchars($annee->libelle, ENT_QUOTES, 'UTF-8') ?>" required></p>
        <p><label for="date_debut">Date de début</label><br><input id="date_debut" type="date" name="date_debut" value="<?= htmlspecialchars((string) $annee->dateDebut, ENT_QUOTES, 'UTF-8') ?>" required></p>
        <p><label for="date_fin">Date de fin</label><br><input id="date_fin" type="date" name="date_fin" value="<?= htmlspecialchars((string) $annee->dateFin, ENT_QUOTES, 'UTF-8') ?>" required></p>
        <p><label for="statut">Statut</label><br>
            <select id="statut" name="statut">
                <option value="A_VENIR" <?= $annee->statut === 'A_VENIR' ? 'selected' : '' ?>>À venir</option>
                <option value="EN_COURS" <?= $annee->statut === 'EN_COURS' ? 'selected' : '' ?>>En cours</option>
                <option value="TERMINEE" <?= $annee->statut === 'TERMINEE' ? 'selected' : '' ?>>Terminée</option>
            </select>
        </p>
        <button type="submit">Enregistrer</button>
        <a href="/annees">Annuler</a>
    </form>
</body>
</html>