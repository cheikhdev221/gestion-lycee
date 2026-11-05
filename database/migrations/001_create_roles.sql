-- ==========================================
-- Migration 001 : Creation de la table roles
-- Projet : Gestion-Lycee
-- SGBD : PostgreSQL 16
-- ==========================================

CREATE TABLE roles (
    id SMALLINT GENERATED ALWAYS AS IDENTITY,
    code VARCHAR(30) NOT NULL,
    nom VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT pk_roles PRIMARY KEY (id),
    CONSTRAINT uq_roles_code UNIQUE (code),
    CONSTRAINT uq_roles_nom UNIQUE (nom),
    CONSTRAINT chk_roles_code CHECK (code ~ '^[A-Z_]+$')
);