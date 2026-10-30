-- db/seed.sql

TRUNCATE TABLE registration, event_schedule, venue, event, membership, organization, student RESTART IDENTITY CASCADE;

INSERT INTO student (student_name, email) VALUES
('Juan Dela Cruz', 'juan.delacruz@university.edu.ph'),
('Maria Santos', 'maria.santos@university.edu.ph'),
('Joshua Cofreros', 'joshua.cofreros@university.edu.ph');

INSERT INTO organization (organization_name, status) VALUES
('Computer Society (CSOC)', 'active'),
('Junior Marketing Association (JMA)', 'active');

INSERT INTO membership (student_id, organization_id, role, membership_date) VALUES
(1, 1, 'President', '2026-01-15'),
(2, 1, 'Event Coordinator', '2026-02-01'),
(3, 1, 'Member', '2026-02-10');

INSERT INTO event (organization_id, event_name, description, status) VALUES
(1, 'Hackathon 2026: Code for Campus', 'Annual 24-hour hackathon focused on campus solutions.', 'confirmed'),
(1, 'WebDev Bootcamp: PHP & PostgreSQL', 'A hands-on workshop covering full-stack backend development.', 'confirmed');

INSERT INTO venue (venue_name, location, capacity) VALUES
('Audio Visual Room (AVR)', 'Building A, 3rd Floor', 150),
('Computer Lab 4', 'IT Building, 2nd Floor', 40);

INSERT INTO event_schedule (event_id, venue_id, event_date, start_time, end_time) VALUES
(1, 1, '2026-11-15', '09:00:00', '17:00:00'),
(2, 2, '2026-11-20', '13:00:00', '16:00:00');

INSERT INTO registration (student_id, event_id, registration_date) VALUES
(1, 1, '2026-10-25'),
(2, 1, '2026-10-26'),
(3, 2, '2026-10-28');