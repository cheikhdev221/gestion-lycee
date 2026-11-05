
-- ==========================================
-- Migration 012 : Journalisation des activites
-- Projet : Gestion-Lycee
-- SGBD : PostgreSQL 16
-- ==========================================

BEGIN;

-- ==========================================
-- 1. TABLE : audit_logs
-- ==========================================

CREATE TABLE audit_logs (

    id INTEGER GENERATED ALWAYS AS IDENTITY,

    utilisateur_id INTEGER,

    action VARCHAR(100) NOT NULL,
    entite VARCHAR(100) NOT NULL,
    entite_id INTEGER,

    anciennes_donnees JSONB,
    nouvelles_donnees JSONB,

    adresse_ip INET,
    user_agent TEXT,

    request_id VARCHAR(100),

    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT pk_audit_logs
        PRIMARY KEY (id),

    CONSTRAINT fk_audit_logs_utilisateur
        FOREIGN KEY (utilisateur_id)
        REFERENCES utilisateurs(id)
        ON UPDATE CASCADE
        ON DELETE SET NULL,

    CONSTRAINT chk_audit_logs_action
        CHECK (action ~ '^[A-Z][A-Z0-9_]*$'),

    CONSTRAINT chk_audit_logs_entite
        CHECK (entite ~ '^[a-z][a-z0-9_]*$'),

    CONSTRAINT chk_audit_logs_entite_id
        CHECK (entite_id IS NULL OR entite_id > 0),

    CONSTRAINT chk_audit_logs_request_id
        CHECK (request_id IS NULL OR request_id <> ''),

    CONSTRAINT chk_audit_logs_user_agent
        CHECK (user_agent IS NULL OR length(user_agent) <= 1000)
);

-- ==========================================
-- 2. INDEXATION
-- ==========================================

CREATE INDEX idx_audit_logs_utilisateur
    ON audit_logs (utilisateur_id);

CREATE INDEX idx_audit_logs_action
    ON audit_logs (action);

CREATE INDEX idx_audit_logs_entite
    ON audit_logs (entite, entite_id);

CREATE INDEX idx_audit_logs_created_at
    ON audit_logs (created_at DESC);

CREATE INDEX idx_audit_logs_request_id
    ON audit_logs (request_id)
    WHERE request_id IS NOT NULL;

CREATE INDEX idx_audit_logs_nouvelles_donnees
    ON audit_logs USING GIN (nouvelles_donnees);

CREATE INDEX idx_audit_logs_anciennes_donnees
    ON audit_logs USING GIN (anciennes_donnees);

-- ==========================================
-- 3. PROTECTION CONTRE UPDATE ET DELETE
-- ==========================================

CREATE FUNCTION interdire_modification_audit()
RETURNS TRIGGER
LANGUAGE plpgsql
SET search_path = pg_catalog
AS $$
BEGIN

    RAISE EXCEPTION
        'Les journaux d audit sont immuables. Modification et suppression interdites.';

    RETURN NULL;
END;
$$;

CREATE TRIGGER trg_audit_logs_immutable
BEFORE UPDATE OR DELETE
ON audit_logs
FOR EACH ROW
EXECUTE FUNCTION interdire_modification_audit();

-- ==========================================
-- 4. PROTECTION CONTRE TRUNCATE
-- ==========================================

CREATE FUNCTION interdire_truncate_audit()
RETURNS TRIGGER
LANGUAGE plpgsql
SET search_path = pg_catalog
AS $$
BEGIN

    RAISE EXCEPTION
        'La suppression globale des journaux d audit est interdite.';

    RETURN NULL;
END;
$$;

CREATE TRIGGER trg_audit_logs_no_truncate
BEFORE TRUNCATE
ON audit_logs
FOR EACH STATEMENT
EXECUTE FUNCTION interdire_truncate_audit();

-- ==========================================
-- 5. PERMISSIONS
-- ==========================================

-- Aucun droit de modification ou suppression
-- n'est accordé à PUBLIC.

REVOKE ALL ON TABLE audit_logs FROM PUBLIC;

REVOKE ALL ON SEQUENCE audit_logs_id_seq FROM PUBLIC;

REVOKE ALL ON FUNCTION interdire_modification_audit()
FROM PUBLIC;

REVOKE ALL ON FUNCTION interdire_truncate_audit()
FROM PUBLIC;

-- ==========================================
-- 6. FIN DE MIGRATION
-- ==========================================

COMMIT;

-- ==========================================
-- FIN MIGRATION 012
-- ==========================================
