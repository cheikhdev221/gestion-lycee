
-- ==========================================
-- Migration 011 : Paiements securises
-- Projet : Gestion-Lycee
-- SGBD : PostgreSQL 16
-- ==========================================

BEGIN;

-- ==========================================
-- 1. TABLE : PAIEMENTS
-- ==========================================

CREATE TABLE paiements (

    id INTEGER GENERATED ALWAYS AS IDENTITY,

    inscription_id INTEGER NOT NULL,

    transaction_ref VARCHAR(100) NOT NULL,
    provider_transaction_ref VARCHAR(150),

    provider VARCHAR(20) NOT NULL,

    statut VARCHAR(20) NOT NULL DEFAULT 'INITIE',

    montant NUMERIC(12,2) NOT NULL,
    devise CHAR(3) NOT NULL DEFAULT 'XOF',

    description TEXT,

    date_initiation TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    date_paiement TIMESTAMPTZ,
    date_remboursement TIMESTAMPTZ,

    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT pk_paiements
        PRIMARY KEY (id),

    CONSTRAINT uq_paiements_id_provider
        UNIQUE (id, provider),

    CONSTRAINT uq_paiements_transaction_ref
        UNIQUE (transaction_ref),

    CONSTRAINT uq_paiements_provider_transaction
        UNIQUE (provider, provider_transaction_ref),

    CONSTRAINT fk_paiements_inscription
        FOREIGN KEY (inscription_id)
        REFERENCES inscriptions(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT chk_paiements_provider
        CHECK (provider IN ('WAVE', 'CARTE')),

    CONSTRAINT chk_paiements_statut
        CHECK (statut IN (
            'INITIE',
            'EN_ATTENTE',
            'PAYE',
            'ECHEC',
            'ANNULE',
            'REMBOURSE'
        )),

    CONSTRAINT chk_paiements_montant
        CHECK (montant > 0),

    CONSTRAINT chk_paiements_devise
        CHECK (devise ~ '^[A-Z]{3}$'),

    CONSTRAINT chk_paiements_dates
        CHECK (
            (statut NOT IN ('PAYE', 'REMBOURSE')
                OR date_paiement IS NOT NULL)
            AND
            (statut <> 'REMBOURSE'
                OR date_remboursement IS NOT NULL)
            AND
            (date_paiement IS NULL
                OR date_paiement >= date_initiation)
            AND
            (date_remboursement IS NULL
                OR date_paiement IS NOT NULL
                AND date_remboursement >= date_paiement)
        )
);

-- ==========================================
-- 2. TABLE : EVENEMENTS WEBHOOK
-- ==========================================

CREATE TABLE paiement_webhook_events (

    id INTEGER GENERATED ALWAYS AS IDENTITY,

    paiement_id INTEGER NOT NULL,

    provider VARCHAR(20) NOT NULL,
    event_ref VARCHAR(150) NOT NULL,

    type_evenement VARCHAR(100) NOT NULL,

    payload JSONB NOT NULL,

    signature_verifiee BOOLEAN NOT NULL DEFAULT FALSE,

    traite BOOLEAN NOT NULL DEFAULT FALSE,

    date_reception TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    date_traitement TIMESTAMPTZ,

    CONSTRAINT pk_paiement_webhook_events
        PRIMARY KEY (id),

    CONSTRAINT uq_paiement_webhook_event
        UNIQUE (provider, event_ref),

    CONSTRAINT fk_webhook_paiement_provider
        FOREIGN KEY (paiement_id, provider)
        REFERENCES paiements(id, provider)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT chk_webhook_provider
        CHECK (provider IN ('WAVE', 'CARTE')),

    CONSTRAINT chk_webhook_traitement
        CHECK (
            (traite = FALSE AND date_traitement IS NULL)
            OR
            (traite = TRUE AND date_traitement IS NOT NULL)
        ),

    CONSTRAINT chk_webhook_signature
        CHECK (
            traite = FALSE OR signature_verifiee = TRUE
        )
);

-- ==========================================
-- 3. INDEXATION
-- ==========================================

CREATE INDEX idx_paiements_inscription
    ON paiements(inscription_id);

CREATE INDEX idx_paiements_statut
    ON paiements(statut);

CREATE INDEX idx_paiements_created_at
    ON paiements(created_at DESC);

CREATE INDEX idx_webhook_paiement_provider
    ON paiement_webhook_events(paiement_id, provider);

CREATE INDEX idx_webhook_non_traites
    ON paiement_webhook_events(date_reception)
    WHERE traite = FALSE;

-- ==========================================
-- 4. FONCTION DE CONFIRMATION
-- ==========================================

CREATE FUNCTION public.confirmer_paiement(
    p_paiement_id INTEGER
)
RETURNS VOID
LANGUAGE plpgsql
SECURITY DEFINER
SET search_path = pg_catalog, public
AS $$
DECLARE
    v_inscription_id INTEGER;
    v_statut VARCHAR(20);
    v_statut_inscription VARCHAR(20);
BEGIN

    -- Verrouillage du paiement.
    SELECT inscription_id, statut
    INTO v_inscription_id, v_statut
    FROM public.paiements
    WHERE id = p_paiement_id
    FOR UPDATE;

    IF NOT FOUND THEN
        RAISE EXCEPTION 'Paiement introuvable';
    END IF;

    -- Idempotence.
    IF v_statut = 'PAYE' THEN
        RETURN;
    END IF;

    IF v_statut NOT IN ('INITIE', 'EN_ATTENTE') THEN
        RAISE EXCEPTION
            'Transition de paiement non autorisee';
    END IF;

    -- Verrouillage de l'inscription.
    SELECT statut
    INTO v_statut_inscription
    FROM public.inscriptions
    WHERE id = v_inscription_id
    FOR UPDATE;

    IF NOT FOUND THEN
        RAISE EXCEPTION 'Inscription introuvable';
    END IF;

    IF v_statut_inscription <> 'PENDING' THEN
        RAISE EXCEPTION
            'Inscription non eligible a la validation';
    END IF;

    -- Confirmation du paiement.
    UPDATE public.paiements
    SET statut = 'PAYE',
        date_paiement = CURRENT_TIMESTAMP,
        updated_at = CURRENT_TIMESTAMP
    WHERE id = p_paiement_id;

    -- Validation de l'inscription.
    UPDATE public.inscriptions
    SET statut = 'PAYE',
        updated_at = CURRENT_TIMESTAMP
    WHERE id = v_inscription_id;

END;
$$;

CREATE FUNCTION public.proteger_transition_paye()
RETURNS TRIGGER
LANGUAGE plpgsql
SET search_path = pg_catalog
AS $$
BEGIN
    IF NEW.statut = 'PAYE'
       AND (TG_OP = 'INSERT' OR OLD.statut <> 'PAYE')
       AND current_user = session_user THEN
        RAISE EXCEPTION
            'Le statut PAYE doit etre defini par une fonction de confirmation privilegiee';
    END IF;

    RETURN NEW;
END;
$$;

CREATE TRIGGER trg_paiements_proteger_statut_paye
BEFORE INSERT OR UPDATE OF statut
ON paiements
FOR EACH ROW
EXECUTE FUNCTION public.proteger_transition_paye();

CREATE TRIGGER trg_inscriptions_proteger_statut_paye
BEFORE INSERT OR UPDATE OF statut
ON inscriptions
FOR EACH ROW
EXECUTE FUNCTION public.proteger_transition_paye();

-- ==========================================
-- 5. PROTECTION DE LA FONCTION
-- ==========================================

-- La fonction ne constitue pas une verification
-- HMAC et ne contacte pas le fournisseur.
-- Elle doit etre appelee exclusivement par
-- le service PHP apres confirmation fiable.

REVOKE ALL ON FUNCTION public.confirmer_paiement(INTEGER)
FROM PUBLIC;

REVOKE ALL ON FUNCTION public.proteger_transition_paye()
FROM PUBLIC;

REVOKE UPDATE (statut) ON TABLE paiements, inscriptions
FROM PUBLIC;

COMMIT;

-- ==========================================
-- FIN MIGRATION 011
-- ==========================================
