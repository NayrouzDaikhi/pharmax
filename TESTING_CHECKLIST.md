# ✅ CHECKLIST - Vérification du Système E-Commerce

## 🔍 Vérifications des Routes (Exécutez ceci):

```bash
# Test 1: Vérifier que les routes produits sont correctes
php bin/console debug:router app_front_produits
# Résultat attendu: GET /produits

php bin/console debug:router app_front_detail_produit  
# Résultat attendu: GET /produit/{id}

# Test 2: Vérifier que les routes panier existent
php bin/console debug:router app_panier_ajouter
# Résultat attendu: ANY /panier/ajouter/{id}

php bin/console debug:router app_panier_index
# Résultat attendu: ANY /panier/

# Test 3: Vérifier que les routes dupliquées sont parties
php bin/console debug:router | grep "front_produits\|front_detail"
# Résultat attendu: AUCUN RÉSULTAT (empty)
```

---

## 🌐 Test Web - Workflow Complet

### Étape 1: Voir la liste des produits
1. Ouvrez: `http://127.0.0.1:8000/produits`
2. **Vérifier**:
   - ✅ Page affiche "Nos Produits"
   - ✅ Produits affichés en cartes
   - ✅ Chaque produit a 2 boutons:
     * Bouton bleu "Ajouter" (gauche)
     * Bouton gris "Détails" (droite)
   - ✅ Navbar a icône **shopping bag** (🛍️) avant "Panier"
   - ✅ Badge vide/0 sur l'icône panier

### Étape 2: Ajouter un produit au panier
1. Sur `/produits`, cliquez le bouton **"Ajouter"** du 1er produit
2. **Vérifier**:
   - ✅ Message vert: "Produit ajouté au panier! (1 article)"
   - ✅ Vous restez sur `/produits`
   - ✅ Badge panier change: 0 → **1**
   - ✅ Pas de redirect vers `/admin/produit` (ERREUR 403)

### Étape 3: Ajouter un 2ème produit
1. Cliquez le bouton **"Ajouter"** d'un autre produit
2. **Vérifier**:
   - ✅ Message vert: "Produit ajouté au panier! (2 articles)"
   - ✅ Badge: **2**
   - ✅ Toujours sur `/produits`

### Étape 4: Voir le détail d'un produit
1. Cliquez le bouton **"Détails"**
2. **Vérifier**:
   - ✅ Page `/produit/X` affichée
   - ✅ Détail produit complet
   - ✅ Bouton "Ajouter au Panier" présent
   - ✅ Badge panier toujours à **2**

### Étape 5: Voir le panier
1. Cliquez sur l'icône **shopping bag** (🛍️) dans la navbar
2. **Vérifier**:
   - ✅ Page `/panier/` affichée
   - ✅ Liste les 2 produits ajoutés
   - ✅ Affiche prix unitaire × quantité = sous-total pour chaque
   - ✅ **TOTAL** affiché au bas
   - ✅ Boutons:
     * "Retirer" pour chaque produit
     * "Vider le panier" (haut)
     * "Commander" (bas)

### Étape 6: Retirer un produit
1. Cliquez "Retirer" sur un produit
2. **Vérifier**:
   - ✅ Message: "Produit retiré du panier!"
   - ✅ Panier n'affiche plus ce produit
   - ✅ Badge panier: 2 → **1**
   - ✅ TOTAL recalculé

### Étape 7: Commander
1. Cliquez le bouton **"Commander"**
2. **Vérifier**:
   - ✅ Redirection vers `/commandes/frontend/{id}`
   - ✅ Affiche: "Votre commande a été créée"
   - ✅ Résumé de la commande
   - ✅ Bouton "Télécharger PDF"
   - ✅ Badge panier: 1 → **0** (panier vidé)

### Étape 8: Télécharger le PDF
1. Cliquez "Télécharger PDF"
2. **Vérifier**:
   - ✅ Fichier PDF téléchargé
   - ✅ PDF contient les produits commandés
   - ✅ Contient QR code (si activé)

---

## 🛠️ Tests Additionnels

### Test A: Navigation cohérente
- [ ] Sur `/produits` → Clic "Panier" → Va à `/panier/` ✅
- [ ] Sur `/panier/` → Clic "Produits" → Va à `/produits` ✅
- [ ] Sur `/panier/` → Clic "Accueil" → Va à `/` ✅

### Test B: Augmenter quantité
- [ ] Manuellement modifier l'URL: `href="/panier/ajouter/1"`
- [ ] Cliquer 2 fois le même produit
- [ ] Vérifier: Quantité = 2 (pas 2 entrées) ✅

### Test C: Messages flash
- [ ] Ajouter au panier → Green message ✅
- [ ] Retirer → Green message ✅
- [ ] Vider → Green message ✅
- [ ] Erreur produit introuvable → Red message ✅

### Test D: Données persistantes (Session)
- [ ] F5 rafraîchir sur `/panier/` → Panier par perdu ✅
- [ ] Aller sur `/produits` et retour → Panier conservé ✅
- [ ] Fermer onglet/navigateur → Panier perdu (normal session) ✅

---

## ⚙️ Si Erreurs Rencontrées

### Erreur: "Route 'app_produit_index' not found"
- [ ] ✅ CORRIGÉE - Vérifier que PanierController est à jour
- Command: `grep "app_produit_index" src/Controller/PanierController.php`
- Résultat attendu: AUCUN MATCH

### Erreur: Redirect vers `/admin/produit` (403 Forbidden)
- [ ] ✅ CORRIGÉE - Même problème que ci-dessus
- Agir: Redémarrer le serveur PHP
- Command: Ctrl+C et `php -S 127.0.0.1:8000 -t public`

### Erreur: Icône panier ne s'affiche pas
- [ ] Vérifier que Boxicons CDN est chargé
- Dans `frontend/base.html.twig` ligne ~12:
  ```html
  <link href="https://cdn.jsdelivr.net/npm/boxicons@2.1.4/css/boxicons.min.css">
  ```
- [ ] Si problème: ouvrir DevTools (F12) → Console → Voir les erreurs

### Erreur: Routes dupliquées toujours présentes
- [ ] Command: `php bin/console cache:clear`
- [ ] Attendre que Symfony recharge
- [ ] Rafraîchir navigateur

---

## 📊 Verification Finale

Une fois tous les tests passés:

```bash
# Générer rapport d'intégrité
php bin/console cache:clear
php bin/console doctrine:migrations:status
php bin/console debug:router | wc -l  # Devrait afficher ~60+ routes
```

---

## 🎯 Résumé des Fixes

| Problème | Fichier | Ligne(s) | Status |
|----------|---------|----------|--------|
| Redirects cassées (admin) | PanierController.php | 45, 79 | ✅ FIXED |
| Routes dupliquées | HomeController.php | 101, 122 | ✅ DISABLED |
| Icône panier manquante | frontend/base.html.twig | 51-56 | ✅ ADDED |
| Cache pas à jour | - | - | ✅ CLEARED |

---

**Status**: ✅ READY FOR TESTING

Une fois que vous avez validé les étapes ci-dessus, le système est **100% fonctionnel**!

