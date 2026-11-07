-- Group 06 - ByaheRentals
-- Activity 6: Backup and Recovery Verification

-- =====================================================
-- 1. RESTORE TEST DATABASE
-- Database: car_rental_db_restore_test
-- =====================================================

-- Verify restored table count
SELECT COUNT(*) AS restored_tables
FROM information_schema.tables
WHERE table_schema = 'public'
AND table_type = 'BASE TABLE';

-- Verify representative restored records
SELECT COUNT(*) AS customers FROM public.customer;
SELECT COUNT(*) AS vehicles FROM public.vehicle;
SELECT COUNT(*) AS rentals FROM public.rental;

-- Verify restored primary key constraints
SELECT COUNT(*) AS primary_key_constraints
FROM information_schema.table_constraints
WHERE constraint_schema = 'public'
AND constraint_type = 'PRIMARY KEY';

-- Verify restored foreign key constraints
SELECT COUNT(*) AS foreign_key_constraints
FROM information_schema.table_constraints
WHERE constraint_schema = 'public'
AND constraint_type = 'FOREIGN KEY';

-- Verify rental indexes and exclusion constraint
SELECT indexname
FROM pg_indexes
WHERE schemaname = 'public'
AND tablename = 'rental'
ORDER BY indexname;

-- Verify Activity 4 roles
SELECT rolname, rolsuper, rolcreatedb, rolcanlogin
FROM pg_roles
WHERE rolname IN (
    'app_admin_grp',
    'app_runtime_grp',
    'app_readonly_grp',
    'app_runtime'
)
ORDER BY rolname;

-- Verify customer-table permissions
SELECT grantee, privilege_type
FROM information_schema.role_table_grants
WHERE table_schema = 'public'
AND table_name = 'customer'
AND grantee IN ('app_runtime_grp', 'app_readonly_grp')
ORDER BY grantee, privilege_type;


-- =====================================================
-- 2. RECOVERY TEST DATABASE
-- Database: car_rental_db_recovery_test
-- =====================================================

-- Verify recovered test record
SELECT * FROM public.activity6_recovery_test;

-- Verify recovered record count
SELECT COUNT(*) AS recovered_records
FROM public.activity6_recovery_test;


-- =====================================================
-- 3. ORIGINAL DATABASE INTEGRITY CHECK
-- Database: car_rental_db
-- =====================================================

-- Verify original database records remained intact
SELECT
    (SELECT COUNT(*) FROM public.customer) AS customers,
    (SELECT COUNT(*) FROM public.vehicle) AS vehicles,
    (SELECT COUNT(*) FROM public.rental) AS rentals;