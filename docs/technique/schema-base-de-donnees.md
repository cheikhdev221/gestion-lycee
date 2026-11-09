# Schéma de la base de données

**Statut : documentation technique du schéma défini par les migrations, à valider avant déploiement hors environnement local.** Après l'audit initial, les 12 migrations ont été appliquées avec succès à la base locale `gestion_lycee`, schéma `public`, qui ne contenait alors aucune table. Aucune donnée métier n'a été insérée pendant cette opération. Cette exécution locale ne valide pas un déploiement de production.

## Présentation

Le projet utilise PostgreSQL 16. Les douze migrations créent 18 tables. Elles sont ordonnées par dépendances et chaque fichier est transactionnel. L'extension `btree_gist`, nécessaire aux contraintes d'exclusion combinant des clés scalaires et des plages, est installée dans la migration 003.

Le code PHP présent ne se connecte pas à la base et ne contient pas encore de services métier pour les inscriptions, les évaluations, les notes ou les paiements. Le schéma porte donc les contraintes documentées ici ; les intégrations applicatives restent à développer. Le dépôt ne contient pas de programme de migration ni de données de seed.

Le fichier `.env` est fourni à PostgreSQL par Docker Compose ; la configuration PHP d'accès à la base n'est pas présente. Le README mentionne Bootstrap, mais aucun asset frontend n'est présent.

## Dictionnaire de données

`PK` : clé primaire ; `FK` : clé étrangère ; `UQ` : unicité ; `IDENTITY` : valeur générée ; `NULL` : valeur facultative. Toutes les colonnes non marquées `NULL` sont `NOT NULL`.

| Table | Colonnes |
|---|---|
| `roles` | `id SMALLINT PK IDENTITY`; `code VARCHAR(30) UQ`; `libelle VARCHAR(100) UQ`; `description TEXT NULL`; `created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP` |
| `utilisateurs` | `id INTEGER PK IDENTITY`; `role_id SMALLINT FK`; `nom VARCHAR(100)`; `prenom VARCHAR(100)`; `email VARCHAR(255) UQ`; `password_hash VARCHAR(255)`; `actif BOOLEAN DEFAULT TRUE`; `created_at`, `updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP` |
| `annees_scolaires` | `id SMALLINT PK IDENTITY`; `libelle VARCHAR(20) UQ`; `date_debut DATE`; `date_fin DATE`; `statut VARCHAR(15) DEFAULT 'A_VENIR'`; `created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP` |
| `niveaux` | `id SMALLINT PK IDENTITY`; `code VARCHAR(20) UQ`; `libelle VARCHAR(100) UQ`; `ordre SMALLINT` |
| `series` | `id SMALLINT PK IDENTITY`; `code VARCHAR(20) UQ`; `libelle VARCHAR(100) UQ` |
| `series_niveaux` | `id INTEGER PK IDENTITY`; `serie_id SMALLINT FK`; `niveau_id SMALLINT FK`; paire `(serie_id, niveau_id) UQ` |
| `classes` | `id INTEGER PK IDENTITY`; `libelle VARCHAR(100)`; `niveau_id SMALLINT FK`; `serie_id SMALLINT FK`; `annee_scolaire_id SMALLINT FK`; paires `(id, annee_scolaire_id)` et `(libelle, annee_scolaire_id)` UQ |
| `matieres` | `id SMALLINT PK IDENTITY`; `code VARCHAR(20) UQ`; `libelle VARCHAR(100) UQ`; `description TEXT NULL`; `created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP` |
| `eleves` | `id INTEGER PK IDENTITY`; `utilisateur_id INTEGER FK UQ`; `matricule VARCHAR(20) UQ`; `date_naissance DATE`; `created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP` |
| `enseignants` | `id INTEGER PK IDENTITY`; `utilisateur_id INTEGER FK UQ`; `matiere_id SMALLINT FK`; paire `(id, matiere_id) UQ`; `created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP` |
| `inscriptions` | `id INTEGER PK IDENTITY`; `eleve_id INTEGER FK`; `classe_id INTEGER`; `annee_scolaire_id SMALLINT`; paire `(classe_id, annee_scolaire_id) FK`; paires `(eleve_id, annee_scolaire_id)` et `(eleve_id, classe_id, annee_scolaire_id)` UQ; `statut VARCHAR(20) DEFAULT 'PENDING'`; `date_inscription DATE DEFAULT CURRENT_DATE`; `created_at`, `updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP` |
| `affectations_enseignants` | `id INTEGER PK IDENTITY`; `enseignant_id INTEGER`; `classe_id INTEGER`; `matiere_id SMALLINT`; `annee_scolaire_id SMALLINT`; paire `(enseignant_id, matiere_id) FK`; paire `(classe_id, annee_scolaire_id) FK`; triplet `(id, classe_id, annee_scolaire_id) UQ`; unicité `(enseignant_id, matiere_id, classe_id, annee_scolaire_id)`; `created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP` |
| `semestres` | `id SMALLINT PK IDENTITY`; `annee_scolaire_id SMALLINT FK`; `code VARCHAR(10)`; `libelle VARCHAR(100)`; `date_debut`, `date_fin DATE`; paires `(code, annee_scolaire_id)`, `(libelle, annee_scolaire_id)` et `(id, annee_scolaire_id) UQ`; `created_at`, `updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP` |
| `evaluations` | `id INTEGER PK IDENTITY`; `affectation_id INTEGER`; `semestre_id SMALLINT`; `classe_id INTEGER`; `annee_scolaire_id SMALLINT`; triplet `(affectation_id, classe_id, annee_scolaire_id) FK`; paire `(semestre_id, annee_scolaire_id) FK`; triplet `(id, classe_id, annee_scolaire_id) UQ`; `libelle VARCHAR(150)`; `type VARCHAR(20)`; `coefficient NUMERIC(5,2) DEFAULT 1.00`; `date_evaluation DATE`; `description TEXT NULL`; `created_at`, `updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP` |
| `notes` | `id INTEGER PK IDENTITY`; `evaluation_id INTEGER`; `eleve_id INTEGER`; `classe_id INTEGER`; `annee_scolaire_id SMALLINT`; triplet `(evaluation_id, classe_id, annee_scolaire_id) FK`; triplet `(eleve_id, classe_id, annee_scolaire_id) FK`; `valeur NUMERIC(5,2)`; `commentaire TEXT NULL`; `saisi_par INTEGER FK`; `date_saisie`, `created_at`, `updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP`; paire `(evaluation_id, eleve_id) UQ` |
| `paiements` | `id INTEGER PK IDENTITY`; `inscription_id INTEGER FK`; `transaction_ref VARCHAR(100) UQ`; `provider_transaction_ref VARCHAR(150) NULL`; `provider VARCHAR(20)`; paire `(id, provider) UQ`; paire `(provider, provider_transaction_ref) UQ`; `statut VARCHAR(20) DEFAULT 'INITIE'`; `montant NUMERIC(12,2)`; `devise CHAR(3) DEFAULT 'XOF'`; `description TEXT NULL`; `date_initiation`, `created_at`, `updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP`; `date_paiement`, `date_remboursement TIMESTAMPTZ NULL` |
| `paiement_webhook_events` | `id INTEGER PK IDENTITY`; `paiement_id INTEGER`; `provider VARCHAR(20)`; paire `(paiement_id, provider) FK`; `event_ref VARCHAR(150)`; paire `(provider, event_ref) UQ`; `type_evenement VARCHAR(100)`; `payload JSONB`; `signature_verifiee BOOLEAN DEFAULT FALSE`; `traite BOOLEAN DEFAULT FALSE`; `date_reception TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP`; `date_traitement TIMESTAMPTZ NULL` |
| `audit_logs` | `id INTEGER PK IDENTITY`; `utilisateur_id INTEGER NULL FK`; `action VARCHAR(100)`; `entite VARCHAR(100)`; `entite_id INTEGER NULL`; `anciennes_donnees`, `nouvelles_donnees JSONB NULL`; `adresse_ip INET NULL`; `user_agent TEXT NULL`; `request_id VARCHAR(100) NULL`; `created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP` |

Les champs `classe_id` et `annee_scolaire_id` de `evaluations` et `notes` répètent des valeurs accessibles via leurs relations. Ils sont conservés comme colonnes de cohérence afin que les FK composites puissent garantir, sans trigger métier, l'accord entre affectation, semestre, inscription et note. Les clés uniques composites sur les identifiants `id` sont également nécessaires comme cibles des FK composées, bien que `id` soit déjà une clé primaire.

## Relations

Les deux diagrammes sont des fichiers Mermaid autonomes : [diagramme de classes](../uml/diagramme-classes.mmd) et [diagramme des relations](../uml/diagramme-relations.mmd).

- Un utilisateur possède exactement un rôle. Un utilisateur peut avoir zéro ou un profil élève et zéro ou un profil enseignant ; l'accord entre le rôle et le profil spécialisé n'est pas imposé par le schéma.
- Une classe référence un niveau, une série, une année scolaire et une paire existante dans `series_niveaux`. Son libellé est unique dans une année.
- Un élève a au plus une inscription par année. L'inscription référence une classe de cette même année.
- Une affectation référence un enseignant et la matière de spécialisation de son profil, ainsi qu'une classe de la même année. Plusieurs classes sont possibles pour le même enseignant.
- Une évaluation référence une affectation et un semestre de la même année et de la classe correspondante.
- Une note référence une évaluation et une inscription de l'élève dans la classe et l'année de cette évaluation. Le schéma ne filtre pas les statuts d'inscription pour autoriser une note.
- Un paiement référence une inscription. Un événement webhook référence un paiement avec le même prestataire.
- Un journal d'audit peut référencer un utilisateur ; supprimer cet utilisateur conserve le journal et met son acteur à `NULL`.

## Contraintes importantes

### Rôles, comptes et données scolaires

- Les quatre couples `code`/`libelle` acceptés sont `ADMIN`, `DIRECTEUR`, `ENSEIGNANT` et `ELEVE`; code et libellé doivent être égaux.
- Un trigger applique `LOWER(BTRIM(email))` avant les contraintes d'unicité et de non-vacuité. Les mots de passe doivent commencer par le marqueur Argon2id (`$argon2id$`). Le SQL vérifie le format préfixe, pas le calcul du hash ni ses paramètres.
- Les années ont un libellé au format `AAAA-AAAA`, avec deux années consécutives, et `date_fin > date_debut`. Un index unique partiel limite à une année `EN_COURS`. Une contrainte d'exclusion interdit les périodes d'années qui se chevauchent.
- Les plages de dates sont traitées comme inclusives (`daterange(..., '[]')`), conformément à la convention déjà utilisée par les semestres. Deux années qui partagent le même jour de borne sont donc considérées comme chevauchantes.
- Une année doit avoir exactement deux semestres distincts `S1` et `S2`. Des constraint triggers différés vérifient l'état final à la fin de la transaction ; la création d'une année et de ses deux semestres doit donc être atomique. La mise à jour des dates d'une année est rejetée si elle rend un semestre existant hors période.
- Les périodes de semestres ne se chevauchent pas dans une même année et un trigger vérifie leurs limites par rapport aux dates de l'année. La contrainte `date_fin > date_debut` s'applique.
- Une paire série/niveau doit exister avant la création de sa classe. Un élève ne peut avoir qu'une inscription par année. Les contraintes fixes antérieures sur les dates de naissance et d'inscription (années 2100 et 2000) ont été retirées, faute de justification métier.

### Affectations, évaluations et notes

- La FK composite de l'affectation vers `(enseignant_id, matiere_id)` garantit la concordance avec la spécialité unique de l'enseignant. La clé unique comprend la classe et l'année et n'impose aucune limite d'une affectation par enseignant.
- Les FK composites d'évaluation imposent que l'affectation, la classe et le semestre appartiennent à la même année. Les types sont `DEVOIR` et `COMPOSITION`; le coefficient doit être strictement positif.
- Les FK composites de note imposent une inscription dans la classe et l'année de l'évaluation. Un élève ne peut avoir qu'une note par évaluation ; la valeur est comprise entre 0 et 20. `saisi_par` conserve le lien vers l'utilisateur ayant saisi la note.
- Aucun statut (`PENDING`, `PAYE`, `ANNULEE`) n'est choisi comme critère d'éligibilité aux notes. Cette politique doit être décidée avant de la restreindre.

### Paiements et audit

- Les prestataires sont `WAVE` et `CARTE`; le montant est positif; la devise est au format de trois lettres et vaut `XOF` par défaut. Les références internes sont uniques ; la paire prestataire/référence fournisseur est unique lorsque cette dernière n'est pas `NULL`.
- Un paiement `PAYE` ou `REMBOURSE` exige une date de paiement ; un paiement remboursé exige aussi sa date de remboursement. Les dates ne peuvent pas précéder l'initiation et un remboursement ne peut précéder le paiement.
- Un webhook est dédupliqué par `(provider, event_ref)`, lié à un paiement du même prestataire et ne peut être marqué traité sans signature marquée vérifiée. Ces colonnes ne prouvent pas à elles seules que le prestataire a effectivement réglé le paiement.
- Les journaux d'audit ont des checks de format, un index temporel, des index GIN sur les données JSON et des triggers qui refusent `UPDATE`, `DELETE` et `TRUNCATE`. Les permissions sont retirées à `PUBLIC`. Le propriétaire de la table et un superutilisateur restent des acteurs de confiance pouvant modifier ces protections.

## Index

Les clés primaires et contraintes `UNIQUE` créent leurs index uniques. Les index explicites sont :

| Tables | Index explicites |
|---|---|
| `utilisateurs` | `idx_utilisateurs_role(role_id)` |
| `annees_scolaires` | `uq_annees_scolaires_active(statut)` partiel sur `EN_COURS`; index GiST de la contrainte d'exclusion |
| `series_niveaux` | `idx_series_niveaux_niveau(niveau_id)` |
| `classes` | `idx_classes_niveau(niveau_id)`, `idx_classes_serie_niveau(serie_id, niveau_id)`, `idx_classes_annee(annee_scolaire_id)` |
| `enseignants` | `idx_enseignants_matiere(matiere_id)` |
| `inscriptions` | `idx_inscriptions_classe_annee(classe_id, annee_scolaire_id)`, `idx_inscriptions_annee_classe(annee_scolaire_id, classe_id)` |
| `affectations_enseignants` | `idx_affectations_classe_matiere(classe_id, matiere_id)`, `idx_affectations_classe_annee(classe_id, annee_scolaire_id)`, `idx_affectations_annee(annee_scolaire_id)` |
| `semestres` | `idx_semestres_annee(annee_scolaire_id)`; index GiST de l'exclusion des périodes |
| `evaluations` | `idx_evaluations_affectation_annee(affectation_id, classe_id, annee_scolaire_id)`, `idx_evaluations_semestre_annee(semestre_id, annee_scolaire_id)`, `idx_evaluations_date(date_evaluation)` |
| `notes` | `idx_notes_eleve_classe_annee(eleve_id, classe_id, annee_scolaire_id)`, `idx_notes_evaluation_classe_annee(evaluation_id, classe_id, annee_scolaire_id)`, `idx_notes_saisi_par(saisi_par)` |
| `paiements` | `idx_paiements_inscription(inscription_id)`, `idx_paiements_statut(statut)`, `idx_paiements_created_at(created_at DESC)` |
| `paiement_webhook_events` | `idx_webhook_paiement_provider(paiement_id, provider)`, `idx_webhook_non_traites(date_reception)` partiel sur `traite = FALSE` |
| `audit_logs` | `idx_audit_logs_utilisateur(utilisateur_id)`, `idx_audit_logs_action(action)`, `idx_audit_logs_entite(entite, entite_id)`, `idx_audit_logs_created_at(created_at DESC)`, index partiel `idx_audit_logs_request_id(request_id)`, `idx_audit_logs_nouvelles_donnees` et `idx_audit_logs_anciennes_donnees` en GIN |

Les index des FK composites sont conservés sur les colonnes et dans l'ordre utilisés par les relations. Les index uniques composites redondants sur `id` sont le coût de l'intégrité déclarative des FK composites.

## Fonctions et triggers

| Objet | Rôle |
|---|---|
| `normaliser_email_utilisateur` / `trg_normaliser_email_utilisateur` | Normaliser l'email avant insertion ou changement. |
| `verifier_periode_semestre` / `trg_verifier_periode_semestre` | Verrouiller et vérifier les limites de l'année lors de l'écriture d'un semestre. |
| `verifier_nouvelles_limites_annee_scolaire` / `trg_verifier_limites_annee_scolaire` | Refuser une modification d'année qui exclurait un semestre existant. |
| `verifier_deux_semestres_par_annee` / triggers de contrainte différés sur année et semestre | Garantir exactement deux lignes semestres à l'état final de chaque transaction et sérialiser le contrôle sur la ligne d'année. |
| `confirmer_paiement` | Verrouiller paiement puis inscription, accepter un paiement `INITIE` ou `EN_ATTENTE` et une inscription `PENDING`, puis passer les deux à `PAYE`. Un appel répété sur un paiement déjà payé ne modifie rien. |
| `proteger_transition_paye` / triggers sur paiement et inscription | Refuser une transition ordinaire vers `PAYE`; elle n'est autorisée que depuis un contexte `SECURITY DEFINER`. |
| `interdire_modification_audit` / trigger `trg_audit_logs_immutable` | Refuser la modification et la suppression ligne à ligne. |
| `interdire_truncate_audit` / trigger `trg_audit_logs_no_truncate` | Refuser le vidage complet de l'audit. |

## Confirmation fiable d'un paiement

`confirmer_paiement` ne contacte aucun prestataire et n'interprète pas les webhooks. Le service PHP devra vérifier l'état réel auprès de WAVE ou du processeur carte par un canal fiable, puis seulement appeler la fonction. Un webhook peut déclencher une vérification serveur-à-serveur, mais sa réception ou sa signature seule ne vaut pas confirmation de règlement. Aucun service PHP de paiement n'existe dans le dépôt.

La fonction est `SECURITY DEFINER`, fixe son `search_path`, et son exécution est retirée à `PUBLIC`. Les mises à jour directes du statut vers `PAYE` sont également bloquées pour les sessions ordinaires. L'application doit utiliser un rôle non propriétaire, auquel un administrateur accordera explicitement `EXECUTE` sur la fonction ; ce rôle ne doit pas être propriétaire des tables ni pouvoir modifier les triggers. Aucun rôle applicatif distinct ni octroi de privilèges n'est défini par les fichiers actuels. Le propriétaire PostgreSQL et un superutilisateur restent de confiance et peuvent contourner ces protections.

Le schéma ne définit pas de fonction de remboursement ni de transitions complètes de tous les états. Ce flux doit être défini avant d'exposer le statut `REMBOURSE` dans l'application.

## Points restant à valider

- Confirmer que les dates de fin d'année et de semestre sont inclusives, et que deux périodes partageant le même jour sont considérées chevauchantes.
- Décider si les notes exigent une inscription `PAYE` et/ou interdisent une inscription `ANNULEE`. La FK garantit une inscription de classe/année correspondante, sans appliquer cette politique d'état.
- Décider si la date d'une évaluation doit obligatoirement tomber dans les bornes du semestre ; cette règle n'a pas été ajoutée faute de validation explicite.
- Définir le rôle PostgreSQL de l'application et le processus de vérification auprès du prestataire avant tout flux de paiement.
- Définir une règle d'âge pour `date_naissance` si une limite autre que le type `DATE` est souhaitée.
- Les colonnes `updated_at` ont un défaut à la création, mais aucun trigger générique ne les actualise lors d'autres modifications ; l'application devra les mettre à jour ou une règle devra être validée.