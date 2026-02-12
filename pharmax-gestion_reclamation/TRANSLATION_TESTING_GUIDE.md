# 📘 Guide de Test - Système de Traduction

## ✅ Étapes d'installation terminées

1. ✅ Bibliothèque `stichoza/google-translate-php` installée
2. ✅ Service `TranslateService` créé
3. ✅ Contrôleurs API créés
4. ✅ Interface UI ajoutée sur les pages admin et frontend
5. ✅ Cache Symfony effacé

---

## 🧪 Comment tester la traduction

### **Option 1: Page de Test Interactive** (Recommandée)
Visitez cette URL: **http://localhost:8000/test/translate**

Cette page offre:
- ✅ Traduction simple d'un texte
- ✅ Traduction multiple vers plusieurs langues
- ✅ Documentation complète des APIs
- ✅ Tests directs sans quitter le navigateur

---

### **Option 2: Tester sur les Réclamations (Admin)**

#### Étapes:
1. Allez sur: **http://localhost:8000/admin/reclamations**
2. Cliquez sur une réclamation pour voir ses détails
3. Vous devriez voir le bouton **🌐 Traduire** en bleu
4. Cliquez sur le bouton pour afficher le dropdown
5. Sélectionnez une langue (ex: English, Español, Deutsch, etc.)
6. La traduction s'affichera dans une section dédiée

**Capture d'écran attendue:**
```
┌─────────────────────────────────────┐
│ Détails Réclamation #1              │
├─────────────────────────────────────┤
│ Titre: Ma réclamation               │
│ Description: ...                    │
│ [Modifier Statut] [🌐 Traduire ▼]  │
│   └─ English                        │
│   └─ Español                        │
│   └─ Deutsch                        │
│   └─ ...                            │
└─────────────────────────────────────┘

┌─ 🌐 Traduction English ────────────┐
│ Titre traduit: My complaint        │
│ Description traduite: ...          │
└────────────────────────────────────┘
```

---

### **Option 3: Tester sur les Réclamations (Frontend/Client)**

#### Étapes:
1. Allez sur: **http://localhost:8000/** (page d'accueil)
2. Vérifiez/créez une réclamation 
3. Cliquez sur une réclamation pour voir ses détails
4. Vous devriez voir le bouton **🌐 Traduire** en blue
5. Sélectionnez une langue
6. La traduction s'affichera dans une section dédiée
7. Si la réclamation a des réponses, chaque réponse a son propre bouton **🌐**

---

### **Option 4: API REST directe**

#### Test 1: Traduction Simple (GET)
```bash
curl "http://localhost:8000/api/translate/text?text=Bonjour%2C%20je%20rencontre%20un%20probl%C3%A8me&targetLang=en"
```

**Réponse attendue:**
```json
{
  "success": true,
  "original": "Bonjour, je rencontre un problème",
  "translated": "Hello, I am having a problem",
  "targetLanguage": "en"
}
```

#### Test 2: Traduction Multiple (POST)
```bash
curl -X POST http://localhost:8000/api/translate/multi \
  -H "Content-Type: application/json" \
  -d '{
    "text": "Problème de livraison",
    "targetLangs": ["en", "es", "de"]
  }'
```

**Réponse attendue:**
```json
{
  "success": true,
  "original": "Problème de livraison",
  "translations": {
    "en": "Delivery problem",
    "es": "Problema de entrega",
    "de": "Lieferproblem"
  }
}
```

#### Test 3: Traduction Réclamation
```bash
curl "http://localhost:8000/reclamations/1/translate/es"
```

**Réponse attendue:**
```json
{
  "id": 1,
  "titre_original": "Problème de livraison",
  "titre_traduit": "Problema de entrega",
  "description_original": "Ma commande n'a pas été livrée",
  "description_traduite": "Mi pedido no fue entregado",
  "langue_cible": "es",
  "statut": "En attente"
}
```

---

## 🌍 Langues Supportées

| Code | Langue | Code | Langue |
|------|--------|------|--------|
| en | English | pt | Português |
| es | Español | ja | 日本語 |
| de | Deutsch | zh | 中文 |
| it | Italiano | ar | العربية |
| fr | Français | ru | Русский |

*Et beaucoup d'autres! Plus de 100 langues sont supportées.*

---

## 🎯 Fonctionnalités Implémentées

### Page Admin (Backend)
- ✅ Bouton dropdown de traduction sur chaque réclamation
- ✅ Affichage instantané de la traduction
- ✅ Support de 9 langues
- ✅ Section dédiée pour la traduction

### Page Frontend (Client)
- ✅ Bouton dropdown de traduction pour la réclamation
- ✅ Bouton dropdown de traduction pour chaque réponse
- ✅ Support multi-réponses
- ✅ Affichage élégant des traductions

### API
- ✅ `/api/translate/text` - Traduction simple
- ✅ `/api/translate/multi` - Traduction multiple
- ✅ `/reclamations/{id}/translate/{lang}` - Traduction réclamation

---

## 📁 Fichiers Créés/Modifiés

### Créés:
```
src/Service/TranslateService.php
src/Controller/TranslateController.php
src/Controller/TestTranslateController.php
templates/test/translate.html.twig
```

### Modifiés:
```
src/Controller/ReclamationController.php
templates/backend/reclamation/show.html.twig
templates/frontend/reclamation/show.html.twig
```

---

## 🐛 Troubleshooting

### Le dropdown ne s'affiche pas?
- Vérifiez que Bootstrap est bien chargé dans `base.html.twig`
- Vérifiez la console du navigateur (F12) pour les erreurs JavaScript

### La traduction ne fonctionne pas?
- Vérifiez que le serveur est bien en train de fonctionner
- Vérifiez la connexion Internet (Google Translate a besoin d'une connexion)
- Vérifiez la console du navigateur pour les erreurs AJAX

### Le service TranslateService n'est pas trouvé?
- Assurez-vous d'avoir exécuté: `php bin/console cache:clear`
- Vérifiez que le fichier est dans `src/Service/TranslateService.php`

---

## ✨ Améliorations Possibles

1. **Cache des traductions** - Stocker les traductions déjà effectuées en base de données
2. **Sélection de langue par défaut** - Utiliser la langue du navigateur de l'utilisateur
3. **Export PDF traduit** - Générer un PDF de la réclamation traduite
4. **Webhooks** - Traduire automatiquement lors de certains événements
5. **UI personnalisée** - Créer des drapeaux de pays pour les langues

---

## 📞 Support

En cas de problème:
1. Vérifiez le fichier `var/log/dev.log`
2. Consultez la page de test: http://localhost:8000/test/translate
3. Utilisez les outils de développement du navigateur (F12)

Bonne chance! 🚀
