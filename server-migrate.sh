#!/bin/bash
# Server migration runner
# Usage: ./server-migrate.sh

REMOTE_USER="godaddy"
MIGRATION_DIR="migrations"

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

echo -e "${YELLOW}Running migrations on server...${NC}"

# Upload all migration files to server
echo -e "${YELLOW}Uploading migration files...${NC}"
ssh $REMOTE_USER "mkdir -p ~/migrations"
rsync -av $MIGRATION_DIR/ $REMOTE_USER:~/migrations/

# Create and upload migration runner script for server
cat > temp-server-migrate.sh << 'SERVER_EOF'
#!/bin/bash
MIGRATION_TABLE="schema_migrations"
MIGRATION_DIR="~/migrations"

cd ~/public_html

# Create migrations table if it doesn't exist
wp db query "CREATE TABLE IF NOT EXISTS $MIGRATION_TABLE (
    id INT AUTO_INCREMENT PRIMARY KEY,
    migration VARCHAR(255) NOT NULL UNIQUE,
    executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);"

# Get executed migrations
EXECUTED=$(wp db query "SELECT migration FROM $MIGRATION_TABLE" --skip-column-names 2>/dev/null)

# Run pending migrations
for migration_file in $MIGRATION_DIR/*.sql; do
    if [ -f "$migration_file" ]; then
        migration_name=$(basename "$migration_file" .sql)
        
        if ! echo "$EXECUTED" | grep -q "^$migration_name$"; then
            echo "Running migration: $migration_name"
            
            if wp db query < "$migration_file"; then
                wp db query "INSERT INTO $MIGRATION_TABLE (migration) VALUES ('$migration_name');"
                echo "✓ Migration completed: $migration_name"
            else
                echo "✗ Migration failed: $migration_name"
                exit 1
            fi
        else
            echo "✓ Already executed: $migration_name"
        fi
    fi
done

echo "All migrations completed!"
SERVER_EOF

# Upload and run the migration script
scp temp-server-migrate.sh $REMOTE_USER:~/run-migrations.sh
ssh $REMOTE_USER "chmod +x ~/run-migrations.sh && ~/run-migrations.sh"

# Clean up
rm temp-server-migrate.sh

echo -e "${GREEN}Server migrations completed!${NC}"
