
-- UniSync sample data
-- PostgreSQL
-- Run init.sql FIRST.
-- This script only inserts sample data into existing tables.

BEGIN;

-- =====================================================
-- 1. STUDENT (30 records)
-- Dummy password hashes: these accounts are for sample
-- data only and cannot be used for actual login.
-- =====================================================

INSERT INTO STUDENT (student_name, email, password_hash) VALUES
('Juan Dela Cruz', 'juan.delacruz@university.edu.ph', 'dummy_hash_not_for_login'),
('Maria Santos', 'maria.santos@university.edu.ph', 'dummy_hash_not_for_login'),
('Joshua Cofreros', 'joshua.cofreros@university.edu.ph', 'dummy_hash_not_for_login'),
('Angela Reyes', 'angela.reyes@university.edu.ph', 'dummy_hash_not_for_login'),
('Mark Garcia', 'mark.garcia@university.edu.ph', 'dummy_hash_not_for_login'),
('Sofia Mendoza', 'sofia.mendoza@university.edu.ph', 'dummy_hash_not_for_login'),
('Daniel Ramos', 'daniel.ramos@university.edu.ph', 'dummy_hash_not_for_login'),
('Isabella Flores', 'isabella.flores@university.edu.ph', 'dummy_hash_not_for_login'),
('Gabriel Torres', 'gabriel.torres@university.edu.ph', 'dummy_hash_not_for_login'),
('Camille Navarro', 'camille.navarro@university.edu.ph', 'dummy_hash_not_for_login'),
('Nathan Castillo', 'nathan.castillo@university.edu.ph', 'dummy_hash_not_for_login'),
('Bianca Rivera', 'bianca.rivera@university.edu.ph', 'dummy_hash_not_for_login'),
('Adrian Bautista', 'adrian.bautista@university.edu.ph', 'dummy_hash_not_for_login'),
('Nicole Villanueva', 'nicole.villanueva@university.edu.ph', 'dummy_hash_not_for_login'),
('Ethan Fernandez', 'ethan.fernandez@university.edu.ph', 'dummy_hash_not_for_login'),
('Andrea Morales', 'andrea.morales@university.edu.ph', 'dummy_hash_not_for_login'),
('Kyle Aquino', 'kyle.aquino@university.edu.ph', 'dummy_hash_not_for_login'),
('Patricia Cruz', 'patricia.cruz@university.edu.ph', 'dummy_hash_not_for_login'),
('Miguel Santiago', 'miguel.santiago@university.edu.ph', 'dummy_hash_not_for_login'),
('Hannah Lim', 'hannah.lim@university.edu.ph', 'dummy_hash_not_for_login'),
('Luis Mercado', 'luis.mercado@university.edu.ph', 'dummy_hash_not_for_login'),
('Chloe Gonzales', 'chloe.gonzales@university.edu.ph', 'dummy_hash_not_for_login'),
('Rafael Diaz', 'rafael.diaz@university.edu.ph', 'dummy_hash_not_for_login'),
('Samantha Perez', 'samantha.perez@university.edu.ph', 'dummy_hash_not_for_login'),
('Vincent Lopez', 'vincent.lopez@university.edu.ph', 'dummy_hash_not_for_login'),
('Trisha Valdez', 'trisha.valdez@university.edu.ph', 'dummy_hash_not_for_login'),
('Paolo Dominguez', 'paolo.dominguez@university.edu.ph', 'dummy_hash_not_for_login'),
('Beatrice Herrera', 'beatrice.herrera@university.edu.ph', 'dummy_hash_not_for_login'),
('Christian Salazar', 'christian.salazar@university.edu.ph', 'dummy_hash_not_for_login'),
('Ella Ramirez', 'ella.ramirez@university.edu.ph', 'dummy_hash_not_for_login');


-- =====================================================
-- 2. ORGANIZATION (12 records)
-- =====================================================

INSERT INTO ORGANIZATION (organization_name, status) VALUES
('Computer Society (CSOC)', 'active'),
('Junior Marketing Association (JMA)', 'active'),
('Information Technology Society (ITS)', 'active'),
('Student Government Association (SGA)', 'active'),
('Multimedia Arts Society (MMAS)', 'active'),
('Engineering Students Association (ESA)', 'active'),
('Business Management Society (BMS)', 'active'),
('English Language Society (ELS)', 'active'),
('Science and Research Club (SRC)', 'active'),
('Photography Club (PC)', 'active'),
('Campus Sports Association (CSA)', 'active'),
('Environmental Awareness Club (EAC)', 'active');


-- =====================================================
-- 3. MEMBERSHIP (61 records)
-- First 3 records preserve the original memberships.
-- The remaining 58 are generated without duplicate pairs.
-- =====================================================

INSERT INTO MEMBERSHIP
    (student_id, organization_id, role, membership_date)
VALUES
(1, 1, 'Admin', '2026-01-15'),
(2, 1, 'Manager', '2026-02-01'),
(3, 1, 'Member', '2026-02-10');

WITH membership_candidates AS (
    SELECT
        s.student_id,
        o.organization_id,
        ROW_NUMBER() OVER (
            ORDER BY o.organization_id, s.student_id
        ) AS membership_number
    FROM STUDENT s
    CROSS JOIN ORGANIZATION o
    WHERE NOT (
        o.organization_id = 1
        AND s.student_id IN (1, 2, 3)
    )
)
INSERT INTO MEMBERSHIP
    (student_id, organization_id, role, membership_date)
SELECT
    student_id,
    organization_id,
    CASE membership_number % 3
        WHEN 0 THEN 'Admin'
        WHEN 1 THEN 'Manager'
        ELSE 'Member'
    END,
    DATE '2026-01-01' + (membership_number::INTEGER * 2)
FROM membership_candidates
WHERE membership_number <= 58;


-- =====================================================
-- 4. VENUE (15 records)
-- =====================================================

INSERT INTO VENUE (venue_name, location, capacity) VALUES
('Audio Visual Room (AVR)', 'Building A, 3rd Floor', 150),
('Computer Lab 4', 'IT Building, 2nd Floor', 40),
('Computer Lab 1', 'IT Building, 1st Floor', 35),
('Computer Lab 2', 'IT Building, 1st Floor', 35),
('Computer Lab 3', 'IT Building, 2nd Floor', 40),
('Main Auditorium', 'Main Building, Ground Floor', 300),
('Conference Room A', 'Administration Building, 2nd Floor', 50),
('Conference Room B', 'Administration Building, 2nd Floor', 30),
('Student Activity Center', 'Student Center, Ground Floor', 120),
('Library Discussion Room', 'University Library, 3rd Floor', 20),
('Open Court', 'Sports Complex', 200),
('University Gymnasium', 'Sports Complex', 500),
('Innovation Hub', 'IT Building, 3rd Floor', 60),
('Lecture Hall 1', 'Academic Building, 2nd Floor', 100),
('Lecture Hall 2', 'Academic Building, 3rd Floor', 100);


-- =====================================================
-- 5. EVENT (40 records)
-- The first two preserve the original events.
-- Generated events use only allowed statuses.
-- =====================================================

INSERT INTO EVENT
    (organization_id, event_name, description, status)
VALUES
(1, 'Hackathon 2026: Code for Campus',
 'Annual 24-hour hackathon focused on campus solutions.',
 'confirmed'),

(1, 'WebDev Bootcamp: PHP & PostgreSQL',
 'Hands-on workshop covering full-stack backend development.',
 'confirmed');

INSERT INTO EVENT
    (organization_id, event_name, description, status)
SELECT
    ((g.event_number - 1) % 12) + 1,
    CASE g.event_number
        WHEN 3 THEN 'Introduction to Cybersecurity'
        WHEN 4 THEN 'Campus Leadership Summit'
        WHEN 5 THEN 'Digital Marketing Workshop'
        WHEN 6 THEN 'Photography Basics Workshop'
        WHEN 7 THEN 'Student Research Forum'
        WHEN 8 THEN 'Inter-Organization Sports Fest'
        WHEN 9 THEN 'Environmental Awareness Campaign'
        WHEN 10 THEN 'Creative Design Competition'
        ELSE 'Campus Event ' || g.event_number
    END,
    CASE g.event_number
        WHEN 3 THEN 'An introductory seminar about cybersecurity fundamentals and online safety.'
        WHEN 4 THEN 'A leadership development event for aspiring student leaders.'
        WHEN 5 THEN 'A practical workshop on digital marketing strategies and social media.'
        WHEN 6 THEN 'A beginner-friendly workshop about photography techniques and composition.'
        WHEN 7 THEN 'A forum where students present and discuss academic research.'
        WHEN 8 THEN 'A friendly sports competition between campus organizations.'
        WHEN 9 THEN 'A student-led campaign promoting environmental responsibility.'
        WHEN 10 THEN 'A competition showcasing student creativity and graphic design skills.'
        ELSE 'A university event organized to encourage student participation and collaboration.'
    END,
    CASE g.event_number % 4
        WHEN 0 THEN 'completed'
        WHEN 1 THEN 'confirmed'
        WHEN 2 THEN 'pending'
        ELSE 'cancelled'
    END
FROM GENERATE_SERIES(3, 40) AS g(event_number);


-- =====================================================
-- 6. EVENT_SCHEDULE (40 records)
-- One schedule per event.
-- Completed events receive past dates.
-- Other events receive future dates.
-- =====================================================

INSERT INTO EVENT_SCHEDULE
    (event_id, venue_id, event_date, start_time, end_time)
SELECT
    g.event_number,
    ((g.event_number - 1) % 15) + 1,
    CASE
        WHEN g.event_number % 4 = 0
            THEN DATE '2026-09-01' + g.event_number
        ELSE DATE '2026-11-25' + g.event_number
    END,
    CASE g.event_number % 3
        WHEN 0 THEN TIME '09:00'
        WHEN 1 THEN TIME '13:00'
        ELSE TIME '10:00'
    END,
    CASE g.event_number % 3
        WHEN 0 THEN TIME '12:00'
        WHEN 1 THEN TIME '16:00'
        ELSE TIME '15:00'
    END
FROM GENERATE_SERIES(1, 40) AS g(event_number);


-- =====================================================
-- 7. REGISTRATION (100 records)
-- First 3 preserve the original registrations.
-- Remaining 97 use unique student-event combinations.
-- =====================================================

INSERT INTO REGISTRATION
    (student_id, event_id, registration_date)
VALUES
(1, 1, '2026-10-25 09:00:00'),
(2, 1, '2026-10-26 10:00:00'),
(3, 2, '2026-10-28 11:00:00');

INSERT INTO REGISTRATION
    (student_id, event_id, registration_date)
SELECT
    ((g.registration_number - 1) % 30) + 1,
    3 + ((g.registration_number - 1) / 30),
    TIMESTAMP '2026-10-01 09:00:00'
        + (g.registration_number * INTERVAL '1 hour')
FROM GENERATE_SERIES(1, 97) AS g(registration_number);


-- =====================================================
-- 8. NOTIFICATION
-- Intentionally empty for the initial workflow.
-- =====================================================


-- =====================================================
-- 9. RESET SERIAL SEQUENCES
-- Keeps generated IDs synchronized with inserted data.
-- =====================================================

SELECT SETVAL(
    PG_GET_SERIAL_SEQUENCE('student', 'student_id'),
    (SELECT MAX(student_id) FROM STUDENT)
);

SELECT SETVAL(
    PG_GET_SERIAL_SEQUENCE('organization', 'organization_id'),
    (SELECT MAX(organization_id) FROM ORGANIZATION)
);

SELECT SETVAL(
    PG_GET_SERIAL_SEQUENCE('membership', 'membership_id'),
    (SELECT MAX(membership_id) FROM MEMBERSHIP)
);

SELECT SETVAL(
    PG_GET_SERIAL_SEQUENCE('venue', 'venue_id'),
    (SELECT MAX(venue_id) FROM VENUE)
);

SELECT SETVAL(
    PG_GET_SERIAL_SEQUENCE('event', 'event_id'),
    (SELECT MAX(event_id) FROM EVENT)
);

SELECT SETVAL(
    PG_GET_SERIAL_SEQUENCE('event_schedule', 'schedule_id'),
    (SELECT MAX(schedule_id) FROM EVENT_SCHEDULE)
);

SELECT SETVAL(
    PG_GET_SERIAL_SEQUENCE('registration', 'registration_id'),
    (SELECT MAX(registration_id) FROM REGISTRATION)
);

-- Notification remains empty, so its sequence stays at its initial value.


-- =====================================================
-- 10. VERIFY RECORD COUNTS
-- =====================================================

COMMIT;

SELECT 'STUDENT' AS table_name, COUNT(*) AS total FROM STUDENT
UNION ALL
SELECT 'ORGANIZATION', COUNT(*) FROM ORGANIZATION
UNION ALL
SELECT 'MEMBERSHIP', COUNT(*) FROM MEMBERSHIP
UNION ALL
SELECT 'EVENT', COUNT(*) FROM EVENT
UNION ALL
SELECT 'VENUE', COUNT(*) FROM VENUE
UNION ALL
SELECT 'EVENT_SCHEDULE', COUNT(*) FROM EVENT_SCHEDULE
UNION ALL
SELECT 'REGISTRATION', COUNT(*) FROM REGISTRATION
UNION ALL
SELECT 'NOTIFICATION', COUNT(*) FROM NOTIFICATION;
