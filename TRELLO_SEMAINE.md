# 📋 TABLEAU BLANC - Illustration d'une Semaine (Trello)

**Semaine Affichée**: Semaine 2 du Sprint 1 (du 6 au 11 février 2026)  
**Équipe**: 4 développeurs  
**Focus**: Finalisation API Modération IA + Tests

---

## 🎯 Vue d'ensemble Trello Board

```
╔════════════════════════════════════════════════════════════════════════════╗
║                         PHARMAX SPRINT 1 - WEEK 2                          ║
║                     (Semaine du 6 au 11 février 2026)                      ║
╠════════════════════════════════════════════════════════════════════════════╣
║
║  📋 BACKLOG           📍 TO DO             🔄 IN PROGRESS      ✅ DONE
║  ─────────────────────  ──────────────────  ─────────────────   ────────
║
║  [5] API Dashboard    [1] Tests API       [A] GeminiService   [✓] US#1
║  [6] Chat Bot         [2] Fallback Setup  [B] Moderation      [✓] CRUD
║  [7] Profile Mgmt     [3] Email notif.    [C] Archive Entity  [✓] Forms
║  [8] Reports          [4] Docs API        [D] Frontend JS     [✓] Tests
║      (Future)                                                   [✓] Deploy
║
╚════════════════════════════════════════════════════════════════════════════╝
```

---

## 📅 LUNDI 6 FÉVRIER

### Priorités du jour
- ✅ Finaliser US#1 (Gestion Produits) - Tests finaux
- 🔄 Démarrer US#2 (Modération IA)
- 📊 Setup Gemini API credentials

### Tableau Trello

```
┌─────────────────────────────────────────────────────────────────────┐
│ 📌 BACKLOG                                                           │
├─────────────────────────────────────────────────────────────────────┤
│                                                                      │
│  💳 [BACKLOG] Chat Bot Integration                                 │
│     Points: 18 | Priority: P1 | Est. J9-J10                        │
│     Due: 11/02                                                      │
│                                                                      │
│  💳 [BACKLOG] User Profile Management                              │
│     Points: 13 | Priority: P2 | Sprint 2                           │
│                                                                      │
└─────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────┐
│ 📍 TO DO                                                             │
├─────────────────────────────────────────────────────────────────────┤
│                                                                      │
│  💳 [TODO] Setup Gemini API                                        │
│     Assignée: Alice (Dev Lead) 👨‍💻                                 │
│     Points: 3 | Priority: P0 URGENT                                │
│     Labels: [API] [Config]                                         │
│     ⏰ Due Today (6/02)                                            │
│     Description:                                                   │
│       - Get API key from Google Cloud                              │
│       - Setup env variables                                        │
│       - Test auth                                                  │
│                                                                      │
│  💳 [TODO] Complete Moderation Tests                               │
│     Assignée: Bob (QA) 👨‍🔧                                        │
│     Points: 4 | Priority: P1                                       │
│     Labels: [Testing] [API]                                        │
│     ⏰ Due: 8/02                                                   │
│     Tests to cover:                                                │
│       - Approved comments (✓ 201)                                  │
│       - Blocked comments (✗ 403)                                   │
│       - API errors (timeout, etc.)                                 │
│                                                                      │
│  💳 [TODO] Setup Fallback HuggingFace                              │
│     Assignée: Carol (Dev) 👩‍💻                                      │
│     Points: 3 | Priority: P1                                       │
│     Labels: [API] [Config]                                         │
│     ⏰ Due: 9/02                                                   │
│     If Gemini fails, use HuggingFace API                           │
│                                                                      │
│  💳 [TODO] Frontend JS - Comment Form Integration                  │
│     Assignée: David (Frontend) 🎨                                  │
│     Points: 4 | Priority: P0 URGENT                                │
│     Labels: [Frontend] [JS]                                        │
│     ⏰ Due: 10/02                                                  │
│     Blocked by: GeminiService completion                           │
│                                                                      │
└─────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────┐
│ 🔄 IN PROGRESS                                                       │
├─────────────────────────────────────────────────────────────────────┤
│                                                                      │
│  💳 [WIP] Create GeminiService                                     │
│     Assignée: Alice (Dev Lead) 👨‍💻                                 │
│     Points: 4 | Priority: P0 CRITICAL                              │
│     Time Spent: 2h30m | Est. Completion: 3h30m                     │
│     Labels: [Backend] [Service] [API]                              │
│     Progress: ████░░░░░░ (40%)                                     │
│     Today's tasks:                                                 │
│       ✅ API authentication setup                                  │
│       🔄 Prompt engineering (working on...)                        │
│       ⏳ Response parsing                                          │
│       ⏳ Error handling                                            │
│     Blockers: None                                                 │
│     Next: Integrate entity mapping                                 │
│                                                                      │
│  💳 [WIP] CommentModerationService Enhancement                     │
│     Assignée: Carol (Dev) 👩‍💻                                      │
│     Points: 3 | Priority: P1                                       │
│     Time Spent: 1h | Est. Completion: 2h                           │
│     Labels: [Backend] [Service]                                    │
│     Progress: ███░░░░░░░ (30%)                                     │
│     Today's tasks:                                                 │
│       ✅ Mots-clés list updated                                    │
│       🔄 Bicouche detection logic (in progress...)                 │
│       ⏳ Unit tests                                                │
│     Blockers: Waiting for prompt examples from Alice               │
│     Next: Add sentiment threshold configuration                    │
│                                                                      │
│  💳 [WIP] CommentaireArchive Entity & Migration                    │
│     Assignée: Alice (Dev Lead) 👨‍💻                                 │
│     Points: 3 | Priority: P1                                       │
│     Time Spent: 45m | Est. Completion: 1h30m                       │
│     Labels: [Backend] [Entity] [DB]                                │
│     Progress: ██░░░░░░░░ (20%)                                     │
│     Today's tasks:                                                 │
│       ✅ Entity structure defined                                  │
│       🔄 Doctrine migration creation (in progress...)              │
│       ⏳ Database execution                                        │
│     Next: Test entity relationships                                │
│                                                                      │
└─────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────┐
│ ✅ DONE                                                              │
├─────────────────────────────────────────────────────────────────────┤
│                                                                      │
│  ✓ [DONE] User Story #1: Gestion Produits (CRUD)                  │
│    Completed: 5/02 | Points: 21 | Status: ✅ Production Ready      │
│    - All CRUD operations tested ✅                                 │
│    - Image upload functional ✅                                    │
│    - Form validations complete ✅                                  │
│    - Tests passing 100% ✅                                         │
│    - Ready for demo ✅                                             │
│                                                                      │
│  ✓ [DONE] Entity & Controllers Setup (Day 1-2)                    │
│    Completed: 4/02 | Points: 5                                     │
│    - Produit entity created ✅                                     │
│    - ProduitController generated ✅                                │
│    - Routes configured ✅                                          │
│                                                                      │
│  ✓ [DONE] Templates & Forms (Day 3-4)                             │
│    Completed: 5/02 | Points: 5                                     │
│    - index.twig listing ✅                                         │
│    - show.twig detail ✅                                           │
│    - edit.twig form ✅                                             │
│    - Form builder ProduitType ✅                                   │
│                                                                      │
│  ✓ [DONE] Testing & Bug Fixes (Day 5)                             │
│    Completed: 5/02 | Points: 3                                     │
│    - Unit tests ✅                                                 │
│    - Integration tests ✅                                          │
│    - 3 bugs fixed ✅                                               │
│    - Ready deployment ✅                                           │
│                                                                      │
└─────────────────────────────────────────────────────────────────────┘
```

---

## 📊 Points de Suivi Lundi

| Colonne | Nombre de Cartes | Points Totaux | État |
|---------|------------------|---------------|------|
| 📋 Backlog | 2 | 31 | Normal |
| 📍 To Do | 4 | 14 | ⚠️ À réduire |
| 🔄 In Progress | 3 | 10 | ✅ Bon |
| ✅ Done | 4 | 21 | ✅ Excellent |
| **TOTAL** | **13** | **76** | ✅ **Bon rythme** |

---

## 📅 MARDI 7 FÉVRIER (Mid-Sprint Status)

### Réunion Standup: 9h30

```
Participants: Alice, Bob, Carol, David + Scrum Master

ALICE (Dev Lead):
  ✅ Complété: Setup Gemini API (credentials, auth)
  🔄 En cours: GeminiService implementation (60% done)
  ⏳ Bloqué: Waiting on prompt refinement feedback
  🎯 Today plan: Finish GeminiService, start API endpoint
  📊 Estimate: ✅ On track

BOB (QA):
  ✅ Complété: Test plan for moderation
  🔄 En cours: Running preliminary API tests
  ⏳ Bloqué: None
  🎯 Today plan: Complete all 20 moderation test cases
  📊 Estimate: ✅ On track

CAROL (Dev):
  ✅ Complété: Mots-clés list finalized (52 keywords)
  🔄 En cours: Bicouche detection logic (70% done)
  ⏳ Bloqué: None
  🎯 Today plan: Complete CommentModerationService tests
  📊 Estimate: ✅ On track

DAVID (Frontend):
  ✅ Complété: Comment form wireframe
  🔄 En cours: Waiting for backend API endpoint
  ⏳ Bloqué: API endpoint not ready yet (will be ready Wed)
  🎯 Today plan: Start JavaScript fetch implementation
  📊 Estimate: 🔴 Slightly behind (but acceptable)

SCRUM MASTER:
  📊 Sprint Progress: 36/55 points done (65%)
  🎯 Target: 45/55 by Friday (81%)
  ✅ Burndown: On track! Slight acceleration seen
  ⚠️ Risk: David frontend might slip (waiting for API)
  🛠️ Mitigation: Alice to prioritize API endpoint
```

### Tableau Trello Mardi

```
┌─────────────────────────────────────────────────────────────────────┐
│ IN PROGRESS (Updated 11:00 AM)                                      │
├─────────────────────────────────────────────────────────────────────┤
│                                                                      │
│  💳 [WIP] GeminiService (Updated)                                  │
│     Assignée: Alice 👨‍💻                                           │
│     Progress: ████████░░ (60%) ← Updated from 40%                  │
│     Time Spent: 5h15m | Remaining: 2h                              │
│     Completed tasks:                                               │
│       ✅ API authentication                                        │
│       ✅ Prompt engineering                                        │
│       ✅ Response parsing                                          │
│     In progress:                                                   │
│       🔄 Error handling (timeout, quota)                           │
│     Next:                                                          │
│       ⏳ Create API endpoint controller                             │
│     Due: TODAY EOD ✅                                              │
│                                                                      │
│  💳 [WIP] CommentModerationService (Updated)                       │
│     Assignée: Carol 👩‍💻                                           │
│     Progress: ███████░░░ (70%) ← Updated from 30%                  │
│     Time Spent: 3h | Remaining: 1h30m                              │
│     Completed tasks:                                               │
│       ✅ Mots-clés detection                                       │
│       ✅ Bicouche logic                                            │
│     In progress:                                                   │
│       🔄 Final validation tests                                    │
│     Due: TODAY EOD ✅                                              │
│                                                                      │
│  💳 [WIP] Frontend Comment Form JS (NEW!)                          │
│     Assignée: David 🎨                                            │
│     Progress: ██░░░░░░░░ (10%) ← Just started                      │
│     Time Spent: 30m | Remaining: 3h30m                             │
│     Started tasks:                                                 │
│       ✅ Form HTML template reviewed                               │
│       🔄 Event listeners attachment                                │
│     Blocked by: API endpoint not finalized                         │
│     Status: 🔴 Minor blocker (waiting API)                         │
│     Due: When API ready (Wed expected)                             │
│                                                                      │
│  💳 [WIP] Test Suite - Moderation Cases (NEW!)                     │
│     Assignée: Bob 👨‍🔧                                            │
│     Progress: ██████░░░░ (50%)                                      │
│     Time Spent: 2h30m | Remaining: 2h30m                           │
│     Completed tests:                                               │
│       ✅ Positive comment (approved)                               │
│       ✅ Negative keywords (blocked)                               │
│     In progress:                                                   │
│       🔄 Mixed sentiment tests                                     │
│       🔄 API timeout handling                                      │
│     Due: Thursday ✅                                               │
│                                                                      │
└─────────────────────────────────────────────────────────────────────┘
```

---

## 📅 VENDREDI 11 FÉVRIER (End of Week Sprint Status)

### ✨ SEMAINE RÉSUMÉE

```
╔═══════════════════════════════════════════════════════════════════════╗
║                    📊 RÉSUMÉ DE LA SEMAINE 2                          ║
║                     (6 - 11 février 2026)                             ║
╚═══════════════════════════════════════════════════════════════════════╝

Points Complétés:
  Lundi (6/02):   21 points (US#1 finalisée) + 3 (Gemini setup)
  Mardi (7/02):   6 points (Services avancés)
  Mercredi (8/02): 8 points (API endpoint + tests)
  Jeudi (9/02):   8 points (Frontend + fallback)
  Vendredi (10/02): 9 points (Tests finaux + docs)
  
  TOTAL: 55 points ✅ SPRINT COMPLETE!

Burndown:
  Jour 1 (6/02):  55 - 24 = 31 remaining
  Jour 2 (7/02):  31 - 6  = 25 remaining
  Jour 3 (8/02):  25 - 8  = 17 remaining
  Jour 4 (9/02):  17 - 8  = 9 remaining
  Jour 5 (10/02): 9 - 9   = 0 remaining ✅

Quality Metrics:
  ✅ Tests: 100% pass rate (42/42 tests)
  ✅ Code Review: All PRs approved
  ✅ Bugs: 0 critical, 2 minor (logged for later)
  ✅ Documentation: Complete

Team Velocity:
  Real Velocity: 11 pts/jour (excellent!)
  → Faster than planned (5.5 target)
  → Ready for ambitious Sprint 2
```

### Trello Board Vendredi EOD

```
┌─────────────────────────────────────────────────────────────────────┐
│ ✅ DONE (End of Sprint - ALL COMPLETE!)                            │
├─────────────────────────────────────────────────────────────────────┤
│                                                                      │
│  ✓ US#1: Gestion Produits (CRUD) - 21 pts                         │
│  ✓ GeminiService impl. - 4 pts                                     │
│  ✓ CommentModerationService - 3 pts                                │
│  ✓ CommentaireArchive Entity - 3 pts                               │
│  ✓ API Endpoint POST /api/commentaires - 4 pts                    │
│  ✓ Frontend JS integration - 4 pts                                 │
│  ✓ Fallback HuggingFace - 3 pts                                    │
│  ✓ Test Suite Complete - 4 pts                                     │
│  ✓ Documentation - 2 pts                                           │
│                                                                      │
│  🎉 TOTAL: 55/55 POINTS COMPLETE! 🎉                              │
│                                                                      │
└─────────────────────────────────────────────────────────────────────┘

Sprint Goal Achieved: ✅ YES
  ✅ Deux user stories complétées (CRUD + API IA)
  ✅ API Modération fonctionnelle en production
  ✅ Couverture de tests > 95%
  ✅ Zero blockers
```

---

## 📈 Statistiques de Trello

| Métrique | Semaine 1 | Semaine 2 | Variation |
|----------|-----------|-----------|-----------|
| Cards complétées | 4 | 9 | +125% |
| Points brûlés | 21 | 34 | +62% |
| Velocity moyenne | 5.5 pts/j | 6.8 pts/j | +24% |
| Retards | 1 jour | 0 jours | -100% ✅ |
| Bugs découverts | 3 | 2 | -33% ✅ |
| Test coverage | ~80% | >95% | +19% ✅ |

---

## 🎯 Points Clés de la Semaine

1. **Momentum**: Équipe accélère en fin de sprint (très positif!)
2. **Communication**: Standups efficaces, blockers résolus rapidement
3. **Quality**: Tests complètes => Production-ready code
4. **Collaboration**: Pair programming a été utile pour Alice+Carol
5. **Next Sprint**: Team capacity peut augmenter (velocity démontrée)

