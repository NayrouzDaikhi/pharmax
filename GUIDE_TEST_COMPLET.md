# 🧪 GUIDE DE TEST RAPIDE - MODULE COMMANDES + PANIER

## ✅ VÉRIFICATION PRE-TEST (5 minutes)

### 1️⃣ Vérifier la Base de Données
```bash
cd c:\Users\Asus\Documents\pharmax
php bin/console doctrine:query:sql "SELECT COUNT(*) as produits FROM produit WHERE statut = 1"
php bin/console doctrine:query:sql "SELECT COUNT(*) as commandes FROM commandes"
```

**Résultat attendu:**
- ✅ 4+ produits disponibles (statut = 1/true)
- ✅ 0 commandes initialement (ou plus si tests antérieurs)

### 2️⃣ Vérifier les Routes
```bash
php bin/console debug:router | grep -E "(app_front|app_panier|app_commande|home)"
```

**Routes à Vérifier:**
- ✅ `app_front_produits` → GET /produits
- ✅ `app_front_detail_produit` → GET /produit/{id}
- ✅ `app_panier_index` → /panier/
- ✅ `app_panier_ajouter` → /panier/ajouter/{id}
- ✅ `app_panier_commander` → POST /panier/commander
- ✅ `app_frontend_commande_index` → /commandes/frontend
- ✅ `app_frontend_commande_show` → /commandes/frontend/{id}
- ✅ `app_commande_pdf` → GET /commandes/{id}/pdf
- ✅ `home` → /

---

## 🎬 TEST WORKFLOW COMPLET (15 minutes)

### **Scénario: Compra de 2 produits et commande**

#### **ÉTAPE 1: Accueil**
```
URL: http://127.0.0.1:8000/
✅ Voir: Page d'accueil
✅ Cliquer: "Produits" dans navbar
```

#### **ÉTAPE 2: Voir les Produits**
```
URL: http://127.0.0.1:8000/produits
✅ Voir: Grille de cartes produits
✅ Vérifier: 4+ produits affichés
✅ Chaque produit montre:
   - Image (ou "Pas d'image")
   - Nom
   - Prix
   - Catégorie (badge)
   - 2 boutons: "Ajouter" + "Détails"
```

#### **ÉTAPE 3: Détail Produit #1 (Paracétamol)**
```
URL: http://127.0.0.1:8000/produit/4
✅ Voir: 
   - Image large
   - Nom: "Paracétamol 500mg"
   - Prix: 5.99 DT
   - Catégorie badge
   - Description
   - Date expiration (si présente)
✅ Cliquer: "Ajouter au Panier"
✅ Vérifier: 
   - Flash message: "Produit ajouté au panier!"
   - Redirection: Back to /produits
   - Badge panier navbar: "1"
```

#### **ÉTAPE 4: Détail Produit #2 (Vitamine C)**
```
URL: http://127.0.0.1:8000/produit/5
✅ Cliquer: "Ajouter au Panier"
✅ Flash message: "Produit ajouté au panier! (2 article(s))"
✅ Badge panier navbar: "2"
```

#### **ÉTAPE 5: Voir le Panier**
```
URL: http://127.0.0.1:8000/panier/
✅ Voir: 
   - Tableau avec 2 produits:
     1. Paracétamol 500mg | 5.99 DT | 1 | 5.99 DT | [Supprimer]
     2. Vitamine C 1000mg | 12.5 DT | 1 | 12.5 DT | [Supprimer]
   - Total: 18.49 DT
✅ Maths correctes: 5.99 + 12.5 = 18.49 ✅
✅ Boutons visibles:
   - "Continuer les achats" (gris)
   - "Passer la Commande" (rouge)
```

#### **ÉTAPE 6: Retirer un Produit (Test)**
```
URL: Click [Supprimer] sur Vitamine C
✅ Flash message: "Produit retiré du panier!"
✅ Tableau updated: 1 seul produit (Paracétamol)
✅ Total updated: 5.99 DT
✅ Badge panier: "1"
```

#### **ÉTAPE 7: Re-Ajouter Produit**
```
URL: http://127.0.0.1:8000/produit/5
✅ Cliquer: "Ajouter au Panier"
✅ Flash: "Produit ajouté au panier! (2 article(s))"
✅ Total: 18.49 DT
```

#### **ÉTAPE 8: Créer la Commande**
```
URL: http://127.0.0.1:8000/panier/
✅ Cliquer: "Passer la Commande" (bouton rouge)
✅ Vérifier: POST request to /panier/commander
✅ Redirection: /commandes/frontend/{NEW_ID} (ex: /commandes/frontend/2)
```

#### **ÉTAPE 9: Confirmation Commande**
```
URL: http://127.0.0.1:8000/commandes/frontend/2
✅ Voir:
   - Alert vert: "Commande Confirmée!"
   - #ID: 2
   - Date: 2026-02-12 HH:MM
   - Tableau produits:
     * Paracétamol 500mg | 5.99 DT | 1 | 5.99 DT
     * Vitamine C 1000mg | 12.5 DT | 1 | 12.5 DT
   - Total: 18.49 DT (vérifié)
   - QR Code (scannage optionnel)
✅ Boutons:
   - "Continuer les Achats" (→ /produits)
   - "Mes Commandes" (→ /commandes/frontend)
   - "Télécharger la Facture (PDF)" (→ /commandes/2/pdf)
```

#### **ÉTAPE 10: Télécharger PDF**
```
URL: http://127.0.0.1:8000/commandes/2/pdf
✅ Voir: Téléchargement du fichier "commande_2.pdf"
✅ Ouvrir PDF:
   - Header: "Facture / Commande"
   - #ID: 2
   - Date: 2026-02-12
   - Company: Pharmax
   - Client: contact@pharmax.example
   - Produits table:
     * Paracétamol 500mg | 5.99 | 1 | 5.99
     * Vitamine C 1000mg | 12.5 | 1 | 12.5
   - Total: 18.49 TND
   - QR Code visible
✅ Maths correctes ✅
```

#### **ÉTAPE 11: Voir Mes Commandes**
```
URL: http://127.0.0.1:8000/commandes/frontend
✅ Voir: Grille de cartes commandes
✅ Card pour commande #2:
   - Titre: "Commande #2"
   - Badge statut: "En Attente" (orange)
   - Date: 2026-02-12 ...
   - Produits: 2
   - Montant: 18.49 DT
   - Bouton: "Voir Détails" (→ /commandes/frontend/2)
```

#### **ÉTAPE 12: Admin Dashboard**
```
URL: http://127.0.0.1:8000/admin/commandes
✅ Voir: Dashboard avec stats
✅ Cartes:
   - En cours: N
   - En attente: 1 (la nouvelle commande)
   - Livrée: 0
   - Total: 2 (la test + la nouvelle)
✅ Tableau:
   - Voir commande #2 dans la liste
   - Status: "en_attente"
   - Montant: 18.49
   - Actions: Voir, Modifier, Supprimer
```

#### **ÉTAPE 13: Modifier la Commande (Admin)**
```
URL: http://127.0.0.1:8000/admin/commandes/2
✅ Voir: Détails complets
✅ Form: Modifier statut "en_attente" → "en_cours"
✅ Save: Redirect back aux détails
✅ Verify: Statut updated
```

#### **ÉTAPE 14: Retour au Panier (Panier Vide)**
```
URL: http://127.0.0.1:8000/panier/
✅ Voir: "Votre panier est vide"
✅ Badge navbar: Pas de chiffre (0 ou invisible)
✅ Bouton: "Continuer les achats" → /produits
```

---

## 🚨 TEST DES CAS D'ERREUR

### **Test 1: Produit Inexistant**
```
URL: http://127.0.0.1:8000/produit/99999
✅ Voir: Erreur 404 (Produit non trouvé)
```

### **Test 2: Commande Inexistante**
```
URL: http://127.0.0.1:8000/commandes/frontend/99999
✅ Voir: Erreur 404 (Commande non trouvée)
```

### **Test 3: Ajouter Produit Inexistant au Panier**
```
URL: http://127.0.0.1:8000/panier/ajouter/99999
✅ Flash error: "Produit non trouvé!"
✅ Redirect: /produits
```

### **Test 4: Panier - Quantité Increment**
```
1. Ajouter Paracétamol (quantité: 1)
2. Aller à /panier/ajouter/4 encore
✅ Voir: Quantité devient 2 (pas duplicate entry)
✅ Total: 5.99 × 2 = 11.98 DT
```

---

## 📊 VÉRIFICATION BD POST-TEST

```bash
php bin/console doctrine:query:sql "SELECT COUNT(*) as commandes FROM commandes WHERE statut = 'en_attente'"
# Résultat: 1+ (la commande créée)

php bin/console doctrine:query:sql "SELECT * FROM commandes ORDER BY id DESC LIMIT 1"
# Voir: ID=2, totales=18.49, statut=en_cours (ou en_attente si pas modifié)

php bin/console doctrine:query:sql "SELECT * FROM ligne_commandes WHERE commande_id = 2"
# Résultat: 2 lignes (Paracétamol + Vitamine C)
```

---

## ✅ CHECKLIST FINALE

- [ ] Produits affichés correctement
- [ ] Ajouter au panier fonctionne
- [ ] Panier session persiste
- [ ] Créer commande sauvegarde en BD
- [ ] Page confirmation affiche détails
- [ ] PDF télécharge sans erreur
- [ ] Admin dashboard voit la commande
- [ ] Modifier statut fonctionne
- [ ] QR code affiche sur la page
- [ ] Tous les liens navbar fonctionnent
- [ ] Flash messages apparaissent
- [ ] Responsive design OK (mobile/tablet/desktop)

---

## 🎯 RÉSULTAT ATTENDU

**Status: ✅ WORKFLOW COMPLET FONCTIONNEL**

Après ce test, le système doit:
- ✅ Permettre d'ajouter des produits au panier
- ✅ Calculer correctement les totaux
- ✅ Créer une commande en BD
- ✅ Générer une facture PDF
- ✅ Afficher un QR code
- ✅ Permettre de suivre la commande
- ✅ Laisser l'admin modifier les commandes

---

## 📲 SCREENSHOTS À PRENDRE

1. Page produits (/produits) - grille visible
2. Détail produit (/produit/4) - boutons visibles
3. Panier (/panier/) - tableau + totals
4. Confirmation (/commandes/frontend/2) - alert + QR
5. PDF - facture téléchargée
6. Admin (/admin/commandes) - dashboard

---

**Bon testing! 🚀**
