# Gestion du lycée

Application web de gestion d'un lycée : inscriptions, notes et bulletins.
Stack : PHP (MVC maison), PostgreSQL, Nginx, Bootstrap, Docker.

## Prérequis

- Docker et Docker Compose

## Lancer le projet

1. Copier `.env.example` en `.env` et choisir un mot de passe pour PostgreSQL.
2. Démarrer : `docker compose up -d --build`
3. Ouvrir http://localhost:8080

## Arrêter

`docker compose down`
