
-- ==========================================
-- Migration 001 : Creation de la table roles
BEGIN;

-- Projet : Gestion-Lycee
-- SGBD : PostgreSQL 16
-- ==========================================

CREATE TABLE roles (
    id SMALLINT GENERATED ALWAYS AS IDENTITY,
    code VARCHAR(30) NOT NULL,
    libelle VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT pk_roles PRIMARY KEY (id),
    CONSTRAINT uq_roles_code UNIQUE (code),
    CONSTRAINT uq_roles_libelle UNIQUE (libelle),

    CONSTRAINT chk_roles_code
        CHECK (code IN (
            'ADMIN',
            'DIRECTEUR',
            'ENSEIGNANT',
            'ELEVE'
        )),

    CONSTRAINT chk_roles_libelle
        CHECK (libelle IN (
            'ADMIN',
            'DIRECTEUR',
            'ENSEIGNANT',
            'ELEVE'
        )),

    CONSTRAINT chk_roles_code_libelle
        CHECK (code = libelle)
);

COMMIT;
