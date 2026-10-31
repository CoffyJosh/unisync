
-- =============================================
-- UniSync Database Seed
-- 30 Students | 12 Organizations | 40 Events
-- 15 Venues | 100 Registrations
-- =============================================

TRUNCATE TABLE
    registration,
    event_schedule,
    venue,
    event,
    membership,
    organization,
    student
RESTART IDENTITY CASCADE;


-- =============================================
-- 1. STUDENTS (30)
-- =============================================

INSERT INTO student (student_name, email) VALUES
('Juan Dela Cruz', 'juan.delacruz@university.edu.ph'),
('Maria Santos', 'maria.santos@university.edu.ph'),
('Joshua Cofreros', 'joshua.cofreros@university.edu.ph'),
('Angela Reyes', 'angela.reyes@university.edu.ph'),
('Mark Villanueva', 'mark.villanueva@university.edu.ph'),
('Sofia Mendoza', 'sofia.mendoza@university.edu.ph'),
('Daniel Garcia', 'daniel.garcia@university.edu.ph'),
('Isabella Cruz', 'isabella.cruz@university.edu.ph'),
('Gabriel Ramos', 'gabriel.ramos@university.edu.ph'),
('Nicole Flores', 'nicole.flores@university.edu.ph'),
('Adrian Bautista', 'adrian.bautista@university.edu.ph'),
('Camille Torres', 'camille.torres@university.edu.ph'),
('Nathan Rivera', 'nathan.rivera@university.edu.ph'),
('Andrea Castillo', 'andrea.castillo@university.edu.ph'),
('Miguel Fernandez', 'miguel.fernandez@university.edu.ph'),
('Patricia Lim', 'patricia.lim@university.edu.ph'),
('Christian Aquino', 'christian.aquino@university.edu.ph'),
('Jasmine Navarro', 'jasmine.navarro@university.edu.ph'),
('Ethan Mercado', 'ethan.mercado@university.edu.ph'),
('Bianca Garcia', 'bianca.garcia@university.edu.ph'),
('Rafael Gonzales', 'rafael.gonzales@university.edu.ph'),
('Trisha Valdez', 'trisha.valdez@university.edu.ph'),
('Kevin Mendoza', 'kevin.mendoza@university.edu.ph'),
('Samantha Flores', 'samantha.flores@university.edu.ph'),
('Liam Herrera', 'liam.herrera@university.edu.ph'),
('Chloe Ramirez', 'chloe.ramirez@university.edu.ph'),
('Paolo Santiago', 'paolo.santiago@university.edu.ph'),
('Denise Aquino', 'denise.aquino@university.edu.ph'),
('Francis Tan', 'francis.tan@university.edu.ph'),
('Ella Martinez', 'ella.martinez@university.edu.ph');


-- =============================================
-- 2. ORGANIZATIONS (12)
-- =============================================

INSERT INTO organization (organization_name, status) VALUES
('Computer Society (CSOC)', 'active'),
('Junior Marketing Association (JMA)', 'active'),
('University Student Council (USC)', 'active'),
('Campus Arts Guild (CAG)', 'active'),
('Environmental Awareness Society (EAS)', 'active'),
('Junior Finance Association (JFA)', 'active'),
('Information Technology Society (ITS)', 'active'),
('Campus Journalism Society (CJS)', 'active'),
('Engineering Students Association (ESA)', 'active'),
('University Debate Society (UDS)', 'active'),
('Photography Club (PC)', 'active'),
('Sports and Recreation Club (SRC)', 'active');


-- =============================================
-- 3. MEMBERSHIPS (60)
-- =============================================

INSERT INTO membership
(student_id, organization_id, role, membership_date) VALUES
(1, 1, 'President', '2026-01-15'),
(2, 1, 'Event Coordinator', '2026-02-01'),
(3, 1, 'Member', '2026-02-10'),
(4, 1, 'Member', '2026-03-05'),
(5, 1, 'Member', '2026-03-12'),
(6, 1, 'Member', '2026-04-01'),

(4, 2, 'President', '2026-01-20'),
(5, 2, 'Event Coordinator', '2026-02-05'),
(6, 2, 'Member', '2026-02-15'),
(7, 2, 'Member', '2026-03-10'),
(8, 2, 'Member', '2026-03-18'),

(6, 3, 'President', '2026-01-12'),
(7, 3, 'Secretary', '2026-01-20'),
(8, 3, 'Member', '2026-02-14'),
(9, 3, 'Member', '2026-02-22'),
(10, 3, 'Member', '2026-03-01'),

(8, 4, 'President', '2026-02-03'),
(9, 4, 'Event Coordinator', '2026-02-12'),
(10, 4, 'Member', '2026-03-01'),
(11, 4, 'Member', '2026-03-15'),

(10, 5, 'President', '2026-01-25'),
(11, 5, 'Secretary', '2026-02-10'),
(12, 5, 'Member', '2026-02-18'),
(13, 5, 'Member', '2026-03-05'),

(12, 6, 'President', '2026-01-18'),
(13, 6, 'Event Coordinator', '2026-02-08'),
(14, 6, 'Member', '2026-02-20'),
(15, 6, 'Member', '2026-03-11'),

(14, 7, 'President', '2026-01-22'),
(15, 7, 'Secretary', '2026-02-02'),
(16, 7, 'Member', '2026-02-16'),
(17, 7, 'Member', '2026-03-08'),

(16, 8, 'President', '2026-01-10'),
(17, 8, 'Event Coordinator', '2026-02-04'),
(18, 8, 'Member', '2026-02-19'),
(19, 8, 'Member', '2026-03-14'),

(18, 9, 'President', '2026-01-16'),
(19, 9, 'Secretary', '2026-02-07'),
(20, 9, 'Member', '2026-02-21'),
(21, 9, 'Member', '2026-03-09'),

(20, 10, 'President', '2026-01-19'),
(21, 10, 'Event Coordinator', '2026-02-11'),
(22, 10, 'Member', '2026-02-25'),
(23, 10, 'Member', '2026-03-16'),

(22, 11, 'President', '2026-01-28'),
(23, 11, 'Secretary', '2026-02-13'),
(24, 11, 'Member', '2026-02-27'),
(25, 11, 'Member', '2026-03-19'),

(24, 12, 'President', '2026-01-14'),
(25, 12, 'Event Coordinator', '2026-02-06'),
(26, 12, 'Member', '2026-02-17'),
(27, 12, 'Member', '2026-03-07'),

(28, 1, 'Member', '2026-04-10'),
(29, 2, 'Member', '2026-04-12'),
(30, 3, 'Member', '2026-04-15'),
(1, 4, 'Member', '2026-04-18'),
(2, 5, 'Member', '2026-04-20'),
(3, 6, 'Member', '2026-04-22'),
(4, 7, 'Member', '2026-04-25'),
(5, 8, 'Member', '2026-04-28'),
(6, 9, 'Member', '2026-05-01');


-- =============================================
-- 4. EVENTS (40)
-- =============================================

INSERT INTO event
(organization_id, event_name, description, status) VALUES

-- Computer Society (1)
(1, 'Hackathon 2026: Code for Campus',
 'Annual 24-hour hackathon focused on developing solutions to campus problems.',
 'confirmed'),

(1, 'WebDev Bootcamp: PHP & PostgreSQL',
 'A hands-on workshop covering full-stack backend development.',
 'confirmed'),

(1, 'Introduction to Cybersecurity',
 'A seminar introducing cybersecurity concepts, online threats, and basic digital safety.',
 'confirmed'),

(1, 'Programming Fundamentals Workshop',
 'A beginner-friendly programming workshop covering logic, algorithms, and problem solving.',
 'completed'),

-- Junior Marketing Association (2)
(2, 'Marketing Week 2026',
 'A week-long celebration featuring marketing activities, competitions, and student-led campaigns.',
 'confirmed'),

(2, 'Brand Building Workshop',
 'An interactive workshop introducing branding strategies and digital marketing fundamentals.',
 'pending'),

(2, 'Campus Business Pitch Competition',
 'Students present business concepts and marketing strategies to a panel of judges.',
 'confirmed'),

(2, 'Social Media Marketing Seminar',
 'A seminar discussing content strategy, audience engagement, and social media trends.',
 'cancelled'),

-- University Student Council (3)
(3, 'University Foundation Day',
 'A university-wide celebration featuring student performances, activities, and community events.',
 'confirmed'),

(3, 'Student Leadership Forum',
 'A forum discussing student leadership, campus involvement, and responsible governance.',
 'confirmed'),

(3, 'Freshmen Welcome Assembly',
 'An introductory campus gathering to welcome incoming university students.',
 'completed'),

(3, 'Student Consultation Day',
 'An open consultation where students can raise concerns and share suggestions.',
 'pending'),

-- Campus Arts Guild (4)
(4, 'Campus Arts and Culture Showcase',
 'An exhibition featuring artworks, performances, and creative projects from students.',
 'confirmed'),

(4, 'Photography Basics Workshop',
 'A beginner-friendly workshop covering photography composition, lighting, and camera techniques.',
 'cancelled'),

(4, 'Digital Illustration Workshop',
 'A practical session exploring digital drawing tools and illustration techniques.',
 'confirmed'),

-- Environmental Awareness Society (5)
(5, 'Green Campus Initiative',
 'An environmental awareness activity promoting waste reduction and sustainable practices.',
 'confirmed'),

(5, 'Campus Clean-Up Drive',
 'A volunteer activity focused on maintaining a cleaner and greener campus environment.',
 'pending'),

(5, 'Recycling and Sustainability Seminar',
 'A discussion about responsible waste management and sustainable campus practices.',
 'confirmed'),

-- Junior Finance Association (6)
(6, 'Financial Literacy for Students',
 'A seminar covering budgeting, saving, and responsible personal financial management.',
 'confirmed'),

(6, 'Investment Basics Workshop',
 'An introductory workshop about investment concepts, financial risks, and planning.',
 'pending'),

(6, 'Finance Quiz Challenge',
 'A student competition testing financial knowledge and practical problem-solving.',
 'completed'),

-- Information Technology Society (7)
(7, 'IT Career Exploration Day',
 'An event introducing students to different career paths in the information technology industry.',
 'confirmed'),

(7, 'Cloud Computing Fundamentals',
 'A technical seminar introducing cloud services, deployment models, and cloud infrastructure.',
 'confirmed'),

(7, 'Database Design Workshop',
 'A practical workshop about relational database design, normalization, and SQL.',
 'confirmed'),

-- Campus Journalism Society (8)
(8, 'Campus Press Conference',
 'A gathering for student journalists featuring writing activities and press discussions.',
 'confirmed'),

(8, 'Feature Writing Workshop',
 'A writing workshop focused on storytelling, research, and feature article development.',
 'pending'),

(8, 'Campus News Writing Competition',
 'A competition challenging students to report and write about campus-related topics.',
 'completed'),

-- Engineering Students Association (9)
(9, 'Engineering Innovation Expo',
 'An exhibition of student engineering projects, prototypes, and technical innovations.',
 'confirmed'),

(9, 'Robotics and Automation Seminar',
 'A seminar introducing robotics, automation systems, and their practical applications.',
 'confirmed'),

(9, 'Electronics Fundamentals Workshop',
 'A hands-on introduction to basic electronic components and circuit concepts.',
 'cancelled'),

-- University Debate Society (10)
(10, 'Interdepartmental Debate Tournament',
 'A debate competition featuring student teams from different academic departments.',
 'confirmed'),

(10, 'Public Speaking and Argumentation',
 'A workshop focused on speech delivery, critical thinking, and constructing arguments.',
 'confirmed'),

(10, 'Debate Exhibition Match',
 'A demonstration debate showcasing formal debate structure and techniques.',
 'completed'),

-- Photography Club (11)
(11, 'Campus Photography Walk',
 'A guided photography activity exploring campus locations and visual storytelling.',
 'confirmed'),

(11, 'Portrait Photography Session',
 'A practical session covering portrait composition, lighting, and camera settings.',
 'pending'),

(11, 'Photo Editing Fundamentals',
 'An introduction to image editing, color correction, and basic post-processing.',
 'confirmed'),

-- Sports and Recreation Club (12)
(12, 'University Sports Festival',
 'A campus sports event featuring friendly competitions and recreational activities.',
 'confirmed'),

(12, 'Badminton Open Tournament',
 'A friendly badminton tournament open to interested university students.',
 'confirmed'),

(12, 'Campus Fun Run',
 'A recreational running event promoting physical activity and campus participation.',
 'pending'),

(12, 'Intramural Basketball Finals',
 'The final matches of the university intramural basketball competition.',
 'completed');


-- =============================================
-- 5. VENUES (15)
-- =============================================

INSERT INTO venue (venue_name, location, capacity) VALUES
('Audio Visual Room (AVR)', 'Building A, 3rd Floor', 150),
('Computer Lab 4', 'IT Building, 2nd Floor', 40),
('University Auditorium', 'Main Building, Ground Floor', 500),
('Marketing Conference Room', 'Business Building, 2nd Floor', 60),
('Student Activity Center', 'Student Center, 1st Floor', 200),
('Arts and Culture Hall', 'Arts Building, 2nd Floor', 120),
('Computer Laboratory 1', 'IT Building, 1st Floor', 35),
('Computer Laboratory 2', 'IT Building, 1st Floor', 35),
('Engineering Workshop', 'Engineering Building, Ground Floor', 80),
('University Gymnasium', 'Sports Complex', 600),
('Open Campus Grounds', 'Central Campus Grounds', 1000),
('Library Discussion Room', 'University Library, 3rd Floor', 30),
('Business Lecture Hall', 'Business Building, 3rd Floor', 100),
('Media Production Room', 'Communication Building, 2nd Floor', 45),
('Conference Hall', 'Administration Building, 4th Floor', 180);


-- =============================================
-- 6. EVENT SCHEDULES (40)
-- =============================================

INSERT INTO event_schedule
(event_id, venue_id, event_date, start_time, end_time) VALUES

(1, 1, '2026-11-15', '09:00:00', '17:00:00'),
(2, 2, '2026-11-20', '13:00:00', '16:00:00'),
(3, 1, '2026-11-25', '10:00:00', '12:00:00'),
(4, 7, '2026-09-15', '09:00:00', '12:00:00'),

(5, 4, '2026-11-22', '09:00:00', '17:00:00'),
(6, 13, '2026-12-02', '10:00:00', '12:00:00'),
(7, 3, '2026-12-08', '13:00:00', '17:00:00'),
(8, 4, '2026-09-20', '14:00:00', '16:00:00'),

(9, 3, '2026-12-05', '08:00:00', '17:00:00'),
(10, 1, '2026-12-10', '13:00:00', '16:00:00'),
(11, 5, '2026-09-10', '09:00:00', '11:00:00'),
(12, 15, '2026-12-16', '10:00:00', '15:00:00'),

(13, 6, '2026-12-12', '10:00:00', '16:00:00'),
(14, 6, '2026-12-15', '13:00:00', '15:00:00'),
(15, 14, '2026-12-19', '09:00:00', '12:00:00'),

(16, 5, '2026-12-18', '08:00:00', '12:00:00'),
(17, 11, '2026-12-20', '07:00:00', '11:00:00'),
(18, 1, '2026-12-22', '13:00:00', '15:00:00'),

(19, 1, '2026-11-18', '09:00:00', '12:00:00'),
(20, 13, '2026-12-04', '13:00:00', '16:00:00'),
(21, 4, '2026-09-25', '10:00:00', '12:00:00'),

(22, 1, '2026-11-27', '09:00:00', '12:00:00'),
(23, 2, '2026-12-06', '13:00:00', '16:00:00'),
(24, 7, '2026-12-14', '09:00:00', '12:00:00'),

(25, 15, '2026-11-19', '09:00:00', '16:00:00'),
(26, 14, '2026-12-03', '13:00:00', '16:00:00'),
(27, 15, '2026-09-18', '09:00:00', '12:00:00'),

(28, 9, '2026-11-23', '09:00:00', '16:00:00'),
(29, 9, '2026-12-09', '13:00:00', '16:00:00'),
(30, 9, '2026-12-17', '09:00:00', '12:00:00'),

(31, 3, '2026-11-21', '09:00:00', '17:00:00'),
(32, 1, '2026-12-11', '13:00:00', '16:00:00'),
(33, 3, '2026-09-22', '10:00:00', '12:00:00'),

(34, 11, '2026-11-16', '07:00:00', '10:00:00'),
(35, 6, '2026-12-07', '13:00:00', '16:00:00'),
(36, 14, '2026-12-13', '09:00:00', '12:00:00'),

(37, 10, '2026-11-28', '08:00:00', '17:00:00'),
(38, 10, '2026-12-13', '09:00:00', '16:00:00'),
(39, 11, '2026-12-21', '06:00:00', '09:00:00'),
(40, 10, '2026-09-28', '14:00:00', '17:00:00');


-- =============================================
-- 7. REGISTRATIONS (100)
-- =============================================

INSERT INTO registration
(student_id, event_id, registration_date) VALUES

-- Event 1
(1, 1, '2026-10-25'),
(2, 1, '2026-10-26'),
(3, 1, '2026-10-27'),
(4, 1, '2026-10-28'),
(5, 1, '2026-10-29'),

-- Event 2
(3, 2, '2026-10-28'),
(1, 2, '2026-10-29'),
(6, 2, '2026-10-30'),

-- Event 3
(1, 3, '2026-10-28'),
(4, 3, '2026-10-29'),
(7, 3, '2026-10-30'),

-- Event 5
(4, 5, '2026-10-29'),
(5, 5, '2026-10-29'),
(6, 5, '2026-10-30'),
(8, 5, '2026-10-30'),

-- Event 6
(4, 6, '2026-10-30'),
(9, 6, '2026-10-30'),

-- Event 7
(5, 7, '2026-10-29'),
(7, 7, '2026-10-30'),
(10, 7, '2026-10-30'),

-- Event 9
(6, 9, '2026-10-25'),
(7, 9, '2026-10-26'),
(8, 9, '2026-10-27'),
(9, 9, '2026-10-28'),
(10, 9, '2026-10-29'),
(11, 9, '2026-10-30'),

-- Event 10
(6, 10, '2026-10-30'),
(8, 10, '2026-10-30'),
(12, 10, '2026-10-30'),

-- Event 12
(7, 12, '2026-10-29'),
(9, 12, '2026-10-30'),

-- Event 13
(8, 13, '2026-10-28'),
(10, 13, '2026-10-29'),
(11, 13, '2026-10-30'),

-- Event 15
(9, 15, '2026-10-29'),
(10, 15, '2026-10-30'),
(12, 15, '2026-10-30'),

-- Event 16
(10, 16, '2026-10-28'),
(11, 16, '2026-10-29'),
(12, 16, '2026-10-30'),

-- Event 17
(11, 17, '2026-10-29'),
(13, 17, '2026-10-30'),

-- Event 18
(12, 18, '2026-10-30'),
(14, 18, '2026-10-30'),

-- Event 19
(1, 19, '2026-10-28'),
(2, 19, '2026-10-29'),
(3, 19, '2026-10-30'),

-- Event 20
(12, 20, '2026-10-29'),
(13, 20, '2026-10-30'),

-- Event 22
(14, 22, '2026-10-28'),
(15, 22, '2026-10-29'),
(16, 22, '2026-10-30'),

-- Event 23
(14, 23, '2026-10-29'),
(17, 23, '2026-10-30'),

-- Event 24
(15, 24, '2026-10-30'),
(18, 24, '2026-10-30'),

-- Event 25
(16, 25, '2026-10-27'),
(17, 25, '2026-10-28'),
(18, 25, '2026-10-29'),
(19, 25, '2026-10-30'),

-- Event 26
(16, 26, '2026-10-29'),
(20, 26, '2026-10-30'),

-- Event 28
(18, 28, '2026-10-29'),
(19, 28, '2026-10-30'),

-- Event 29
(19, 29, '2026-10-30'),
(20, 29, '2026-10-30'),

-- Event 30
(20, 30, '2026-10-30'),
(21, 30, '2026-10-30'),

-- Event 31
(20, 31, '2026-10-29'),
(21, 31, '2026-10-30'),
(22, 31, '2026-10-30'),

-- Event 32
(21, 32, '2026-10-29'),
(22, 32, '2026-10-30'),

-- Event 34
(22, 34, '2026-10-28'),
(23, 34, '2026-10-29'),
(24, 34, '2026-10-30'),

-- Event 35
(23, 35, '2026-10-29'),
(25, 35, '2026-10-30'),

-- Event 36
(24, 36, '2026-10-29'),
(26, 36, '2026-10-30'),

-- Event 37
(24, 37, '2026-10-28'),
(25, 37, '2026-10-29'),
(26, 37, '2026-10-30'),

-- Event 38
(25, 38, '2026-10-29'),
(27, 38, '2026-10-30'),

-- Event 39
(26, 39, '2026-10-29'),
(28, 39, '2026-10-30'),

-- Event 40
(27, 40, '2026-09-20'),
(28, 40, '2026-09-21'),
(29, 40, '2026-09-22'),
(30, 40, '2026-09-23');

