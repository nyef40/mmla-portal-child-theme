#!/bin/bash
# Fixed server migration runner
# Usage: ./server-migrate-fixed.sh

REMOTE_USER="godaddy"
MIGRATION_DIR="migrations"

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

echo -e "${YELLOW}Running migrations on server (FIXED VERSION)...${NC}"

# Upload all migration files to server
echo -e "${YELLOW}Uploading migration files...${NC}"
ssh $REMOTE_USER "mkdir -p ~/migrations"
rsync -av $MIGRATION_DIR/ $REMOTE_USER:~/migrations/

# Create and upload fixed migration runner script for server
cat > temp-server-migrate-fixed.sh << 'SERVER_EOF'
#!/bin/bash
MIGRATION_TABLE="schema_migrations"
MIGRATION_DIR="~/migrations"

cd ~/public_html

echo "=== Migration Debug Info ==="
echo "Current directory: $(pwd)"
echo "WordPress database prefix: $(wp config get table_prefix)"

# Get the actual table prefix
TABLE_PREFIX=$(wp config get table_prefix)
echo "Table prefix: $TABLE_PREFIX"

# Create migrations table if it doesn't exist
echo "Creating migrations table if it doesn't exist..."
wp db query "CREATE TABLE IF NOT EXISTS ${TABLE_PREFIX}${MIGRATION_TABLE} (
    id INT AUTO_INCREMENT PRIMARY KEY,
    migration VARCHAR(255) NOT NULL UNIQUE,
    executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);"

# Check if table was created
wp db query "SHOW TABLES LIKE '${TABLE_PREFIX}${MIGRATION_TABLE}';"

# Get executed migrations
echo "Checking executed migrations..."
EXECUTED=$(wp db query "SELECT migration FROM ${TABLE_PREFIX}${MIGRATION_TABLE}" --skip-column-names 2>/dev/null || echo "")
echo "Previously executed migrations:"
echo "$EXECUTED"

echo ""
echo "=== Running Migrations ==="

# Run pending migrations
for migration_file in $MIGRATION_DIR/*.sql; do
    if [ -f "$migration_file" ]; then
        migration_name=$(basename "$migration_file" .sql)
        echo "Processing migration: $migration_name"
        
        # Check if already executed
        if ! echo "$EXECUTED" | grep -q "^$migration_name$"; then
            echo "Running migration: $migration_name"
            
            # Read and modify the migration file to use correct table prefix
            TEMP_MIGRATION=$(mktemp)
            sed "s/lqbk_/${TABLE_PREFIX}/g" "$migration_file" > "$TEMP_MIGRATION"
            
            echo "Migration content:"
            cat "$TEMP_MIGRATION"
            echo ""
            
            if wp db query < "$TEMP_MIGRATION"; then
                wp db query "INSERT INTO ${TABLE_PREFIX}${MIGRATION_TABLE} (migration) VALUES ('$migration_name');"
                echo "✓ Migration completed: $migration_name"
            else
                echo "✗ Migration failed: $migration_name"
                rm "$TEMP_MIGRATION"
                exit 1
            fi
            
            rm "$TEMP_MIGRATION"
        else
            echo "✓ Already executed: $migration_name"
        fi
    fi
done

echo ""
echo "=== Final Status ==="
echo "All executed migrations:"
wp db query "SELECT migration, executed_at FROM ${TABLE_PREFIX}${MIGRATION_TABLE} ORDER BY executed_at;"

echo ""
echo "Current table structure:"
wp db query "DESCRIBE ${TABLE_PREFIX}referral_submissions;"

echo ""
echo "Current records:"
wp db query "SELECT submission_id, provider_name FROM ${TABLE_PREFIX}referral_submissions;"

echo "All migrations completed!"
SERVER_EOF

# Upload and run the migration script
scp temp-server-migrate-fixed.sh $REMOTE_USER:~/run-migrations-fixed.sh
ssh $REMOTE_USER "chmod +x ~/run-migrations-fixed.sh && ~/run-migrations-fixed.sh"

# Clean up
rm temp-server-migrate-fixed.sh

echo -e "${GREEN}Server migrations completed!${NC}"
