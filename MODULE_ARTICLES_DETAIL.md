# 📰 MODULE ARTICLES - SPRINT 2

**Status**: 📋 En Planification  
**User Stories**: US#3 + US#4  
**Points Totaux**: 34 pts  
**Durée Estimée**: 2 semaines

---

## 🎯 USER STORY #3: CRUD ARTICLES (18 pts)

### Description
En tant que **responsable contenu**, je veux **créer, modifier et supprimer des articles** afin de **maintenir un blog à jour avec les dernières informations pharmaceutiques**.

### Critères d'Acceptation

```
✓ Créer article
  - Champs: Titre, Contenu, Image, Date création, Statut
  - Validation: Titre (3-255 chars), Contenu (obligatoire)
  - Image: Format jpg/png, max 5MB
  - Sauvegarde auto dans BD

✓ Afficher article
  - Page détail avec titre, contenu, image
  - Commentaires validés listés
  - Nombre vues + likes visible
  - Lien de partage social

✓ Modifier article
  - Édition du contenu/titre/image
  - Historique des modifications (audit trail)
  - Sauvegarde brouillon automatique

✓ Supprimer article
  - Soft delete (archive articles)
  - Admin uniquement
  - Redirection 404 après suppression

✓ Lister articles
  - Pagination (20 articles/page)
  - Tri: Date (desc), Likes (desc), Vues (desc)
  - Filtres: Statut (publié/brouillon), Date range
  - Lecture estimée affichée

✓ Multilingue
  - Champ contenu_en pour version anglaise
  - Bouton "Translate to English" (auto via API)
  - Switching langue côté client
```

### Tâches Techniques

```
BACKEND:
[ ] ArticleController
    ├─ GET /article → liste articles
    ├─ GET /article/new → formulaire création
    ├─ POST /article → sauvegarder
    ├─ GET /article/{id} → afficher
    ├─ GET /article/{id}/edit → formulaire édition
    ├─ PUT /article/{id} → sauvegarde édition
    └─ DELETE /article/{id} → supprimer

[ ] Améliorer ArticleRepository
    ├─ findAllPublished()
    ├─ findByTitleOrContent(string $search)
    ├─ findByDateRange(DateTime $from, DateTime $to)
    ├─ findMostViewed(int $limit)
    └─ findMostLiked(int $limit)

[ ] Améliorer Article Entity
    ├─ Ajouter: vues (int), likes (int)
    ├─ Ajouter: statut (enum: BROUILLON, PUBLIÉ)
    ├─ Ajouter: slug (pour URL)
    ├─ Ajouter: seo_title, seo_description (SEO)
    ├─ Softdelete via: deletedAt (nullable DateTime)
    └─ Ajouter: authorNote (pour modifications)

[ ] ArticleType FormBuilder
    ├─ TextType: titre
    ├─ TextareaType: contenu
    ├─ TextareaType: contenu_en
    ├─ FileType: image
    ├─ ChoiceType: statut
    ├─ DateType: date_publication
    └─ Validators: @NotBlank, @Length, etc.

[ ] Templates
    ├─ templates/article/index.html.twig (liste)
    ├─ templates/article/show.html.twig (détail)
    ├─ templates/article/form.html.twig (créer/éditer)
    ├─ templates/article/fragments/sidebar.twig (articles populaires)
    └─ templates/article/fragments/comments.twig (commentaires)

[ ] Image Upload Service
    └─ Gérer upload, validation, stockage

[ ] Tests
    ├─ ArticleControllerTest (CRUD operations)
    ├─ ArticleRepositoryTest (queries)
    ├─ ArticleEntityTest (validation)
    └─ Image upload test
```

### Fichiers à Créer/Modifier

| Fichier | Action | Détails |
|---------|--------|---------|
| `src/Entity/Article.php` | ENHANCE | Ajouter: vues, likes, statut, slug, deletedAt |
| `src/Repository/ArticleRepository.php` | ENHANCE | Ajouter 5+ méthodes recherche/filtrage |
| `src/Controller/ArticleController.php` | CREATE | CRUD complet (7 méthodes) |
| `src/Form/ArticleType.php` | CREATE | FormBuilder pour article |
| `src/Service/ImageUploadService.php` | CREATE | Gestion uploads images |
| `templates/article/index.html.twig` | CREATE | Liste articles avec pagination |
| `templates/article/show.html.twig` | CREATE | Détail article + commentaires |
| `templates/article/form.html.twig` | CREATE | Formulaire créer/éditer |
| `tests/Controller/ArticleControllerTest.php` | CREATE | Tests CRUD |
| `public/uploads/articles/` | FOLDER | Dossier images |

### Base de Données

```sql
-- Modifications Article entity
ALTER TABLE article 
  ADD COLUMN vues INT DEFAULT 0,
  ADD COLUMN likes INT DEFAULT 0,
  ADD COLUMN statut VARCHAR(50) DEFAULT 'BROUILLON',
  ADD COLUMN slug VARCHAR(255) UNIQUE,
  ADD COLUMN seo_title VARCHAR(255),
  ADD COLUMN seo_description TEXT,
  ADD COLUMN author_note TEXT,
  ADD COLUMN deleted_at DATETIME NULL;

-- Spécialst pour recherche rapide
CREATE INDEX idx_article_statut_date ON article(statut, created_at DESC);
CREATE INDEX idx_article_likes ON article(likes DESC);
CREATE INDEX idx_article_slug ON article(slug);
CREATE FULLTEXT INDEX idx_article_search ON article(titre, contenu);
```

### Exemples Cas Test

```php
// Test 1: Créer article
POST /article/new
Form {
  titre: "Prévention grippe 2026",
  contenu: "Lorem ipsum dolor...",
  contenu_en: "Lorem ipsum dolor... (EN)",
  image: [file],
  statut: "PUBLIÉ"
}
→ 302 Redirect /article/{id}
→ Flash message: "✓ Article créé avec succès"
→ Base de données: INSERT

// Test 2: Afficher article
GET /article/42
→ 200 OK
→ Affiche: titre, contenu, image, commentaires, likes=0
→ Incrémente vues: +1 dans BD

// Test 3: Modifier article
GET /article/42/edit
→ Toutes les données pré-remplies
PUT /article/42
Body: { titre: "Nouveau titre" }
→ 302 Redirect /article/42
→ Flash: "✓ Mise à jour"
→ authorNote: "Modifié le 2026-02-15 par Admin"

// Test 4: Supprimer article (Soft Delete)
DELETE /article/42
→ 302 Redirect
→ deleted_at = NOW()
→ GET /article/42 → 404

// Test 5: Lister articles
GET /article?page=1&sort=-likes&statut=PUBLIÉ
→ 200 OK
→ Max 20 articles avec pagination
→ Sortés par likes décroissants
```

### Architecture

```
Article
  ├─ id (PK)
  ├─ titre (string, 255)
  ├─ contenu (text)
  ├─ contenu_en (text, nullable)
  ├─ image (string, 255)
  ├─ vues (int) [NEW]
  ├─ likes (int) [NEW]
  ├─ statut (enum: BROUILLON, PUBLIÉ) [NEW]
  ├─ slug (string, 255, unique) [NEW]
  ├─ seo_title (string, 255) [NEW]
  ├─ seo_description (text) [NEW]
  ├─ author_note (text) [NEW]
  ├─ deleted_at (datetime, nullable) [NEW]
  ├─ created_at (datetime)
  ├─ updated_at (datetime)
  ├─ 1:M → Commentaire (validés)
  └─ 0:M → CommentaireArchive (rejetés)
```

### KPIs de Succès

| KPI | Cible | Métrique |
|-----|-------|----------|
| **Couverture Tests** | > 95% | Code coverage |
| **Temps créer article** | < 2min | UX |
| **Erreurs validation** | 0 | Robustesse |
| **Temps chargement page** | < 500ms | Performance |

---

## 🔌 USER STORY #4: API AVANCÉE - RECHERCHE ARTICLES (16 pts)

### Description
En tant que **développeur mobile**, je veux une **API REST pour récupérer et chercher les articles** afin de **pouvoir intégrer le blog dans mon app mobile**.

### Endpoints API

```
GET  /api/articles
     → Lister tous articles (paginated, 20 par défaut)
     → Filter: statut, date_min, date_max, author
     → Sort: -created_at, -likes, -vues
     → Response: 200 OK + Array[Article]

GET  /api/articles/search?q=grippe&lang=fr
     → Recherche plein-texte sur titre + contenu
     → Multi-langue support
     → Response: 200 OK + Array[Article found]
     → Filter par langue: ?lang=en|fr|all

GET  /api/articles/recommandes?user_id=5
     → Articles recommandés basés sur historique utilisateur
     → ML: Utilise les articles visités précédemment
     → Response: 200 OK + Array[Article recommended]

GET  /api/articles/{id}
     → Affiche UN article avec tous les détails
     → +1 vues automatiquement
     → Response: 200 OK + Article object
     → 404 Not Found si n'existe pas

GET  /api/articles/{id}/comments
     → Commentaires validés de cet article
     → Paginated, filtres, tri
     → Response: 200 OK + Array[Commentaire]

POST /api/articles/{id}/like
     → Like un article (idempotent)
     → Envoyer: { user_id }
     → Response: 200 OK + { likes_count: 145 }
```

### Requests/Responses Examples

```bash
# Request 1: Lister articles avec filtres
GET /api/articles?page=1&limit=20&statut=PUBLIÉ&sort=-likes
Accept: application/json

Response 200:
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "titre": "Prévention grippe",
      "excerpt": "Lorem ipsum dolor...",
      "image": "/uploads/articles/grippe.jpg",
      "likes": 145,
      "vues": 3200,
      "created_at": "2026-02-10T10:30:00Z",
      "slug": "prevention-grippe"
    },
    ...
  ],
  "pagination": {
    "page": 1,
    "limit": 20,
    "total": 245,
    "pages": 13
  }
}

# Request 2: Recherche plein-texte
GET /api/articles/search?q=covid&lang=fr
Accept: application/json

Response 200:
{
  "status": "success",
  "query": "covid",
  "results": 12,
  "data": [
    {
      "id": 5,
      "titre": "COVID-19: Symptômes et prévention",
      "excerpt": "Article talks about COVID vaccines...",
      "relevance": 0.95,
      "matches": ["COVID-19", "prévention"]
    }
  ]
}

# Request 3: Recommandations personnalisées
GET /api/articles/recommandes?user_id=5&limit=5
Accept: application/json
Authorization: Bearer eyJhbGc...

Response 200:
{
  "status": "success",
  "recommendations": [
    {
      "id": 12,
      "titre": "Santé cardiovasculaire",
      "score": 0.87,
      "reason": "Similaire à 'Prevention grippe' que vous avez lue"
    }
  ]
}

# Request 4: Like un article
POST /api/articles/42/like
Content-Type: application/json
Authorization: Bearer eyJhbGc...

Body:
{
  "user_id": 5
}

Response 200:
{
  "status": "success",
  "article_id": 42,
  "likes_count": 146,
  "user_liked": true
}
```

### Tâches Techniques

```
[ ] Api/ArticleApiController
    ├─ getArticles() - GET /api/articles
    ├─ searchArticles() - GET /api/articles/search
    ├─ getRecommendations() - GET /api/articles/recommandes
    ├─ getArticle() - GET /api/articles/{id}
    ├─ getArticleComments() - GET /api/articles/{id}/comments
    └─ likeArticle() - POST /api/articles/{id}/like

[ ] ArticleSearchService (NEW)
    ├─ search(string $query, string $lang = 'fr')
    ├─ searchFullText(string $q) - Elasticsearch-ready
    ├─ getRecommendations(int $userId) - ML-ready
    └─ rankResults(array $results, string $query)

[ ] Enhanced ArticleRepository
    ├─ findBySearchQuery(string $q, string $lang)
    ├─ findRecommendedFor(int $userId)
    ├─ findMostLikedByMonth(int $month, int $year)
    └─ findTrendingArticles(int $limit = 10)

[ ] Serialization
    ├─ ArticleNormalizer (custom serializer)
    ├─ Groups: 'article:list', 'article:detail'
    ├─ Truncate contenu pour list (excerpt)
    └─ Inclure relations (comments count)

[ ] Caching Stratégie
    ├─ Cache liste articles (24h invalidation)
    ├─ Cache article détail (7j)
    ├─ Cache recommandations (1 semaine per user)
    └─ Tag-based invalidation

[ ] Tests API
    ├─ ArticleApiControllerTest
    ├─ 30+ test cases (success + errors)
    ├─ Performance tests (< 500ms)
    └─ Pagination tests

[ ] Documentation OpenAPI
    └─ Swagger spec pour tous les endpoints
```

### Response Format Standard

```json
Success:
{
  "status": "success",
  "data": [ ... ],
  "pagination": { "page": 1, "total": 100 }
}

Error 400 (Bad Request):
{
  "status": "error",
  "error": {
    "code": "INVALID_FILTER",
    "message": "Unknown filter: invalid_param"
  }
}

Error 404 (Not Found):
{
  "status": "error",
  "error": {
    "code": "ARTICLE_NOT_FOUND",
    "message": "Article with ID 999 not found"
  }
}

Error 500 (Server Error):
{
  "status": "error",
  "error": {
    "code": "SEARCH_SERVICE_UNAVAILABLE",
    "message": "Search service temporarily unavailable"
  }
}
```

### Algorithme Recommandations

```
Pour chaque utilisateur U avec historique H:
  1. Articles visités: V = {A1, A2, A3, ...}
  2. Catégories préférées: C = extract_categories(V)
  3. Tags populaires: T = extract_tags(V)
  
  Recommandations:
    - Articles publié après dernière visite
    - Articles dans catégories C (score +2)
    - Articles avec tags T (score +1)
    - Pas article déjà visite (excl.)
    - Score final = sum(tag_score, category_score, popularity_score)
    - Retourner top 5 par score
```

### Fichiers à Créer

| Fichier | Type | Lignes |
|---------|------|--------|
| `src/Controller/Api/ArticleApiController.php` | NEW | ~250 |
| `src/Service/ArticleSearchService.php` | NEW | ~180 |
| `src/Normalizer/ArticleNormalizer.php` | NEW | ~100 |
| `tests/Api/ArticleApiTest.php` | NEW | ~400 |
| `config/serializer/Article.yaml` | NEW | ~30 |

### Base de Données - Indexes

```sql
-- Pour recherche optimisée
CREATE FULLTEXT INDEX idx_article_ft ON article(titre, contenu);
CREATE INDEX idx_article_visible ON article(statut, deleted_at) WHERE statut='PUBLIÉ' AND deleted_at IS NULL;
CREATE INDEX idx_article_popular ON article(likes DESC, vues DESC);
CREATE INDEX idx_article_recent ON article(created_at DESC);

-- Stats query
SELECT 
  COUNT(*) as total,
  AVG(likes) as avg_likes,
  MAX(vues) as max_vues
FROM article 
WHERE statut='PUBLIÉ' AND deleted_at IS NULL;
```

### Performance Targets

| Métrique | Cible | Acceptable |
|----------|-------|-----------|
| GET /api/articles | < 200ms | < 500ms |
| GET /api/articles/search | < 300ms | < 800ms |
| Recommandations | < 150ms | < 400ms |
| Like article | < 100ms | < 200ms |
| Memory usage | < 20MB | < 50MB |

---

## 📊 RÉSUMÉ MODULE ARTICLES

| Aspect | Détail |
|--------|--------|
| **Points Totaux** | 34 pts (18 + 16) |
| **Durée Estimée** | 2 semaines |
| **Équipe** | 3 développeurs (1 backend, 1 frontend, 1 QA) |
| **Dépendances** | Aucune bloker |
| **Risques** | Recherche full-text (complexité), Recommandations ML |
| **Tests** | 50+ cas (CRUD + API) |
| **Fichiers** | 25+ fichiers créés/modifiés |

