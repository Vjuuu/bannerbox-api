# CodeIgniter 3 API Backend - Apache Local Setup Guide

## Prerequisites

Before starting, ensure you have the following installed on your local system:

- **XAMPP/WAMP/LAMP** (Apache + MySQL + PHP)
- **PHP 7.0 or higher** (recommended: PHP 7.4+)
- **MySQL 5.6 or higher**
- **Apache with mod_rewrite enabled**
- **Git** (optional, for version control)

## Step 1: Download and Install XAMPP

1. **Download XAMPP** from [https://www.apachefriends.org/](https://www.apachefriends.org/)
2. **Install XAMPP** following the installer instructions
3. **Start Apache and MySQL** services from XAMPP Control Panel

## Step 2: Download CodeIgniter 3 System Files

The project requires CodeIgniter 3 system files which are not included in this repository.

### Option A: Download from Official Source
```bash
# Navigate to your project directory
cd /path/to/your/project

# Download CodeIgniter 3.1.13 (latest stable version)
wget https://github.com/bcit-ci/CodeIgniter/archive/3.1.13.zip

# Extract the zip file
unzip 3.1.13.zip

# Copy the system folder to your project
cp -r CodeIgniter-3.1.13/system ./

# Remove downloaded files (optional)
rm -rf CodeIgniter-3.1.13*
```

### Option B: Manual Download
1. Go to [CodeIgniter 3 Releases](https://github.com/bcit-ci/CodeIgniter/releases)
2. Download the latest 3.x version (3.1.13)
3. Extract the zip file
4. Copy the `system` folder to your project root directory

## Step 3: Project Setup

### 1. Copy Project Files
```bash
# If using Git
git clone <your-repository-url>
cd codeigniter-api-backend

# Or manually copy all project files to your Apache document root
# Default XAMPP path: C:\xampp\htdocs\codeigniter-api-backend (Windows)
# Default LAMP path: /var/www/html/codeigniter-api-backend (Linux)
```

### 2. Set Proper Permissions (Linux/Mac)
```bash
# Make sure Apache can read/write necessary directories
sudo chown -R www-data:www-data /var/www/html/codeigniter-api-backend
sudo chmod -R 755 /var/www/html/codeigniter-api-backend
sudo chmod -R 777 /var/www/html/codeigniter-api-backend/application/logs
sudo chmod -R 777 /var/www/html/codeigniter-api-backend/application/cache
```

### 3. Create Required Directories
```bash
# Create logs and cache directories if they don't exist
mkdir -p application/logs
mkdir -p application/cache
```

## Step 4: Apache Configuration

### 1. Enable mod_rewrite Module

#### For XAMPP (Windows):
1. Open `C:\xampp\apache\conf\httpd.conf`
2. Find the line `#LoadModule rewrite_module modules/mod_rewrite.so`
3. Remove the `#` to uncomment it: `LoadModule rewrite_module modules/mod_rewrite.so`
4. Restart Apache from XAMPP Control Panel

#### For LAMP (Linux):
```bash
# Enable mod_rewrite
sudo a2enmod rewrite

# Restart Apache
sudo systemctl restart apache2
```

### 2. Configure Virtual Host (Recommended)

#### Create Virtual Host Configuration:

**For XAMPP (Windows):**
1. Open `C:\xampp\apache\conf\extra\httpd-vhosts.conf`
2. Add the following configuration:

```apache
<VirtualHost *:80>
    DocumentRoot "C:/xampp/htdocs/codeigniter-api-backend"
    ServerName codeigniter-api.local
    ServerAlias www.codeigniter-api.local
    
    <Directory "C:/xampp/htdocs/codeigniter-api-backend">
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog "logs/codeigniter-api-error.log"
    CustomLog "logs/codeigniter-api-access.log" common
</VirtualHost>
```

**For LAMP (Linux):**
1. Create a new virtual host file:
```bash
sudo nano /etc/apache2/sites-available/codeigniter-api.conf
```

2. Add the following configuration:
```apache
<VirtualHost *:80>
    DocumentRoot /var/www/html/codeigniter-api-backend
    ServerName codeigniter-api.local
    ServerAlias www.codeigniter-api.local
    
    <Directory /var/www/html/codeigniter-api-backend>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/codeigniter-api-error.log
    CustomLog ${APACHE_LOG_DIR}/codeigniter-api-access.log combined
</VirtualHost>
```

3. Enable the site:
```bash
sudo a2ensite codeigniter-api.conf
sudo systemctl reload apache2
```

### 3. Update Hosts File

Add the following line to your hosts file:

**Windows:** `C:\Windows\System32\drivers\etc\hosts`
**Linux/Mac:** `/etc/hosts`

```
127.0.0.1    codeigniter-api.local
```

## Step 5: Database Setup

### 1. Create Database
1. Open **phpMyAdmin** (usually at `http://localhost/phpmyadmin`)
2. Click **"New"** to create a new database
3. Name it `codeigniter_api`
4. Set collation to `utf8_general_ci`
5. Click **"Create"**

### 2. Import Database Schema
1. Select the `codeigniter_api` database
2. Click **"Import"** tab
3. Choose the `database.sql` file from your project
4. Click **"Go"** to import

### Alternative: Command Line Import
```bash
# Navigate to project directory
cd /path/to/codeigniter-api-backend

# Import database
mysql -u root -p codeigniter_api < database.sql
```

## Step 6: Configuration

### 1. Update Database Configuration
Edit `application/config/database.php`:

```php
$db['default'] = array(
    'dsn'    => '',
    'hostname' => 'localhost',
    'username' => 'root',           // Your MySQL username
    'password' => '',               // Your MySQL password (empty for XAMPP default)
    'database' => 'codeigniter_api',
    'dbdriver' => 'mysqli',
    'dbprefix' => '',
    'pconnect' => FALSE,
    'db_debug' => (ENVIRONMENT !== 'production'),
    'cache_on' => FALSE,
    'cachedir' => '',
    'char_set' => 'utf8',
    'dbcollat' => 'utf8_general_ci',
    'swap_pre' => '',
    'encrypt' => FALSE,
    'compress' => FALSE,
    'stricton' => FALSE,
    'failover' => array(),
    'save_queries' => TRUE
);
```

### 2. Update Base URL
Edit `application/config/config.php`:

```php
// If using virtual host
$config['base_url'] = 'http://codeigniter-api.local/';

// If using direct path
$config['base_url'] = 'http://localhost/codeigniter-api-backend/';
```

### 3. Update Security Keys
Edit `application/config/config.php`:

```php
// Generate a secure 32-character encryption key
$config['encryption_key'] = 'your-32-character-secret-key-here123456';

// Generate a secure JWT secret key
$config['jwt_secret_key'] = 'your-super-secret-jwt-key-here-make-it-long-and-complex-2024';

// Set your API key
$config['api_key'] = 'your-api-key-12345';
```

## Step 7: Test the Installation

### 1. Basic Test
Open your browser and navigate to:
- Virtual Host: `http://codeigniter-api.local/`
- Direct Path: `http://localhost/codeigniter-api-backend/`

You should see a CodeIgniter welcome page or API response.

### 2. Test API Endpoints

#### Test with cURL:

**Get Categories:**
```bash
curl -X GET "http://codeigniter-api.local/api/categories" \
  -H "X-API-KEY: your-api-key-12345"
```

**Login Test:**
```bash
curl -X POST "http://codeigniter-api.local/api/auth/login" \
  -H "Content-Type: application/json" \
  -H "X-API-KEY: your-api-key-12345" \
  -d '{
    "email": "admin@example.com",
    "password": "password"
  }'
```

**Get Posters:**
```bash
curl -X GET "http://codeigniter-api.local/api/posters" \
  -H "X-API-KEY: your-api-key-12345"
```

### 3. Test with Postman

1. **Import the following collection:**
   - Base URL: `http://codeigniter-api.local`
   - Add header: `X-API-KEY: your-api-key-12345`
   - Test all endpoints listed in the README.md

## Step 8: Development Tools (Optional)

### 1. Install PHP Development Tools
```bash
# Install Composer (for future dependencies)
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install PHP CodeSniffer (for code quality)
composer global require "squizlabs/php_codesniffer=*"
```

### 2. IDE Configuration
- **VS Code:** Install PHP extensions
- **PHPStorm:** Configure PHP interpreter and CodeIgniter support

## Troubleshooting

### Common Issues:

#### 1. 404 Error on API Routes
- **Solution:** Ensure mod_rewrite is enabled and .htaccess is working
- **Test:** Create a simple .htaccess test file

#### 2. Database Connection Error
- **Solution:** Check database credentials in `application/config/database.php`
- **Test:** Verify MySQL service is running

#### 3. Permission Denied Errors
- **Solution:** Set proper file permissions (Linux/Mac)
```bash
sudo chmod -R 755 /var/www/html/codeigniter-api-backend
sudo chmod -R 777 application/logs
sudo chmod -R 777 application/cache
```

#### 4. CORS Issues
- **Solution:** The .htaccess file includes CORS headers, but you may need to adjust them for your specific needs

#### 5. JWT Token Issues
- **Solution:** Ensure the JWT secret key is properly set in config.php

### Debug Mode
To enable debug mode, edit `index.php`:
```php
define('ENVIRONMENT', 'development');
```

## Security Considerations

### For Production:
1. **Change default passwords** in the database
2. **Update all secret keys** in configuration
3. **Set ENVIRONMENT to 'production'** in index.php
4. **Disable error reporting** in production
5. **Use HTTPS** for all API communications
6. **Implement rate limiting**
7. **Regular security updates**

## Default Test Credentials

After importing the database, you can use these test accounts:

- **Admin:** admin@example.com / password
- **Pro User:** john@example.com / password  
- **Basic User:** jane@example.com / password

## Next Steps

1. **Test all API endpoints** using the examples in README.md
2. **Customize the database schema** as needed for your project
3. **Add additional API endpoints** following the existing patterns
4. **Implement frontend integration** using the provided API
5. **Set up proper logging and monitoring**

## Support

If you encounter any issues during setup:
1. Check the Apache error logs
2. Enable CodeIgniter error logging
3. Verify all configuration files
4. Test database connectivity
5. Ensure all required PHP extensions are installed

The API should now be fully functional and ready for development!