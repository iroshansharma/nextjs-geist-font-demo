# Installation Guide for CRM System

## Prerequisites
- Web server with PHP 7.4 or higher
- MySQL 5.7 or higher
- Composer (optional, if using PHP dependencies)

## Steps

1. Clone or download the CRM system source code to your web server directory.

2. Import the database schema:
   - Use a MySQL client or command line:
     ```
     mysql -u your_username -p your_database < crm-system/database/schema.sql
     ```

3. Configure database connection:
   - Edit the configuration file in `src/backend/config.php` (to be created) with your database credentials.

4. Set up web server:
   - Ensure the document root points to `crm-system/src/frontend` or configure routing accordingly.

5. Open the CRM system in your browser:
   - Access the URL where the system is hosted.

6. Login using demo user accounts from `demo_users.md`.

## Troubleshooting
- Ensure PHP extensions for MySQL are enabled.
- Check file permissions for web server access.
