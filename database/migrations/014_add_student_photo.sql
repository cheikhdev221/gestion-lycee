BEGIN;

ALTER TABLE eleves
    ADD COLUMN photo_path VARCHAR(255);

ALTER TABLE eleves
    ADD CONSTRAINT chk_eleves_photo_path
    CHECK (
        photo_path IS NULL
        OR photo_path ~ '^[a-f0-9]{32}\.(jpg|png|webp)$'
    );

COMMIT;
