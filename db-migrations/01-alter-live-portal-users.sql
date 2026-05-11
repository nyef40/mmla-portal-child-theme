-- Migration: Add missing columns to lqbk_portal_users on live server
-- Run this on live database: i9762009_jvnp1
-- Target table: lqbk_portal_users

USE i9762009_jvnp1;

-- Add missing columns (if they don't exist)
ALTER TABLE lqbk_portal_users
ADD COLUMN IF NOT EXISTS username varchar(60) DEFAULT NULL AFTER wp_user_id,
ADD COLUMN IF NOT EXISTS email varchar(100) DEFAULT NULL AFTER username,
ADD COLUMN IF NOT EXISTS first_name varchar(50) DEFAULT NULL AFTER email,
ADD COLUMN IF NOT EXISTS last_name varchar(50) DEFAULT NULL AFTER first_name,
ADD COLUMN IF NOT EXISTS phone varchar(20) DEFAULT NULL AFTER last_name,
ADD COLUMN IF NOT EXISTS practice varchar(100) DEFAULT NULL AFTER phone,
ADD COLUMN IF NOT EXISTS address varchar(100) DEFAULT NULL AFTER practice,
ADD COLUMN IF NOT EXISTS city varchar(50) DEFAULT NULL AFTER address,
ADD COLUMN IF NOT EXISTS state varchar(20) DEFAULT NULL AFTER city,
ADD COLUMN IF NOT EXISTS zip varchar(10) DEFAULT NULL AFTER state,
ADD COLUMN IF NOT EXISTS email_verified tinyint(1) DEFAULT 0 AFTER zip,
ADD COLUMN IF NOT EXISTS role varchar(50) DEFAULT NULL AFTER license_number,
ADD COLUMN IF NOT EXISTS updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at,
ADD COLUMN IF NOT EXISTS validation_token varchar(64) DEFAULT NULL AFTER updated_at;

-- Rename 'verified' to 'email_verified' if it exists and email_verified doesn't
-- (This handles the column name difference)
-- Note: MariaDB 10.6 doesn't support IF NOT EXISTS for CHANGE COLUMN, so check first
SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA = 'i9762009_jvnp1' 
    AND TABLE_NAME = 'lqbk_portal_users' 
    AND COLUMN_NAME = 'verified');

SET @col_new_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA = 'i9762009_jvnp1' 
    AND TABLE_NAME = 'lqbk_portal_users' 
    AND COLUMN_NAME = 'email_verified');

-- If 'verified' exists and 'email_verified' doesn't, rename it
SET @sql = IF(@col_exists = 1 AND @col_new_exists = 0, 
    'ALTER TABLE lqbk_portal_users CHANGE verified email_verified tinyint(1) DEFAULT 0', 
    'SELECT "Column already renamed or does not exist"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add unique indexes for username and email
CREATE UNIQUE INDEX IF NOT EXISTS idx_username ON lqbk_portal_users(username);
CREATE UNIQUE INDEX IF NOT EXISTS idx_email ON lqbk_portal_users(email);

-- Verify the schema
DESCRIBE lqbk_portal_users;
