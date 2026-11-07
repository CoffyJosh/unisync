-- Group 06: ByaheRentals
-- Activity 5: Database Maintenance
-- Account tested: app_runtime
-- Database: car_rental_db

-- Maintenance was not completed because app_runtime
-- lacks the required permissions on public.customer.

-- Maintenance commands requiring an authorized account:
-- ANALYZE VERBOSE public.customer;
-- VACUUM (VERBOSE) public.customer;

-- Actual results:
-- ANALYZE: permission denied to analyze "customer", skipping it.
-- VACUUM: permission denied to vacuum "customer", skipping it.