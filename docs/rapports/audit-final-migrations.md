# Rapport final d'audit des migrations SQL

**Projet :** Gestion-Lycée
**Cible :** PostgreSQL 16
**Statut :** rapport statique rédigé avant l'application des migrations ; la mise à jour postérieure ci-dessous décrit leur application locale.

> **Mise à jour postérieure :** après la rédaction de cet audit, à la demande de l'utilisateur, les douze migrations ont été exécutées avec succès sur la base locale `gestion_lycee`, schéma `public`, après vérification que ce schéma ne contenait aucune table. Elles ont créé les 18 tables. Aucune donnée métier ni aucun volume n'a été supprimé ou réinitialisé. Le reste de ce rapport décrit l'audit statique réalisé avant cette exécution.

## Synthèse

- Migrations examinées : **12 sur 12**.
- Migrations modifiées : **12 sur 12**.
- Constats techniques recensés : **15**.
- Constats corrigés dans le schéma : **14**.
- Limite non résoluble par les migrations seules : **1**, la confirmation d'un règlement réel auprès d'un prestataire externe.
- Tables documentées : **18 sur 18**.

Le nombre de constats est un décompte des catégories distinctes ci-dessous, pas un décompte de chaque contrainte individuelle. Les modifications portent sur les fichiers source et la documentation uniquement.

## Problèmes et corrections

| # | Constat | Correction / statut |
|---|---|---|
| 1 | Les codes de rôle acceptaient des valeurs hors des quatre rôles validés et pouvaient différer du libellé. | Domaine limité à `ADMIN`, `DIRECTEUR`, `ENSEIGNANT`, `ELEVE`; code et libellé doivent correspondre. |
| 2 | L'email non normalisé était rejeté plutôt que normalisé avant unicité. | Trigger `BEFORE INSERT/UPDATE` applique `LOWER(BTRIM(email))`; la contrainte et l'index unique restent actifs. |
| 3 | Les dates de naissance et d'inscription avaient des bornes fixes non justifiées (2100 et 2000). | Contraintes retirées ; une politique d'âge éventuelle reste à définir fonctionnellement. |
| 4 | Deux années scolaires pouvaient se chevaucher. | Exclusion GiST sur leurs plages de dates inclusives ; extension requise créée en migration 003. |
| 5 | Les FK de l'inscription et de l'affectation visaient `(classes.id, classes.annee_scolaire_id)` sans contrainte unique correspondante. | `UNIQUE (id, annee_scolaire_id)` ajouté à `classes`, et index enfant composé ajouté. |
| 6 | Une affectation pouvait désigner une matière autre que la spécialité de l'enseignant. | FK composite `(enseignant_id, matiere_id)` vers le profil enseignant ; aucune limite de classes ajoutée. |
| 7 | Le schéma permettait zéro, un ou plusieurs semestres par année et la modification des dates de l'année pouvait rendre des semestres invalides. | Checks différés garantissent exactement deux semestres à l'état final ; triggers vérifient les bornes et protègent les dates parent. |
| 8 | Une évaluation pouvait associer une affectation et un semestre d'années différentes. | Colonnes d'intégrité `classe_id` et `annee_scolaire_id`, FK composites vers affectation et semestre. |
| 9 | Une note pouvait relier un élève sans inscription correspondante dans la classe et l'année de l'évaluation. | Colonnes `classe_id` et `annee_scolaire_id` et FK composites vers l'évaluation et l'inscription. |
| 10 | Un événement webhook pouvait référencer un paiement d'un autre prestataire. | Clé unique `(paiements.id, paiements.provider)` et FK composite depuis l'événement. |
| 11 | Un rôle ordinaire pouvant écrire les tables pouvait définir directement `PAYE`; la fonction de confirmation n'avait pas de contexte de sécurité renforcé. | Fonction `SECURITY DEFINER` avec `search_path` fixe, exécution retirée à `PUBLIC`, trigger bloquant une écriture ordinaire de `PAYE`, et retrait explicite de `UPDATE(statut)` à `PUBLIC`. |
| 12 | Les dates de paiement et de remboursement pouvaient être incohérentes avec l'initiation et entre elles. | Checks chronologiques ; un état payé ou remboursé exige une date de paiement, et remboursé exige aussi sa date de remboursement. |
| 13 | Plusieurs relations composites n'avaient pas d'index enfant adapté ou indexaient seulement une partie des colonnes. | Index composites ajoutés ou réordonnés pour les vérifications FK et les recherches usuelles. |
| 14 | Les migrations 001–010 n'étaient pas chacune explicitement transactionnelles. | `BEGIN`/`COMMIT` ajoutés aux fichiers ; 011 et 012 avaient déjà une transaction. |
| 15 | Une migration SQL ne peut pas prouver qu'un prestataire a effectivement réglé le paiement. | **Non résolu par le schéma seul.** L'appel privilégié doit rester après une vérification serveur-à-serveur fiable dans l'application. Aucun service de paiement PHP n'existe dans le dépôt. |

## Contraintes et choix structurants

- Les FKs utilisent `ON DELETE RESTRICT`, sauf l'acteur d'audit qui utilise `ON DELETE SET NULL`. Aucune suppression en cascade n'a été introduite. Les mises à jour référentielles restent en cascade comme dans les migrations initiales.
- Les paires et triplets uniques sur `id` sont techniquement redondants avec la PK simple mais nécessaires comme cibles PostgreSQL des FK composites. Les index enfant suivent l'ordre des colonnes de ces FK.
- Les colonnes `classe_id` et `annee_scolaire_id` dans évaluations et notes sont des clés de cohérence dénormalisées. Leur coût est quelques colonnes et index supplémentaires ; elles permettent de garantir les relations par FK déclaratives sans trigger de vérification métier.
- Les dates de fin sont considérées inclusives, alignées sur la convention déjà utilisée par les semestres. Deux années partageant un même jour de borne sont donc en chevauchement. À confirmer avec le calendrier réel du lycée.
- La création d'une année et de ses deux semestres doit se faire dans la même transaction, du fait du contrôle différé. Cette exigence est délibérée pour garantir exactement `S1` et `S2` sans autoriser un état final incomplet.
- Les migrations sont ordonnées ainsi : rôles, utilisateurs, années scolaires, structure scolaire, matières/profils, inscriptions, affectations, semestres, évaluations, notes, paiements/webhooks, audit.
- Les index uniques composites supplémentaires sur inscription, affectation, évaluation et paiement/webhook servent directement aux FK et ne sont pas des doublons accidentels.

## Ambiguïtés et risques restants

1. **Rôle applicatif PostgreSQL :** le dépôt ne définit pas de compte séparé. Il faudra un compte d'application non propriétaire des tables, auquel l'administrateur accordera explicitement `EXECUTE` sur `confirmer_paiement`. Le rôle propriétaire doit rester réservé aux migrations et à l'administration.
2. **Vérification de paiement :** la vérification externe de l'état du paiement et des remboursements doit être développée dans le service PHP. `signature_verifiee` signifie uniquement qu'une vérification de signature a été faite ; cela ne prouve pas le règlement. Aucun trigger webhook n'appelle `confirmer_paiement`.
3. **Éligibilité aux notes :** la FK impose une inscription liée à la bonne classe/année, mais ne décide pas si `PENDING` ou `ANNULEE` autorise une note. Le statut admissible doit être approuvé avant d'ajouter un trigger.
4. **Date d'évaluation :** le schéma impose que le semestre et l'affectation partagent leur année, mais n'impose pas que `date_evaluation` soit comprise dans les bornes du semestre. Cette règle n'était pas explicitement validée.
5. **Date de naissance :** la borne arbitraire a été retirée. Une limite d'âge ou un rejet des dates futures nécessite une règle dédiée, notamment parce qu'une contrainte `CHECK` ne doit pas dépendre de la date courante.
6. **Privilèges de confiance :** propriétaire de schéma et superutilisateur peuvent désactiver triggers, modifier les tables ou contourner les règles. Les protections visent les opérations ordinaires, pas un administrateur hostile.
7. **Migrations déjà appliquées :** l'état de la base n'a pas été consulté et n'est pas connu. Les fichiers corrigés décrivent le schéma cible pour un déploiement contrôlé ; modifier un ancien fichier ne met pas à jour une base existante. Il faudra décider manuellement entre déploiement neuf et migrations correctives vers l'avant.
8. **Extensions et transaction :** le rôle exécutant la migration 003 doit pouvoir installer `btree_gist`. Le dépôt ne fournit ni runner, ni mécanisme de journalisation des migrations ; l'opérateur devra exécuter les fichiers dans l'ordre et suivre leur état.
9. **Colonnes `updated_at` :** elles ont une valeur par défaut à la création mais ne sont pas actualisées automatiquement par la base. L'application doit les modifier ou cette politique doit être validée.

## Fichiers modifiés

**Migrations SQL (12) :**

- `database/migrations/001_create_roles.sql`
- `database/migrations/002_create_utilisateurs.sql`
- `database/migrations/003_create_annees_scolaires.sql`
- `database/migrations/004_create_structure_scolaire.sql`
- `database/migrations/005_create_eleves_enseignants.sql`
- `database/migrations/006_create_inscriptions.sql`
- `database/migrations/007_create_affectations_enseignants.sql`
- `database/migrations/008_create_semestres.sql`
- `database/migrations/009_create_evaluations.sql`
- `database/migrations/010_create_notes.sql`
- `database/migrations/011_create_paiements.sql`
- `database/migrations/012_create_audit_logs.sql`

**Documentation mise à jour :** `docs/propositions/audit-schema.md` est marqué comme audit historique, antérieur aux corrections.

## Fichiers créés

- `docs/technique/schema-base-de-donnees.md`
- `docs/uml/diagramme-classes.mmd`
- `docs/uml/diagramme-relations.mmd`
- `docs/rapports/audit-final-migrations.md` (ce rapport)

## Vérifications statiques effectuées avant l'exécution

- Lecture des 12 migrations et vérification statique des dépendances et types de colonnes des FK composites.
- Vérification de l'ordre de création des tables et contraintes référencées.
- Revue des clés primaires, `UNIQUE`, `CHECK`, valeurs par défaut, index, actions `ON DELETE`/`ON UPDATE`, fonctions, triggers, transactions et révocations de privilèges.
- Vérification que les diagrammes et le dictionnaire décrivent les 18 tables et les FK composites finales.
- Contrôles de diagnostics du workspace effectués sur les fichiers modifiés ; ils ne constituent pas une exécution PostgreSQL ni un test du parseur SQL.
- Aucun test dépendant d'une base n'a été lancé pendant l'audit statique. À cette étape, aucune commande SQL, migration, commande Docker ou opération sur volume n'avait été exécutée.

## Opérations manuelles restantes

- Faire valider les ambiguïtés métier ci-dessus.
- Établir l'état réel des migrations dans l'environnement cible sans réexécuter les fichiers existants à l'aveugle ; préparer des migrations correctives vers l'avant si nécessaire.
- Planifier sauvegarde, fenêtre de déploiement, droits pour `btree_gist`, rôle applicatif non propriétaire et octroi explicite d'exécution de la fonction de paiement.
- Implémenter et vérifier côté serveur l'état réel auprès du prestataire avant tout appel à `confirmer_paiement`.
- Après approbation et sauvegarde, faire effectuer par l'exploitant un test de création du schéma sur une base de validation isolée. Aucun tel test n'a été fait ici.

## Confirmation de périmètre

**Lors de la rédaction initiale de ce rapport, aucune migration ni commande SQL n'avait été exécutée. Par la suite, les 12 migrations ont été appliquées à la base locale `gestion_lycee` après vérification que son schéma `public` était vide. Aucune donnée métier n'a été insérée et aucun volume n'a été supprimé ou réinitialisé.**