# Installation Guide

Complete guide to installing Pixly.

## Requirements

### Server Requirements

| Requirement | Minimum | Recommended |
|-------------|---------|-------------|
| PHP | 8.4+ | 8.4+ |
| MySQL | 8.0+ | 8.0+ |
| Web Server | Apache 2.4+ | Apache 2.4+ with mod_rewrite |

### PHP Extensions Required

- PDO
- PDO_MySQL
- GD or Imagick
- JSON
- mbstring
- fileinfo

### PHP Settings

```ini
upload_max_filesize = 10M
post_max_size = 12M
memory_limit = 256M
max_execution_time = 60
```

## Installation Methods

### Method 1: Installation Wizard (Recommended)

The easiest way to install Pixly.

1. **Download/Clone the repository**
   ```bash
   git clone https://github.com/yourusername/pixly.git
   ```

2. **Upload to your server**

   Upload all files to your server via FTP or file manager.

3. **Configure web server**

   Point your domain's document root to the `public_html` directory.

4. **Run the installer**

   Visit `http://yourdomain.com/install` in your browser.

5. **Follow the wizard**
   - Step 1: System requirements check
   - Step 2: Database configuration
   - Step 3: Admin account creation
   - Step 4: Complete installation

6. **Delete the install directory** (security)
   ```bash
   rm -rf app/Controllers/Install
   rm -rf app/Views/install
   ```

### Method 2: Manual Installation

For advanced users or automated deployments.

1. **Clone the repository**
   ```bash
   git clone https://github.com/yourusername/pixly.git
   cd pixly
   ```

2. **Create database**
   ```sql
   CREATE DATABASE fwp_gallery CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

3. **Configure database connection**
   ```bash
   cp app/Config/database.php.example app/Config/database.php
   ```

   Edit `app/Config/database.php`:
   ```php
   return [
       'driver' => 'mysql',
       'host' => 'localhost',
       'port' => 3306,
       'database' => 'fwp_gallery',
       'username' => 'your_username',
       'password' => 'your_password',
       'charset' => 'utf8mb4',
       'collation' => 'utf8mb4_unicode_ci',
       'prefix' => '',
       'options' => [
           PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
           PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
           PDO::ATTR_EMULATE_PREPARES => false,
       ],
   ];
   ```

4. **Import database schema**
   ```bash
   mysql -u username -p fwp_gallery < database/schema.sql
   ```

5. **Run migrations**
   ```bash
   for file in database/migrations/*.sql; do
       mysql -u username -p fwp_gallery < "$file"
   done
   ```

6. **Create admin user**
   ```sql
   INSERT INTO users (username, email, password_hash, role, is_active, email_verified_at)
   VALUES (
       'admin',
       'admin@example.com',
       '$argon2id$v=19$m=65536,t=4,p=1$...', -- Use PHP password_hash()
       'superadmin',
       1,
       NOW()
   );
   ```

7. **Set directory permissions**
   ```bash
   chmod -R 755 storage/
   chmod -R 755 public_html/uploads/
   chmod -R 755 public_html/cache/
   ```

8. **Configure application**

   Edit `app/Config/config.php`:
   ```php
   'app' => [
       'url' => 'https://yourdomain.com',
       'env' => 'production',
       'debug' => false,
   ],
   ```

## Web Server Configuration

### Apache

The `.htaccess` file is included in `public_html/`. Ensure mod_rewrite is enabled:

```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

Virtual host example:
```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    DocumentRoot /var/www/fwp/public_html

    <Directory /var/www/fwp/public_html>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/fwp_error.log
    CustomLog ${APACHE_LOG_DIR}/fwp_access.log combined
</VirtualHost>
```

### Nginx

```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /var/www/fwp/public_html;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

## Post-Installation

### 1. Configure Settings

Log in to Admin Panel > Settings and configure:

- Site name and description
- Logo and branding
- Feature toggles
- API keys (DeepSeek, Unsplash, Pexels)

### 2. Set Up Cron Jobs (Optional)

For automated tasks:
```bash
# Clear expired sessions daily
0 0 * * * php /path/to/fwp/app/Commands/cleanup.php sessions

# Process AI queue every 5 minutes
*/5 * * * * php /path/to/fwp/app/Commands/process-ai-queue.php
```

### 3. SSL Certificate

For production, always use HTTPS:
```bash
sudo certbot --apache -d yourdomain.com
```

Update `app/Config/config.php`:
```php
'session' => [
    'secure' => true,  // Enable for HTTPS
],
```

## Troubleshooting

### Common Issues

**500 Internal Server Error**
- Check `storage/logs/error.log`
- Verify PHP version: `php -v`
- Check file permissions

**Database Connection Failed**
- Verify credentials in `database.php`
- Check MySQL is running: `systemctl status mysql`
- Test connection: `mysql -u user -p database`

**Uploads Not Working**
- Check `upload_max_filesize` in php.ini
- Verify `public_html/uploads/` is writable
- Check disk space

**Blank Page**
- Enable debug mode temporarily
- Check PHP error log
- Verify all PHP extensions are installed

### Getting Help

- Check [GitHub Issues](https://github.com/yourusername/pixly/issues)
- Review error logs in `storage/logs/`
- Enable debug mode for detailed errors
