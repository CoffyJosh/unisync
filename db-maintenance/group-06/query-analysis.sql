-- Group 06: ByaheRentals
-- Activity 5: Query Performance Analysis
-- Account tested: app_runtime
-- Based on a customer rental lookup using public.rental.

EXPLAIN
SELECT rental_id, vehicle_id, pickup_datetime,
       return_datetime, rental_status
FROM public.rental
WHERE customer_id = 1
ORDER BY pickup_datetime DESC;

-- Observed plan:
-- Index Scan using idx_rental_customer on rental
-- Sort by pickup_datetime DESC
-- Estimated output: 1 row
-- Estimated total cost: 8.18

-- The existing customer index supports the lookup.
-- The additional sort has a low estimated cost.
-- No immediate index changes are recommended.
