
-- ==========================================
-- Migration 002 : Creation de la table utilisateurs
BEGIN;

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
        ON DELETE RESTRICT,

    CONSTRAINT chk_utilisateurs_email
        CHECK (
            email = LOWER(TRIM(email))
            AND email <> ''
        ),

    CONSTRAINT chk_utilisateurs_password_hash
        CHECK (password_hash LIKE '$argon2id$%')
);

CREATE FUNCTION normaliser_email_utilisateur()
RETURNS TRIGGER
LANGUAGE plpgsql
SET search_path = pg_catalog
AS $$
BEGIN
    NEW.email := LOWER(BTRIM(NEW.email));
    RETURN NEW;
END;
$$;

CREATE TRIGGER trg_normaliser_email_utilisateur
BEFORE INSERT OR UPDATE OF email
ON utilisateurs
FOR EACH ROW
EXECUTE FUNCTION normaliser_email_utilisateur();

CREATE INDEX idx_utilisateurs_role
    ON utilisateurs (role_id);

COMMIT;
