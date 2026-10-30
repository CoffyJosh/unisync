-- UniSync database initialization
-- PostgreSQL
-- WARNING: This script DROPS the existing UniSync tables and their data.
-- Run this only when you intend to reset the database.

BEGIN;

-- Drop tables in dependency order so the database can be refreshed safely.
DROP TABLE IF EXISTS NOTIFICATION CASCADE;
DROP TABLE IF EXISTS REGISTRATION CASCADE;
DROP TABLE IF EXISTS EVENT_SCHEDULE CASCADE;
DROP TABLE IF EXISTS EVENT CASCADE;
DROP TABLE IF EXISTS MEMBERSHIP CASCADE;
DROP TABLE IF EXISTS VENUE CASCADE;
DROP TABLE IF EXISTS ORGANIZATION CASCADE;
DROP TABLE IF EXISTS STUDENT CASCADE;

-- 1. STUDENT
CREATE TABLE STUDENT (
    student_id      SERIAL PRIMARY KEY,
    student_name    VARCHAR(150) NOT NULL,
    email           VARCHAR(255) NOT NULL UNIQUE,
    password_hash   TEXT NOT NULL
);

-- 2. ORGANIZATION
CREATE TABLE ORGANIZATION (
    organization_id   SERIAL PRIMARY KEY,
    organization_name VARCHAR(150) NOT NULL UNIQUE,
    status            VARCHAR(30) NOT NULL DEFAULT 'active'
);

-- 3. MEMBERSHIP
CREATE TABLE MEMBERSHIP (
    membership_id    SERIAL PRIMARY KEY,
    student_id       INTEGER NOT NULL,
    organization_id  INTEGER NOT NULL,
    role             VARCHAR(20) NOT NULL DEFAULT 'Member',
    membership_date  DATE NOT NULL DEFAULT CURRENT_DATE,

    CONSTRAINT fk_membership_student
        FOREIGN KEY (student_id) REFERENCES STUDENT(student_id)
        ON DELETE CASCADE,

    CONSTRAINT fk_membership_organization
        FOREIGN KEY (organization_id) REFERENCES ORGANIZATION(organization_id)
        ON DELETE CASCADE,

    CONSTRAINT uq_student_organization
        UNIQUE (student_id, organization_id),

    CONSTRAINT chk_membership_role
        CHECK (role IN ('Admin', 'Manager', 'Member'))
);

-- 4. EVENT
CREATE TABLE EVENT (
    event_id        SERIAL PRIMARY KEY,
    organization_id INTEGER NOT NULL,
    event_name      VARCHAR(200) NOT NULL,
    description     TEXT,
    status          VARCHAR(20) NOT NULL DEFAULT 'pending',

    CONSTRAINT fk_event_organization
        FOREIGN KEY (organization_id) REFERENCES ORGANIZATION(organization_id)
        ON DELETE CASCADE,

    CONSTRAINT chk_event_status
        CHECK (status IN ('pending', 'confirmed', 'cancelled', 'completed'))
);

-- 5. VENUE
CREATE TABLE VENUE (
    venue_id    SERIAL PRIMARY KEY,
    venue_name  VARCHAR(150) NOT NULL UNIQUE,
    location    VARCHAR(255),
    capacity    INTEGER,

    CONSTRAINT chk_venue_capacity
        CHECK (capacity IS NULL OR capacity > 0)
);

-- 6. EVENT_SCHEDULE
CREATE TABLE EVENT_SCHEDULE (
    schedule_id SERIAL PRIMARY KEY,
    event_id    INTEGER NOT NULL,
    venue_id    INTEGER NOT NULL,
    event_date  DATE NOT NULL,
    start_time  TIME NOT NULL,
    end_time    TIME NOT NULL,

    CONSTRAINT fk_schedule_event
        FOREIGN KEY (event_id) REFERENCES EVENT(event_id)
        ON DELETE CASCADE,

    CONSTRAINT fk_schedule_venue
        FOREIGN KEY (venue_id) REFERENCES VENUE(venue_id),

    CONSTRAINT chk_schedule_time
        CHECK (end_time > start_time)
);

-- 7. REGISTRATION
CREATE TABLE REGISTRATION (
    registration_id   SERIAL PRIMARY KEY,
    student_id        INTEGER NOT NULL,
    event_id          INTEGER NOT NULL,
    registration_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_registration_student
        FOREIGN KEY (student_id) REFERENCES STUDENT(student_id)
        ON DELETE CASCADE,

    CONSTRAINT fk_registration_event
        FOREIGN KEY (event_id) REFERENCES EVENT(event_id)
        ON DELETE CASCADE,

    CONSTRAINT uq_student_event
        UNIQUE (student_id, event_id)
);

-- 8. NOTIFICATION
-- Prepared for future implementation. Notifications are not required
-- for the initial event-registration workflow.
CREATE TABLE NOTIFICATION (
    notification_id      SERIAL PRIMARY KEY,
    student_id           INTEGER NOT NULL,
    organization_id      INTEGER NOT NULL,
    event_id              INTEGER,
    notification_message TEXT NOT NULL,
    is_read               BOOLEAN NOT NULL DEFAULT FALSE,
    created_at            TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_notification_student
        FOREIGN KEY (student_id) REFERENCES STUDENT(student_id)
        ON DELETE CASCADE,

    CONSTRAINT fk_notification_organization
        FOREIGN KEY (organization_id) REFERENCES ORGANIZATION(organization_id)
        ON DELETE CASCADE,

    CONSTRAINT fk_notification_event
        FOREIGN KEY (event_id) REFERENCES EVENT(event_id)
        ON DELETE SET NULL
);

-- Helpful indexes for common lookups.
CREATE INDEX idx_membership_organization
    ON MEMBERSHIP(organization_id);

CREATE INDEX idx_event_organization
    ON EVENT(organization_id);

CREATE INDEX idx_schedule_date_venue
    ON EVENT_SCHEDULE(event_date, venue_id);

CREATE INDEX idx_registration_event
    ON REGISTRATION(event_id);

CREATE INDEX idx_notification_student_created
    ON NOTIFICATION(student_id, created_at DESC);

COMMIT;
