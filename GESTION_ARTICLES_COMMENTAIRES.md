# Gestion Articles & Commentaires - Vue d'Ensemble Technique

## 📊 APIs Utilisées (2)

### 1. **Google Translation API**
- **Service:** `GoogleTranslationService`
- **Utilisation:** Traduction multi-langues du contenu d'articles
- **Intégration:** Via libraire `stichoza/google-translate-php`
- **Location:** `src/Service/GoogleTranslationService.php`
- **Fallback:** Gestion des erreurs réseau intégrée

### 2. **HuggingFace Inference API**
- **Service:** `CommentModerationService`
- **Modèle:** `unitary/toxic-bert`
- **Utilisation:** Détection automatique de contenu toxique/offensant dans les commentaires
- **Endpoint:** `https://api-inference.huggingface.co/models/unitary/toxic-bert`
- **Location:** `src/Service/CommentModerationService.php`
- **Fallback:** Blacklist locale si API indisponible

---

## 📦 Bundles Symfony (4)

### 1. **KnpPaginatorBundle** (v6.10)
- **Usage:** Pagination des articles et commentaires dans les listes
- **Controllers:** `ArticleController::index()`, `BlogController::index()`
- **Features:** Support AJAX, personnalisation CSS/templates

### 2. **DoctrineBundle** (v2.18)
- **Usage:** ORM complet pour Articles et Commentaires
- **Features:** 
  - Relations ManyToOne (Commentaire → Article)
  - Cascade delete
  - Query lifecycle verification

### 3. **DoctrineMigrationsBundle** (v3.7)
- **Usage:** Versioning et évolution du schéma DB
- **Migrations:** Création table `user_saved_articles`, colonnes `isDraft`, etc.

### 4. **Symfony Translation Component** (v6.4)
- **Usage:** Localisation interface en français
- **Features:** Traducteur `|trans` dans Twig, fichier YAML `messages.fr.yaml`
- **Localisation:** 
  - Labels UI (Articles, Commentaires, Publié, Brouillon, etc.)
  - Messages flash (article_published, article_saved_draft)
  - Choix de formulaires (En Stock, Hors Stock)

---

## 🤖 AI Intégrées (2)

### 1. **HuggingFace Toxic-BERT** (Modération Commentaires)
- **Type:** NLP Classification Model
- **Niveau Intégration:** Modération au moment de la création
- **Flux:**
  ```
  Utilisateur soumet commentaire 
    → CommentModerationService::checkContent()
    → HuggingFace API ou Blacklist locale
    → Statut 'en_attente' ou 'bloque'
    → Enregistrement en DB
  ```
- **Statuts:** 
  - `valide` - Approuvé automatiquement
  - `en_attente` - Nécessite review admin
  - `bloque` - Flagged comme toxique
- **Location:** `src/Service/CommentModerationService.php:102-130`

### 2. **Ollama Mistral** (Chat sur Articles)
- **Type:** Local LLM Models (Mistral, Llama, etc.)
- **Modèle:** `mistral:latest` (configurable)
- **Endpoint:** `http://localhost:11434/api/generate`
- **Niveau Intégration:** Réponse à questions sur articles via API
- **Services Liés:**
  - `OllamaService` - Client API Ollama
  - `ChatBotService` - Orchestration (contexte + prompt + réponse)
  - `ArticleSearchService` - Récupère articles pertinents
- **Flux:**
  ```
  Question utilisateur (via API /chatbot/ask)
    → ChatBotService::answerQuestion()
    → ArticleSearchService::searchRelevantArticles()
    → Récupère contenu articles comme contexte
    → Ollama génère réponse basée sur contexte article
    → Réponse retournée au frontend
  ```
- **Location:** 
  - `src/Service/OllamaService.php` - Intégration API
  - `src/Service/ChatBotService.php` - Logique métier
  - `src/Controller/Api/ChatBotApiController.php` - Endpoint `/api/chatbot/ask`
- **Configuration:** `.env` → `OLLAMA_URL=http://localhost:11434`

---

## 🏗️ Métiers Avancés Implémentés (6)

### 1. **Service Pattern (Injection de dépendances)**
```php
- ArticleStatisticsService (Dashboard analytics)
  - getDashboardStats(), getTotalArticles(), getCommentsByStatus()
  - Fournit données agrégées via DQL

- CommentModerationService (Validation IA)
  - checkContent(), detectBlacklistedWords()
  - Décision binaire (approprié/inapproprié)

- GoogleTranslationService (Traduction)
  - translate() avec gestion d'erreurs
```

### 2. **Repository Pattern**
```php
- ArticleRepository
  - Requêtes métier: findAll(), findPublished()
  
- CommentaireRepository
  - Requêtes métier: findByStatut(), findByArticle()
  
- Abstraction de la persistance ORM
```

### 3. **Fluent Interface / Method Chaining**
```php
Article::publish()      // Setter retourne $this
       ::saveDraft()   // Permet chaînage: $article->publish()->flush()
```

### 4. **Doctrine Query Language (DQL)**
```php
SELECT SUM(a.likes) as total FROM App\Entity\Article a
// Agrégation côté base pour TotalLikes
```

### 5. **Entity-Driven Business Logic**
```php
Class Article {
  private bool $isDraft = true;
  
  public function publish(): static { ... }
  public function isDraft(): bool { ... }
  // Logique métier encapsulée dans l'entité
}
```

### 6. **Middleware de Validation (Pre-Persistence)**
```php
CommentModerationService::isAppropriate()
  ↓ appelé AVANT persist() 
  ↓ détermine le statut
  → Comment seulement enregistré si validation passée
```

---

## 📈 Flux Métier

### Création Article
```
Controller::new() 
  → ArticleType Form validation
  → Défaut: isDraft = true
  → EntityManager::persist()
  → Admin doit cliquer "Publié" pour activer
```

### Publication Article
```
Controller::togglePublish() 
  → Article::publish() 
  → EntityManager::flush()
  → Flash message
  → Visible public (isDraft = false)
```

### Création Commentaire
```
BlogController::createComment()
  → CommentModerationService::checkContent()
    → HuggingFace API (si clé configurée)
    → OU Blacklist locale
  → Statut défini: 'en_attente' ou 'bloque'
  → EntityManager::persist()
  → Admin valide depuis /admin/article
  → Visible public si statut='valide'
```

### Statistiques
```
ArticleController::index()
  → ArticleStatisticsService::getDashboardStats()
  → Calculs agrégés (DQL)
  → Charts.js visualization
```

---

## 🔒 Sécurité Intégrée

- **CSRF Protection:** Tous les POST require token
- **Modération IA:** Double couche (Blacklist + ML)
- **Rôles:** ROLE_USER (comments), ROLE_ADMIN (article management)
- **Rate Limiting:** Prévu via Symfony Middleware

---

## 💾 Relation Données

```
Article (1) ←→ (N) Commentaire
User (1) ←→ (N) Commentaire
User (N) ←→ (N) Article (saved_articles)
```

**Cascade Delete:** Suppression article → suppression commentaires liés

---

## 📝 Fichiers Clés

| Fichier | Rôle |
|---------|------|
| `Article.php` | Entity + business logic (publish/draft) |
| `Commentaire.php` | Entity + relations |
| `ArticleController.php` | Admin: CRUD articles + publish toggle |
| `BlogController.php` | Public: list, show, comments |
| `CommentModerationService.php` | IA modération |
| `ArticleStatisticsService.php` | Analytics dashboard |
