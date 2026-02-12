# PHARMAX - Guide d'Utilisation Complet

## 📌 Vue d'Ensemble

**PHARMAX** est une plateforme intégrée de gestion de blog pharmaceutique et de produits avec:
- ✅ Gestion complète des articles avec traduction automatique
- ✅ Gestion des produits pharmaceutiques
- ✅ Système de commentaires modérés
- ✅ Dashboard administrateur unifié
- ✅ Interface frontend élégante

---

## 🚀 Démarrage Rapide

### 1️⃣ Lancer le Serveur

```bash
cd c:\Users\Asus\Documents\pharmax
php -S 127.0.0.1:8000 -t public
```

### 2️⃣ Accéder aux Interfaces

| Interface | URL | Description |
|-----------|-----|-------------|
| **Frontend** | http://127.0.0.1:8000/ | Blog & Produits publics |
| **Backoffice** | http://127.0.0.1:8000/dashboard | Admin - Dashboard |
| **Gestion Articles** | http://127.0.0.1:8000/article | Admin - Articles CRUD |
| **Gestion Produits** | http://127.0.0.1:8000/produit | Admin - Produits CRUD |

---

## 📂 Structure des Pages

### Frontend (Public)

#### 🏠 Accueil Blog
- **URL:** `http://127.0.0.1:8000/`
- **Contenu:** Liste des derniers articles
- **Fonctionnalités:**
  - Liste des articles avec images
  - Compteur de likes
  - Compteur de commentaires
  - Lien vers détail article

#### 📄 Détail Article avec Traduction
- **URL:** `http://127.0.0.1:8000/blog/{id}`
- **Fonctionnalités:**
  - Contenu article complet
  - 🌐 Bouton "Translate to English"
  - Sélecteur langue (Français/English)
  - Traduction automatique via Google Translate
  - Section commentaires
  - Bouton like
  - Navigation article précédent/suivant

#### 🏥 Produits Pharmacie
- **URL:** `http://127.0.0.1:8000/produits`
- **Contenu:** Grille de tous les produits
- **Fonctionnalités:**
  - Filtrage par catégorie
  - Recherche produits
  - Affichage prix/stock
  - Badge "En Stock" / "Rupture"
  - Tri par prix
  - Lien vers détail produit

#### 📦 Détail Produit
- **URL:** `http://127.0.0.1:8000/produit/{id}`
- **Fonctionnalités:**
  - Image produit grandeur
  - Description détaillée
  - Prix
  - Statut stock
  - Date expiration
  - Catégorie
  - Boutons partage (Facebook, Twitter, Copier lien)
  - Articles recommandés (sidebar)

---

### Backoffice (Admin)

#### 📊 Dashboard
- **URL:** `http://127.0.0.1:8000/dashboard`
- **Statistiques:**
  - Total Articles
  - Total Produits
  - Total Commentaires
  - Prix moyen produits
  - Produits en stock
  - Articles en rupture
  - Activité récente

#### 📰 Gestion Articles

**Liste Articles:**
- **URL:** `http://127.0.0.1:8000/article`
- **Actions:**
  - 👁️ Voir détail
  - ✏️ Éditer
  - 🗑️ Supprimer
  - 🌐 Traduire

**Créer Article:**
- **URL:** `http://127.0.0.1:8000/article/new`
- **Champs:**
  - Titre (requis)
  - Contenu (requis)
  - Image (optionnel)
  - Sauvegarde automatique traduction EN

**Éditer Article:**
- **URL:** `http://127.0.0.1:8000/article/{id}/edit`
- **Champs:** Même que création
- **Actions:** Mise à jour + Supprimer

**Traduire Article:**
- **Méthode:** POST vers `/article/{id}/translate`
- **Résultat:** Traduction auto via Google Translate
- **Stockage:** Champ `contenuEn` de l'Article

#### 💊 Gestion Produits

**Liste Produits:**
- **URL:** `http://127.0.0.1:8000/produit`
- **Affichage:** Grille avec cartes produit
- **Actions:** Voir, Éditer, Supprimer

**Créer Produit:**
- **URL:** `http://127.0.0.1:8000/produit/new`
- **Champs:**
  - Nom (requis)
  - Description (requis)
  - Prix (requis, décimal)
  - Quantité (requis, entier)
  - Statut (Actif/Inactif/En rupture)
  - Date expiration (optionnel)
  - Catégorie (requis, sélection)
  - Image (optionnel, upload)

**Éditer Produit:**
- **URL:** `http://127.0.0.1:8000/produit/{id}/edit`
- **Champs:** Même que création
- **Upload Image:** Remplace ancienne image

**Supprimer Produit:**
- **Méthode:** POST
- **Confirmation:** Dépôt avec token CSRF

---

## 🎨 Fonctionnalités Principales

### 1️⃣ Traduction Articles

**Comment ça marche:**
1. Admin crée article en français
2. Clic sur "Translate to English"
3. Envoi texte vers Google Translate API (gratuit)
4. Traduction sauvegardée dans `Article.contenuEn`
5. Frontend affiche option langue pour lecteur

**Langues supportées:** Français ↔ English

**Remarque:** Aucune clé API requise (utilise endpoint public Google)

### 2️⃣ Gestion Images

**Articles:**
- Upload depuis formulaire création
- Format: JPEG, PNG, GIF, WebP
- Taille max: 5MB
- Redimensionnement auto

**Produits:**
- Upload depuis formulaire création
- Format: JPEG, PNG, GIF, WebP
- Taille max: 5MB
- Stockage: `/public/uploads/images/`

### 3️⃣ Catégories Produits

**Disponibles:**
1. 💊 Médicaments
2. 🥗 Vitamines
3. 🧼 Hygiène

**Ajouter Catégorie:**
- Dans `ProduitType.php` form builder
- Lier produit à catégorie en création

### 4️⃣ Commentaires Articles

**Frontend:**
- Section commentaires sous article
- Formulaire ajout commentaire
- Modération requise avant affichage

**Backoffice:**
- Via `/article/{id}` détail
- Affiche commentaires modérés et en attente
- Actions: Approuver, Rejeter, Supprimer

---

## 📊 Données de Test

### Produits Pré-chargés

| ID | Nom | Prix | Stock | Catégorie |
|----|-----|------|-------|-----------|
| 1 | Paracétamol 500mg | 5.99 DTN | 100 | Médicaments |
| 2 | Vitamine C 1000mg | 12.50 DTN | 50 | Vitamines |
| 3 | Savon Antibactérien | 3.99 DTN | 200 | Hygiène |

### Catégories

- 💊 Médicaments
- 🥗 Vitamines
- 🧼 Hygiène

### Articles de Test

Environ 5-10 articles blog pré-existants (via fixture)

---

## 🔧 Architecture Technique

### Données Persistées

**Base de Données:** SQLite (`var/data.db`)

**Tables:**
- `article` - Articles blog
- `produit` - Produits
- `categorie` - Catégories
- `commentaire` - Commentaires
- `doctrine_migration_versions` - Versioning BD

### Controllers

| Controller | Routes | Responsabilité |
|-----------|--------|-----------------|
| DashboardController | /dashboard | Statistiques admin |
| ArticleController | /article/* | CRUD articles |
| ProduitController | /produit/* | CRUD produits |
| BlogController | /blog/*, /produits | Frontend public |

### Entités

| Entité | Relations | Champs Clés |
|--------|-------------|------------|
| Article | 1:N Commentaire | titre, contenu, contenuEn, image, likes |
| Produit | N:1 Categorie | nom, description, prix, quantite, dateExpiration |
| Categorie | 1:N Produit | nom, description, image |
| Commentaire | N:1 Article | contenu, statut, datePublication, email |

---

## ⚙️ Services Disponibles

### GoogleTranslationService
```php
$service->translate('Text', 'en'); // Traduit en anglais
```
**Fichier:** `src/Service/GoogleTranslationService.php`

### Form Types
- `ArticleType` - Formulaire article
- `ProduitType` - Formulaire produit
- `CategorieType` - Formulaire catégorie

### Repositories
- `ArticleRepository` - Requêtes article
- `ProduitRepository` - Requêtes produit (avec filtres avancés)
- `CategorieRepository` - Requêtes catégorie
- `CommentaireRepository` - Requêtes commentaire

---

## 📱 Responsive Design

- ✅ Desktop (1920px+)
- ✅ Tablet (768px - 1024px)
- ✅ Mobile (320px - 767px)

**CSS:** 
- Framework Bootstrap SNEAT (admin)
- Custom CSS (frontend blog)
- Couleur thème: #5ea96b (vert)

---

## 🛡️ Sécurité

- ✅ Tokens CSRF sur tous formulaires
- ✅ Validation serveur/client
- ✅ Upload fichier sécurisé
- ✅ Nettoyage SQL via Doctrine ORM
- ✅ Gestion erreurs appropriée

---

## 📝 Utilisation API (Avancé)

### Récupérer Articles (JSON)
```
GET /api/articles
```

### Récupérer Produits (JSON)
```
GET /api/produits
```

### Crear Produit
```
POST /produit/new
Content-Type: multipart/form-data
```

---

## 🐛 Dépannage

### Erreur "Produit not found"
- Vérifier l'ID du produit existe
- Vérifier URL `/produit/1` (ID valide)

### Image uploadée pas affichée
- Vérifier permissions dossier `/public/uploads/images/`
- Vérifier format fichier (JPEG/PNG/GIF/WebP)
- Vérifier taille < 5MB

### Traduction non fonctionnelle
- Vérifier connexion internet
- Vérifier `GoogleTranslationService.php` syntaxe
- Tenter depuis admin: `/article/1/translate`

---

## 📞 Support

- **Documentation:** Voir fichiers `.md` dans racine projet
- **Tests:** Exécuter `php test_final_validation.php`
- **Routes:** `php bin/console debug:router`

---

**Statut:** ✅ Production Ready
**Version:** 1.0.0
**Dernière Mise à Jour:** 11 Février 2026
