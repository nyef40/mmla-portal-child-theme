-- Add token_expiry to portal_users (live table may lack it; local has it).
-- Run once on live DB so registration insert matches local.
-- Replace lqbk_ with your table prefix if different.
-- If the column already exists, you'll get "Duplicate column" and can ignore it.

ALTER TABLE lqbk_portal_users
ADD COLUMN token_expiry DATETIME NULL
AFTER validation_token;
