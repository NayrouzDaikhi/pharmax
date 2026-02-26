# 🎯 SPRINT BACKLOG COMPLET - PHARMAX

**Sprint**: Multi-Sprint Planning (Sprints 1-3)  
**Projet**: PHARMAX - Gestion Produits & Articles  
**Statut**: En planification  
**Capacité**: 165 points totaux

---

## 📊 ROADMAP GLOBALE

```
SPRINT 1 (Complété) ✅
├─ US#1: CRUD Produits (21 pts)
└─ US#2: API Modération IA (34 pts)

SPRINT 2 (Current) 🔄
├─ US#3: CRUD Articles (18 pts)
├─ US#4: API Recherche d'Articles (16 pts)
├─ US#5: CRUD Catégories (8 pts)
└─ US#6: API Tags/Filtres (12 pts)

SPRINT 3 (Planifié) 📋
├─ US#7: CRUD Réclamations (16 pts)
├─ US#8: API Réponses Automatiques (14 pts)
├─ US#9: CRUD Commandes (18 pts)
└─ US#10: API Tracking Commandes (14 pts)

SPRINT 4 (Futur) 🔮
├─ US#11: Gestion Utilisateurs (16 pts)
├─ US#12: API Authentification & Profils (18 pts)
└─ US#13: Système de Notifications (12 pts)
```

---

# 📦 MODULE 1: PRODUITS

## USER STORY #1 (SPRINT 1) - ✅ TERMINÉE
**Titre**: Gestion CRUD des Produits  
**Points**: 21  
**Status**: ✅ COMPLÉTÉE  
**Description**: Gérer l'inventaire des médicaments

**Critères d'Acceptation**:
- ✅ Créer produit (nom, description, prix, quantité, catégorie)
- ✅ Lister tous les produits avec pagination
- ✅ Afficher détails produit
- ✅ Modifier produit existant
- ✅ Supprimer produit
- ✅ Upload image produit
- ✅ Validations complètes

**Fichiers**:
- `src/Entity/Produit.php` ✅
- `src/Controller/ProduitController.php` ✅
- `src/Repository/ProduitRepository.php` ✅
- `src/Form/ProduitType.php` ✅
- `templates/produit/*.twig` ✅

---

## USER STORY #2 (SPRINT 2) - 🔄 EN COURS
**Titre**: API Avancée - Gestion des Produits  
**Points**: 16  
**Status**: 🔄 Planifié  
**Description**: API REST complète pour manipulation des produits

**Critères d'Acceptation**:
- ⏳ Endpoint GET /api/produits (liste complète avec pagination)
- ⏳ Endpoint GET /api/produits/{id} (détails produit)
- ⏳ Endpoint POST /api/produits (créer produit)
- ⏳ Endpoint PUT /api/produits/{id} (modifier produit)
- ⏳ Endpoint DELETE /api/produits/{id} (supprimer produit)
- ⏳ Filtrage par catégorie, prix, disponibilité
- ⏳ Tri par prix, nom, date création
- ⏳ Recherche par mots-clés
- ⏳ Sérialization JSON avec Symfony Serializer
- ⏳ Validation et gestion erreurs HTTP (400, 404, 409)

**Tâches Techniques**:
- [ ] Créer `Api/ProduitApiController`
- [ ] Implémenter tous les endpoints REST
- [ ] Ajouter QueryBuilder pour filtrage/recherche
- [ ] Configurer serializer groups
- [ ] Tests API complets (20+ cas)
- [ ] Documentation OpenAPI/Swagger
- [ ] Rate limiting & caching

**Fichiers à créer**:
- `src/Controller/Api/ProduitApiController.php` (NEW)
- `tests/Api/ProduitApiTest.php` (NEW)
- `docs/api/produits.md` (NEW)

**Tests Acceptés**:
```bash
# Test 1: Lister tous les produits
GET /api/produits?page=1&limit=20
→ 200 OK + Array de Produits

# Test 2: Filtrer par catégorie
GET /api/produits?categorie=Medicaments&page=1
→ 200 OK + Produits filtrés

# Test 3: Recherche par nom
GET /api/produits?search=Paractamol
→ 200 OK + Résultats pertinents

# Test 4: Tri par prix
GET /api/produits?sort=-prix&page=1
→ 200 OK + Trié décroissant

# Test 5: Créer produit
POST /api/produits
Body: { nom, description, prix, quantite, categorie }
→ 201 Created + Location header

# Test 6: Modifier produit
PUT /api/produits/{id}
Body: { nom, prix, quantite }
→ 200 OK + Produit mis à jour

# Test 7: Supprimer produit
DELETE /api/produits/{id}
→ 204 No Content

# Test 8: Produit non trouvé
GET /api/produits/99999
→ 404 Not Found
```

| Points | Compl. | Status |
|--------|--------|--------|
| 16 | 0% | 📋 To Do |

---

# 📰 MODULE 2: ARTICLES

## USER STORY #3 (SPRINT 2) - 📋 À FAIRE
**Titre**: Gestion CRUD des Articles  
**Points**: 18  
**Status**: 📋 Planifié  
**Description**: Créer et gérer les articles du blog

**Critères d'Acceptation**:
- ⏳ Créer article (titre, contenu, image, catégorie)
- ⏳ Lister articles avec pagination
- ⏳ Afficher article détaillé avec commentaires
- ⏳ Modifier article existant
- ⏳ Supprimer article
- ⏳ Compteur likes/vues
- ⏳ Multilingue (FR/EN)
- ⏳ Traduction automatique via Google Translate API

**Tâches Techniques**:
- [ ] Créer `ArticleController` (list, show, new, edit, delete)
- [ ] Améliorer `ArticleRepository` (recherche, filtrage)
- [ ] Créer `ArticleType` FormBuilder
- [ ] Templates article (index.twig, show.twig, edit.twig)
- [ ] Intégration Google Translate
- [ ] Tests complets

**Fichiers à créer/modifier**:
- `src/Controller/ArticleController.php` (ENHANCE)
- `src/Repository/ArticleRepository.php` (ENHANCE)
- `src/Form/ArticleType.php` (CREATE)
- `templates/article/*.twig` (CREATE)

**Tests Acceptés**:
```bash
# Test 1: Créer article
POST /article/new
Form: { titre, contenu, image }
→ 302 Redirect + Flash "Article créé"

# Test 2: Afficher article
GET /article/{id}
→ 200 OK + Article + Commentaires

# Test 3: Modifier article
PUT /article/{id}/edit
Form: { titre, contenu }
→ 200 OK + Mise à jour

# Test 4: Traduction
GET /article/{id}/translate?lang=en
→ 200 OK + Article traduit

# Test 5: Supprimer article
DELETE /article/{id}
→ 302 Redirect + Flash "Supprimé"
```

| Points | Compl. | Status |
|--------|--------|--------|
| 18 | 0% | 📋 To Do |

---

## USER STORY #4 (SPRINT 2) - 📋 À FAIRE
**Titre**: API Avancée - Recherche & Recommandations  
**Points**: 16  
**Status**: 📋 Planifié  
**Description**: API intelligent pour articles avec recherche plein-texte

**Critères d'Acceptation**:
- ⏳ Endpoint GET /api/articles (liste avec pagination)
- ⏳ Endpoint GET /api/articles/search (recherche plein-texte)
- ⏳ Endpoint GET /api/articles/recommandes (recommandations IA)
- ⏳ Endpoint GET /api/articles/{id}/comments (commentaires modérés)
- ⏳ Endpoint POST /api/articles/{id}/likes (like article)
- ⏳ Filtrage par date, auteur, catégorie
- ⏳ Tri par popularité (likes/vues)
- ⏳ Recherche multi-langue
- ⏳ Caching des résultats

**Tâches Techniques**:
- [ ] Implémenter recherche Elasticsearch (optionnel)
- [ ] Créer `ArticleSearchService`
- [ ] Endpoint recommandations (basé sur vues de l'utilisateur)
- [ ] Caching Redis (24h)
- [ ] Rate limiting (100 req/min)
- [ ] Tests de performance

**Fichiers à créer**:
- `src/Controller/Api/ArticleApiController.php` (NEW)
- `src/Service/ArticleSearchService.php` (NEW)
- `tests/Api/ArticleApiTest.php` (NEW)

**Exemple Réponse API**:
```json
GET /api/articles?search=sante&lang=en
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "titre": "Health Tips",
      "resume": "10 tips para la santé...",
      "likes": 145,
      "vues": 2340,
      "created_at": "2026-02-10T10:30:00Z",
      "auteur": "Dr. Smith"
    }
  ],
  "pagination": {
    "page": 1,
    "limit": 20,
    "total": 245
  }
}
```

| Points | Compl. | Status |
|--------|--------|--------|
| 16 | 0% | 📋 To Do |

---

# 🏷️ MODULE 3: CATÉGORIES & TAGS

## USER STORY #5 (SPRINT 2) - 📋 À FAIRE
**Titre**: Gestion CRUD des Catégories  
**Points**: 8  
**Status**: 📋 Planifié  
**Description**: Gérer catégories de produits/articles

**Critères d'Acceptation**:
- ⏳ Créer catégorie (nom, description, couleur)
- ⏳ Lister catégories
- ⏳ Modifier catégorie
- ⏳ Supprimer catégorie
- ⏳ Associer produits/articles à catégories
- ⏳ Hiérarchie (parent/enfant pour sous-catégories)

**Tâches Techniques**:
- [ ] Controller CRUD
- [ ] Templates formulaires
- [ ] Validation cascades Doctrine
- [ ] Tests

**Fichiers**:
- `src/Controller/CategorieController.php` (ENHANCE)
- `src/Form/CategorieType.php` (CREATE)

| Points | Compl. | Status |
|--------|--------|--------|
| 8 | 0% | 📋 To Do |

---

## USER STORY #6 (SPRINT 2) - 📋 À FAIRE
**Titre**: API Filtrage Avancé - Catégories & Tags  
**Points**: 12  
**Status**: 📋 Planifié  
**Description**: Endpoints pour récupérer prod/articles filtrés

**Critères d'Acceptation**:
- ⏳ GET /api/categories (liste)
- ⏳ GET /api/produits?categorie=X&prix_min=Y&prix_max=Z
- ⏳ GET /api/articles?tags=X,Y,Z
- ⏳ Facettes (count par catégorie)
- ⏳ Agrégations (prix moyen, articles moyens par catégorie)

**Fichiers**:
- `src/Controller/Api/CategorieApiController.php` (NEW)

| Points | Compl. | Status |
|--------|--------|--------|
| 12 | 0% | 📋 To Do |

---

# 🎯 MODULE 4: RÉCLAMATIONS

## USER STORY #7 (SPRINT 3) - 📋 À FAIRE
**Titre**: Gestion CRUD des Réclamations  
**Points**: 16  
**Status**: 📋 Planifié  
**Description**: Système de gestion des réclamations clients

**Critères d'Acceptation**:
- ⏳ Créer réclamation (titre, description, type)
- ⏳ Lister réclamations avec statuts (En attente, En cours, Résolu)
- ⏳ Afficher détails réclamation
- ⏳ Modifier statut (Admin uniquement)
- ⏳ Assigner à équipe support
- ⏳ Priorités (Faible, Normal, Urgent)
- ⏳ Timeline historique des changements

**Tâches Techniques**:
- [ ] Améliorer `ReclamationController`
- [ ] Créer workflow statuts (state machine)
- [ ] Emails notifications (assignation, résolution)
- [ ] Dashboard réclamations (Admin)
- [ ] Tests

**Fichiers à modifier**:
- `src/Controller/AdminReclamationController.php` (ENHANCE)
- `src/Service/ReclamationStatusService.php` (CREATE)
- `templates/admin/reclamation/*.twig` (ENHANCE)

**Exemple Workflow**:
```
Créée (En attente)
  ↓
Assignée à Agent
  ↓
En cours d'investigation
  ↓
Response agnt / Résolvenue
  ↓
Clôturée OU Réouverte
```

| Points | Compl. | Status |
|--------|--------|--------|
| 16 | 0% | 📋 To Do |

---

## USER STORY #8 (SPRINT 3) - 📋 À FAIRE
**Titre**: API Avancée - Réponses Automatiques (Chatbot)  
**Points**: 14  
**Status**: 📋 Planifié  
**Description**: IA pour répondre automatiquement aux réclamations

**Critères d'Acceptation**:
- ⏳ Analyser type de réclamation (retard, qualité, prix, autre)
- ⏳ Générer réponse automatique via Gemini API
- ⏳ Proposer solutions (remboursement, remplacement)
- ⏳ Escalade si problème complexe → Agent humain
- ⏳ Tracker satisfaction client (1-5 stars)
- ⏳ Endpoint POST /api/reclamations avec IA

**Tâches Techniques**:
- [ ] Créer `ReclamationAiService` (Gemini integration)
- [ ] Classification automatique réclamations
- [ ] Prompt engineering pour solutions
- [ ] API endpoint avec classification
- [ ] Tests IA (20+ cas)

**Fichiers à créer**:
- `src/Service/ReclamationAiService.php` (NEW)
- `src/Controller/Api/ReclamationApiController.php` (NEW)

**Exemple Classification**:
```
Réclamation: "J'ai reçu ma commande en retard"
  ↓
Classification: RETARDED_DELIVERY (85% confidence)
  ↓
Réponse IA: "Nous nous excusons. Vous êtes éligible pour:
  1. Remboursement 15% de la commande
  2. Bon d'achat 20€
  Préférence?"
  ↓
Track if customer satisfied (feedback form)
```

| Points | Compl. | Status |
|--------|--------|--------|
| 14 | 0% | 📋 To Do |

---

# 🛒 MODULE 5: COMMANDES & LIGNES DE COMMANDE

## USER STORY #9 (SPRINT 3) - 📋 À FAIRE
**Titre**: Gestion CRUD des Commandes  
**Points**: 18  
**Status**: 📋 Planifié  
**Description**: Gestion complète du cycle de commande

**Critères d'Acceptation**:
- ⏳ Créer commande (sélectionner produits, quantités)
- ⏳ Panier persistant (session/DB)
- ⏳ Lister les commandes utilisateur
- ⏳ Afficher détails commande (lignes, total, statut)
- ⏳ Modifier commande (avant paiement)
- ⏳ Annuler commande
- ⏳ Historique des modifications
- ⏳ Calculs TVA/Port automatiques
- ⏳ Génération facture PDF

**Tâches Techniques**:
- [ ] Controller commandes utilisateur
- [ ] Gestionnaire panier (Panier service)
- [ ] Lignes de commande (CRUD)
- [ ] Calcul totaux (prix + TVA + port)
- [ ] PDF generation (TCPDF/dompdf)
- [ ] Tests transactionnels

**Fichiers à créer/modifier**:
- `src/Controller/CommandeController.php` (ENHANCE)
- `src/Service/PanierService.php` (CREATE)
- `src/Service/FactureService.php` (CREATE)
- `templates/commande/*.twig` (CREATE)

**Statuts des Commandes**:
```
En attente (panier) → En cours (paiement) → Env. / Livrée / Annulée
                ↓
        Remboursement complet (si annulée)
```

| Points | Compl. | Status |
|--------|--------|--------|
| 18 | 0% | 📋 To Do |

---

## USER STORY #10 (SPRINT 3) - 📋 À FAIRE
**Titre**: API Avancée - Tracking & Notifications  
**Points**: 14  
**Status**: 📋 Planifié  
**Description**: API pour suivi commandes temps réel

**Critères d'Acceptation**:
- ⏳ Endpoint GET /api/commandes (mes commandes)
- ⏳ Endpoint GET /api/commandes/{id} (détails + tracking)
- ⏳ Tracking en temps réel (position/statut)
- ⏳ Notifications email à chaque changement de statut
- ⏳ SMS optionnel (Twilio)
- ⏳ Webhook pour intégrations externes
- ⏳ Estimée de livraison
- ⏳ Endpoint pour modifier couleur d'un produit (personnalisation)

**Tâches Techniques**:
- [ ] API REST pour commandes
- [ ] Envoi notifications (email, SMS)
- [ ] Webhooks pour partenaires
- [ ] Websockets pour tracking real-time (optionnel)
- [ ] Tests API

**Fichiers à créer**:
- `src/Controller/Api/CommandeApiController.php` (NEW)
- `src/Service/NotificationService.php` (ENHANCE)
- `src/EventListener/CommandeStatusListener.php` (NEW)

**Exemple Réponse API**:
```json
GET /api/commandes/42/tracking
{
  "id": 42,
  "statut": "En cours de livraison",
  "progression": 75,
  "etapes": [
    { "date": "2026-02-24", "statut": "Confirmée", "done": true },
    { "date": "2026-02-24", "statut": "Préparée", "done": true },
    { "date": "2026-02-24", "statut": "En livraison", "done": true },
    { "date": "2026-02-26", "statut": "Livrée", "done": false, "estimated": true }
  ],
  "gps": { "lat": 48.8566, "lng": 2.3522 },
  "chauffeur": "Jean Dupont",
  "numero_suivi": "FR123456789"
}
```

| Points | Compl. | Status |
|--------|--------|--------|
| 14 | 0% | 📋 To Do |

---

# 👤 MODULE 6: UTILISATEURS & AUTHENTIFICATION

## USER STORY #11 (SPRINT 4) - 📋 À FAIRE
**Titre**: Gestion Complète des Utilisateurs  
**Points**: 16  
**Status**: 📋 Planifié  
**Description**: Système complet de gestion des utilisateurs

**Critères d'Acceptation**:
- ⏳ CRUD Utilisateur (créer, lire, modifier, supprimer)
- ⏳ Profil utilisateur (avatar, bio, adresse)
- ⏳ Changement mot de passe
- ⏳ Réinitialisation mot de passe par email
- ⏳ Rôles (ADMIN, USER, MODERATOR, SUPPORT)
- ⏳ Permissions granulaires
- ⏳ Blocage/déblocage utilisateur (Admin)
- ⏳ Export données utilisateur (RGPD)
- ⏳ Suppression compte (soft delete)

**Tâches Techniques**:
- [ ] Améliorer `UserController`
- [ ] Créer `UserProfileController`
- [ ] Réinitialisation mot de passe (tokens)
- [ ] Gestion rôles/permissions (Symfony Security)
- [ ] RGPD export/delete
- [ ] Tests sécurité

**Fichiers à modifier**:
- `src/Controller/UserController.php` (ENHANCE)
- `src/Security/UserVoter.php` (CREATE)
- `templates/user/*.twig` (CREATE)

| Points | Compl. | Status |
|--------|--------|--------|
| 16 | 0% | 📋 À Faire |

---

## USER STORY #12 (SPRINT 4) - 📋 À FAIRE
**Titre**: API Authentification & Profils  
**Points**: 18  
**Status**: 📋 Planifié  
**Description**: API OAuth2 + JWT pour authentification

**Critères d'Acceptation**:
- ⏳ Endpoint POST /api/auth/login (email/password)
- ⏳ Endpoint POST /api/auth/register (inscription)
- ⏳ JWT tokens (access + refresh)
- ⏳ OAuth2 Google (SSO)
- ⏳ OAuth2 Facebook (SSO)
- ⏳ GET /api/me (mon profil)
- ⏳ PUT /api/me (modifier mon profil)
- ⏳ POST /api/me/avatar (upload avatar)
- ⏳ Token refresh automatique
- ⏳ Logout + revocation tokens

**Sécurité**:
- Hash passwords (bcrypt)
- CORS configuration
- Rate limiting authentification (5 tentatives)
- Tokens expiration (access 1h, refresh 7j)
- HTTPS only

**Tâches Techniques**:
- [ ] JWT configuration (LexikJWTAuthenticationBundle)
- [ ] API Controllers (login, register, profile)
- [ ] OAuth2 providers (Google, Facebook)
- [ ] Tests sécurité (SQL injection, CSRF, etc.)

**Fichiers à créer**:
- `src/Controller/Api/AuthApiController.php` (NEW)
- `src/Security/JwtTokenProvider.php` (NEW)
- `tests/Security/AuthSecurityTest.php` (NEW)

**Exemple Login**:
```bash
POST /api/auth/login
{
  "email": "user@example.com",
  "password": "SecurePass123!"
}

Response 200:
{
  "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
  "expires_in": 3600,
  "user": {
    "id": 5,
    "email": "user@example.com",
    "firstName": "John",
    "roles": ["ROLE_USER"]
  }
}
```

| Points | Compl. | Status |
|--------|--------|--------|
| 18 | 0% | 📋 À Faire |

---

## USER STORY #13 (SPRINT 4) - 📋 À FAIRE
**Titre**: Système de Notifications  
**Points**: 12  
**Status**: 📋 Planifié  
**Description**: Notifications multi-canaux (email, SMS, in-app)

**Critères d'Acceptation**:
- ⏳ Center notifications in-app
- ⏳ Emails notifications (transactionnels)
- ⏳ SMS optionnel (Twilio)
- ⏳ Push notifications (web)
- ⏳ Préférences notification utilisateur
- ⏳ Emails templates
- ⏳ Schedule d'envoi (immediate vs batch)

**Événements à notifier**:
- Commande confirmée/livrée
- Réclamation assignée/résolue
- Nouveaux commentaires approuvés
- Nouveau produit/article dans catégorie
- Point de fidélité gagné

**Tâches Techniques**:
- [ ] EVENT SYSTEM Symfony
- [ ] `Notification` entity + repository
- [ ] Email templates
- [ ] SMS integration (optionnel)
- [ ] Notification center UI
- [ ] Tests

**Fichiers à créer**:
- `src/Listener/NotificationListener.php` (NEW)
- `templates/notification/*.twig` (NEW)
- `src/Service/NotificationService.php` (ENHANCE)

| Points | Compl. | Status |
|--------|--------|--------|
| 12 | 0% | 📋 To Do |

---

# 📊 RÉSUMÉ TABLEAU POINTS

| # | USER STORY | Module | Points | Status | Sprint |
|---|-----------|--------|--------|--------|--------|
| 1 | CRUD Produits | Produits | 21 | ✅ Done | 1 |
| 2 | API Modération IA | Commentaires | 34 | ✅ Done | 1 |
| 3 | CRUD Articles | Articles | 18 | 📋 Todo | 2 |
| 4 | API Recherche Articles | Articles | 16 | 📋 Todo | 2 |
| 5 | CRUD Catégories | Catégories | 8 | 📋 Todo | 2 |
| 6 | API Filtrage Catégories | Catégories | 12 | 📋 Todo | 2 |
| 7 | CRUD Réclamations | Réclamations | 16 | 📋 Todo | 3 |
| 8 | API Réponses IA | Réclamations | 14 | 📋 Todo | 3 |
| 9 | CRUD Commandes | Commandes | 18 | 📋 Todo | 3 |
| 10 | API Tracking Commandes | Commandes | 14 | 📋 Todo | 3 |
| 11 | Gestion Utilisateurs | Users | 16 | 📋 Todo | 4 |
| 12 | API Authentification | Users | 18 | 📋 Todo | 4 |
| 13 | Notifications Multi-canal | Notifications | 12 | 📋 Todo | 4 |
| | **TOTAL** | | **227** | | |

---

# 📅 PLANNING SPRINTS

## SPRINT 2 (Semaine du 17-23 février)
**Points**: 70 / Capacity: 55 pts  
⚠️ À réduire: Choisir les 3 US critiques

**Option A (54 pts)**:
- US#3: CRUD Articles (18 pts)
- US#4: API Recherche (16 pts)
- US#5: CRUD Catégories (8 pts)
- US#6: API Filtrage (12 pts) - Split

**Option B (56 pts)**:
- US#3: CRUD Articles (18 pts)
- US#6: API Filtrage (12 pts)
- US#5: CRUD Catégories (8 pts)
- US#4: API Recherche (16 pts) - Sprint suivant

---

## SPRINT 3 (Semaine du 24 mars - 6 avril)
**Points**: 62 pts / Capacity: 55 pts

**Sélection**:
- US#7: CRUD Réclamations (16 pts)
- US#8: API Réponses IA (14 pts)
- US#9: CRUD Commandes (18 pts)
- US#10: API Tracking (14 pts) - Split

---

## SPRINT 4 (Semaine du 14-27 avril)
**Points**: 46 pts / Capacity: 55 pts

**Sélection**:
- US#11: Gestion Utilisateurs (16 pts)
- US#12: API Authentification (18 pts)
- US#13: Notifications (12 pts)

---

# 🎯 PRIORITIES & DEPENDENCIES

## Critical Path (Ordre d'implémentation)
```
1. ✅ US#1 + US#2 (Sprint 1) - DONE
2. → US#3 (Articles CRUD) - Dépend de: Rien
3. → US#4 (Search API) - Dépend de: US#3
4. → US#5 (Catégories) - Dépend de: Rien
5. → US#6 (Filtrage) - Dépend de: US#5
6. → US#7 (Réclamations) - Dépend de: US#11 (Users)
7. → US#8 (Réclamations AI) - Dépend de: US#7
8. → US#9 (Commandes) - Dépend de: US#11 (Users)
9. → US#10 (Tracking) - Dépend de: US#9
10. → US#11 (Users) - Priority HIGH (auth system)
11. → US#12 (Auth API) - Dépend de: US#11
12. → US#13 (Notifications) - Dépend de: US#9 + US#7
```

---

# 💡 DÉTAILS TECHNIQUES IMPORTANTS

## Points Clés pour Chaque Module

### Produits
- Repository: Filtrage prix, catégorie, recherche
- Service: Gestion stock, validation quantités
- API: Serialization avec groups

### Articles
- Attachement commentaires validés
- Traduction multilingue
- Likes/Vues tracking
- Search via QueryBuilder

### Réclamations
- Workflow State Machine
- Événements pour notifications
- IA classification (Gemini)
- Escalade automatique

### Commandes
- Session panier + DB backup
- Calculs TVA/Port génériques
- Génération PDF
- Timeline historique

### Users
- Password reset tokens (24h)
- Rôles/Permissions (Symfony Voter)
- JWT vs Sessions (choisir 1)
- RGPD compliance

---

# ✅ DEFINITION OF DONE (Toutes les US)

Pour que chaque US soit "Done":
- ✅ Code écrit + testé (>90% coverage)
- ✅ Code review approuvé
- ✅ Tests unitaires + intégration passent
- ✅ Documentation API (OpenAPI/Swagger)
- ✅ Temps complexe analysé (DB queries, etc.)
- ✅ Sécurité vérifiée (injection, CSRF, etc.)
- ✅ Merged en `main`
- ✅ Documenté en Markdown

