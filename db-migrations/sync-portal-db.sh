#!/bin/bash
# Database sync script: Local → Live
# Run this from your local machine with SSH access to live server

set -e  # Exit on error

echo "=== Portal Database Migration: Local → Live ==="
echo ""

# Configuration
LOCAL_DB="wordpress"
LOCAL_USER="root"
LOCAL_PASS="password"
LIVE_HOST="p3plzcpnl503868.prod.phx3.secureserver.net"
LIVE_USER="i9762009_jvnp1"
LIVE_PASS="H.qHF9chIbSErvfWUmw65"
LIVE_DB="i9762009_jvnp1"
TEMP_DIR="/tmp/portal_migration_$(date +%s)"

mkdir -p "$TEMP_DIR"

echo "Step 1: Backing up live database..."
ssh c9gyjyiudq9m@$LIVE_HOST "mysqldump -u $LIVE_USER -p$LIVE_PASS $LIVE_DB lqbk_portal_users > ~/portal_users_backup_$(date +%Y%m%d_%H%M%S).sql"
echo "✓ Live backup created"
echo ""

echo "Step 2: Altering live table schema..."
scp db-migrations/01-alter-live-portal-users.sql c9gyjyiudq9m@$LIVE_HOST:~/
ssh c9gyjyiudq9m@$LIVE_HOST "mysql -u $LIVE_USER -p$LIVE_PASS $LIVE_DB < ~/01-alter-live-portal-users.sql"
echo "✓ Live schema updated"
echo ""

echo "Step 3: Exporting local data..."
docker exec mmla-portal-wordpress-1 mysql -u $LOCAL_USER -p$LOCAL_PASS $LOCAL_DB -e "
SELECT 
    id,
    wp_user_id,
    IFNULL(username, '') as username,
    IFNULL(email, '') as email,
    IFNULL(first_name, '') as first_name,
    IFNULL(last_name, '') as last_name,
    IFNULL(phone, '') as phone,
    IFNULL(practice, '') as practice,
    IFNULL(address, '') as address,
    IFNULL(city, '') as city,
    IFNULL(state, '') as state,
    IFNULL(zip, '') as zip,
    IFNULL(email_verified, 0) as email_verified,
    IFNULL(specialty, '') as specialty,
    IFNULL(license_number, '') as license_number,
    IFNULL(role, '') as role,
    created_at,
    IFNULL(updated_at, created_at) as updated_at,
    IFNULL(validation_token, '') as validation_token
FROM lqbk_portal_users
ORDER BY id
" > "$TEMP_DIR/portal_users_data.csv"
echo "✓ Local data exported to $TEMP_DIR/portal_users_data.csv"
echo ""

echo "Step 4: Creating import SQL..."
# Convert CSV to INSERT statements
cat "$TEMP_DIR/portal_users_data.csv" | tail -n +2 | while IFS=$'\t' read -r id wp_user_id username email first_name last_name phone practice address city state zip email_verified specialty license_number role created_at updated_at validation_token; do
    echo "INSERT INTO lqbk_portal_users (id, wp_user_id, username, email, first_name, last_name, phone, practice, address, city, state, zip, email_verified, specialty, license_number, role, created_at, updated_at, validation_token) VALUES ($id, $wp_user_id, '$username', '$email', '$first_name', '$last_name', '$phone', '$practice', '$address', '$city', '$state', '$zip', $email_verified, '$specialty', '$license_number', '$role', '$created_at', '$updated_at', '$validation_token') ON DUPLICATE KEY UPDATE username='$username', email='$email', first_name='$first_name', last_name='$last_name', phone='$phone', practice='$practice', address='$address', city='$city', state='$state', zip='$zip', email_verified=$email_verified, specialty='$specialty', license_number='$license_number', role='$role', updated_at='$updated_at', validation_token='$validation_token';"
done > "$TEMP_DIR/import_data.sql"
echo "✓ Import SQL created"
echo ""

echo "Step 5: Uploading and importing data to live..."
scp "$TEMP_DIR/import_data.sql" c9gyjyiudq9m@$LIVE_HOST:~/
ssh c9gyjyiudq9m@$LIVE_HOST "mysql -u $LIVE_USER -p$LIVE_PASS $LIVE_DB < ~/import_data.sql"
echo "✓ Data imported to live"
echo ""

echo "Step 6: Verifying migration..."
ssh c9gyjyiudq9m@$LIVE_HOST "mysql -u $LIVE_USER -p$LIVE_PASS $LIVE_DB -e 'SELECT COUNT(*) as total_records FROM lqbk_portal_users; DESCRIBE lqbk_portal_users;'"
echo "✓ Verification complete"
echo ""

echo "=== Migration Complete ==="
echo "Temp files saved in: $TEMP_DIR"
echo "Live backup location: ~/portal_users_backup_*.sql on server"
