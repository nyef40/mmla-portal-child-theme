#!/bin/bash
# Database migration runner
# Usage: ./migrate.sh [up|down|status|create]

MIGRATION_DIR="migrations"
MIGRATION_TABLE="schema_migrations"

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

# Ensure migrations directory exists
mkdir -p $MIGRATION_DIR

case $1 in
    "create")
        if [ -z "$2" ]; then
            echo "Usage: $0 create migration_name"
            exit 1
        fi
        
        TIMESTAMP=$(date +%Y%m%d_%H%M%S)
        MIGRATION_NAME="$2"
        FILENAME="${MIGRATION_DIR}/${TIMESTAMP}_${MIGRATION_NAME}.sql"
        
        cat > $FILENAME << 'MIGRATION_EOF'
-- Migration: MIGRATION_NAME_PLACEHOLDER
-- Created: TIMESTAMP_PLACEHOLDER

-- Add your SQL statements here
-- Example:
-- ALTER TABLE lqbk_referral_submissions ADD COLUMN new_field VARCHAR(255);

MIGRATION_EOF
        
        sed -i '' "s/MIGRATION_NAME_PLACEHOLDER/$MIGRATION_NAME/g" $FILENAME
        sed -i '' "s/TIMESTAMP_PLACEHOLDER/$(date)/g" $FILENAME
        
        echo -e "${GREEN}Created migration: $FILENAME${NC}"
        echo "Edit the file to add your SQL statements, then commit to Git."
        ;;
        
    "up")
        echo -e "${YELLOW}Running pending migrations...${NC}"
        
        # Create migrations table if it doesn't exist
        docker exec -it mmla-portal-db-1 mysql -u root -proot wordpress -e "
        CREATE TABLE IF NOT EXISTS $MIGRATION_TABLE (
            id INT AUTO_INCREMENT PRIMARY KEY,
            migration VARCHAR(255) NOT NULL UNIQUE,
            executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );"
        
        # Get list of executed migrations
        EXECUTED=$(docker exec -it mmla-portal-db-1 mysql -u root -proot wordpress -se "SELECT migration FROM $MIGRATION_TABLE" 2>/dev/null | tr -d '\r')
        
        # Run pending migrations
        for migration_file in $MIGRATION_DIR/*.sql; do
            if [ -f "$migration_file" ]; then
                migration_name=$(basename "$migration_file" .sql)
                
                if ! echo "$EXECUTED" | grep -q "^$migration_name$"; then
                    echo -e "${YELLOW}Running migration: $migration_name${NC}"
                    
                    if docker exec -i mmla-portal-db-1 mysql -u root -proot wordpress < "$migration_file"; then
                        # Record successful migration
                        docker exec -it mmla-portal-db-1 mysql -u root -proot wordpress -e "INSERT INTO $MIGRATION_TABLE (migration) VALUES ('$migration_name');"
                        echo -e "${GREEN}✓ Migration completed: $migration_name${NC}"
                    else
                        echo -e "${RED}✗ Migration failed: $migration_name${NC}"
                        exit 1
                    fi
                else
                    echo -e "${GREEN}✓ Already executed: $migration_name${NC}"
                fi
            fi
        done
        ;;
        
    "status")
        echo -e "${YELLOW}Migration Status:${NC}"
        
        # Check if migrations table exists
        if ! docker exec -it mmla-portal-db-1 mysql -u root -proot wordpress -e "DESCRIBE $MIGRATION_TABLE" >/dev/null 2>&1; then
            echo "No migrations have been run yet."
            exit 0
        fi
        
        # Get executed migrations
        EXECUTED=$(docker exec -it mmla-portal-db-1 mysql -u root -proot wordpress -se "SELECT migration FROM $MIGRATION_TABLE ORDER BY executed_at" 2>/dev/null | tr -d '\r')
        
        echo "Executed migrations:"
        if [ -n "$EXECUTED" ]; then
            echo "$EXECUTED" | while read migration; do
                echo -e "${GREEN}✓ $migration${NC}"
            done
        else
            echo "None"
        fi
        
        echo ""
        echo "Pending migrations:"
        PENDING=false
        for migration_file in $MIGRATION_DIR/*.sql; do
            if [ -f "$migration_file" ]; then
                migration_name=$(basename "$migration_file" .sql)
                if ! echo "$EXECUTED" | grep -q "^$migration_name$"; then
                    echo -e "${YELLOW}⏳ $migration_name${NC}"
                    PENDING=true
                fi
            fi
        done
        
        if [ "$PENDING" = false ]; then
            echo "None"
        fi
        ;;
        
    *)
        echo "Usage: $0 {create|up|status}"
        echo "  create <name> - Create a new migration file"
        echo "  up            - Run pending migrations"
        echo "  status        - Show migration status"
        ;;
esac
