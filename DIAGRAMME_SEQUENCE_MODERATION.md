# 📊 DIAGRAMME DE SÉQUENCE - Système de Modération IA

## Fonctionnalité Avancée: Modération Automatique des Commentaires (API Gemini)

### Scénario: Un utilisateur publie un commentaire sur un article

```
Utilisateur              Navigateur            Serveur              API Gemini          Base de Données
    |                        |                    |                      |                    |
    |--Remplir formulaire --> |                    |                      |                    |
    |                        |                    |                      |                    |
    |<--Afficher formulaire--|                    |                      |                    |
    |                        |                    |                      |                    |
    |--Clic "Poster"-------> |                    |                      |                    |
    |                        |                    |                      |                    |
    |                        |--POST /api/commentaires-->                 |                    |
    |                        |   { articleId, auteur, contenu }          |                    |
    |                        |                    |                      |                    |
    |                        |                    |1. Validation Mots-clés                   |
    |                        |                    |   (CommentModerationService)             |
    |                        |                    |                      |                    |
    |                        |                    |2. if(mots-clés trouvés)                 |
    |                        |                    |   └─> Archiver + 403 ✗                  |
    |                        |                    |                      |                    |
    |                        |                    |3. Sinon, Appel API Gemini=================>
    |                        |                    | POST /v1beta/generateContent            |
    |                        |                    | Prompt: "Analyser sentiment..."          |
    |                        |                    |                      |                    |
    |                        |                    |<==Response: Score ✓/✗                   |
    |                        |                    |                      |                    |
    |                        |                    |4. Vérifier score Gemini                  |
    |                        |                    |   if(score < 0.5) → 403 ✗                |
    |                        |                    |                      |                    |
    |                        |                    |5. Sinon, Sauvegarder Commentaire=======>|
    |                        |                    |   INSERT INTO commentaire               |
    |                        |                    |   (articleId, auteur, contenu, status)  |
    |                        |                    |                      |                  |
    |                        |                    |<==INSERT OK (201)==========================|
    |                        |                    |                      |                    |
    |                        | <--JSON 201 Created---|                      |                    |
    |                        |                    |                      |                    |
    |<--Afficher ✓--------- |                    |                      |                    |
    |  "Commentaire publié"  |                    |                      |                    |
    |                        |                    |                      |                    |

```

---

## 🔄 Flux Détaillé: 3 Cas de Résultat

### Case 1: ✅ Commentaire APPROUVÉ (Score > 0.5)

```
1. Utilisateur soumet: "Excellent article très utile!"

2. Validation Mots-clés:
   ✅ Aucun mot-clé trouvé

3. Appel API Gemini:
   {
     "model": "gemini-2.0-flash",
     "contents": [{
       "parts": [{
         "text": "Analyser: 'Excellent article très utile!'. 
                   Sentiment (0-1)? Approprié (oui/non)?"
       }]
     }]
   }

4. Response Gemini:
   {
     "candidates": [{
       "content": {
         "parts": [{
           "text": "Score: 0.92 | Sentiment positif | Approprié: OUI"
         }]
       }
     }]
   }

5. Score 0.92 > 0.5 ✅

6. Sauvegarde:
   INSERT INTO commentaire 
   VALUES (NULL, 1, 'User', 'Excellent...', 'VALIDE', NOW())

7. Réponse API:
   HTTP 201 Created
   {
     "id": 45,
     "status": "published",
     "message": "Commentaire publié avec succès!"
   }

8. UX: Message vert "✓ Commentaire publié!"
```

---

### Case 2: ❌ Commentaire REJETÉ - Mots-clés (Detection immédiate)

```
1. Utilisateur soumet: "SPAM spam spam buy now!"

2. Validation Mots-clés (CommentModerationService):
   → Détection: "SPAM", "spam", "buy now" ❌
   → Match trouvé!

3. Archivage immédiat:
   INSERT INTO commentaire_archive
   VALUES (NULL, 1, 'User', 'SPAM...', 'inappropriate', 'keywords_detected', NOW())

4. Réponse API (SANS appel Gemini, économie temps):
   HTTP 403 Forbidden
   {
     "status": "rejected",
     "reason": "harmful_content_detected",
     "message": "Commentaire contient du contenu non autorisé"
   }

5. UX: Message rouge "✗ Commentaire rejeté (contenu inapproprié)"
```

---

### Case 3: ❌ Commentaire REJETÉ - IA Gemini (Score < 0.5)

```
1. Utilisateur soumet: "Article pourri, arnaque totale!"

2. Validation Mots-clés:
   ✅ Aucun mots-clés (arnaque.com n'est pas dans la liste)

3. Appel API Gemini:
   Prompt: "Analyser: 'Article pourri, arnaque...'
            Sentiment (0-1)? Approprié (oui/non)?"

4. Response Gemini:
   {
     "candidates": [{
       "content": {
         "parts": [{
           "text": "Score: 0.25 | Sentiment négatif | Approprié: NON"
         }]
       }
     }]
   }

5. Score 0.25 < 0.5 ❌

6. Archivage:
   INSERT INTO commentaire_archive
   VALUES (NULL, 1, 'User', 'Article...', 'inappropriate', 
           'ai_negative_sentiment', NOW())

7. Réponse API:
   HTTP 403 Forbidden
   {
     "status": "rejected",
     "reason": "negative_sentiment_detected",
     "message": "Votre commentaire ne respecte pas nos règles"
   }

8. UX: Message rouge "✗ Commentaire rejeté (sentiment négatif)"
```

---

## 🔐 Gestion des Erreurs

### Si API Gemini non disponible:

```
Utilisateur soumet commentaire
    ↓
Validation Mots-clés: ✅ Pass
    ↓
Appel API Gemini: ❌ TIMEOUT/ERROR
    ↓
FALLBACK: Utiliser HuggingFace API
    ├─ Retry avec timeout plus court
    └─ Si pas encore disponible:
       └─ Utiliser simple Keyword Check
           └─ Si aucun indice négatif detected:
              └─ Approuver en mode "safe"

Résultat: Commentaire sauvegardé avec flag "moderate_later"
```

---

## 📊 Statistiques d'Exécution

| Cas | Temps Réponse | Appel DB | Appel API | Status HTTP |
|-----|---------------|----------|-----------|------------|
| Approuvé (IA) | ~800ms | 1 INSERT | 1 call | 201 |
| Rejeté (Mots-clés) | ~50ms | 1 INSERT archive | 0 calls | 403 |
| Rejeté (IA) | ~900ms | 1 INSERT archive | 1 call | 403 |
| Erreur API | ~2000ms | 1 INSERT archive | 1 retry + fallback | 400/403 |

---

## 🎯 Points Clés de cette Fonctionnalité Avancée

1. **Bicouche Detection**: Mots-clés (rapide) + IA (précis)
2. **API Externe**: Intégration Gemini API en temps réel
3. **Fallback Strategy**: Système hybride en cas de panne
4. **Real-time Processing**: Réponse < 1s utilisateur
5. **Audit Trail**: Tous les rejets archivés avec raison
6. **UX Feedback**: Messages clairs selon raison rejet

