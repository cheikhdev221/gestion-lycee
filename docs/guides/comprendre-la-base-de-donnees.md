# Comprendre la base de données du lycée

Ce guide explique simplement ce que nous avons préparé et comment regarder les tables depuis le terminal. Il ne faut pas connaître SQL pour le lire.

## Ce qui a été fait

Le projet utilise PostgreSQL pour conserver les informations. Nous avons préparé 12 fichiers SQL, appelés **migrations**. Une migration est une recette qui demande à PostgreSQL de créer une partie de la structure.

Après avoir vérifié que la base locale `gestion_lycee` ne contenait aucune table, nous avons exécuté les 12 migrations dans l'ordre. Elles ont toutes terminé correctement. La base locale contient maintenant 18 tables.

Les migrations ont créé les tables et leurs règles. Elles n'ont pas créé d'élèves, de comptes ou d'autres données d'exemple. Elles n'ont pas écrit la logique de l'application : pour le moment, le PHP n'utilise pas encore ces tables.

Cette exécution concerne la base locale de Docker, pas une base de production.

## C'est quoi une table ?

Une table ressemble à une feuille de calcul :

- les **colonnes** décrivent les informations, par exemple `nom`, `email` ou `date_debut` ;
- les **lignes** sont les enregistrements, par exemple une personne ou une année scolaire.

La migration crée les colonnes et les règles. Tant qu'aucune donnée n'est ajoutée, la table existe mais ne contient pas d'enregistrement.

## Les 18 tables, en mots simples

**Comptes et école**

- `roles` : les types de compte autorisés, comme ADMIN ou ELEVE.
- `utilisateurs` : les comptes, leurs noms, leurs emails et le hash du mot de passe.
- `annees_scolaires` : les années scolaires et leurs dates.
- `niveaux` : les niveaux d'étude.
- `series` : les séries proposées par le lycée.
- `series_niveaux` : les associations qui disent quelles séries existent à quels niveaux.
- `classes` : les classes d'un niveau, d'une série et d'une année scolaire.
- `matieres` : les matières enseignées.

**Élèves et enseignement**

- `eleves` : les informations propres aux élèves, reliées à leur compte utilisateur.
- `enseignants` : le profil des enseignants et leur matière de spécialisation.
- `inscriptions` : l'inscription d'un élève dans une classe pour une année.
- `affectations_enseignants` : les classes où un enseignant enseigne sa matière.
- `semestres` : les périodes S1 et S2 d'une année scolaire.
- `evaluations` : les devoirs et compositions associés à une affectation et un semestre.
- `notes` : la note d'un élève pour une évaluation.

**Paiements et traçabilité**

- `paiements` : les paiements associés aux inscriptions.
- `paiement_webhook_events` : les notifications reçues des prestataires de paiement.
- `audit_logs` : les événements que l'application pourra journaliser.

## Comment les tables sont reliées

Une **clé** est une valeur qui sert à retrouver une ligne. Les identifiants `id` sont créés automatiquement.

- Une ligne dans `utilisateurs` possède un rôle indiqué dans `roles`.
- Un élève ou un enseignant possède un compte dans `utilisateurs`.
- Une classe référence son niveau, sa série et son année. La paire série/niveau doit exister dans `series_niveaux`.
- Une inscription relie un élève, une classe et une année.
- Une affectation relie un enseignant, sa matière, une classe et une année.
- Une évaluation relie une affectation et un semestre de la même année.
- Une note relie une évaluation et une inscription de l'élève dans la classe correspondante.
- Un paiement appartient à une inscription ; ses événements webhook pointent vers le paiement correspondant.

Le dessin complet de ces liens est dans [le diagramme des relations](../uml/diagramme-relations.mmd). Le [diagramme de classes](../uml/diagramme-classes.mmd) donne aussi les colonnes principales.

## Quelques mots techniques

- **Clé primaire (`PRIMARY KEY`)** : identifiant unique d'une ligne, souvent `id`.
- **Clé étrangère (`FOREIGN KEY`)** : lien vers une ligne d'une autre table. Elle empêche, par exemple, une inscription de pointer vers une classe inexistante.
- **`UNIQUE`** : interdit les doublons pour une valeur ou un groupe de colonnes, comme l'email d'un compte.
- **`CHECK`** : impose une règle de valeur, par exemple une note entre 0 et 20.
- **Valeur par défaut (`DEFAULT`)** : valeur utilisée automatiquement si on n'en fournit pas, comme le statut initial `PENDING`.
- **Index** : aide PostgreSQL à retrouver des lignes plus rapidement. C'est l'équivalent d'un index à la fin d'un livre.
- **Trigger** : petite règle exécutée automatiquement par PostgreSQL lors d'une action. Par exemple, l'email est mis en minuscules à l'enregistrement.
- **Transaction (`BEGIN` / `COMMIT`)** : groupe de changements validé d'un seul bloc. Si une migration échoue avant son `COMMIT`, ses changements sont annulés.

## Regarder les tables dans le terminal

Ouvre un terminal dans le dossier du projet. Pour afficher les noms des tables :

```bash
docker compose exec db sh -c 'psql -U "$POSTGRES_USER" -d "$POSTGRES_DB" -c "\\dt"'
```

Pour voir les colonnes et les règles d'une table, remplace `utilisateurs` par le nom voulu :

```bash
docker compose exec db sh -c 'psql -U "$POSTGRES_USER" -d "$POSTGRES_DB" -c "\\d utilisateurs"'
```

Pour lire quelques lignes sans rien modifier :

```bash
docker compose exec db sh -c 'psql -U "$POSTGRES_USER" -d "$POSTGRES_DB" -c "SELECT * FROM utilisateurs LIMIT 10;"'
```

`SELECT` sert à lire. `LIMIT 10` demande au maximum dix lignes. Si la table ne contient pas encore de données, le résultat sera vide : c'est normal.

On peut aussi ouvrir une session PostgreSQL interactive :

```bash
docker compose exec db sh -c 'psql -U "$POSTGRES_USER" -d "$POSTGRES_DB"'
```

Dans cette session, tape `\dt` pour voir les tables, `\d utilisateurs` pour décrire une table, une requête `SELECT` pour lire, puis `\q` pour quitter. Ne tape pas `\` devant une requête SQL ordinaire.

Évite les commandes `INSERT`, `UPDATE` et `DELETE` tant que tu ne sais pas exactement ce qu'elles changent : elles écrivent dans la base.

## Ce qui reste à construire

Les tables sont le rangement et les règles de cohérence ; elles ne constituent pas encore l'application complète. Le PHP devra plus tard lire et écrire dans PostgreSQL pour créer des comptes, inscrire les élèves, saisir les notes, etc.

Avant de développer les paiements, il faudra faire vérifier le paiement auprès du prestataire par le serveur. La simple réception d'un webhook ne prouve pas qu'un paiement a été réglé. Il faudra aussi revoir les transitions de statuts de paiement et définir quel compte PostgreSQL l'application utilisera. Ces points sont expliqués dans le [rapport d'audit](../rapports/audit-final-migrations.md).

Les migrations ont déjà été appliquées dans la base locale actuelle. Modifier un fichier de migration après coup ne changera pas automatiquement cette base ; une modification future du schéma devra être planifiée et appliquée séparément.