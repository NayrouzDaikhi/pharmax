# 🚀 PHARMAX - Quick Start Guide

## ⚡ 5 Minutes pour Démarrer

### 1️⃣ Ouvrir Terminal
```bash
cd c:\Users\Asus\Documents\pharmax
```

### 2️⃣ Lancer le Serveur
```bash
php -S 127.0.0.1:8000 -t public
```

**⏳ Attendre:** Server running at `http://127.0.0.1:8000/`

### 3️⃣ Ouvrir les Pages

| **Page** | **URL** | **Description** |
|----------|---------|-----------------|
| 🏠 **Accueil** | http://127.0.0.1:8000/ | Blog public |
| 📊 **Admin** | http://127.0.0.1:8000/dashboard | Dashboard stats |
| 📰 **Articles** | http://127.0.0.1:8000/article | Gestion articles |
| 💊 **Produits** | http://127.0.0.1:8000/produit | Gestion produits |
| 🛍️ **Shop** | http://127.0.0.1:8000/produits | Boutique publique |

---

## 👤 Comptes Admin

**Pas d'authentification requise** - Accès direct à l'admin ✅

---

## 📝 Actions Rapides

### Créer un Article
1. `http://127.0.0.1:8000/article`
2. Clic "Créer Nouvel Article"
3. Remplir formulaire
4. Clic "Sauvegarder"

### Créer un Produit
1. `http://127.0.0.1:8000/produit`
2. Clic "Créer Nouveau Produit"
3. Remplir formulaire avec:
   - Nom (ex: Paracétamol)
   - Description
   - Prix (ex: 5.99)
   - Quantité
   - Catégorie (ex: Médicaments)
4. Upload image
5. Clic "Sauvegarder"

### Traduire un Article en Anglais
1. Créer/Éditer article en français
2. Clic "Sauvegarder"
3. Bouton "Translate to English"
4. ✅ Traduction auto!

### Voir Dashboard
1. `http://127.0.0.1:8000/dashboard`
2. Affiche statistiques:
   - Total Articles
   - Total Produits
   - Total Commentaires
   - Prix moyen
   - Stock total
   - Derniers items

---

## 🎨 Personnalisation Rapide

### Changer Couleur Thème
```css
/* assets/styles/app.css */
:root {
  --primary: #5ea96b;  /* Vert actuel */
  --secondary: #f39c12; /* Votre couleur */
}
```

### Ajouter Catégorie Produit
```php
// Éditer en direct la fixture ou ajouter en BDD:
INSERT INTO categorie (nom, description) 
VALUES ('Nouvelle', 'Description');
```

### Importer Produits (CSV)
- Actuellement: Manuel via formulaire
- À implémenter: Import CSV

---

## 🔍 Vérification Rapide

### Tous les systèmes OK?
```bash
php test_final_validation.php
```
✅ Si "TOUS LES TESTS RÉUSSIS!" → Tout fonctionne

### Afficher Toutes les Routes
```bash
php bin/console debug:router
```

### Tester Base Données
```bash
php bin/console doctrine:query:sql "SELECT COUNT(*) FROM produit"
```

---

## 🐛 Problèmes Courants

### "Page not found" 
- Vérifier URL (ex: `/produits` pas `/produit`)
- Vérifier serveur lancé

### Image ne s'affiche pas
- Format: JPEG, PNG, GIF, WebP
- Taille: < 5MB
- Réessayer upload

### Traduction lente
- Première tentative lente (appel Google API)
- Résultats en cache automatiquement

---

## 📁 Fichiers Importants

| Fichier | Utilité |
|---------|---------|
| `config/routes.yaml` | Routes principales |
| `src/Controller/* | Logic métier |
| `templates/* | Pages HTML |
| `src/Entity/* | Modèles données |
| `assets/styles/app.css` | ToutleStyle |

---

## 🎯 Prochain Step

✅ **Maintenant que tout fonctionne**, vous pouvez:

1. **Ajouter Produits**
   - Aller `/produit`
   - Ajouter vos produits
   - Upload images

2. **Créer Articles**
   - Aller `/article`
   - Écrire articles
   - Ajouter traduction

3. **Customiser Design**
   - Modifier CSS dans `assets/styles/app.css`
   - Changer couleurs, fonts, layouts
   - Recharger page (et garder cache)

4. **Ajouter Utilisateurs**
   - Implémenter authentification
   - Ajouter rôles (admin, editor, user)

5. **Améliorer Fonctionnalités**
   - Filtres avancés
   - Panier (shopping cart)
   - Paiement
   - Commentaires modérés

---

## 📞 Aide Rapide

| Question | Réponse |
|----------|---------|
| Où lancer serveur? | `php -S 127.0.0.1:8000 -t public` |
| Où accéder admin? | `http://127.0.0.1:8000/dashboard` |
| Créer article? | Form à `/article/new` |
| Créer produit? | Form à `/produit/new` |
| Changer couleur? | Éditer `assets/styles/app.css` |
| Ajouter route? | Ajouter `#[Route(...)]` dans Controller |
| Créer entity? | `php bin/console make:entity` |

---

## ✨ Raccourcis Utiles

```bash
# Voir routes
php bin/console debug:router

# Voir erreurs
php bin/console lint:twig templates/

# Créer migration
php bin/console make:migration

# Exécuter migrations
php bin/console doctrine:migrations:migrate

# Vider cache
php bin/console cache:clear
```

---

## 🎉 Vous êtes Prêt!

1. ✅ Serveur lancé
2. ✅ Admin accessible
3. ✅ Produits créables
4. ✅ Articles créables
5. ✅ Traduction fonctionnelle
6. ✅ Design responsive

**Profitez de PHARMAX!** 🚀

---

**Version:** 1.0.0
**Statut:** ✅ Production Ready
**Support:** Voir USER_GUIDE_COMPLETE.md pour détails

