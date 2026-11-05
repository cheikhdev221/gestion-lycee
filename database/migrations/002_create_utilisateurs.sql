-- ==========================================
-- Migration 002 : Creation de la table utilisateurs
-- Projet : Gestion-Lycee
-- SGBD : PostgreSQL 16
-- ==========================================

CREATE TABLE utilisateurs (
    
    id INTEGER GENERATED ALWAYS AS IDENTITY,
    role_id SMALLINT NOT NULL,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    actif BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT pk_utilisateurs PRIMARY KEY (id),
    CONSTRAINT uq_utilisateurs_email UNIQUE (email),

    CONSTRAINT fk_utilisateurs_roles
        FOREIGN KEY (role_id)
        REFERENCES roles(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
);