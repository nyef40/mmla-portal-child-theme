# Portal Database Migration Guide

## Overview
Sync `lqbk_portal_users` schema and data from local to live server.

## Current State
- **Local**: 19 columns (full schema with user profile data)
- **Live**: 6 columns (minimal schema, missing user details)

## Migration Steps

### Option A: Automated (Recommended)
```bash
cd wordpress/wp-content/themes/blocksy-child/db-migrations
chmod +x sync-portal-db.sh verify-migration.sh
./sync-portal-db.sh
```

### Option B: Manual

#### 1. Backup Live Database
```bash
ssh c9gyjyiudq9m@p3plzcpnl503868.prod.phx3.secureserver.net
mysqldump -u i9762009_jvnp1 -pH.qHF9chIbSErvfWUmw65 i9762009_jvnp1 lqbk_portal_users > portal_users_backup.sql
```

#### 2. Alter Live Schema
```bash
mysql -u i9762009_jvnp1 -pH.qHF9chIbSErvfWUmw65 i9762009_jvnp1 < 01-alter-live-portal-users.sql
```

#### 3. Export Local Data
```bash
# On local machine
docker exec mmla-portal-wordpress-1 mysqldump -u root -ppassword wordpress lqbk_portal_users --no-create-info > portal_users_data.sql
```

#### 4. Import to Live
```bash
# Upload SQL file
scp portal_users_data.sql c9gyjyiudq9m@p3plzcpnl503868.prod.phx3.secureserver.net:~/

# Import on live
ssh c9gyjyiudq9m@p3plzcpnl503868.prod.phx3.secureserver.net
mysql -u i9762009_jvnp1 -pH.qHF9chIbSErvfWUmw65 i9762009_jvnp1 < ~/portal_users_data.sql
```

#### 5. Verify
```bash
./verify-migration.sh
```

## What Gets Migrated

### Schema Changes
- Adds 13 missing columns to live `lqbk_portal_users`
- Renames `verified` → `email_verified`
- Adds unique indexes on `username` and `email`
- Sets proper defaults and constraints

### Data Migration
- All existing live records preserved
- Local records imported with `ON DUPLICATE KEY UPDATE`
- wp_user_id foreign key maintained

## Rollback
If migration fails:
```bash
ssh c9gyjyiudq9m@p3plzcpnl503868.prod.phx3.secureserver.net
mysql -u i9762009_jvnp1 -pH.qHF9chIbSErvfWUmw65 i9762009_jvnp1 < ~/portal_users_backup_*.sql
```

## Post-Migration
1. Test portal login on live site
2. Verify user profile data displays correctly
3. Check registration flow
4. Update any hardcoded SQL queries in PHP if needed
