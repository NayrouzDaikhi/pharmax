# 👤 MODULE UTILISATEURS & AUTHENTIFICATION - SPRINT 4

**Status**: 📋 En Planification  
**User Stories**: US#11 + US#12 + US#13  
**Points Totaux**: 46 pts (16 + 18 + 12)  
**Durée Estimée**: 3 semaines

---

## 👥 USER STORY #11: GESTION COMPLÈTE UTILISATEURS (16 pts)

### Description
En tant qu'**admin**, je veux **gérer complètement les utilisateurs** avec rôles, permissions et **audit de sécurité** afin de **contrôler l'accès au système**.

### Critères d'Acceptation

```
✓ USER REGISTRATION
  - Formulaire: Email, Password (2x), Prénom, Nom
  - Validations: Email unique, Password strong (8+chars, majuscules, chiffres)
  - Email verification link (24h expiration)
  - Post-verify: Créer User avec role ROLE_USER (default)
  - Welcome email avec lien login

✓ USER LOGIN / SESSION
  - Login traditionnel: Email + Password
  - Remember-me option (14 jours)
  - Login history: Timestamp, IP, Browser
  - Logout: Destroy session + token

✓ USER PROFILE
  - Edit prénom, nom, avatar, bio
  - Change email (verify new email)
  - Change password (verify old password)
  - Two-factor authentication (optionnel)
  - Préférences notifications
  - Liste appareils connectés (sessions actives)
  - "Se déconnecter partout" button

✓ ADMIN: Gestion Utilisateurs
  - Lister tous les utilisateurs
  - Filtres: Rôle, Status (ACTIF, BLOCKED), Date creation
  - Modifier: Rôle, Status, Email
  - Actions: Block/Unblock, Delete (soft delete)
  - Import CSV utilisateurs (bulk)
  - Export utilisateurs (CSV, PDF)
  - Assigner roles/permissions

✓ ROLES & PERMISSIONS
  - Rôles: ROLE_USER, ROLE_ADMIN, ROLE_SUPPORT, ROLE_MODERATOR
  - Permissions granulaires:
    * EDIT_OWN_PROFILE
    * VIEW_ORDERS
    * EDIT_ORDERS
    * MANAGE_USERS
    * MANAGE_CONTENT
    * MODERATE_COMMENTS
    * VIEW_ANALYTICS
  - Voter system (Symfony Security)
  - Role hierarchy visible en admin

✓ PASSWORD RESET
  - Forgot password link
  - Email with reset token (1h expiration)
  - Set new password (unique reset token consumed)
  - Email confirmation après reset

✓ ACCOUNT DELETION
  - "Supprimer mon compte" button
  - Confirmation (30 sec timeout)
  - Soft delete: user.deleted_at = NOW()
  - Data anonymization: email, prénom → "DELETED_USER"
  - Keep audit trail (commandes, réclamations still exist)

✓ ADMIN: GDPR Compliance
  - Export user data (DSAR - Data Subject Access Request)
  - Format: JSON ou CSV avec toutes les données
  - Accessible à user ou admin
  - Timestamp & IP logged

✓ SECURITY AUDIT LOG
  - Log: Login, Logout, Password change, Role change, Permissions used
  - IP, Browser, Timestamp, Status (Success/Failed)
  - Retentio: 2 années
  - Admin can view audit trail per user
```

### Tâches Techniques

```
ENTITIES/MODELS:

[ ] Améliorer User Entity
    ├─ Ajouter: phone (string nullable)
    ├─ Ajouter: bio (text nullable)
    ├─ Ajouter: avatar (string nullable)
    ├─ Ajouter: role (enum ou simple string)
    ├─ Ajouter: status (ACTIVE, BLOCKED, UNVERIFIED)
    ├─ Ajouter: deleted_at (datetime nullable - soft delete)
    ├─ Ajouter: email_verified_at (datetime nullable)
    ├─ Ajouter: last_login_at (datetime nullable)
    ├─ Ajouter: last_login_ip (string nullable)
    ├─ Ajouter: two_factor_enabled (boolean)
    ├─ Ajouter: two_factor_secret (string nullable)
    └─ Many:One → Adresse (for addresses)

[ ] NEW: SecurityAuditLog Entity
    ├─ id, user_id (FK), action, ip_address
    ├─ browser_user_agent, timestamp
    ├─ status (SUCCESS, FAILED), details
    └─ Soft delete: keep 2 years of history

[ ] NEW: EmailVerificationToken Entity
    ├─ id, user_id (FK), token (unique)
    ├─ expires_at (datetime): 24h duration
    ├─ used_at (datetime nullable)
    ├─ purpose (REGISTER, PASSWORD_RESET, EMAIL_CHANGE)

[ ] NEW: UserSession Entity
    ├─ id, user_id (FK), session_id, ip_address
    ├─ browser, device, created_at, last_activity
    ├─ Allows: "Logout everywhere"

[ ] NEW: Permission Entity
    ├─ id, code (unique: EDIT_OWN_PROFILE, etc.)
    ├─ description, category
    ├─ Many:Many → Role

[ ] NEW: Role Entity (if not simple enum)
    ├─ id, name (ADMIN, USER, SUPPORT, MODERATOR)
    ├─ description
    ├─ Many:Many → Permission
    ├─ Hierarchy: ADMIN > MODERATOR > SUPPORT > USER

CONTROLLERS:

[ ] AuthenticationController
    ├─ GET /register → Formulaire inscription
    ├─ POST /register → Créer user, send email
    ├─ GET /register/verify/{token} → Verify email
    ├─ GET /login → Formulaire connectées
    ├─ POST /login → Symfony security (delegated)
    ├─ POST /logout → Destroy session
    ├─ GET /forgot-password → Request reset
    ├─ POST /forgot-password → Send email token
    ├─ GET /reset-password/{token} → Reset form
    └─ POST /reset-password → Save new password

[ ] ProfileController
    ├─ GET /profile → Afficher mon profil
    ├─ PUT /profile → Éditer profil
    ├─ POST /profile/avatar → Upload avatar
    ├─ PUT /profile/password → Changer password
    ├─ PUT /profile/email → Change email (verify)
    ├─ GET /profile/sessions → Sessions actives
    ├─ POST /profile/sessions/{id}/revoke → Logout device
    ├─ POST /profile/sessions/revoke-all → Logout everywhere
    └─ POST /profile/delete → Soft delete account

[ ] Admin/UserController
    ├─ GET /admin/users → Lister users
    ├─ GET /admin/users/{id} → Détail user
    ├─ PUT /admin/users/{id} → Modifier (rôle, status)
    ├─ POST /admin/users/{id}/block → Blocker user
    ├─ POST /admin/users/{id}/unblock → Déblocker
    ├─ DELETE /admin/users/{id} → Soft delete
    ├─ POST /admin/users/import → Bulk import CSV
    ├─ GET /admin/users/{id}/export → Export user data
    ├─ GET /admin/users/{id}/audit → Audit log
    ├─ POST /admin/users/{id}/permission → Manage permissions
    └─ GET /admin/users/stats → User statistics

SERVICES:

[ ] UserService
    ├─ createUser(email, password, firstName, lastName)
    ├─ updateProfile(User, $data)
    ├─ changePassword(User, $oldPassword, $newPassword)
    ├─ blockUser(User)
    ├─ unblockUser(User)
    ├─ softDeleteUser(User) → Anonymize data
    ├─ exportUserData(User) → JSON/CSV
    ├─ verifyEmail(User, $token)
    └─ resetPassword(User, $token, $newPassword)

[ ] EmailVerificationService
    ├─ generateToken(User, $purpose) → Token object
    ├─ sendVerificationEmail(User, $token)
    ├─ verifyToken(string $token) → User | null
    ├─ cleanExpiredTokens()
    └─ resendVerificationEmail(User)

[ ] SecurityAuditService
    ├─ logAction(User, $action, $details = [])
    ├─ logLogin(User, $ip, $userAgent)
    ├─ logLogout(User, $ip)
    ├─ logPasswordChange(User, $ip)
    ├─ logPermissionChange(User, $oldPerms, $newPerms, $admin)
    ├─ getAuditLog(User) → Array
    ├─ cleanup_old_logs() → Keep 2 years
    └─ detect_suspicious_activity(User) → bool

[ ] SessionService
    ├─ createSession(User, $ip, $userAgent)
    ├─ updateLastActivity(Session)
    ├─ revokeSession(Session)
    ├─ revokeAllSessions(User)
    ├─ getActiveSessions(User) → Array
    └─ cleanupExpiredSessions()

[ ] PermissionService
    ├─ grantPermission(User, $permissionCode)
    ├─ revokePermission(User, $permissionCode)
    ├─ hasPermission(User, $permissionCode) → bool
    ├─ checkPermission throws AccessDenied
    └─ getAllPermissions() → Array

TEMPLATES:

[ ] Users
    ├─ templates/auth/register.html.twig
    ├─ templates/auth/login.html.twig
    ├─ templates/auth/forgot_password.html.twig
    ├─ templates/auth/reset_password.html.twig
    ├─ templates/profile/index.html.twig
    ├─ templates/profile/edit.html.twig
    ├─ templates/profile/change_password.html.twig
    ├─ templates/profile/sessions.html.twig
    └─ templates/auth/email_verification.html.twig

[ ] Admin
    ├─ templates/admin/user/index.html.twig
    ├─ templates/admin/user/show.html.twig
    ├─ templates/admin/user/edit.html.twig
    ├─ templates/admin/user/audit.html.twig
    ├─ templates/admin/user/permissions.html.twig
    ├─ templates/admin/user/import.html.twig
    └─ templates/admin/user/stats.html.twig

TESTS:

[ ] AuthenticationTest (30+ cases)
[ ] ProfileTest (20+ cases)
[ ] UserAdminTest (25+ cases)
[ ] PermissionTest (20+ cases)
[ ] AuditTest (15+ cases)
```

### Base de Données

```sql
-- User amélioration
ALTER TABLE `user`
  ADD COLUMN phone VARCHAR(20),
  ADD COLUMN bio TEXT,
  ADD COLUMN avatar VARCHAR(255),
  ADD COLUMN status VARCHAR(50) DEFAULT 'ACTIVE',
  ADD COLUMN deleted_at DATETIME,
  ADD COLUMN email_verified_at DATETIME,
  ADD COLUMN last_login_at DATETIME,
  ADD COLUMN last_login_ip VARCHAR(50),
  ADD COLUMN two_factor_enabled BOOLEAN DEFAULT FALSE,
  ADD COLUMN two_factor_secret VARCHAR(255);

-- SecurityAuditLog
CREATE TABLE security_audit_log (
  id INT PRIMARY KEY AUTO_INCREMENT,
  user_id INT,
  action VARCHAR(100),
  details JSON,
  ip_address VARCHAR(50),
  browser_user_agent TEXT,
  status VARCHAR(50),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES `user`(id) ON DELETE SET NULL,
  INDEX idx_user_date (user_id, created_at DESC)
);

-- EmailVerificationToken
CREATE TABLE email_verification_token (
  id INT PRIMARY KEY AUTO_INCREMENT,
  user_id INT NOT NULL,
  token VARCHAR(255) UNIQUE NOT NULL,
  expires_at DATETIME NOT NULL,
  used_at DATETIME,
  purpose VARCHAR(50),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES `user`(id) ON DELETE CASCADE
);

-- UserSession
CREATE TABLE user_session (
  id INT PRIMARY KEY AUTO_INCREMENT,
  user_id INT NOT NULL,
  session_id VARCHAR(255) UNIQUE,
  ip_address VARCHAR(50),
  browser VARCHAR(255),
  device VARCHAR(255),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  last_activity DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES `user`(id) ON DELETE CASCADE
);

-- Role
CREATE TABLE role (
  id INT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(100) UNIQUE NOT NULL,
  description TEXT,
  hierarchy_level INT DEFAULT 0
);

-- Permission
CREATE TABLE permission (
  id INT PRIMARY KEY AUTO_INCREMENT,
  code VARCHAR(100) UNIQUE NOT NULL,
  description TEXT,
  category VARCHAR(50)
);

-- Role_Permission
CREATE TABLE role_permission (
  role_id INT,
  permission_id INT,
  PRIMARY KEY (role_id, permission_id),
  FOREIGN KEY (role_id) REFERENCES role(id) ON DELETE CASCADE,
  FOREIGN KEY (permission_id) REFERENCES permission(id) ON DELETE CASCADE
);
```

### Workflow Authentification

```
Registration:
  1. POST /register {email, password, firstName, lastName}
  2. Validate inputs
  3. Hash password (bcrypt)
  4. Create User + status=UNVERIFIED
  5. Generate token (24h)
  6. Send email with verification link
  7. Email link: /register/verify/{token}
  8. Update: email_verified_at = NOW() + status=ACTIVE
  9. Redirect /login

Login:
  1. GET /login → form
  2. POST /login {email, password}
  3. Symfony authenticator
  4. Password verify
  5. Check status = ACTIVE (not BLOCKED)
  6. Create session
  7. Log: action=LOGIN, ip, browser
  8. Redirect /dashboard

Password Reset:
  1. GET /forgot-password → form
  2. POST /forgot-password {email}
  3. Find user (no error if not found - security)
  4. Generate token (1h expiration)
  5. Send email with reset link
  6. Link: /reset-password/{token}
  7. POST /reset-password {token, password}
  8. Verify token, update password
  9. Log: action=PASSWORD_RESET
  10. Email: "Password changed successfully"

Account Deletion:
  1. POST /profile/delete {password_confirmation}
  2. Soft delete: user.deleted_at = NOW()
  3. Anonymize: email="DELETED_#{id}", firstName="DELETED"
  4. Log: action=ACCOUNT_DELETED
  5. Redirect: Thank you page
```

### Casos de Test

```php
// Test 1: Registro
POST /register
{
  "email": "newuser@example.com",
  "password": "SecurePass123!",
  "password_confirm": "SecurePass123!",
  "firstName": "John",
  "lastName": "Doe"
}
→ 302 Redirect /check-email
→ Email sent: Verification link
→ User.status = UNVERIFIED

// Test 2: Email Verification
GET /register/verify/token_abc123xyz
→ 302 Redirect /login
→ User.email_verified_at = NOW()
→ User.status = ACTIVE
→ Flash: "Email verified! Login now"

// Test 3: Login
POST /login
{
  "email": "user@example.com",
  "password": "SecurePass123!",
  "remember_me": true
}
→ 302 Redirect /dashboard
→ Session set (14 days if remember_me)
→ Log entry: action=LOGIN, ip=192.168...

// Test 4: Change Password
PUT /profile/password
{
  "old_password": "SecurePass123!",
  "new_password": "NewPass456!",
  "new_password_confirm": "NewPass456!"
}
→ 200 OK
→ Password updated
→ Email: "Password changed"
→ Audit log: action=PASSWORD_CHANGED

// Test 5: Block User (Admin)
POST /admin/users/5/block
{
  "reason": "Spam behavior"
}
→ 200 OK
→ User.status = BLOCKED
→ All sessions revoked
→ Email to user: "Account suspended"

// Test 6: Delete Account
POST /profile/delete
{
  "password": "SecurePass123!"
}
→ 200 OK
→ user.deleted_at = NOW()
→ Email anonymized
→ Audit: action=ACCOUNT_DELETED
→ Redirect: /goodbye (thank you page)

// Test 7: Export User Data
GET /admin/users/5/export
→ Content-Type: application/json
→ Full JSON with: email, orders, comments, audit logs
→ 30 second download delay (GDPR)

// Test 8: Revoke All Sessions
POST /profile/sessions/revoke-all
→ 200 OK
→ ALL user sessions deleted
→ User logged out everywhere
→ Email: "Logged out from all devices"
```

### Matriz Permisos

```
User Role:
├─ EDIT_OWN_PROFILE ✓
├─ VIEW_OWN_ORDERS ✓
└─ SUBMIT_RECLAMATION ✓

Support Role:
├─ VIEW_ALL_RECLAMATIONS ✓
├─ RESPOND_RECLAMATIONS ✓
├─ VIEW_CUSTOMER_ORDERS ✓
└─ EDIT_OWN_PROFILE ✓

Moderator Role:
├─ MODERATE_COMMENTS ✓
├─ VIEW_USERS ✓
├─ BLOCK_COMMENTS ✓
└─ EDIT_OWN_PROFILE ✓

Admin Role:
├─ MANAGE_USERS ✓
├─ MANAGE_CONTENT ✓
├─ VIEW_ANALYTICS ✓
├─ MANAGE_ORDERS ✓
├─ MANAGE_PERMISSIONS ✓
└─ EVERYTHING ELSE ✓
```

---

## 🔐 USER STORY #12: API AUTHENTIFICATION & PROFILS (18 pts)

### Description
En tant que **développeur mobile**, je veux une **API JWT pour authentifier les utilisateurs** et **gérer les profils** afin de **construire une app mobile native**.

### Architecture JWT

```
POST /api/auth/login
{
  "email": "user@example.com",
  "password": "SecurePass123!"
}

Response 200:
{
  "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiI1IiwiZXhwIjoxNjM5OTAyNDAwfQ...",
  "refresh_token": "refresh_token_abc123xyz",
  "expires_in": 3600,
  "token_type": "Bearer",
  "user": {
    "id": 5,
    "email": "user@example.com",
    "firstName": "John",
    "lastName": "Doe",
    "roles": ["ROLE_USER"],
    "permissions": ["EDIT_OWN_PROFILE", "VIEW_OWN_ORDERS"]
  }
}

// Subsequent API calls:
GET /api/me
Headers: {
  "Authorization": "Bearer eyJhbGciOiJ..."
}
```

### Endpoints API

```
POST /api/auth/login
  → Validate credentials
  → Generate JWT + refresh token
  → Response: tokens + user info

POST /api/auth/register
  → Create user, send verification email
  → Response: { message, user_id }

POST /api/auth/refresh
  → Exchange refresh token for new JWT
  → Response: { token, expires_in }

POST /api/auth/logout
  → Revoke tokens
  → Response: { message: "Logged out" }

POST /api/auth/verify-email/{token}
  →ify email address
  → Response: { verified: true }

GET /api/me
  → Current authenticated user
  → Response: { user object }

PUT /api/me
  → Update profile (firstName, lastName, bio, etc.)
  → Response: { user object }

POST /api/me/avatar
  → Upload avatar image
  → Response: { avatar_url }

PUT /api/me/password
  → Change password
  → Response: { message: "Password changed" }

PUT /api/me/email
  → Change email (resend verification)
  → Response: { message: "Verification email sent" }

POST /api/auth/forgot-password
  → Request password reset
  → Response: { message: "Reset email sent" }

POST /api/auth/reset-password/{token}
  → Reset password with token
  → Response: { token, user }

POST /api/auth/oauth/google
  → OAuth2 Google login
  → Body: { google_token }
  → Response: { token, user }

POST /api/auth/oauth/facebook
  → OAuth2 Facebook login
  → Body: { facebook_token }
  → Response: { token, user }
```

### Tâches Técnicas

```
[ ] LexikJWTAuthenticationBundle setup
    ├─ Configure: private.pem, public.pem
    ├─ Token TTL: 3600 seconds (1 hour)
    ├─ Refresh TTL: 604800 seconds (7 days)
    └─ Algorithm: RS256 (RSA)

[ ] Api/AuthApiController
    ├─ login() - POST /api/auth/login
    ├─ register() - POST /api/auth/register
    ├─ refresh() - POST /api/auth/refresh
    ├─ logout() - POST /api/auth/logout
    ├─ forgotPassword() - POST /api/auth/forgot-password
    ├─ resetPassword() - POST /api/auth/reset-password/{token}
    ├─ oauthGoogle() - POST /api/auth/oauth/google
    └─ oauthFacebook() - POST /api/auth/oauth/facebook

[ ] Api/ProfileApiController
    ├─ getMe() - GET /api/me
    ├─ updateProfile() - PUT /api/me
    ├─ uploadAvatar() - POST /api/me/avatar
    ├─ changePassword() - PUT /api/me/password
    ├─ changeEmail() - PUT /api/me/email
    └─ deleteAccount() - POST /api/me/delete

[ ] OAuth2 Integration
    ├─ Google OAuth2 provider
    │  ├─ Client ID, Secret from Google Cloud
    │  ├─ Redirect URI: /auth/callback/google
    │  └─ Scope: email, profile
    │
    └─ Facebook OAuth2 provider
       ├─ App ID, Secret from Facebook
       ├─ Redirect URI: /auth/callback/facebook
       └─ Scope: email, public_profile

[ ] JWT Authenticator (Symfony Security)
    ├─ Implements AuthenticatorInterface
    ├─ Extract token from Authorization header
    ├─ Validate token signature
    ├─ Load user from DB
    ├─ Check user status (ACTIVE vs BLOCKED)
    └─ Return authenticated token

[ ] Tests API (50+ cases)
    ├─ LoginTest
    ├─ RegisterTest
    ├─ RefreshTokenTest
    ├─ OAuthTest (mocked providers)
    ├─ ProfileTest
    ├─ AuthErrorTest (invalid credent.)
    └─ TokenExpiration Test

[ ] CORS Configuration
    ├─ Allow origins: http://localhost:3000 (dev), https://yourdomain.com (prod)
    ├─ Allow methods: GET, POST, PUT, DELETE, OPTIONS
    ├─ Allow headers: Authorization, Content-Type
    ├─ Credentials: true
    └─ Max age: 3600
```

### Security Best Practices

```yaml
# config/packages/security.yaml

security:
  password_hashers:
    App\Entity\User:
      algorithm: bcrypt
      cost: 12  # Higher cost = slower (more secure against brute force)

  authenticators:
    - jwt: "%env(JWT_PRIVATE_KEY)%"  # RS256
    - oauth_google
    - oauth_facebook

  access_control:
    - { path: ^/api/auth/login, roles: PUBLIC_ACCESS }
    - { path: ^/api/auth/register, roles: PUBLIC_ACCESS }
    - { path: ^/api/auth/, roles: PUBLIC_ACCESS }
    - { path: ^/api/, roles: ROLE_USER }
    - { path: ^/admin, roles: ROLE_ADMIN }

  role_hierarchy:
    ROLE_ADMIN: [ROLE_USER, ROLE_MODERATOR]
    ROLE_MODERATOR: [ROLE_USER]
    ROLE_SUPPORT: [ROLE_USER]
```

### JWT Token Structure

```
HEADER:
{
  "alg": "RS256",
  "typ": "JWT"
}

PAYLOAD:
{
  "sub": "5",                          // User ID
  "email": "user@example.com",
  "firstName": "John",
  "roles": ["ROLE_USER"],
  "permissions": ["EDIT_OWN_PROFILE", "VIEW_OWN_ORDERS"],
  "iat": 1639898400,                   // Issued at
  "exp": 1639902000,                   // Expires in (1 hour)
  "iss": "pharmax.api",                // Issuer
  "aud": "pharmax-mobile"              // Audience
}

SIGNATURE:
HMACSHA256(
  base64UrlEncode(header) + "." +
  base64UrlEncode(payload),
  private_key
)
```

### Ejemplo Flujo OAuth Google

```
1. Mobile app initialize Google sign-in
   ↓
2. User clicks "Sign in with Google"
   ↓
3. Google OAuth dialog
   ↓
4. User authorizes app
   ↓
5. Mobile app receives: id_token + access_token
   ↓
6. App sends to backend:
   POST /api/auth/oauth/google
   { "id_token": "eyJhbGc..." }
   
7. Backend verifies token with Google:
   GET https://oauth2.googleapis.com/tokeninfo?id_token=...
   
8. Backend checks:
   - Token valid
   - Not expired
   - Audience matches
   - Email verified
   
9. Backend creates/updates User in DB:
   - If not exists: create User with google_id
   - Link social account
   
10. Backend generates JWT:
    POST response:
    {
      "token": "eyJhbGc...",
      "user": { id, email, firstName, ... }
    }
    
11. Mobile app stores JWT in secure storage:
    - Keychain (iOS)
    - Keystore (Android)
    
12. All future API calls use JWT in header:
    Authorization: Bearer eyJhbGc...
```

### Casos Test API Auth

```bash
# Test 1: Login
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email": "user@example.com", "password": "SecurePass123!"}'

Response 200:
{
  "token": "eyJhbGciOiJIUzI1NiIs...",
  "refresh_token": "refresh_abc123",
  "expires_in": 3600,
  "user": { "id": 5, "email": "user@example.com", ... }
}

# Test 2: Invalid credentials
curl -X POST http://localhost:8000/api/auth/login \
  -d '{"email": "user@example.com", "password": "WRONG"}'

Response 401:
{
  "error": "INVALID_CREDENTIALS",
  "message": "Email or password incorrect"
}

# Test 3: Get profile (authenticated)
curl -X GET http://localhost:8000/api/me \
  -H "Authorization: Bearer eyJhbGc..."

Response 200:
{
  "id": 5,
  "email": "user@example.com",
  "firstName": "John",
  "lastName": "Doe",
  "avatar": "/uploads/avatars/user_5.jpg",
  "bio": "Pharmacy enthusiast",
  "roles": ["ROLE_USER"],
  "permissions": ["EDIT_OWN_PROFILE", "VIEW_OWN_ORDERS"]
}

# Test 4: Refresh token
curl -X POST http://localhost:8000/api/auth/refresh \
  -d '{"refresh_token": "refresh_abc123"}'

Response 200:
{
  "token": "eyJhbGc...",
  "expires_in": 3600
}

# Test 5: Expired token
curl -X GET http://localhost:8000/api/me \
  -H "Authorization: Bearer expired_token"

Response 401:
{
  "error": "TOKEN_EXPIRED",
  "message": "Your session has expired. Please login again"
}

# Test 6: OAuth Google
curl -X POST http://localhost:8000/api/auth/oauth/google \
  -H "Content-Type: application/json" \
  -d '{"id_token": "eyJhbGc..."}'

Response 200:
{
  "token": "eyJhbGc...",
  "user": { "id": 6, "email": "googleuser@gmail.com", ... }
}
```

---

## 🔔 USER STORY #13: NOTIFICATIONS MULTI-CANAUX (12 pts)

### Description
En tant que **client**, je veux **recevoir des notifications** sur les événements importants (commande, réclamation, nouveau contenu) via **email, SMS et push** selon mes préférences.

### Critères d'Acceptation

```
✓ NOTIFICATION CENTER (In-App)
  - /notifications dashboard
  - Liste toutes les notifications
  - Marquer comme lue
  - Filtrer: lues/non-lues
  - Supprimer notification
  - Badge counter (# unread)

✓ EMAIL NOTIFICATIONS
  - Transactional emails (OrderConfirmation, PasswordReset, etc)
  - HTML templates elegantes
  - Unsubscribe link (GDPR)
  - Tracking: open rate, click rate

✓ PUSH NOTIFICATIONS (Optional)
  - Web push (Desktop Chrome, Firefox)
  - Mobile push (if app exists)
  - Service Worker integration
  - Rich notifications (with images)

✓ SMS NOTIFICATIONS (Optional)
  - Twilio integration
  - Phone number optional field
  - Send on urgent events: Order shipped, Urgent reclamation

✓ PREFERENCE CENTER
  - /notification-preferences
  - Per-event: Email, Push, SMS toggle
  - Batch frequency: Instant, Daily, Weekly
  - Do-not-disturb hours (9PM-8AM)
  - Opt-out entirely

✓ EVENT SYSTEM
  - Events fired:
    * order.created, order.shipped, order.delivered
    * reclamation.created, reclamation.resolved
    * comment.approved, comment.replied
    * article.published, article_liked
    * stock_available (restock notification)
```

### Architecture

```
Event System:
  1. Event occurs (e.g., OrderCreatedEvent)
  2. Dispatcher fires event
  3. Multiple listeners subscribe:
     ├─ EmailNotificationListener
     ├─ PushNotificationListener
     ├─ SmsNotificationListener
     └─ DatabaseNotificationListener (save notification)
     
4. Each listener checks user preferences
5. Sends notification if enabled
6. Logs result (success/failure)
7. Retry queue for failures (queue system)
```

### Tâches Técnicas

```
[ ] Entities
    ├─ Notification Entity
    │  ├─ id, user_id (FK), type, title, message
    │  ├─ data (JSON - event data), read_at
    │  ├─ created_at, expires_at
    │  └─ action_url (link to related entity)
    │
    └─ NotificationPreference Entity
       ├─ id, user_id (FK), event_type
       ├─ email_enabled, push_enabled, sms_enabled
       ├─ batch_frequency (INSTANT, DAILY, WEEKLY)
       └─ quiet_hours_from, quiet_hours_to

[ ] Services
    ├─ NotificationService (orchestrate all channels)
    │  ├─ notify(Event) → Dispatch to all channels
    │  ├─ preferences(User) → Get user settings
    │  └─ sendBatch() → Send queued notifications
    │
    ├─ EmailNotificationService
    │  ├─ send(User, $template, $data)
    │  ├─ sendMultiple(User[], ...)
    │  ├─ Track open/click rates
    │  └─ Queue for async sending
    │
    ├─ PushNotificationService (opt)
    │  ├─ subscribe(User, $subscription)
    │  ├─ send(User, $message)
    │  ├─ broadcast(User[], $message)
    │  └─ UnsubscribeIfError()
    │
    └─ SmsNotificationService (opt)
       ├─ send(User, $message)
       ├─ getCredits() → Twilio balance
       └─ LogDelivery()

[ ] Event System
    ├─ Symfony EventDispatcher configuration
    ├─ Custom events:
    │  ├─ OrderCreatedEvent
    │  ├─ OrderShippedEvent
    │  ├─ ReclamationResolvedEvent
    │  ├─ ArticlePublishedEvent
    │  ├─ CommentApprovedEvent
    │  └─ StockAvailableEvent
    │
    └─ Event Listeners:
       ├─ EmailNotificationListener
       ├─ PushNotificationListener
       ├─ SmsNotificationListener
       └─ DatabaseNotificationListener

[ ] Email Templates
    ├─ order_confirmation.html.twig
    ├─ order_shipped.html.twig
    ├─ order_delivered.html.twig
    ├─ password_reset.html.twig
    ├─ email_verification.html.twig
    ├─ reclamation_resolved.html.twig
    ├─ comment_approved.html.twig
    ├─ article_published.html.twig
    ├─ stock_available.html.twig
    └─ Unsubscribe option in all emails

[ ] Controllers
    ├─ NotificationController
    │  ├─ GET /notifications → dashboard
    │  ├─ PUT /notifications/{id}/read → Mark as read
    │  ├─ DELETE /notifications/{id} → Delete
    │  └─ GET /notifications/count → Unread count
    │
    └─ PreferenceController
       ├─ GET /notification-preferences → form
       ├─ PUT /notification-preferences → Save
       ├─ GET /notification-preferences/templates → See email examples
       └─ POST /notification-preferences/test → Send test email

[ ] Background Jobs (Queue)
    ├─ Send emails async (Symfony Messenger)
    ├─ Send SMS async
    ├─ Cleanup old notifications (30+ days)
    ├─ Retry failed sends (exponential backoff)
    └─ Generate daily/weekly digests

[ ] Tests (40+ cases)
    ├─ NotificationServiceTest
    ├─ EmailTest (with mock mailer)
    ├─ PreferenceTest
    ├─ EventListenerTest
    └─ QueueTest
```

### Database

```sql
-- Notification
CREATE TABLE notification (
  id INT PRIMARY KEY AUTO_INCREMENT,
  user_id INT NOT NULL,
  type VARCHAR(100),
  title VARCHAR(255),
  message LONGTEXT,
  data JSON,
  read_at DATETIME,
  action_url VARCHAR(500),
  expires_at DATETIME,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES `user`(id) ON DELETE CASCADE,
  INDEX idx_user_created (user_id, created_at DESC)
);

-- NotificationPreference
CREATE TABLE notification_preference (
  id INT PRIMARY KEY AUTO_INCREMENT,
  user_id INT NOT NULL UNIQUE,
  event_type VARCHAR(100),
  email_enabled BOOLEAN DEFAULT TRUE,
  push_enabled BOOLEAN DEFAULT FALSE,
  sms_enabled BOOLEAN DEFAULT FALSE,
  batch_frequency VARCHAR(50) DEFAULT 'INSTANT',
  quiet_hours_from TIME,
  quiet_hours_to TIME,
  FOREIGN KEY (user_id) REFERENCES `user`(id) ON DELETE CASCADE
);

-- Email queue
CREATE TABLE email_queue (
  id INT PRIMARY KEY AUTO_INCREMENT,
  user_id INT,
  to_email VARCHAR(255),
  subject VARCHAR(255),
  body LONGTEXT,
  status VARCHAR(50) DEFAULT 'PENDING',
  retry_count INT DEFAULT 0,
  last_error TEXT,
  sent_at DATETIME,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
```

### Casos Uso

```
// Cuando Order créé:
1. OrderCreatedEvent dispatched
2. EmailListener checks: user preferences
   - email_enabled = true?
3. If yes: Send confirmation email
4. PushListener: Is batch_frequency INSTANT?
   - If yes: Send push notification
5. DatabaseListener: Always save notification record
6. SmsListener: Is SMS enabled + urgent? 
   - Only for critical orders

// Flow:
Event → Dispatcher → Listeners → Services → Channels
                               ↓
                         Preferences
                               ↓
                    Queue/Send async
```

### Dashboard Notification

```html
<!-- /notifications -->

<div class="notification-center">
  <h2>Notifications (12 Unread)</h2>
  
  <tabs>
    ☐ All | ◉ Unread (12) | ✓ Read | 🗑 Archived
  </tabs>
  
  <list>
    ☐ [NEW] ✓ Commande expédiée
       Votre commande #CMD-001 est en route!
       15 feb 2026, 10:30
       [View Order]
    
    ☐ [NEW] 💬 Nouvelle réponse
       Quelqu'un a répondu à votre réclamation
       14 feb 2026, 14:20
       [View Reclamation]
    
    ✓ 📰 New Article Published
       "10 Health Tips for 2026"
       10 feb 2026
  </list>
  
  <preferences-link>
    ⚙️ Manage notification preferences
  </preferences-link>
</div>

<!-- /notification-preferences -->

<div class="preferences">
  <h2>Notification Preferences</h2>
  
  <settings>
    ☑ Order Confirmé
      ☑ Email ☐ Push ☐ SMS
      Batch: [Instant ▼]
    
    ☑ Order Shipped  
      ☑ Email ☑ Push ☐ SMS
      Batch: [Instant ▼]
    
    ☑ Order Delivered
      ☑ Email ☑ Push ☐ SMS
      Batch: [Instant ▼]
    
    Do Not Disturb:
      From [21:00 ▼] to [08:00 ▼]
    
    [Save Preferences] [Send Test Email]
  </settings>
</div>
```

---

## 📊 RÉSUMÉ MODULE UTILISATEURS

| Aspect | Détail |
|--------|--------|
| **Points Totaux** | 46 pts (16 + 18 + 12) |
| **Durée Estimée** | 3 semaines |
| **Équipe** | 4-5 devs (2 backend, 1 frontend, 1 QA, 1 DevOps) |
| **Complexité** | Très Haute (Security, OAuth, Async) |
| **Intégrations** | Google OAuth, Facebook OAuth, Twilio SMS, Email Queue |
| **Sécurité** | CRITICAL - Password hashing, JWT, HTTPS only |
| **Tests** | 100+ cas de test |
| **Performance** | < 200ms API, Queue async emails |

---

# 🎊 RÉSUMÉ COMPLET: TOUS LES MODULES

## Points par Module

| Module | US CRUD | US API | Points | Sprint |
|--------|---------|--------|--------|--------|
| **Produits** | 21 | 16 | 37 | 1-2 |
| **Articles** | 18 | 16 | 34 | 2 |
| **Catégories** | 8 | 12 | 20 | 2 |
| **Réclamations** | 16 | 14 | 30 | 3 |
| **Commandes** | 18 | 14 | 32 | 3 |
| **Utilisateurs** | 16 | 18 | 34 | 4 |
| **Notifications** | — | — | 12 | 4 |
| **TOTAL** | | | **229** | |

## Timeline

```
SPRINT 1: ✅ Done (55 pts)
  - Produits CRUD
  - Modération IA Commentaires

SPRINT 2: 📋 Todo (54 pts)
  - Articles CRUD
  - Articles API (rechcher)
  - Catégories CRUD
  - Catégories API (filtrage)

SPRINT 3: 📋 Todo (62 pts)
  - Réclamations CRUD
  - Réclamations API (IA)
  - Commandes CRUD
  - Commandes API (tracking)

SPRINT 4: 📋 Todo (46 pts)
  - Utilisateurs CRUD
  - Authentification API (JWT/OAuth)
  - Notifications Multi-canaux

SPRINT 5+: 🔮 Future (Features avancées)
  - Analytics
  - Recommandations ML
  - Mobile App
```

