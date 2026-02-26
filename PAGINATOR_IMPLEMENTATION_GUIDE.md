# 📄 Guide: Pagination KnpPaginatorBundle - Réclamations Admin

## ✅ Installation & Configuration Complétées

KnpPaginatorBundle a été installé et configuré pour la liste des réclamations dans l'admin.

---

## 📊 Modifications Apportées

### 1. Installation du Bundle
```bash
composer require knplabs/knp-paginator-bundle
```

**Version installée:** `knplabs/knp-paginator-bundle: ^6.10.0`

### 2. Configuration (config/packages/knp_paginator.yaml)
```yaml
knp_paginator:
  page_range: 5                       # 5 pages visibles
  default_options:
    page_name: page                   # Paramètre ?page=X
    sort_field_name: sortBy           # Paramètre &sortBy=
    sort_direction_name: sortOrder    # Paramètre &sortOrder=
    distinct: true
  template:
    pagination: '@KnpPaginator/Pagination/bootstrap_v5_pagination.html.twig'
```

### 3. Modifications du Contrôleur
**Fichier:** `src/Controller/AdminReclamationController.php`

#### Import du PaginatorInterface
```php
use Knp\Component\Pager\PaginatorInterface;
```

#### Injection dans le constructeur
```php
public function __construct(
    private EntityManagerInterface $em,
    private PaginatorInterface $paginator
) {
}
```

#### Utilisation dans la méthode index()
```php
// Avant: $reclamations = $qb->getQuery()->getResult();
// Après:
$reclamations = $this->paginator->paginate(
    $qb->getQuery(),
    $page,
    15 // 15 éléments par page
);
```

### 4. Template Twig Mis à Jour
**Fichier:** `templates/admin/reclamation/index.html.twig`

#### Affichage de la pagination
```twig
<!-- Pagination résponsive avec Bootstrap 5 -->
<div class="card-footer">
    <nav aria-label="pagination">
        <!-- Liens Précédent/Suivant -->
        <!-- Numéros de pages -->
        <!-- Compteur de résultats -->
    </nav>
</div>
```

---

## 🎯 Fonctionnalités

### Pagination Automatique

✅ **Liens Précédent/Suivant**
- Désactivés si pas de page suivante/précédente
- Maintiennent les filtres et le tri

✅ **Numérosage des Pages**
- Affiche les pages numérotées (avec ellipsis si besoin)
- Page actuelle surlignée
- Cliqueurs pour naviguer

✅ **Compteur de Résultats**
```
Affichage 1 à 15 sur 247 résultats
```

✅ **Conservation des Filtres**
Tous les paramètres de recherche sont conservés :
- Recherche par titre/utilisateur
- Filtre par statut
- Filtrage par date
- Tri et ordre

### Performance

✅ **Requête Optimisée**
- Le paginator n'exécute que la requête pour la page actuelle
- Pas de chargement de toutes les données en mémoire
- Query SQL : `LIMIT 15 OFFSET (page-1)*15`

---

## 🔧 Configuration Détaillée

### Nombre d'Éléments par Page

Modifier dans AdminReclamationController.php (ligne ~95):
```php
$reclamations = $this->paginator->paginate(
    $qb->getQuery(),
    $page,
    20  // Changer 15 à 20 (ou autre nombre)
);
```

### Nombre de Pages Visibles

Modifier dans config/packages/knp_paginator.yaml:
```yaml
knp_paginator:
  page_range: 7  # Afficher 7 pages au lieu de 5
```

### Template de Pagination Personnalisé

Pour utiliser un template custom au lieu de Bootstrap 5:
```yaml
knp_paginator:
  template:
    pagination: 'admin/reclamation/pagination.html.twig'
```

---

## 📱 Affichage Responsive

La pagination est entièrement responsive :
- **Desktop:** Tous les éléments visibles
- **Tablet:** Adaptation de l'espacement
- **Mobile:** Pagination responsive avec ... pour les pages non affichées

---

## 🔗 Intégration avec Filtres Existants

Les filtres existants fonctionnent parfaitement avec la pagination :

```php
// Les filtres sont automatiquement maintenues dans les URLs :
{{ path('admin_reclamation_index', {
    search: filters.search,         // ← Conservé
    statut: filters.statut,         // ← Conservé
    date: filters.date,             // ← Conservé
    sortBy: sortBy,                 // ← Conservé
    sortOrder: sortOrder,           // ← Conservé
    page: reclamations.nextPageNumber  // ← Page change
}) }}
```

---

## 📊 Exemple de Résultat

### URL Sans Pagination
```
/admin/reclamation?search=&statut=&date=&sortBy=dateCreation&sortOrder=DESC
```

### URL Avec Pagination (Page 2)
```
/admin/reclamation?search=&statut=&date=&sortBy=dateCreation&sortOrder=DESC&page=2
```

### URL Avec Filtres ET Pagination
```
/admin/reclamation?search=problème&statut=En%20cours&sortBy=dateCreation&sortOrder=DESC&page=1
```

---

## 🎨 Personnalisation du Styles

Pour personnaliser l'apparence de la pagination, modifier le template Twig:

`templates/admin/reclamation/index.html.twig` (environ ligne 270)

Exemples de classes Bootstrap 5 utilisées:
- `.pagination` - Conteneur
- `.page-link` - Lien/Bouton
- `.page-link.disabled` - État désactivé
- `.page-link.active` - Page actuelle

---

## 🚀 Cas d'Utilisation Avancés

### 1. Pagination Rapide (50 items par page)
```php
$reclamations = $this->paginator->paginate(
    $qb->getQuery(),
    $page,
    50
);
```

### 2. Pagination AJAX (optionnel)
```html
<!-- Ajouter data-pagination="ajax" à la pagination -->
<div data-pagination="ajax" data-url="/admin/reclamation/api">
```

### 3. Export Paginé (CSV)
```php
public function exportCsv(Request $request)
{
    $qb = $this->em->getRepository(Reclamation::class)->createQueryBuilder('r');
    $allReclamations = $qb->getQuery()->getResult();
    // Exporter TOUS (sans pagination)
}
```

---

## 📚 API du Paginator

### Propriétés Disponibles dans Twig

```twig
{# Général #}
{{ reclamations.currentPageNumber }}      {# Numéro de la page actuelle #}
{{ reclamations.lastPageNumber }}         {# Dernier numéro de page #}
{{ reclamations.totalItemCount }}         {# Nombre total d'items #}
{{ reclamations.itemNumberPerPage }}      {# Items par page (15) #}

{# Navigation #}
{{ reclamations.hasNextPage }}            {# Booléen #}
{{ reclamations.hasPreviousPage }}        {# Booléen #}
{{ reclamations.nextPageNumber }}         {# Numéro suivant #}
{{ reclamations.previousPageNumber }}     {# Numéro précédent #}

{# Items #}
{{ reclamations.firstItemNumber }}        {# Item #1 actuel (ex: 31 for page 3) #}
{{ reclamations.lastItemNumber }}         {# Item #N actuel (ex: 45 for page 3) #}

{# Pagination #}
{{ reclamations.paginationData.pageRange }}  {# Array [1, 2, 3, ...] #}
```

---

## ✅ Vérification Rapide

Pour tester que tout fonctionne :

1. **Aller à l'admin**: http://localhost:8000/admin/reclamation
2. **Voir la pagination**: Au bas de la liste
3. **Cliquer sur une page**: Vérifier que les filtres sont conservés
4. **Trier**: Vérifier que la pagination se remet à 1
5. **Rechercher**: Vérifier que la pagination s'adapte

---

## 🐛 Dépannage

### La pagination n'apparaît pas
```bash
# Vider le cache
php bin/console cache:clear

# Vérifier la configuration
php bin/console config:dump-reference knp_paginator
```

### Les filtres ne sont pas conservés
Vérifier que tous les paramètres de requête sont passés au `path()` helper.

### Pas assez/trop de pages affichées
Modifier `page_range` dans `config/packages/knp_paginator.yaml`

### Erreur "too many items"
Réduire le nombre d'items par page dans le contrôleur (3éme paramètre de `paginate()`)

---

## 📖 Ressources

- **Documentation Officielle**: https://knpbundles.com/KnpLabs/KnpPaginatorBundle
- **GitHub**: https://github.com/KnpLabs/KnpPaginatorBundle
- **Bootstrap Pagination**: https://getbootstrap.com/docs/5.3/components/pagination/

---

## 🎉 Résumé

✅ **KnpPaginatorBundle installé et configuré**
✅ **Pagination 15 items par page**
✅ **Conservation automatique des filtres**
✅ **Responsive et accessible**
✅ **Liens Précédent/Suivant**
✅ **Compteur de résultats**
✅ **Performance optimisée (SQL LIMIT/OFFSET)**

Votre liste de réclamations est maintenant **paginée et professionnelle!** 🚀

---

**Prochaines étapes (optionnel):**
- Ajouter les commentaires/réponses
- Implémenter les workflows de statut
- Ajouter l'audit (DoctrineAuditBundle)
- Exporter en CSV/Excel
