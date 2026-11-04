-- Migrate the legacy STUDENT identity table to ACCOUNT without deleting data.
-- Run once against an existing UniSync database before deploying the updated app.
-- This migration deliberately does not run init.sql, which resets the schema.

BEGIN;

DO $$
BEGIN
    IF to_regclass('public.student') IS NOT NULL
       AND to_regclass('public.account') IS NOT NULL THEN
        RAISE EXCEPTION 'Both STUDENT and ACCOUNT exist; resolve the duplicate identity tables before migrating.';
    ELSIF to_regclass('public.student') IS NOT NULL THEN
        ALTER TABLE student RENAME TO account;
        ALTER TABLE account RENAME COLUMN student_id TO account_id;
        ALTER TABLE account RENAME COLUMN student_name TO account_name;
    ELSIF to_regclass('public.account') IS NULL THEN
        RAISE EXCEPTION 'Neither STUDENT nor ACCOUNT exists; cannot migrate this database.';
    END IF;

    IF EXISTS (
        SELECT 1 FROM information_schema.columns
        WHERE table_schema = 'public' AND table_name = 'account'
          AND column_name = 'student_id'
    ) THEN
        ALTER TABLE account RENAME COLUMN student_id TO account_id;
    END IF;

    IF EXISTS (
        SELECT 1 FROM information_schema.columns
        WHERE table_schema = 'public' AND table_name = 'account'
          AND column_name = 'student_name'
    ) THEN
        ALTER TABLE account RENAME COLUMN student_name TO account_name;
    END IF;

    IF EXISTS (
        SELECT 1 FROM information_schema.columns
        WHERE table_schema = 'public' AND table_name = 'membership'
          AND column_name = 'student_id'
    ) THEN
        ALTER TABLE membership RENAME COLUMN student_id TO account_id;
    END IF;

    IF EXISTS (
        SELECT 1 FROM information_schema.columns
        WHERE table_schema = 'public' AND table_name = 'registration'
          AND column_name = 'student_id'
    ) THEN
        ALTER TABLE registration RENAME COLUMN student_id TO account_id;
    END IF;
END $$;

ALTER TABLE account
    ADD COLUMN IF NOT EXISTS avatar_url TEXT,
    ADD COLUMN IF NOT EXISTS account_status VARCHAR(30) NOT NULL DEFAULT 'active';

ALTER TABLE membership
    ADD COLUMN IF NOT EXISTS status VARCHAR(30) NOT NULL DEFAULT 'active';

ALTER TABLE registration
    ADD COLUMN IF NOT EXISTS status VARCHAR(30) NOT NULL DEFAULT 'active';

COMMIT;
