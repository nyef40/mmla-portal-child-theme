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
