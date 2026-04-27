-- Vertue CRM cPanel Hotfix (2026-04-27)
-- Purpose:
-- 1) Add missing university columns used by advanced dependent search.
-- 2) Sync student portal users from students table.
-- 3) Fix common "Invalid credentials" for student portal by setting a known temporary password.
--
-- Temporary student password after this script:
--   Student123!
--
-- IMPORTANT:
-- - Run this once in phpMyAdmin on the target cPanel database.
-- - After running, ask students to change password.

START TRANSACTION;

-- 1) Ensure required columns exist on universities (for country -> city -> type search).
SET @sql_city = (
    SELECT IF(
        EXISTS(
            SELECT 1
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'universities'
              AND COLUMN_NAME = 'city'
        ),
        'SELECT 1',
        'ALTER TABLE universities ADD COLUMN city VARCHAR(120) NULL AFTER country'
    )
);
PREPARE stmt_city FROM @sql_city;
EXECUTE stmt_city;
DEALLOCATE PREPARE stmt_city;

SET @sql_type = (
    SELECT IF(
        EXISTS(
            SELECT 1
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'universities'
              AND COLUMN_NAME = 'institution_type'
        ),
        'SELECT 1',
        'ALTER TABLE universities ADD COLUMN institution_type VARCHAR(40) NOT NULL DEFAULT ''university'' AFTER city'
    )
);
PREPARE stmt_type FROM @sql_type;
EXECUTE stmt_type;
DEALLOCATE PREPARE stmt_type;

-- Backfill type defaults for old rows.
UPDATE universities
SET institution_type = CASE
    WHEN LOWER(COALESCE(name, '')) LIKE '%school%' THEN 'school'
    ELSE 'university'
END
WHERE institution_type IS NULL OR institution_type = '';

-- 2) Ensure students.user_id exists to keep student<->user mapping stable.
SET @sql_students_user_id = (
    SELECT IF(
        EXISTS(
            SELECT 1
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'students'
              AND COLUMN_NAME = 'user_id'
        ),
        'SELECT 1',
        'ALTER TABLE students ADD COLUMN user_id BIGINT UNSIGNED NULL AFTER tenant_id'
    )
);
PREPARE stmt_students_user_id FROM @sql_students_user_id;
EXECUTE stmt_students_user_id;
DEALLOCATE PREPARE stmt_students_user_id;

-- 3) Create missing student user accounts from students records.
-- Temporary password hash below = Student123!
INSERT INTO users (
    tenant_id,
    name,
    email,
    password,
    role_slug,
    language,
    is_active,
    created_at,
    updated_at
)
SELECT
    s.tenant_id,
    s.full_name,
    LOWER(TRIM(s.email)),
    '$2y$10$IhV7NTbnAQjuQ7HE/vMVvuJ0VEWSau.fndgjg3hmBAb4qo/2Ir1gG',
    'student',
    'en',
    1,
    NOW(),
    NOW()
FROM students s
LEFT JOIN users u ON LOWER(TRIM(u.email)) = LOWER(TRIM(s.email))
WHERE s.deleted_at IS NULL
  AND s.email IS NOT NULL
  AND TRIM(s.email) <> ''
  AND u.id IS NULL;

-- 4) Link students.user_id to student role users.
UPDATE students s
JOIN users u
  ON LOWER(TRIM(u.email)) = LOWER(TRIM(s.email))
 AND u.role_slug = 'student'
SET s.user_id = u.id
WHERE s.deleted_at IS NULL
  AND (s.user_id IS NULL OR s.user_id = 0);

-- 5) Ensure student users are active.
UPDATE users
SET is_active = 1, deleted_at = NULL
WHERE role_slug = 'student';

-- 6) Reset all student passwords to temporary password (fixes lingering invalid-credential cases).
-- If you do NOT want a global reset, comment this block.
UPDATE users
SET password = '$2y$10$IhV7NTbnAQjuQ7HE/vMVvuJ0VEWSau.fndgjg3hmBAb4qo/2Ir1gG'
WHERE role_slug = 'student'
  AND deleted_at IS NULL;

COMMIT;

