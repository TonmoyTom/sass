-- ─────────────────────────────────────────────────────────────────────
-- MySQL Init Script
-- Runs once on first container start (when mysql_data volume is empty)
-- ─────────────────────────────────────────────────────────────────────

-- Central / landlord database already created via MYSQL_DATABASE env var.
-- Grant the app user full privileges to create tenant databases dynamically.

GRANT ALL PRIVILEGES ON *.* TO 'laravel'@'%' WITH GRANT OPTION;
FLUSH PRIVILEGES;
