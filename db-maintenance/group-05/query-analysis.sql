
-- Group 05: SHIORI Café
-- Activity 5: Query Performance Analysis
-- Query: Retrieve active menu items ordered by item name.
-- Account tested: app_runtime
-- Result: Blocked by schema permission restriction.

SET ROLE app_runtime;

EXPLAIN
SELECT
    menu_item_id,
    item_name,
    current_price
FROM shioricafe_schema.menu_item
WHERE is_active = TRUE
ORDER BY item_name;

-- Actual result:
-- ERROR: permission denied for schema shioricafe_schema
-- app_runtime does not have USAGE privilege on the schema.
-- No query execution plan was produced.
-- No permissions were modified to bypass the restriction.
