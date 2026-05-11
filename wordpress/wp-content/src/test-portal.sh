#!/bin/bash
# test-portal.sh

echo "=== Testing Portal Setup ==="
echo ""

echo "1. Checking manifest..."
MANIFEST="../themes/blocksy-child/portal/dist/manifest.json"
if [ -f "$MANIFEST" ]; then
    echo "Manifest exists:"
    cat "$MANIFEST"
    
    # Check for correct keys
    if grep -q '"portal.css"' "$MANIFEST" && grep -q '"portal.js"' "$MANIFEST"; then
        echo "✓ Manifest has correct keys"
    else
        echo "✗ Manifest has wrong keys"
        echo "Fixing manifest..."
        cat > "$MANIFEST" << 'EOF'
{
  "portal.css": "portal.4f8a898a.css",
  "portal.js": "portal.f6124f0d.js"
}
EOF
        echo "Fixed manifest:"
        cat "$MANIFEST"
    fi
else
    echo "✗ Manifest not found"
fi

echo ""
echo "2. Checking files exist..."
CSS_FILE=$(grep -o '"portal.css": "[^"]*' "$MANIFEST" | cut -d'"' -f4)
JS_FILE=$(grep -o '"portal.js": "[^"]*' "$MANIFEST" | cut -d'"' -f4)

if [ -f "../themes/blocksy-child/portal/dist/$CSS_FILE" ]; then
    echo "✓ CSS file exists: $CSS_FILE"
else
    echo "✗ CSS file not found: $CSS_FILE"
fi

if [ -f "../themes/blocksy-child/portal/dist/$JS_FILE" ]; then
    echo "✓ JS file exists: $JS_FILE"
else
    echo "✗ JS file not found: $JS_FILE"
fi

echo ""
echo "3. Testing WordPress..."
docker exec mmla-portal-wordpress-1 wp cache flush --allow-root
echo "Cache flushed"

echo ""
echo "4. Testing REST API registration..."
# Create a test endpoint
docker exec mmla-portal-wordpress-1 bash -c "cat > /tmp/test-rest.php << 'EOF'
<?php
require_once('/var/www/html/wp-load.php');

// Register a test endpoint
add_action('rest_api_init', function() {
    register_rest_route('portal/v1', '/test', [
        'methods' => 'GET',
        'callback' => function() {
            return ['success' => true, 'message' => 'Portal API working'];
        },
        'permission_callback' => '__return_true'
    ]);
});

// Check if registered
\$routes = rest_get_server()->get_routes();
foreach (\$routes as \$route => \$handlers) {
    if (strpos(\$route, '/portal/') !== false) {
        echo \"Found: \$route\\n\";
    }
}
EOF"

docker exec mmla-portal-wordpress-1 wp eval-file /tmp/test-rest.php --allow-root

echo ""
echo "5. Test URL: http://localhost:8080/portal/"
echo ""
echo "6. Check in browser:"
echo "   - Open DevTools (F12)"
echo "   - Check Console for errors"
echo "   - Check Network tab for portal.js/portal.css (should be 200 OK)"
echo "   - Look for 'React Portal Script Loaded' message"