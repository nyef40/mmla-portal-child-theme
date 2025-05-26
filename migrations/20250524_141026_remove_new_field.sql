-- Migration: remove_new_field
-- Created: Sat May 24 14:10:26 PDT 2025

-- Add your SQL statements here
-- Example:
-- ALTER TABLE lqbk_referral_submissions ADD COLUMN new_field VARCHAR(255);
ALTER TABLE lqbk_referral_submissions DROP COLUMN new_field;
