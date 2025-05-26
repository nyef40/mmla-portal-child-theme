-- Migration: update_existing_data
-- Created: Sat May 24 15:35:48 PDT 2025

-- Add your SQL statements here
-- Example:
-- ALTER TABLE lqbk_referral_submissions ADD COLUMN new_field VARCHAR(255);
-- UPDATE lqbk_referral_submissions SET new_field = 'default_value' WHERE new_field IS NULL;
delete from lqbk_referral_submissions where submission_id = 16;