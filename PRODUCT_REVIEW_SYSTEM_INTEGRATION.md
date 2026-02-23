# Système d'Avis et Commentaires pour Produits - Documentation d'Intégration

## Résumé de l'Intégration

Un système complet d'avis et de commentaires pour les produits a été intégré en réutilisant l'entité `Commentaire` existante. Les utilisateurs peuvent désormais laisser des avis sur les pages de détail des produits, et les administrateurs peuvent modérer ces avis.

---

## Modifactions Effectuées

### 1. Entité Commentaire (`src/Entity/Commentaire.php`)

**Avant:**
- Relation ManyToOne avec Article (nullable: false - obligatoire)
- Ne supportait que les commentaires d'articles

**Après:**
- Relation ManyToOne avec Article (nullable: true - optionnel)
- Relation ManyToOne avec Produit (nullable: true - optionnel)
- Permet les avis sur les produits ET les commentaires sur les articles
- Getters/Setters ajoutés pour `produit`

```php
#[ORM\ManyToOne(inversedBy: 'commentaires')]
#[ORM\JoinColumn(nullable: true)]
private ?Article $article = null;

#[ORM\ManyToOne(inversedBy: 'avis')]
#[ORM\JoinColumn(nullable: true)]
private ?Produit $produit = null;
```

### 2. Entité Produit (`src/Entity/Produit.php`)

**Avant:**
- Aucune relation avec les commentaires

**Après:**
- Relation OneToMany avec Commentaire (inversedBy: 'avis')
- Collection pour gérer les avis du produit
- Méthodes addAvis() et removeAvis() pour gérer la collection

```php
#[ORM\OneToMany(targetEntity: Commentaire::class, mappedBy: 'produit', cascade: ['remove'])]
private Collection $avis;

public function getAvis(): Collection { ... }
public function addAvis(Commentaire $avis): static { ... }
public function removeAvis(Commentaire $avis): static { ... }
```

### 3. Contrôleur BlogController (`src/Controller/BlogController.php`)

**Méthode `detailProduit` modifiée:**
- Accepte désormais les requêtes POST (GET + POST)
- Gère la création de commentaires à partir du formulaire frontend
- Récupère uniquement les avis validés ('valide' statut)
- Les nouveaux avis commencent avec le statut 'en_attente' pour modération

```php
#[Route('/produit/{id}', name: 'app_front_detail_produit', methods: ['GET', 'POST'])]
public function detailProduit(
    string $id, 
    ProduitRepository $produitRepository, 
    CommentaireRepository $commentaireRepository, 
    EntityManagerInterface $entityManager, 
    Request $request
): Response
```

### 4. Formulaire Commentaire (`src/Form/CommentaireType.php`)

**Avant:**
- Champ article obligatoire
- Aucun support pour les produits

**Après:**
- Champ article optionnel (required: false)
- Champ produit optionnel (required: false)
- Permet de créer des commentaires pour articles OU produits
- Champ statut pour la modération (en_attente, valide, bloque)

```php
->add('article', EntityType::class, [
    'class' => Article::class,
    'choice_label' => 'titre',
    'required' => false,
])
->add('produit', EntityType::class, [
    'class' => Produit::class,
    'choice_label' => 'nom',
    'required' => false,
])
```

### 5. Template Produit Detail (`templates/blog/product_detail.html.twig`)

**Ajouts:**
- Formulaire pour laisser un avis (textarea + validation)
- Affichage des avis validés
- Section "Avis et Commentaires des Clients"
- Design responsive et cohérent avec le reste du site

**Formulaire:**
```html
<textarea name="contenu" minlength="2" maxlength="1000">
    Partager votre expérience avec ce produit...
</textarea>
```

**Affichage des avis:**
- Affiche l'auteur, la date, et le contenu
- Badge "Validé" avec icône
- Formate la date au format "d M Y à H:i"
- Message vide si aucun avis

### 6. Migrations Doctrine

**Migration créée:** `migrations/Version20260211222111.php`
- Ajoute la colonne `produit_id` (INTEGER, nullable)
- Ajoute la clé étrangère vers la table `produit`
- Rend `article_id` nullable (permet colonne NULL)
- Crée les index pour les performances

```sql
ALTER TABLE commentaire ADD COLUMN produit_id INTEGER DEFAULT NULL;
ALTER TABLE commentaire ADD FOREIGN KEY (produit_id) REFERENCES produit(id);
CREATE INDEX IDX_67F068BCF347EFB ON commentaire (produit_id);
```

---

## Flux de Fonctionnement

### 1. Avis Client - Soumission

```
Page Produit (/produit/{id})
    ↓
Utilisateur remplir le formulaire d'avis
    ↓
POST vers /produit/{id}
    ↓
BlogController::detailProduit() capture le formulaire
    ↓
Crée un Commentaire avec:
  - contenu: le texte saisi
  - produit: le produit actuel
  - statut: 'en_attente' (modération requise)
  - date_publication: aujourd'hui
    ↓
Redirect vers /produit/{id} (GET)
```

### 2. Modération - Admin

```
Accès à /commentaire
    ↓
CommentaireController::index() affiche tous les commentaires
    ↓
Admin clique sur un commentaire pour le modifier
    ↓
Change le statut à 'valide' ou 'bloque'
    ↓
Sauvegarde
```

### 3. Affichage - Frontend

```
Page Produit Affiche:
    ↓
CommentaireRepository->findBy(
    ['produit' => $produit, 'statut' => 'valide'],
    ['date_publication' => 'DESC']
)
    ↓
Boucle Twig affiche les avis validés
```

---

## Accès Utilisateur

### Frontend (Visiteurs)
- **URL:** `http://localhost/produit/{id}`
- **Qu'ils peuvent faire:**
  - Voir les avis validés du produit
  - Soumettre un nouvel avis (formulaire simple)
  - Voir la date et le contenu des avis

### Admin (Modérateurs)
- **URL:** `http://localhost/commentaire`
- **Qu'ils peuvent faire:**
  - Voir tous les commentaires (articles et produits)
  - Filtrer par statut (en_attente, valide, bloque)
  - Valider ou rejeter les avis
  - Supprimer les avis

---

## Validation et Sécurité

### Frontend
- **Longueur du contenu:** minimum 2, maximum 1000 caractères
- **Champ requis:** textarea obligatoire (required)
- **HTML5 Validation:** min/maxlength

### Backend
- **Validateurs Symfony:**
  - `NotBlank`: contenu ne peut pas être vide
  - `Length(min: 2, max: 1000)`: contrôle la longueur
- **Filtrage:** Seuls les avis avec statut='valide' sont affichés au public

### Database
- **Contraintes:**
  - `article_id` nullable
  - `produit_id` nullable
  - Clés étrangères avec intégrité référentielle

---

## Architecture - Relation entre Entités

```
Article (1) ----< (Many) Commentaire >---- (1) Produit
              article_id              produit_id

Commentaire:
- Si article_id != NULL et produit_id = NULL → Commentaire d'article
- Si article_id = NULL et produit_id != NULL → Avis de produit
- Jamais: article_id != NULL ET produit_id != NULL (à enforcer)
```

---

## Tests Effectués

✅ **Entités:**
- Produit a la collection d'avis
- Commentaire a la propriété produit
- Relations OneToMany/ManyToOne configurées
- Article relation rendue nullable

✅ **Migrations:**
- Migration créée et appliquée avec succès
- Colonne produit_id ajoutée
- Clé étrangère créée
- Données existantes migrées (7 requêtes SQL)

✅ **Contrôleur:**
- detailProduit accepte GET et POST
- Création de commentaires fonctionnelle
- Liaison du commentaire au produit
- Récupération des avis validés

✅ **Template:**
- Formulaire d'avis présent
- Boucle d'affichage des avis présente
- Design cohérent

✅ **Formulaire:**
- Champs article et produit optionnels
- Champ contenu avec validation

---

## Fonctionnalités Futures (Optionnel)

1. **Système de notation (stars)**
   - Ajouter un champ `note` (1-5) à Commentaire
   - Afficher les stars dans le template

2. **Moyenne des avis**
   - Calculer la note moyenne par produit
   - Afficher dans la page liste et détail

3. **Filtrage des avis**
   - Filtrer par note (5 stars, 4 stars, etc.)
   - Trier par date, utilité, note

4. **Notifications admin**
   - Email quand un nouvel avis est soumis
   - Rappel des avis en attente de modération

5. **Photo d'utilisateur**
   - Ajouter une relation optionnelle avec User
   - Afficher le nom d'utilisateur (pas "Client")

6. **Réponses aux avis**
   - Ajouter une relation auto-référencée (parent - child)
   - Permettre aux vendeurs/admins de répondre

---

## Code Exemple - Utilisation dans Twig

```twig
{# Afficher les avis d'un produit #}
{% for avis in produit.avis %}
    {% if avis.statut == 'valide' %}
        <div class="avis">
            <p>{{ avis.contenu }}</p>
            <small>{{ avis.datePublication|date('d M Y') }}</small>
        </div>
    {% endif %}
{% endfor %}
```

```twig
{# Formulaire dans contrôleur #}
{% form_theme form 'bootstrap_5_layout.html.twig' %}
{{ form_start(form) }}
    {{ form_widget(form.contenu) }}
    {{ form_widget(form.statut) }}
    {{ form_widget(form.article) }}
    {{ form_widget(form.produit) }}
    <button>Soumettre</button>
{{ form_end(form) }}
```

---

## Fichiers Modifiés

```
✓ src/Entity/Commentaire.php
✓ src/Entity/Produit.php
✓ src/Controller/BlogController.php
✓ src/Form/CommentaireType.php
✓ templates/blog/product_detail.html.twig
✓ migrations/Version20260211222111.php (générée)
```

---

## Commandes Utiles

```bash
# Voir tous les commentaires
php bin/console doctrine:query:sql "SELECT * FROM commentaire"

# Voir les avis d'un produit en attente
php bin/console doctrine:query:sql "SELECT * FROM commentaire WHERE produit_id = 1 AND statut = 'en_attente'"

# Mettre à jour le statut d'un avis
php bin/console doctrine:query:sql "UPDATE commentaire SET statut = 'valide' WHERE id = 1"

# Supprimer tous les avis d'un produit
php bin/console doctrine:query:sql "DELETE FROM commentaire WHERE produit_id = 1"
```

---

## Note de Sécurité

⚠️ **Important:** 
- À l'avenir, implémenter l'authentification pour capturer l'utilisateur
- Ajouter une validation CSRF pour le formulaire
- Implémenter des rates limits (max 5 avis par IP par jour)
- Sanitizer l'input utilisateur si HTML autorisé
