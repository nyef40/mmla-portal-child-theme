-- Export data from local lqbk_portal_users
-- Run this on LOCAL database: wordpress

USE wordpress;

-- Export to CSV (run in MySQL client or via command line)
-- mysql -u root -ppassword wordpress -e "SELECT * FROM lqbk_portal_users" > /tmp/portal_users_export.csv

SELECT 
    id,
    wp_user_id,
    username,
    email,
    first_name,
    last_name,
    phone,
    practice,
    address,
    city,
    state,
    zip,
    email_verified,
    specialty,
    license_number,
    role,
    created_at,
    updated_at,
    validation_token
FROM lqbk_portal_users
ORDER BY id;
