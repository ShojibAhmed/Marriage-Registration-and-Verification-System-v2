-- Kazi account validity migration for the existing marriage_project database.
--
-- SAFETY:
--   1. Take and verify a fresh database backup before proceeding.
--   2. Run the preflight queries first and inspect their results. Do not
--      continue if either required table is missing, the history table
--      structure differs from the application contract, or created_at has
--      NULL values.
--   3. Run this migration during a maintenance window, using one MariaDB
--      connection. Do not run setup.php or the project setup SQL.
--   4. This script never creates or drops a table and never changes created_at.
--
-- TIMEZONE INTERPRETATION:
--   created_at is a DATETIME and contains no timezone metadata. This migration
--   treats its stored clock value as Asia/Dhaka local time and preserves that
--   value when calculating expiry. SET time_zone establishes the session
--   timezone; it does not convert existing DATETIME values. If existing
--   created_at values are UTC, stop and decide on an explicit conversion before
--   applying this migration.
--
-- PARTIAL-RUN SAFETY:
--   MariaDB DDL may commit independently. The conditional column/index
--   additions and NULL-only/idempotent backfills allow a reviewed rerun after
--   checking the actual schema and prior results. Do not blindly rerun after
--   an error.

SET time_zone = '+06:00';

-- PREFLIGHT: inspect results before proceeding to the migration statements.
SELECT DATABASE() AS selected_database, VERSION() AS database_version;

SELECT table_name
FROM information_schema.tables
WHERE table_schema = DATABASE()
  AND table_name IN ('kazi_users', 'kazi_account_history')
ORDER BY table_name;

SELECT table_name, column_name, column_type, is_nullable, column_default
FROM information_schema.columns
WHERE table_schema = DATABASE()
  AND table_name IN ('kazi_users', 'kazi_account_history')
ORDER BY table_name, ordinal_position;

SELECT table_name, index_name, non_unique, seq_in_index, column_name
FROM information_schema.statistics
WHERE table_schema = DATABASE()
  AND table_name IN ('kazi_users', 'kazi_account_history')
ORDER BY table_name, index_name, seq_in_index;

SELECT
  COUNT(*) AS total_accounts,
  SUM(created_at IS NULL) AS missing_registration_date_count
FROM kazi_users;

-- MIGRATION: add only missing columns/index; retain the existing history table.
-- MariaDB supports IF NOT EXISTS for these ALTER TABLE additions.
ALTER TABLE kazi_users
  ADD COLUMN IF NOT EXISTS expires_at DATETIME NULL AFTER created_at,
  ADD COLUMN IF NOT EXISTS last_renewed_at DATETIME NULL AFTER expires_at,
  ADD INDEX IF NOT EXISTS idx_kazi_status_expires (status, expires_at);

-- Backfill from original created_at. A February 29 registration whose
-- anniversary year is not a leap year expires on March 1 at the same time.
-- Existing non-NULL expiries and all created_at values are preserved.
UPDATE kazi_users
SET expires_at = CASE
  WHEN MONTH(created_at) = 2
    AND DAY(created_at) = 29
    AND MOD(YEAR(created_at) + 1, 400) <> 0
    AND (
      MOD(YEAR(created_at) + 1, 4) <> 0
      OR MOD(YEAR(created_at) + 1, 100) = 0
    )
  THEN TIMESTAMP(
    DATE_ADD(DATE_ADD(DATE(created_at), INTERVAL 1 DAY), INTERVAL 1 YEAR),
    TIME(created_at)
  )
  ELSE DATE_ADD(created_at, INTERVAL 1 YEAR)
END
WHERE expires_at IS NULL
  AND created_at IS NOT NULL;

-- Reuse the pre-existing kazi_account_history table. The NOT EXISTS condition
-- makes sequential reruns safe; run during maintenance to prevent concurrent
-- backfill executions from racing.
INSERT INTO kazi_account_history
  (kazi_id, action_type, previous_status, new_status, previous_expiry,
   new_expiry, admin_id, action_at, reason)
SELECT k.kazi_id, 'registered', NULL, k.status, NULL, k.expires_at, NULL,
       k.created_at, 'Initial account history backfill'
FROM kazi_users AS k
WHERE k.created_at IS NOT NULL
  AND k.expires_at IS NOT NULL
  AND NOT EXISTS (
    SELECT 1
    FROM kazi_account_history AS h
    WHERE h.kazi_id = k.kazi_id
      AND h.action_type = 'registered'
  );

-- POST-MIGRATION VERIFICATION: review all results before enabling the
-- schema-dependent application code.
SELECT
  COUNT(*) AS total_accounts,
  SUM(expires_at IS NULL) AS missing_expiry_count,
  SUM(created_at IS NULL) AS missing_registration_date_count
FROM kazi_users;

SELECT
  k.kazi_id,
  k.created_at,
  k.expires_at,
  k.last_renewed_at
FROM kazi_users AS k
ORDER BY k.kazi_id;

SELECT
  h.kazi_id,
  h.action_type,
  COUNT(*) AS registration_event_count
FROM kazi_account_history AS h
WHERE h.action_type = 'registered'
GROUP BY h.kazi_id, h.action_type
HAVING COUNT(*) > 1;

-- ROLLBACK GUIDANCE (manual, not executable here):
--   * If validation fails, keep the application in maintenance mode and do not
--     enable code that requires expiry columns.
--   * Investigate and correct forward where possible. DDL can partially apply;
--     inspect information_schema before any retry.
--   * Before any account renewal/status actions have been recorded, a reviewed
--     rollback may remove only columns/indexes that this migration actually
--     added, after confirming the verified backup is restorable. Never drop
--     kazi_account_history or overwrite/drop pre-existing columns.
--   * Once operational history or renewal data exists, do not remove these
--     fields or restore the backup blindly; that would discard valid changes.
--   * Do not change expires_at to NOT NULL until missing_expiry_count is zero
--     and every exceptional record has been explicitly resolved.
