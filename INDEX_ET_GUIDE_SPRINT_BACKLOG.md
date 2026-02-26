# 📚 INDEX SPRINT BACKLOG COMPLET - PHARMAX

**Date**: 25 février 2026  
**Document**: Sprint Planning & Roadmap  
**Statut**: En Planification  

---

## 📋 Documents Créés

### 1️⃣ **SPRINT_BACKLOG_COMPLET.md** ⭐
**Le document maître avec:**
- Roadmap globale (Sprints 1-4)
- 13 User Stories listées
- Points par story (55-227 totaux)
- Planning Sprints détaillé
- Priorisation & dépendances
- Definition of Done

**À consulter pour**: Vue d'ensemble globale, planning cross-user stories

---

### 2️⃣ **MODULE_ARTICLES_DETAIL.md** 📰
**USER STORY #3 & #4 (34 pts)**

**Contient:**

**US#3: CRUD Articles (18 pts)**
- Description complète
- Critères d'acceptation détaillés (13 points)
- Tâches techniques (controllers, entities, templates, tests)
- Base de données + migrations SQL
- Cas de test complets
- Architecture entités

**US#4: API Recherche Articles (16 pts)**
- 6 endpoints REST listés
- Requests/Responses JSON examples
- Tâches techniques (SearchService, Serializer, Caching)
- Algorithme recommandations ML
- Tests acceptés
- Performance targets

---

### 3️⃣ **MODULE_RECLAMATIONS_DETAIL.md** 🎯
**USER STORY #7 & #8 (30 pts)**

**Contient:**

**US#7: CRUD Réclamations (16 pts)**
- Critères d'acceptation pour client & admin (13 points clés)
- Workflow État (5 états: Créée → Assignée → En cours → Résolu → Fermée)
- Tâches techniques complets (services, controllers, entities)
- Entities nouvelles: ReclamationAttachment, ReclamationStatus, Response
- Workflow State Machine diagram
- Cas de test détaillés

**US#8: API Réponses IA (14 pts)**
- Architecture système IA (Classification → Suggestion → Escalade)
- Code PHP complet (GeminiReclamationAiService)
- Endpoint API avec response JSON
- Feedback loop & ML training
- Dashboard statistiques IA
- Classification example avec Gemini

---

### 4️⃣ **MODULE_COMMANDES_DETAIL.md** 🛒
**USER STORY #9 & #10 (32 pts)**

**Contient:**

**US#9: CRUD Commandes (18 pts)**
- Critères d'acceptation [client/admin] (13 points détaillés)
- Workflow Commande (PANIER → PAYÉE → TRAITÉE → EXPÉDIÉE → LIVRÉE)
- Architecture:
  * 4 nouvelles entities: LigneCommande, CommandeNote, Coupon, Adresse
  * 5 services: Panier, Commande, Paiement, Facture, Coupon
  * Controllers panier, commande et admin
  * Templates frontend & admin
- BD migrations complètes
- Cas tests détaillés (add, checkout, cancel, refund)

**US#10: API Tracking Commandes (14 pts)**
- Endpoints tracking public/authenticated
- Response JSON avec GPS, timeline, chauffeur
- CarrierIntegration (DHL, UPS, Fedex)
- WebHooks for carrier events
- Notifications (email, SMS, push)
- Real-time tracking (Websockets)
- Caching stratégie
- Tests tracking complets

---

### 5️⃣ **MODULE_UTILISATEURS_DETAIL.md** 👤
**USER STORY #11 & #12 & #13 (46 pts)**

**Contient:**

**US#11: Gestion Utilisateurs (16 pts)**
- Critères d'acceptation complets (registration, login, profile, password reset)
- RBAC (Role-Based Access Control):
  * 4 rôles: USER, ADMIN, SUPPORT, MODERATOR
  * 7+ permissions granulaires
  * Voter system Symfony
  * Role hierarchy diagram
- Entities: User amélioration, SecurityAuditLog, Token, Session, Role, Permission
- Controllers auth, profile et admin
- Services: User, Email verification, Audit, Session, Permission
- GDPR compliance: export data, soft delete
- Audit trail complet (login, logout, password change, role changes)

**US#12: API Authentification (18 pts)**
- JWT architecture complète
- Endpoints: login, register, refresh, logout, OAuth Google/Facebook
- Token structure (header, payload, signature)
- RS256 algorithm
- OAuth2 flow complet (Google & Facebook)
- CORS configuration
- Test cases avec curl examples
- Security best practices (bcrypt cost, rate limiting, HTTPS)

**US#13: Notifications Multi-canaux (12 pts)**
- Notification Center in-app
- Email templates (9 templates)
- Push notifications (web service worker)
- SMS (Twilio integration - optionnel)
- User preferences dashboard
- Event System (14 events totaux)
- Background Queue (async sending)
- Preference per-event toggles
- Do-Not-Disturb hours
- Database notifications storage

---

## 🎯 RÉSUMÉ STRUCTURÉ

### Par Type d'Entité

| Entité | CRUD | API | Points |
|--------|------|-----|--------|
| **Produit** | ✅ (21) | ✅ (16) | 37 |
| **Article** | 📋 (18) | 📋 (16) | 34 |
| **Catégorie** | 📋 (8) | 📋 (12) | 20 |
| **Réclamation** | 📋 (16) | 📋 (14) | 30 |
| **Commande** | 📋 (18) | 📋 (14) | 32 |
| **Utilisateur** | 📋 (16) | 📋 (18) | 34 |
| **Notification** | — | — | 12 |
| **TOTAL** | 97 | 90 | **229** |

### Par Sprint

```
SPRINT 1 ✅ (55 pts) - Completed
├─ Produits CRUD (21)
└─ Modération IA (34)

SPRINT 2 📋 (54 pts) - Planifié
├─ Articles CRUD (18)
├─ Articles API (16)
├─ Catégories CRUD (8) [partial]
└─ Catégories API (12) [partial]

SPRINT 3 📋 (62 pts) - Planifié
├─ Réclamations CRUD (16)
├─ Réclamations API (14)
├─ Commandes CRUD (18)
└─ Commandes API (14)

SPRINT 4 📋 (46 pts) - Planifié
├─ Utilisateurs (16)
├─ Authentification (18)
└─ Notifications (12)

TOTAL: 229 points
Sprints: 4 semaines x 55pts = 220pts capacity
Overflow: +9 points (phase review finale)
```

---

## 🔍 GUIDE DE LECTURE

### Pour un **Manager/Stakeholder**:
1. Lire: **SPRINT_BACKLOG_COMPLET.md** (5 min)
   - Voir roadmap globale
   - Voir points par sprint
   - Voir planning global

2. Consulter: Sections "Résumé" de chaque module

### Pour un **Développeur Backend**:
1. **MODULE_UTILISATEURS_DETAIL.md** (Lecture prioritaire)
   - RBAC & JWT foundation
   - Security best practices
   
2. **MODULE_ARTICLES_DETAIL.md** + **MODULE_COMMANDES_DETAIL.md**
   - Architecture entités
   - Services business logic
   - Tests strategies

3. **MODULE_RECLAMATIONS_DETAIL.md**
   - State machine pattern
   - Gemini API integration

### Pour un **Développeur Frontend**:
1. **MODULE_COMMANDES_DETAIL.md** (Checkout complex UI)
2. **MODULE_UTILISATEURS_DETAIL.md** (Auth, OAuth, profile)
3. **MODULE_ARTICLES_DETAIL.md** (Blog templates)

### Pour un **DevOps/QA**:
1. **MODULE_COMMANDES_DETAIL.md** (Payment webhook, carrier webhooks)
2. **MODULE_UTILISATEURS_DETAIL.md** (Security, JWT setup)
3. **SPRINT_BACKLOG_COMPLET.md** (Overall testing strategy)

### Pour un **Product Owner**:
1. **SPRINT_BACKLOG_COMPLET.md** (entire document)
2. Section critères d'acceptation de chaque US

---

## 📊 STATISTIQUES

### Code Estimate
```
Total LOC Estimated: ~15,000+ lines
  ├─ Backend (SQL + PHP): ~8,000 lines
  ├─ Frontend (HTML + JS): ~3,000 lines
  ├─ Templates (Twig): ~2,500 lines
  ├─ Tests (PHP): ~1,500 lines
  └─ Config/Docs: ~500 lines

Entities: 20+
  ├─ 13 existing (Produit, Article, etc)
  └─ 7 new (Coupon, Permission, etc)

Controllers: 25+
  ├─ 15 Client/Admin (CRUD)
  └─ 10 API (REST)

Services: 20+
  ├─ Business logic services
  └─ Integration services (Stripe, Gemini, etc)

Tests: 250+ test cases
  ├─ Unit tests: ~100
  ├─ Integration tests: ~100
  └─ API tests: ~50
```

### Complexity by Module

```
SIMPLE (Easy):
  - Categories CRUD (8 pts)
  - Articles CRUD (18 pts)

MEDIUM (Moderate):
  - Produits CRUD (21 pts)
  - Réclamations CRUD (16 pts)

COMPLEX (Hard):
  - Utilisateurs (16 pts) - RBAC, Audit, GDPR
  - Articles API (16 pts) - Search, Recommendations
  - Commandes CRUD (18 pts) - Payment, Inventory

VERY COMPLEX (Very Hard):
  - Modération IA (34 pts) - Gemini API, Archives
  - Authentification API (18 pts) - JWT, OAuth2
  - Réclamations API (14 pts) - Classification, Escalation
  - Commandes API (14 pts) - Tracking, Webhooks
```

---

## 🔗 DÉPENDANCES ENTRE USER STORIES

```
                    ┌──────────────────────────────────┐
                    │  SPRINT 1 (FOUNDATION)          │
                    │  ✅ Produits CRUD (21)          │
                    │  ✅ Modération IA (34)          │
                    └────────────────┬─────────────────┘
                                     │
                    ┌────────────────▼──────────────────┐
                    │  SPRINT 2 (CONTENT BASE)         │
                    │  Articles CRUD (18)              │
                    │  Articles API (16) ◄─┐           │
                    │  Catégories CRUD (8)─┘           │
                    │  Catégories API (12)             │
                    └────────────────┬──────────────────┘
                                     │
        ┌────────────────────────────┼─────────────────────────┐
        │                            │                         │
        │  ┌─────────────────────────▼──────────────────┐       │
        │  │  SPRINT 3 (BUSINESS)                      │       │
        │  │  Réclamations CRUD (16)                   │       │
        │  │  Réclamations IA (14)                     │       │
        │  │  Commandes CRUD (18)                      │       │
        │  │  Commandes API (14)                       │       │
        │  └─────────────────────────┬──────────────────┘       │
        │                            │                         │
        │  ┌─────────────────────────▼──────────────────┐       │
        │  │  SPRINT 4 (SECURITY & COMMS)              │       │
        │  │  Utilisateurs (16) ◄─────────────────────────┐    │
        │  │  Authentification API (18) ◄───────────┐    │    │
        │  │  Notifications (12) ◄──────────────────┴────┘    │
        │  └───────────────────────────────────────┘       │
        │                                                   │
        │  Users foundation needed for:                    │
        │  - Permission checks (all modules)              │
        │  - Audit trails                                 │
        │  - Notifications                                │
        └───────────────────────────────────────────────────┘
```

---

## ✅ DEFINITION OF DONE (Universal)

Chaque User Story DOIT avoir:

```
CODE QUALITY:
  ☑ Code written (100%)
  ☑ Code reviewed (peer review)
  ☑ Tests written (> 80% coverage)
  ☑ Tests passing (100%)
  ☑ No security issues (security review)
  ☑ Performance OK (< target times)
  ☑ Linting passed (PSR-12 for PHP)

DOCUMENTATION:
  ☑ README section written
  ☑ API docs (Swagger/OpenAPI)
  ☑ Database migrations documented
  ☑ Code comments for complex logic
  ☑ User guide (if applicable)

TESTING:
  ☑ Unit tests (>80% coverage)
  ☑ Integration tests (all flows)
  ☑ API tests (all endpoints)
  ☑ Error scenarios tested
  ☑ Edge cases covered
  ☑ Manual testing done

DEPLOYMENT:
  ☑ Database migrations executable
  ☑ Environment variables listed (if needed)
  ☑ Dependencies added (composer.json)
  ☑ Can deploy to staging
  ☑ No breaking changes
  ☑ Rollback plan documented

ACCEPTANCE:
  ☑ All acceptance criteria met
  ☑ QA approved
  ☑ Product owner approved
  ☑ Merged to main branch
  ☑ Release notes prepared
```

---

## 🛠️ TECHNOLOGIES UTILISÉES

```
Backend:
  - Symfony 6+
  - PHP 8.1+
  - MySQL 8.0+
  - Doctrine ORM
  - JWT (LexikJWTAuthenticationBundle)
  - CQRS pattern (optional)

APIs Externes:
  - Google Gemini API (IA, modération, réclamations)
  - Google Translate API (articles multilingue)
  - Stripe API (paiements)
  - DHL/UPS/FedEx APIs (tracking)
  - Twilio API (SMS)
  - OAuth2 (Google, Facebook)

Frontend:
  - Twig (templates)
  - Bootstrap/Tailwind (CSS)
  - JavaScript (Vanilla JS + Fetch API)
  - Service Worker (push notifications)

Testing:
  - PHPUnit (unit tests)
  - Behat (BDD)
  - PHPStan (static analysis)
  - OWASP testing tools

DevOps:
  - Docker
  - GitHub Actions (CI/CD)
  - Redis (caching)
  - RabbitMQ (message queue) [optionnel]
```

---

## 📅 TIMELINE RECOMMANDÉE

```
Week 1-2 (Sprint 1): 55 pts
  - Produits CRUD ✅
  - Modération IA ✅
  
Week 3-4 (Sprint 2): 54 pts
  - Articles CRUD
  - Articles API
  - Catégories (partial)

Week 5-6 (Sprint 3): 62 pts
  - Réclamations
  - Commandes CRUD
  - Commandes API (partial)

Week 7-8 (Sprint 4): 46 pts
  - Utilisateurs
  - Authentification
  - Notifications

TOTAL: 8 semaines = ~2 mois
Team size: 4-5 developers
```

---

## 🚀 QUICK START FOR DEVELOPERS

### 1. Clone & Setup
```bash
git clone <repo>
cd pharmax
composer install
npm install
cp .env.example .env
php bin/console doctrine:migration:migrate
npm run dev
```

### 2. Read Documentation
- Start with: **SPRINT_BACKLOG_COMPLET.md**
- Deep dive: **MODULE_*_DETAIL.md** for your assigned features

### 3. Run Tests
```bash
./vendor/bin/phpunit              # Unit tests
./vendor/bin/behat                # BDD scenarios
php bin/console lint:twig         # Template validation
php -S localhost:8000 -t public   # Dev server
```

### 4. Database
```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load
```

### 5. Environment Variables
```
GEMINI_API_KEY=...
STRIPE_PUBLIC_KEY=...
STRIPE_SECRET_KEY=...
GOOGLE_OAUTH_ID=...
GOOGLE_OAUTH_SECRET=...
JWT_SECRET=...
```

---

## 📞 SUPPORT & QUESTIONS

- **Architecture Questions** → Check SPRINT_BACKLOG_COMPLET.md / Dependencies
- **Module Implementation** → Read MODULE_*_DETAIL.md
- **API Spec** → Module detail + Swagger docs
- **Database Schema** → module detail "Base de Données"
- **Testing Strategy** → Definition of Done section

---

**Document Last Updated**: 25 février 2026  
**Next Review**: After Sprint 2 completion  
**Maintained By**: Project Lead / Scrum Master

