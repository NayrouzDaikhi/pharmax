# Pharmax Database Setup - Quick Reference

## Current Status ✅

Your database configuration has been optimized based on the provided `pharmax.sql` dump.

### Changes Made:

1. **Updated .env Database URL**:
   - ✅ Fixed database name: `pharm` → `pharmax`
   - ✅ Updated server version: MySQL 8.0.32 → MariaDB 10.4.32
   - Connection: `DATABASE_URL="mysql://root:@127.0.0.1:3306/pharmax?serverVersion=10.4.32-MariaDB&charset=utf8mb4"`

2. **Created Configuration Files**:
   - ✅ `.env.local.example` - Template for local environment variables
   - ✅ `DATABASE_SETUP.md` - Comprehensive setup guide
   - ✅ `setup-database.ps1` - PowerShell setup script (Windows)
   - ✅ `setup-database.sh` - Bash setup script (Linux/macOS)

3. **Improved Migration System**:
   - ✅ `Version20260306010000.php` - Safe migration for `data_face_api` column
   - ✅ Automatic migration metadata sync during setup

## Quick Start (Windows PowerShell)

```powershell
# 1. Make sure MariaDB is running
Get-Service MariaDB | Start-Service

# 2. Import the database
.\setup-database.ps1

# 3. Copy and configure env
Copy-Item .env.local.example .env.local
# Edit .env.local if needed

# 4. Start development
php bin/console server:start
```

## Database Features Included

✅ **User Authentication**
- Email/password login
- Google OAuth
- JWT tokens
- 2FA (TOTP)
- Face recognition

✅ **CMS**
- Blog articles
- Comments system
- Comment moderation & archiving

✅ **E-Commerce**
- Products & categories
- Shopping cart/orders
- Stripe payment integration
- Delivery tracking

✅ **Support**
- Customer reclamations
- Response management
- Notification system

## Test Users in Database

| Email | Role | Password |
|-------|------|----------|
| amal.aguir88@gmail.com | USER | Reset needed |
| lola.aguir@gmail.com | SUPER_ADMIN | Reset needed |
| test@gmail.com | USER | test123 |
| mqsdf@gmail.com | USER | Has face data |

## Important Directories

```
pharmax/
├── config/
│   ├── bundles.php                 ← All enabled bundles
│   ├── jwt/                        ← JWT key files
│   ├── packages/
│   │   ├── doctrine.yaml           ← Database config
│   │   ├── lexik_jwt_authentication.yaml
│   │   ├── scheb_2fa.yaml
│   │   ├── stripe.yaml
│   │   └── security.yaml
│   └── services.yaml
├── migrations/                     ← Database migrations
├── src/
│   ├── Entity/
│   │   └── User.php               ← Has getDataFaceApi() method
│   ├── Controller/
│   │   ├── FaceAuthController.php
│   │   └── TwoFactorAuthController.php
│   └── Security/
│       ├── JwtAuthenticator.php
│       └── GoogleAuthenticator.php
├── DATABASE_SETUP.md              ← Full setup guide
├── setup-database.ps1             ← Windows setup
├── setup-database.sh              ← Linux/Mac setup
├── .env                           ← Updated with correct DB
├── .env.local.example             ← Template for local config
└── pharmax.sql                    ← Database dump
```

## Next Steps

1. **Install Dependencies**:
   ```bash
   composer install --ignore-platform-reqs
   ```

2. **Import Database**:
   ```powershell
   .\setup-database.ps1
   ```

3. **Configure Local Settings**:
   ```bash
   cp .env.local.example .env.local
   # Edit .env.local to add API keys
   ```

4. **Start Development**:
   ```bash
   php bin/console server:start
   # Access at http://localhost:8000
   ```

## Included Services & Integrations

- ✅ **Stripe**: Payment processing
- ✅ **Google OAuth**: Social login
- ✅ **Gemini AI**: AI features
- ✅ **HuggingFace**: Comment moderation
- ✅ **Ollama**: Local LLM chatbot
- ✅ **Mercure**: Real-time updates
- ✅ **2FA/TOTP**: Google Authenticator
- ✅ **Face Recognition**: ML-based authentication

## Troubleshooting

**Connection error?** → See [DATABASE_SETUP.md](DATABASE_SETUP.md#troubleshooting)

**Migrations failing?** → Run: 
```bash
php bin/console doctrine:migrations:sync-metadata-storage
```

**Missing API keys?** → Copy `.env.local.example` to `.env.local` and fill in values

---

**All changes committed to git!** ✅

Files changed: 6
Total additions: ~561 lines of setup scripts and documentation
