# Pharmax Database Setup Guide

## Overview

This guide explains how to set up the Pharmax database using the provided SQL dump file (`pharmax.sql`) and configure your local development environment.

## Prerequisites

- **MySQL or MariaDB**: Version 10.4.32 or higher (the provided dump is MariaDB 10.4.32)
- **PHP CLI**: Version 8.1+ with MySQL/MySQLi extension
- **Symfony CLI** (optional but recommended)

### Installation

#### On Windows

1. **Download and Install MariaDB**:
   - Visit: https://mariadb.com/downloads/
   - Select MariaDB 10.4.x or 11.x
   - During installation, remember your root password (default: empty)

2. **Install MariaDB Client Tools**:
   - Required to run the `mysql` command
   - Usually included with MariaDB Server installation

3. **Verify Installation**:
   ```powershell
   mysql --version
   mariadb --version
   ```

#### On macOS

```bash
brew install mysql
# or
brew install mariadb
```

#### On Linux (Ubuntu/Debian)

```bash
sudo apt-get install mysql-server
# or
sudo apt-get install mariadb-server
```

## Quick Setup

### Option 1: Using PowerShell Script (Recommended for Windows)

```powershell
cd C:\Users\Asus\Documents\pharmax
.\setup-database.ps1
```

Or with custom parameters:

```powershell
.\setup-database.ps1 -DbHost 127.0.0.1 -DbPassword "your_password" -SqlDumpFile pharmax.sql
```

### Option 2: Using Bash Script (Recommended for Linux/macOS)

```bash
cd /path/to/pharmax
chmod +x setup-database.sh
./setup-database.sh pharmax.sql
```

### Option 3: Manual Import

```bash
# Connect to MySQL/MariaDB
mysql -h 127.0.0.1 -P 3306 -u root

# Create database
CREATE DATABASE IF NOT EXISTS pharmax;
USE pharmax;

# Import the SQL dump
SOURCE pharmax.sql;

# Verify import
SHOW TABLES;
SHOW COLUMNS FROM user;
```

## Configure .env File

1. **Copy example configuration**:
   ```bash
   cp .env.local.example .env.local
   ```

2. **Update database connection in .env**:
   ```env
   DATABASE_URL="mysql://root:@127.0.0.1:3306/pharmax?serverVersion=10.4.32-MariaDB&charset=utf8mb4"
   ```

3. **For MariaDB with password**:
   ```env
   DATABASE_URL="mysql://root:your_password@127.0.0.1:3306/pharmax?serverVersion=10.4.32-MariaDB&charset=utf8mb4"
   ```

4. **Add API keys** (if needed for development):
   - Stripe keys
   - Gemini API key
   - Google OAuth credentials
   - etc.

## Database Structure

The provided SQL dump includes the following tables:

| Table | Purpose |
|-------|---------|
| `user` | User accounts with authentication, 2FA, face recognition data |
| `article` | Blog articles |
| `commentaire` | Comments on articles and products |
| `archive_de_commentaire` | Archived comments |
| `produit` | Product catalog |
| `categorie` | Product categories |
| `commandes` | Customer orders |
| `ligne_commandes` | Order line items |
| `payments` | Payment records (Stripe integration) |
| `livraisons` | Shipping information |
| `reclamation` | Customer complaints/reclamations |
| `reponse` | Responses to reclamations |
| `notification` | User notifications |
| `reset_password_request` | Password reset tokens |
| `messenger_messages` | Async message queue |
| `doctrine_migration_versions` | Doctrine migration tracking |

## Test Users

The database comes with pre-populated test users:

```
1. amal.aguir88@gmail.com (ROLE_USER)
   - Password: Not set - use password reset
   
2. lola.aguir@gmail.com (ROLE_SUPER_ADMIN, ROLE_USER)
   - Password: Not set - use password reset
   - Google ID: 103100691489100385015
   - Avatar: Google profile picture
   
3. test@gmail.com (ROLE_USER)
   - Password: Hash available in database
   
4. mqsdf@gmail.com (ROLE_USER)
   - Has face recognition data registered
   - Data: 128-dimensional face descriptor array
```

## Important Columns

The `user` table includes these important fields related to security and authentication:

```sql
-- Authentication
email VARCHAR(180) UNIQUE
password VARCHAR(255)
roles JSON - stored as ["ROLE_USER"] or ["ROLE_SUPER_ADMIN"]

-- 2FA (Two-Factor Authentication)
google_authenticator_secret VARCHAR(255) - TOTP secret key
google_authenticator_secret_pending VARCHAR(255) - temporary secret during setup
is_2fa_setup_in_progress TINYINT(1) - flag for ongoing 2FA setup

-- Face Recognition
data_face_api LONGTEXT - 128-dimensional face descriptor array

-- Google OAuth
google_id VARCHAR(255) - Google account ID
avatar VARCHAR(255) - User profile photo

-- Account Status
status VARCHAR(16) - BLOCKED or UNBLOCKED
created_at DATETIME
updated_at DATETIME
```

## Verify Setup

After import, verify everything is working:

```bash
# Check database connection
php bin/console doctrine:database:create --if-not-exists

# Verify schema
php bin/console doctrine:schema:validate

# Check migrations
php bin/console doctrine:migrations:status

# Clear cache
php bin/console cache:clear
```

## Next Steps

1. **Configure API Keys** (in .env.local):
   - Stripe Public/Secret keys
   - Gemini API key
   - Google OAuth credentials
   - Mercure JWT secret

2. **Start Development Server**:
   ```bash
   php bin/console server:start
   # or
   symfony serve
   ```

3. **Generate Test JWT Keys** (if not already present):
   ```bash
   php bin/console make:jwt-keys
   ```

4. **Run Database Migrations**:
   ```bash
   php bin/console doctrine:migrations:migrate
   ```

5. **Access the Application**:
   - Local: http://localhost:8000
   - Admin panel: http://localhost:8000/admin

## Troubleshooting

### "Connection refused" Error

**Problem**: `SQLSTATE[HY000] [2002] Aucune connexion n'a pu être établie`

**Solution**:
1. Verify MySQL/MariaDB is running:
   ```bash
   # Windows - Check Services
   Get-Service MariaDB
   
   # macOS
   brew services list
   ```

2. Check database credentials in .env
3. Verify database exists: `SHOW DATABASES;`
4. Test connection:
   ```bash
   mysql -h 127.0.0.1 -u root -p pharmax -e "SELECT 1;"
   ```

### "No such file or directory" (pharmax.sql)

**Solution**: Make sure you're in the project root directory:
```bash
cd c:\Users\Asus\Documents\pharmax
ls pharmax.sql  # Verify file exists
```

### MySQLi Extension Not Found

**Solution**: Enable MySQLi in PHP:
```bash
# Find PHP config
php -i | grep "Loaded Configuration File"

# Edit php.ini and uncomment:
extension=mysqli
extension=pdo_mysql
```

## Advanced Configuration

### Using Docker (Optional)

If you prefer Docker over local MySQL installation:

```yaml
# In compose.yaml
mariadb:
  image: mariadb:10.4
  environment:
    MYSQL_DATABASE: pharmax
    MYSQL_ROOT_PASSWORD: root_password
  ports:
    - "3306:3306"
  volumes:
    - ./pharmax.sql:/docker-entrypoint-initdb.d/pharmax.sql
```

Then:
```bash
docker-compose up -d
```

### Connection Pooling

For production, consider using [PDO connection pooling](https://www.php.net/manual/en/pdo.mysql.persistent.html):

```env
DATABASE_URL="mysql://root:pass@127.0.0.1:3306/pharmax?serverVersion=10.4.32-MariaDB&charset=utf8mb4&connect_timeout=10"
```

## Support

For issues with:
- **Symfony/Doctrine**: Check `config/packages/doctrine.yaml`
- **Security/Authentication**: See `src/Security/` directory
- **2FA Setup**: Check `src/Security/GoogleAuthenticator.php`
- **Face Recognition**: Check `src/Controller/FaceAuthController.php`

---

**Last Updated**: March 6, 2026
**Database Version**: MariaDB 10.4.32
**Symfony Version**: 7.0+
