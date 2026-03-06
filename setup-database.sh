#!/bin/bash
# Database Setup Script for Pharmax
# This script imports the provided SQL dump and sets up the database

set -e

DB_NAME="pharmax"
DB_USER="root"
DB_PASSWORD=""  # Edit this if you have a password
DB_HOST="127.0.0.1"
DB_PORT="3306"
SQL_DUMP_FILE="${1:-pharmax.sql}"

# Color codes for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${YELLOW}=== Pharmax Database Setup ===${NC}"

# Check if SQL dump file exists
if [ ! -f "$SQL_DUMP_FILE" ]; then
    echo -e "${RED}Error: SQL dump file '$SQL_DUMP_FILE' not found!${NC}"
    echo "Usage: $0 [path/to/pharmax.sql]"
    exit 1
fi

echo -e "${YELLOW}Importing database from: $SQL_DUMP_FILE${NC}"

# Import the SQL dump
mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" ${DB_PASSWORD:+-p"$DB_PASSWORD"} < "$SQL_DUMP_FILE"

echo -e "${GREEN}Database imported successfully!${NC}"

# Run Symfony migrations (will skip already-executed ones)
echo -e "${YELLOW}Running Symfony migrations...${NC}"

# Sync migration metadata (safe to run after SQL import)
echo -e "${YELLOW}Syncing migration metadata...${NC}"
php bin/console doctrine:migrations:sync-metadata-storage --no-interaction

# Run migrations
php bin/console doctrine:migrations:migrate --no-interaction

if [ $? -eq 0 ]; then
    echo -e "${GREEN}Migrations completed successfully!${NC}"
else
    echo -e "${YELLOW}Note: Some migrations may have already been applied. This is normal.${NC}"
fi
echo ""
echo -e "${YELLOW}Existing test users:${NC}"
echo "  Email: amal.aguir88@gmail.com (Password needed)"
echo "  Email: lola.aguir@gmail.com (ROLE_SUPER_ADMIN)"
echo "  Email: test@gmail.com (ROLE_USER)"
echo "  Email: mqsdf@gmail.com (ROLE_USER - has face data registered)"
