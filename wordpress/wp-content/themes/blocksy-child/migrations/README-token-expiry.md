# token_expiry and portal_users

## Situation

- **Local** (Docker): `lqbk_portal_users` has column `token_expiry` (datetime, nullable). Registration insert includes it and works.
- **Live** (GoDaddy): Table was missing `token_expiry`, so the same insert failed and no row was created (success was still returned).

## Local test (executed)

- `DESCRIBE lqbk_portal_users` on local: **token_expiry** is present (datetime, YES, NULL).
- `SELECT ... token_expiry FROM lqbk_portal_users`: column exists; existing rows show NULL (older data). A new registration will populate it.

## Decision

**Keep the insert as-is (with `token_expiry`)** and **add the column on live** so live matches local. No code change; run the migration on live only.

## Local test (run before/after)

1. **Check schema (local)**  
   ```bash
   docker exec -it mmla-portal-db-1 mysql -u root -proot wordpress -e "DESCRIBE lqbk_portal_users;"
   ```  
   Confirm `token_expiry` is present.

2. **Register a new user (local)**  
   - Open http://localhost/register/ (or your local register URL).
   - Fill the form with a new username/email and submit.

3. **Verify row and token_expiry (local)**  
   ```bash
   docker exec -it mmla-portal-db-1 mysql -u root -proot wordpress -e "
   SELECT id, wp_user_id, username, email, created_at, validation_token IS NOT NULL AS has_token, token_expiry
   FROM lqbk_portal_users
   ORDER BY id DESC LIMIT 3;
   "
   ```  
   The new row should have `token_expiry` set to a future datetime.

4. **Conclusion**  
   If the new row has `token_expiry` set, the code is correct. Apply the migration on **live** so the same insert works there.

## Apply on live

Run the migration once on the live database (e.g. phpMyAdmin or `mysql` CLI):

```sql
ALTER TABLE lqbk_portal_users
ADD COLUMN token_expiry DATETIME NULL
AFTER validation_token;
```

If you see "Duplicate column name 'token_expiry'", the column already exists; no further action needed.
