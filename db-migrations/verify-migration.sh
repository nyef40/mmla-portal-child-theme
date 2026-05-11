#!/bin/bash
# Verify database migration was successful

echo "=== Verifying Portal Database Migration ==="
echo ""

LIVE_HOST="p3plzcpnl503868.prod.phx3.secureserver.net"
LIVE_USER="i9762009_jvnp1"
LIVE_PASS="H.qHF9chIbSErvfWUmw65"
LIVE_DB="i9762009_jvnp1"

echo "Local Database Schema:"
docker exec mmla-portal-db-1 mysql -u wordpress -pwordpress wordpress -e "DESCRIBE lqbk_portal_users;"
echo ""

echo "Live Database Schema:"
ssh c9gyjyiudq9m@$LIVE_HOST "mysql -u $LIVE_USER -p$LIVE_PASS $LIVE_DB -e 'DESCRIBE lqbk_portal_users;'"
echo ""

echo "Local Record Count:"
docker exec mmla-portal-db-1 mysql -u wordpress -pwordpress wordpress -e "SELECT COUNT(*) as total FROM lqbk_portal_users;"
echo ""

echo "Live Record Count:"
ssh c9gyjyiudq9m@$LIVE_HOST "mysql -u $LIVE_USER -p$LIVE_PASS $LIVE_DB -e 'SELECT COUNT(*) as total FROM lqbk_portal_users;'"
echo ""

echo "Sample Records Comparison:"
echo "Local:"
docker exec mmla-portal-db-1 mysql -u wordpress -pwordpress wordpress -e 'SELECT id, username, email, first_name, last_name, specialty FROM lqbk_portal_users ORDER BY id;'
echo ""
echo "Live:"
ssh c9gyjyiudq9m@$LIVE_HOST "mysql -u $LIVE_USER -p$LIVE_PASS $LIVE_DB -e 'SELECT id, username, email, first_name, last_name, specialty FROM lqbk_portal_users ORDER BY id;'"