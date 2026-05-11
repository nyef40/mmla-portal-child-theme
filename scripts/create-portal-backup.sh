#!/bin/bash

echo "📦 CREATING PORTAL BACKUP"

# Create backup directory with timestamp
BACKUP_DIR="backups/portal-backup-$(date +%Y%m%d-%H%M%S)"
mkdir -p "$BACKUP_DIR"

echo "1️⃣ Backing up WordPress files..."
docker exec mmla-portal-wordpress-1 tar -czf /tmp/wordpress-files.tar.gz -C /var/www/html wp-content/themes/blocksy-child
docker cp mmla-portal-wordpress-1:/tmp/wordpress-files.tar.gz "$BACKUP_DIR/"

echo "2️⃣ Backing up database..."
docker exec mmla-portal-db-1 mysqldump -u wordpress -pwordpress wordpress > "$BACKUP_DIR/database.sql"

echo "3️⃣ Backing up Docker configuration..."
cp docker-compose.yml "$BACKUP_DIR/"
cp -r config "$BACKUP_DIR/" 2>/dev/null || true

echo "4️⃣ Creating restore script..."
cat > "$BACKUP_DIR/restore.sh" << 'EOF'
#!/bin/bash
echo "🔄 RESTORING PORTAL FROM BACKUP"

# Stop containers
docker compose down

# Restore database
echo "Restoring database..."
docker compose up -d db
sleep 10
docker exec -i mmla-portal-db-1 mysql -u wordpress -pwordpress wordpress < database.sql

# Restore WordPress files
echo "Restoring WordPress files..."
docker compose up -d wordpress
sleep 10
docker cp wordpress-files.tar.gz mmla-portal-wordpress-1:/tmp/
docker exec mmla-portal-wordpress-1 tar -xzf /tmp/wordpress-files.tar.gz -C /var/www/html

# Set permissions
docker exec mmla-portal-wordpress-1 chown -R www-data:www-data /var/www/html/wp-content

# Restart containers
docker compose restart

echo "✅ RESTORE COMPLETE!"
EOF

chmod +x "$BACKUP_DIR/restore.sh"

echo "5️⃣ Creating backup info..."
cat > "$BACKUP_DIR/backup-info.txt" << EOF
Portal Backup Information
========================
Created: $(date)
Portal Version: Working State v1.0
Database: Included
WordPress Files: Included
Docker Config: Included

Features Backed Up:
- Portal navigation system
- Login/logout functionality
- Dashboard with stats
- Contact form with validation
- Resources page
- User authentication
- All portal pages and templates

To Restore:
1. Navigate to this backup directory
2. Run: ./restore.sh
3. Wait for containers to start
4. Test portal functionality

URLs to Test After Restore:
- Main site: http://localhost:8080/
- Portal home: http://localhost:8080/portal/
- Portal login: http://localhost:8080/portal-login/
- Dashboard: http://localhost:8080/dashboard/
- Contact: http://localhost:8080/contact/
- Resources: http://localhost:8080/portal-resources/
EOF

echo "✅ BACKUP CREATED: $BACKUP_DIR"
echo ""
echo "📋 BACKUP CONTENTS:"
ls -la "$BACKUP_DIR"
echo ""
echo "🔄 TO RESTORE: cd $BACKUP_DIR && ./restore.sh"
