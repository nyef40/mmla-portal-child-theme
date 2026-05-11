#!/bin/bash

echo "🧪 COMPREHENSIVE PORTAL END-TO-END TESTING"
echo "=========================================="

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Test URLs
BASE_URL="http://localhost:8080"
PORTAL_URL="$BASE_URL/portal/"
LOGIN_URL="$BASE_URL/portal-login/"
DASHBOARD_URL="$BASE_URL/dashboard/"
CONTACT_URL="$BASE_URL/contact/"
RESOURCES_URL="$BASE_URL/portal-resources/"

echo -e "${BLUE}1. TESTING PORTAL ACCESSIBILITY${NC}"
echo "================================"

# Test main portal page
echo -n "Testing portal home page... "
if curl -s -o /dev/null -w "%{http_code}" "$PORTAL_URL" | grep -q "200"; then
    echo -e "${GREEN}✅ PASS${NC}"
else
    echo -e "${RED}❌ FAIL${NC}"
fi

# Test login page
echo -n "Testing login page... "
if curl -s -o /dev/null -w "%{http_code}" "$LOGIN_URL" | grep -q "200"; then
    echo -e "${GREEN}✅ PASS${NC}"
else
    echo -e "${RED}❌ FAIL${NC}"
fi

# Test dashboard page (should redirect if not logged in)
echo -n "Testing dashboard page... "
if curl -s -o /dev/null -w "%{http_code}" "$DASHBOARD_URL" | grep -q -E "(200|302)"; then
    echo -e "${GREEN}✅ PASS${NC}"
else
    echo -e "${RED}❌ FAIL${NC}"
fi

echo ""
echo -e "${BLUE}2. TESTING DATABASE CONNECTIVITY${NC}"
echo "================================="

# Test database connection
echo -n "Testing database connection... "
if docker exec mmla-portal-db-1 mysql -u wordpress -pwordpress -e "SELECT 1;" wordpress > /dev/null 2>&1; then
    echo -e "${GREEN}✅ PASS${NC}"
else
    echo -e "${RED}❌ FAIL${NC}"
fi

# Test user table
echo -n "Testing user table... "
USER_COUNT=$(docker exec mmla-portal-db-1 mysql -u wordpress -pwordpress -se "SELECT COUNT(*) FROM lqbk_users;" wordpress 2>/dev/null)
if [ "$USER_COUNT" -gt 0 ]; then
    echo -e "${GREEN}✅ PASS ($USER_COUNT users)${NC}"
else
    echo -e "${RED}❌ FAIL${NC}"
fi

# Test contact submissions table
echo -n "Testing contact submissions table... "
if docker exec mmla-portal-db-1 mysql -u wordpress -pwordpress -e "DESCRIBE lqbk_contact_submissions;" wordpress > /dev/null 2>&1; then
    CONTACT_COUNT=$(docker exec mmla-portal-db-1 mysql -u wordpress -pwordpress -se "SELECT COUNT(*) FROM lqbk_contact_submissions;" wordpress 2>/dev/null)
    echo -e "${GREEN}✅ PASS ($CONTACT_COUNT submissions)${NC}"
else
    echo -e "${YELLOW}⚠️  Table doesn't exist yet${NC}"
fi

echo ""
echo -e "${BLUE}3. TESTING WORDPRESS FUNCTIONALITY${NC}"
echo "=================================="

# Test WordPress CLI
echo -n "Testing WordPress CLI... "
if docker exec mmla-portal-wordpress-1 wp --version --allow-root > /dev/null 2>&1; then
    echo -e "${GREEN}✅ PASS${NC}"
else
    echo -e "${RED}❌ FAIL${NC}"
fi

# Test cache functionality
echo -n "Testing cache flush... "
if docker exec mmla-portal-wordpress-1 wp cache flush --allow-root > /dev/null 2>&1; then
    echo -e "${GREEN}✅ PASS${NC}"
else
    echo -e "${RED}❌ FAIL${NC}"
fi

# Test theme files
echo -n "Testing theme files... "
THEME_FILES=$(docker exec mmla-portal-wordpress-1 ls /var/www/html/wp-content/themes/blocksy-child/ | wc -l)
if [ "$THEME_FILES" -gt 5 ]; then
    echo -e "${GREEN}✅ PASS ($THEME_FILES files)${NC}"
else
    echo -e "${RED}❌ FAIL${NC}"
fi

echo ""
echo -e "${BLUE}4. MANUAL TESTING CHECKLIST${NC}"
echo "============================"

echo -e "${YELLOW}Please test the following manually:${NC}"
echo ""

echo "🔐 LOGIN FLOW:"
echo "1. Go to: $LOGIN_URL"
echo "2. Login with: mmla2024 / tiger2025"
echo "3. Should redirect to: $DASHBOARD_URL"
echo "4. Should show welcome message with user name"
echo ""

echo "📊 DASHBOARD:"
echo "1. Should display 4 stat cards"
echo "2. Should show quick action buttons"
echo "3. Should display recent activity"
echo "4. All navigation links should work"
echo ""

echo "📞 CONTACT FORM:"
echo "1. Go to: $CONTACT_URL"
echo "2. Fill out the form with valid data"
echo "3. Subject dropdown should be tall enough"
echo "4. Phone should format as: 123-456-7890"
echo "5. Should show success message after submission"
echo "6. Check database for new entry"
echo ""

echo "🔄 LOGOUT FLOW:"
echo "1. Click logout from any portal page"
echo "2. Should return to portal home"
echo "3. Should show login/register cards"
echo "4. Navigation should update correctly"
echo ""

echo "📱 RESPONSIVE DESIGN:"
echo "1. Test on mobile viewport"
echo "2. All cards should stack properly"
echo "3. Navigation should remain functional"
echo ""

echo ""
echo -e "${BLUE}5. DATABASE VALIDATION COMMANDS${NC}"
echo "==============================="

echo "Run these commands to validate data:"
echo ""
echo -e "${YELLOW}# Check user login:${NC}"
echo "docker exec -it mmla-portal-db-1 mysql -u wordpress -pwordpress wordpress"
echo "SELECT user_login, user_email, display_name FROM lqbk_users WHERE user_login='mmla2024';"
echo ""

echo -e "${YELLOW}# Check contact submissions:${NC}"
echo "SELECT * FROM lqbk_contact_submissions ORDER BY id DESC LIMIT 5;"
echo ""

echo -e "${YELLOW}# Check user meta:${NC}"
echo "SELECT meta_key, meta_value FROM lqbk_usermeta WHERE user_id=(SELECT ID FROM lqbk_users WHERE user_login='mmla2024') AND meta_key IN ('first_name', 'last_name', 'practice_name', 'last_login');"
echo ""

echo ""
echo -e "${BLUE}6. TROUBLESHOOTING${NC}"
echo "=================="

echo "If login fails with 'Security check failed':"
echo "1. Clear browser cache and cookies"
echo "2. Try in incognito/private mode"
echo "3. Check browser console for JavaScript errors"
echo ""

echo "If emails are not being sent:"
echo "1. Check WordPress debug log: docker exec mmla-portal-wordpress-1 tail -f /var/www/html/wp-content/debug.log"
echo "2. Verify SMTP settings in wp-config.php"
echo "3. Check spam folder"
echo ""

echo "If dashboard doesn't load:"
echo "1. Verify user is logged in: check cookies"
echo "2. Check page template assignment in database"
echo "3. Clear WordPress cache"
echo ""

echo ""
echo -e "${GREEN}✅ TESTING SCRIPT COMPLETE${NC}"
echo "=========================="
echo "Please run through the manual testing checklist above."
echo "Report any issues found during testing."
