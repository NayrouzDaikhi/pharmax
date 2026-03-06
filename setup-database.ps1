# Database Setup Script for Pharmax (Windows PowerShell)
# This script imports the provided SQL dump and sets up the database

param(
    [string]$SqlDumpFile = "pharmax.sql",
    [string]$DbHost = "127.0.0.1",
    [string]$DbPort = "3306",
    [string]$DbUser = "root",
    [string]$DbPassword = "",
    [string]$DbName = "pharmax"
)

$ErrorActionPreference = "Stop"

Write-Host "=== Pharmax Database Setup ===" -ForegroundColor Yellow
Write-Host ""

# Check if SQL dump file exists
if (-not (Test-Path $SqlDumpFile)) {
    Write-Host "Error: SQL dump file '$SqlDumpFile' not found!" -ForegroundColor Red
    Write-Host "Usage: .\setup-database.ps1 -SqlDumpFile path\to\pharmax.sql"
    exit 1
}

Write-Host "Using SQL dump: $SqlDumpFile" -ForegroundColor Yellow

# Check if mysql command is available
try {
    $null = mysql --version
} catch {
    Write-Host "Error: MySQL/MariaDB client not found! Please install MySQL or MariaDB client tools." -ForegroundColor Red
    Write-Host "Download from: https://dev.mysql.com/downloads/mysql/"
    exit 1
}

# Build MySQL connection string
$mysqlArgs = @("-h", $DbHost, "-P", $DbPort, "-u", $DbUser)
if ($DbPassword) {
    $mysqlArgs += @("-p$DbPassword")
}

Write-Host "Connecting to database server: $DbHost`:$DbPort" -ForegroundColor Yellow
Write-Host "Importing SQL dump..." -ForegroundColor Yellow

# Import the SQL dump
try {
    $sqlContent = Get-Content $SqlDumpFile -Raw
    $sqlContent | & mysql @mysqlArgs
    Write-Host "Database imported successfully!" -ForegroundColor Green
} catch {
    Write-Host "Error importing database: $_" -ForegroundColor Red
    exit 1
}

Write-Host ""
Write-Host "Running Symfony migrations..." -ForegroundColor Yellow

Write-Host "Running Symfony migrations..." -ForegroundColor Yellow

# Sync migration metadata (safe to run after SQL import)
Write-Host "Syncing migration metadata..." -ForegroundColor Cyan
try {
    & php bin/console doctrine:migrations:sync-metadata-storage --no-interaction
    Write-Host "Migration metadata synced!" -ForegroundColor Green
} catch {
    Write-Host "Warning: Could not sync migration metadata." -ForegroundColor Yellow
}

# Run migrations
try {
    & php bin/console doctrine:migrations:migrate --no-interaction
    Write-Host "Migrations completed!" -ForegroundColor Green
} catch {
    Write-Host "Warning: Could not run migrations. The database might already be up to date." -ForegroundColor Yellow
}

Write-Host ""
Write-Host "=== Database Setup Complete ===" -ForegroundColor Green
Write-Host ""
Write-Host "Existing test users:" -ForegroundColor Yellow
Write-Host "  - Email: amal.aguir88@gmail.com (Password needed)"
Write-Host "  - Email: lola.aguir@gmail.com (ROLE_SUPER_ADMIN)"
Write-Host "  - Email: test@gmail.com (ROLE_USER)"
Write-Host "  - Email: mqsdf@gmail.com (ROLE_USER - has face data registered)"
Write-Host ""
Write-Host "Next steps:" -ForegroundColor Cyan
Write-Host "  1. Copy .env.local.example to .env.local"
Write-Host "  2. Update API keys in .env.local (Stripe, Gemini, etc.)"
Write-Host "  3. Run: composer install"
Write-Host "  4. Run: php bin/console server:start"
