# Gestion Lycée

Application web de gestion scolaire réalisée en PHP avec PostgreSQL. Elle propose des espaces adaptés aux rôles de l’administration, de la direction, des enseignants et des élèves.

## Fonctionnalités

- Gestion des élèves, enseignants, classes et années scolaires
- Inscriptions scolaires et suivi des frais pédagogiques
- Évaluations, notes et suivi des résultats
- Espaces de travail selon le rôle et contrôle d’accès
- Création de compte élève avec photo d’identité privée
- Interface d’accueil publique et navigation responsive

Les paiements Wave et carte présents dans cette version sont simulés. Aucun débit réel n’est effectué.

## Technologies

- PHP 8.5 et PostgreSQL 16
- Nginx et Docker Compose
- Bootstrap 5

## Démarrage

Prérequis : Docker avec Docker Compose.

1. Créer le fichier de configuration et choisir un mot de passe PostgreSQL :

   ```sh
   cp .env.example .env
   ```

2. Construire et démarrer les conteneurs :

   ```sh
   docker compose up -d --build
   ```

3. Appliquer les migrations à une base vide :

   ```sh
   for migration in database/migrations/*.sql; do
     docker compose exec -T db sh -c 'psql -v ON_ERROR_STOP=1 -U "$POSTGRES_USER" -d "$POSTGRES_DB"' < "$migration"
   done
   ```

   Cette commande s’exécute une seule fois sur une base neuve. Les migrations ne sont pas rejouées automatiquement.

4. Ouvrir [http://localhost:8080](http://localhost:8080).

## Créer le premier administrateur

Les rôles sont initialisés par l’application. Aucun compte administrateur avec mot de passe par défaut n’est fourni. Créer le premier compte depuis un terminal interactif :

```sh
docker compose exec php php scripts/create-admin.php
```

Puis se connecter sur [http://localhost:8080/login](http://localhost:8080/login).

## Comptes élèves

Un élève peut créer son compte sur [http://localhost:8080/inscription-eleve](http://localhost:8080/inscription-eleve). Le rôle élève est attribué automatiquement. La photo d’identité acceptée est au format JPEG, PNG ou WebP, avec une taille maximale de 2 Mo. Elle est conservée dans un volume Docker privé.

## Tests

```sh
docker compose exec -T php php tests/run.php
```

Les tests d’intégration utilisent une transaction annulée à la fin et ne conservent pas les données de test.

## Arrêter les services

```sh
docker compose down
```
