# 🎯 MODULE RÉCLAMATIONS - SPRINT 3

**Status**: 📋 En Planification  
**User Stories**: US#7 + US#8  
**Points Totaux**: 30 pts (16 + 14)  
**Durée Estimée**: 2 semaines

---

## 🔧 USER STORY #7: CRUD RÉCLAMATIONS (16 pts)

### Description
En tant que **client**, je veux **soumettre une réclamation** et en tant que **modérateur**, **gérer les réclamations avec workflow complet** afin de **résoudre les problèmes efficacement**.

### Critères d'Acceptation

```
✓ CLIENT: Créer réclamation
  - Formulaire: titre, description, type (select), priorité
  - Types: Livraison, Qualité, Facturation, Autre
  - Priorités: Basse, Normale, Urgente
  - Pièces jointes: Upload images/documents (max 10MB x 3)
  - Email confirmation: Envoi ticket number
  - Référence commande optionnelle

✓ CLIENT: Tracker réclamation
  - Page "Mes réclamations" avec statuts
  - Timeline des mises à jour
  - Notifications email à chaque changement

✓ ADMIN: Lister réclamations
  - Dashboard avec filtre: Statut, Priorité, Date
  - Tri: Par date desc, par priorité
  - Statuts: En attente, En cours, Résolu, Fermé, Réouvert
  - Assigner à agent support (dropdown)
  - Réassigner si nécessaire
  - Bulk actions: Marquer résolu, fermer, etc.

✓ ADMIN: Afficher détails réclamation
  - Infos client
  - Historique statut (timeline)
  - Tous les commentaires/réponses
  - Pièces jointes
  - Bouton "Résoudre" avec raison

✓ ADMIN: Ajouter réponse
  - TextArea pour réponse personnalisée
  - Suggestion de réponses automatiques (voir US#8)
  - Marquer fini après réponse

✓ ADMIN: Fermer réclamation
  - Afficher formulaire satisfaction (1-5 stars)
  - Optionnel: sondage feedback
  - Email récap au client
  - Archive réclamation

✓ CLIENT: Réouvrir réclamation
  - Si pas satisfait (< 3 stars)
  - Ajouter raison
  - Re-soumis à l'équipe
```

### Tâches Techniques

```
MODELS/ENTITIES:

[ ] Améliorer Reclamation Entity
    ├─ Ajouter: priority (enum: LOW, NORMAL, URGENT)
    ├─ Ajouter: assigned_to (FK → User/Agent)
    ├─ Ajouter: resolved_at (datetime nullable)
    ├─ Ajouter: closed_at (datetime nullable)
    ├─ Ajouter: satisfaction_score (int 1-5, nullable)
    ├─ Ajouter: reopened_at (datetime nullable)
    ├─ Ajouter: resolution_notes (text)
    └─ Ajouter: tags (array for categorization)

[ ] NEW: ReclamationAttachment Entity
    ├─ id, reclamation_id (FK), filename, filepath
    ├─ file_type, file_size, uploaded_at
    └─ Validation: Type whitelist (pdf, jpg, png, docx)

[ ] NEW: ReclamationStatus Entity (pour timeline)
    ├─ id, reclamation_id (FK), old_status, new_status
    ├─ changed_at (datetime), changed_by (FK)
    ├─ comment (reason for change)
    └─ auto_created (bool, true si système)

[ ] NEW: Response Entity (pour réponses agent)
    ├─ id, reclamation_id (FK), author_id (FK)
    ├─ content (text), is_automated (bool)
    ├─ created_at (datetime), updated_at
    └─ attachments (relation)

CONTROLLERS:

[ ] ReclamationController (Client)
    ├─ GET /reclamation/new → formulaire création
    ├─ POST /reclamation → créer
    ├─ GET /reclamation → liste mes réclamations
    ├─ GET /reclamation/{id} → détail
    ├─ POST /reclamation/{id}/response → ajouter réponse
    ├─ POST /reclamation/{id}/reopen → réouvrir
    └─ POST /reclamation/{id}/satisfaction → voter

[ ] Admin / AdminReclamationController
    ├─ GET /admin/reclamation → dashboard
    ├─ GET /admin/reclamation/{id} → détail admin
    ├─ PUT /admin/reclamation/{id}/status → changer statut
    ├─ PUT /admin/reclamation/{id}/assign → assigner agent
    ├─ POST /admin/reclamation/{id}/response → réponse agent
    ├─ PUT /admin/reclamation/{id}/resolve → résolver
    ├─ DELETE /admin/reclamation/{id}/archive → archiver
    └─ GET /admin/reclamation/stats → statistiques

SERVICES:

[ ] ReclamationWorkflowService
    ├─ transitionStatus(Reclamation, $newStatus)
    ├─ resolveReclamation(Reclamation, $reason)
    ├─ closeReclamation(Reclamation)
    ├─ reopenReclamation(Reclamation)
    ├─ assignToAgent(Reclamation, $agent)
    └─ recordStatusChange(Reclamation, $from, $to, $by)

[ ] ReclamationNotificationService
    ├─ notifyCreated(Reclamation)
    ├─ notifyStatusChanged(Reclamation, $event)
    ├─ notifyAssigned(Reclamation, $agent)
    ├─ notifyResolved(Reclamation)
    └─ Envoie emails + in-app notifications

[ ] ReclamationAttachmentService
    ├─ uploadAttachment($file) → validate & store
    ├─ deleteAttachment($id)
    └─ getAttachmentPath($ref) → secure download

TEMPLATES:

[ ] Client UI
    ├─ templates/reclamation/new.html.twig → Créer
    ├─ templates/reclamation/index.html.twig → Liste
    ├─ templates/reclamation/show.html.twig → Détail
    └─ templates/reclamation/fragments/timeline.twig → Timeline

[ ] Admin UI
    ├─ templates/admin/reclamation/index.html.twig → Dashboard
    ├─ templates/admin/reclamation/show.html.twig → Détail
    └─ templates/admin/reclamation/stats.html.twig → Stats

TESTS:

[ ] ReclamationControllerTest
    ├─ Test création réclamation
    ├─ Test affichage détail
    ├─ Test réouverture
    ├─ Test upload pièces jointes
    └─ Test satisfaction voting

[ ] ReclamationWorkflowTest
    ├─ Test transitions statuts valides
    ├─ Test transitions statuts invalides
    ├─ Test assignation
    ├─ Test timeline enregistrée

[ ] Notification Tests
```

### Base de Données Migrations

```sql
-- Réclamation (amélioration)
ALTER TABLE reclamation
  ADD COLUMN priority VARCHAR(50) DEFAULT 'NORMAL',
  ADD COLUMN assigned_to INT NULL,
  ADD COLUMN resolved_at DATETIME NULL,
  ADD COLUMN closed_at DATETIME NULL,
  ADD COLUMN satisfaction_score INT(1) NULL,
  ADD COLUMN reopened_at DATETIME NULL,
  ADD COLUMN resolution_notes LONGTEXT NULL,
  ADD COLUMN tags JSON DEFAULT NULL,
  ADD FOREIGN KEY (assigned_to) REFERENCES `user`(id) ON DELETE SET NULL;

-- ReclamationAttachment (NEW)
CREATE TABLE reclamation_attachment (
  id INT PRIMARY KEY AUTO_INCREMENT,
  reclamation_id INT NOT NULL,
  filename VARCHAR(255) NOT NULL,
  filepath VARCHAR(500) NOT NULL,
  file_type VARCHAR(50),
  file_size INT,
  uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (reclamation_id) REFERENCES reclamation(id) ON DELETE CASCADE
);

-- ReclamationStatus (NEW - Timeline)
CREATE TABLE reclamation_status (
  id INT PRIMARY KEY AUTO_INCREMENT,
  reclamation_id INT NOT NULL,
  old_status VARCHAR(50),
  new_status VARCHAR(50) NOT NULL,
  changed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  changed_by INT,
  comment TEXT,
  auto_created BOOLEAN DEFAULT FALSE,
  FOREIGN KEY (reclamation_id) REFERENCES reclamation(id) ON DELETE CASCADE,
  FOREIGN KEY (changed_by) REFERENCES `user`(id) ON DELETE SET NULL
);

-- Response (NEW)
CREATE TABLE reclamation_response (
  id INT PRIMARY KEY AUTO_INCREMENT,
  reclamation_id INT NOT NULL,
  author_id INT NOT NULL,
  content LONGTEXT NOT NULL,
  is_automated BOOLEAN DEFAULT FALSE,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (reclamation_id) REFERENCES reclamation(id) ON DELETE CASCADE,
  FOREIGN KEY (author_id) REFERENCES `user`(id) ON DELETE CASCADE
);

-- Indexes
CREATE INDEX idx_reclamation_status ON reclamation(statut);
CREATE INDEX idx_reclamation_assigned ON reclamation(assigned_to);
CREATE INDEX idx_reclamation_priority ON reclamation(priority);
CREATE INDEX idx_reclamation_date ON reclamation(date_creation DESC);
CREATE INDEX idx_reclamation_satisfaction ON reclamation(satisfaction_score);
```

### Workflow État Réclamation

```
                    ┌─────────────────────┐
                    │   CRÉÉE (En attente)│
                    │  Client submises    │
                    └──────────┬──────────┘
                               │
                        Assignée à Agent
                               │
                    ┌──────────▼──────────┐
                    │  ASSIGNÉE (En cours)│
                    │  Agent investigates │
                    └──────────┬──────────┘
                               │
                        Agent répond
                               │
                    ┌──────────▼──────────┐
                    │   EN_COURS (Resolv.)│
                    │  Awaiting feedback  │
                    └──────────┬──────────┘
                      ┌────────┴────────┐
          Client               │         Client
          satisfied?           │         réouvre?
          (Vote ≥3★)           │         (Vote <3★)
               │                │            │
          Oui │                │         Non│
               │                │            │
         ┌─────▼──┐        ┌────▼─────┐    │
         │RÉSOLUE │        │ RÉOUVERTE│◄───┘
         └────────┘        └────┬─────┘
                                 │
                           Re-assigner
                                 │
                                 ▼
                          (back to EN_COURS)
         
         ┌─────────────────────┐
         │   FERMÉE (Archived) │
         │  Case closed        │
         └─────────────────────┘
```

### Cas de Test

```php
// Test 1: Créer réclamation
POST /reclamation/new
Form {
  titre: "Produit endommagé",
  description: "J'ai reçu un médicament avec l'emballage cassé",
  type: "QUALITE",
  priorite: "NORMAL",
  commande_ref: "#CMD-12345",
  attachments: [image.jpg]
}
→ 302 Redirect /reclamation/X
→ Email envoyé au client: Ticket #REC-2026-001234

// Test 2: Assigner à agent (Admin)
PUT /admin/reclamation/X/assign
{
  agent_id: 5
}
→ 200 OK
→ Email Agent: "Nouvelle réclamation assignée"
→ Timeline mise à jour

// Test 3: Ajouter réponse (Agent)
POST /admin/reclamation/X/response
{
  content: "Nous envoyons un remplacement...",
  is_automated: false
}
→ 201 Created
→ Email Client: "Nouvelle réponse à votre réclamation"

// Test 4: Marquer résolu (Agent)
PUT /admin/reclamation/X/resolve
{
  resolution_notes: "Remplacement expédié le 2026-02-15"
}
→ 200 OK
→ Status: RESOLU
→ Email Client + Customer feedback form

// Test 5: Client vote satisfaction
POST /reclamation/X/satisfaction
{
  score: 4,
  comment: "Bien gérée mais un peu lent"
}
→ 200 OK
→ Si score < 3: Bouton "Réouvrir" visible

// Test 6: Ajouter pièce jointe
POST /reclamation/X/attachment
File: broken_package.jpg (2.5MB)
→ 201 Created + File stored securely
  
// Test 7: Afficher timeline
GET /reclamation/X → Affiche:
  - 2026-02-10 10:30: Créée par Client
  - 2026-02-10 14:15: Assignée à Jean (Agent)
  - 2026-02-11 09:00: Status → EN_COURS
  - 2026-02-12 16:45: Response from Agent
  - 2026-02-14 08:30: Marked RESOLVED
  - 2026-02-14 10:15: Feedback: 4★ (Satisfied)
```

---

## 🤖 USER STORY #8: API AVANCÉE - RÉPONSES AUTOMATIQUES IA (14 pts)

### Description
En tant que **modérateur**, je veux que le **système propose automatiquement des réponses IA** pour accélérer la résolution et **assurer les clients sont satisfaits**.

### Critères d'Acceptation

```
✓ Classification automatique
  - Analyser titre + description réclamation
  - Détecter type: LIVRAISON, QUALITE, FACTURATION, AUTRE
  - Score confiance affichée (80%, 90%, etc.)
  - Fallback: Si confiance < 60% → "Complexe, nécessite humain"

✓ Génération réponse maison
  - Prompt Gemini intelligent basé sur type
  - Proposer solutions: Remboursement %, Remplacement, etc.
  - Mode "Draft" (agent peut éditer avant envoi)
  - Template personas (Responsable, Empathique, etc.)

✓ Système escalade
  - Si classification faible → Flaguer "Complex"
  - Si keywords urgents → Marquer URGENT
  - Si réclamation similar exists → Proposer merge
  - Alerter équipe support en cas de spike

✓ ML Training Loop
  - Trackern satisfaction avec réponse (1-5 stars)
  - Retrainer modèle avec feedback
  - A/B testing: Réponse auto vs humaaine
  - Dashboard: Satisfaction rate par type

✓ Endpoint API
  - POST /api/reclamations/{id}/suggest-response
  - Returns: { suggestion, confidence, alternatives, escalation_needed }
```

### Architecture Système

```
┌─────────────────────┐
│  Client submits     │
│  Reclamation       │
└──────────┬──────────┘
           │
           ▼
┌─────────────────────────────────────────┐
│ GeminiReclamationAiService::analyze()   │
├─────────────────────────────────────────┤
│ 1. Classification (Gemini)              │
│    Prompt: "Classify this complaint"    │
│    Response: { type, confidence }       │
│                                         │
│ 2. Extract Keywords                     │
│    - URGENT: "immédiat", "urgent", etc. │
│    - QUALITY: "cassé", "endommagé"      │
│    - DELIVERY: "retard", "non reçu"     │
│                                         │
│ 3. Check Escalation Triggers            │
│    if confidence < 60% OR has_urgency   │
│    → Set: needs_escalation = true       │
│    → Alert team                         │
│                                         │
│ 4. Generate Solution                    │
│    if NOT escalation_needed:            │
│      - Gemini: Generate response        │
│      - Apply template (tone, style)     │
│      - Return 2-3 alternatives          │
│                                         │
│ 5. Save as Draft                        │
│    Agent can edit & send                │
└─────────────────────────────────────────┘
           │
           ▼
  ┌────────────────────┐
  │ Draft created      │
  │ Ready for review   │
  │ or auto-send (opt.)│
  └────────────────────┘
           │
           ▼
    ┌──────────────────┐
    │ Client receives  │
    │ Escalation flag? │
    │ (to human agent) │
    └──────────────────┘
```

### Implementation Details

```php
// GeminiReclamationAiService.php

class GeminiReclamationAiService {
  
  public function analyzAndSuggestResponse(Reclamation $rec): array {
    // Step 1: Classification
    $classification = $this->classifyReclamation($rec);
    // Output: ['type' => 'DELIVERY', 'confidence' => 0.92]
    
    // Step 2: Extract keywords
    $keywords = $this->extractKeywords($rec->getDescription());
    // Output: ['urgent' => true, 'quality_issues' => ['cassé']]
    
    // Step 3: Check escalation
    $escalation = $this->shouldEscalate($classification, $keywords);
    // Output: ['needed' => false, 'reasons' => []]
    
    if ($escalation['needed']) {
      // Return escalation object
      return [
        'status' => 'escalation_required',
        'reason' => $escalation['reasons'][0],
        'suggestion' => null,
        'alternatives' => []
      ];
    }
    
    // Step 4: Generate response
    $suggestion = $this->generateResponse(
      $classification['type'],
      $rec,
      'empathetic' // tone template
    );
    // Output: Full response text draft
    
    // Step 5: Generate alternatives
    $alternatives = $this->generateAlternatives(
      $rec,
      $suggestion,
      count: 2
    );
    // Output: [ResponseA, ResponseB]
    
    return [
      'status' => 'success',
      'classification' => $classification,
      'suggestion' => $suggestion,
      'alternatives' => $alternatives,
      'confidence' => $classification['confidence'],
      'auto_sendable' => $classification['confidence'] > 0.85
    ];
  }
  
  private function classifyReclamation(Reclamation $rec): array {
    $prompt = <<<PROMPT
Analyze this customer complaint and classify it:
Title: {$rec->getTitre()}
Description: {$rec->getDescription()}

Classify as ONE of: DELIVERY, QUALITY, BILLING, OTHER

Return JSON:
{
  "type": "DELIVERY|QUALITY|BILLING|OTHER",
  "confidence": 0.0-1.0,
  "summary": "Brief explanation"
}
PROMPT;

    $response = $this->geminiService->generate($prompt);
    return json_decode($response, true);
  }
  
  private function generateResponse(
    string $type,
    Reclamation $rec,
    string $tone = 'professional'
  ): string {
    $templates = [
      'DELIVERY' => "Nous nous excusons du retard de livraison...",
      'QUALITY' => "Nous sommes désolés de la qualité du produit...",
      'BILLING' => "Merci d'avoir signalé cette erreur facturation...",
      'OTHER' => "Merci de nous avoir contactés..."
    ];
    
    $prompt = <<<PROMPT
Generate a $tone customer service response to this complaint:
Type: $type
Title: {$rec->getTitre()}
Description: {$rec->getDescription()}

Template opening: {$templates[$type]}

Propose concrete solutions (refund %, replacement, etc.)
Keep response under 150 words.
Be empathetic and professional.
PROMPT;

    return $this->geminiService->generate($prompt);
  }
  
  private function shouldEscalate(
    array $classification,
    array $keywords
  ): array {
    $escalation_triggers = [
      'low_confidence' => $classification['confidence'] < 0.60,
      'urgent_keywords' => $keywords['urgent'] === true,
      'legal_mentions' => isset($keywords['legal_words']),
      'high_severity' => $keywords['severity_level'] === 'high'
    ];
    
    $reasons = array_keys(array_filter($escalation_triggers));
    
    return [
      'needed' => !empty($reasons),
      'reasons' => $reasons,
      'priority' => $keywords['urgent'] ? 'HIGH' : 'NORMAL'
    ];
  }
}
```

### API Endpoint

```
POST /api/reclamations/{id}/suggest-response
Authorization: Bearer <token>

Response 200:
{
  "status": "success",
  "classification": {
    "type": "DELIVERY",
    "confidence": 0.92,
    "summary": "Complaint about delayed delivery"
  },
  "suggestion": {
    "text": "Nous nous excusons du retard...",
    "is_draft": true,
    "tone": "empathetic",
    "generated_at": "2026-02-15T10:30:00Z"
  },
  "alternatives": [
    { "text": "Alternative response 1...", "tone": "friendly" },
    { "text": "Alternative response 2...", "tone": "formal" }
  ],
  "solutions_proposed": [
    { "type": "partial_refund", "percentage": 15, "reason": "Delivery delay" },
    { "type": "replacement", "priority": "expedited" }
  ],
  "escalation": {
    "needed": false,
    "reasons": []
  },
  "auto_sendable": true,
  "confidence_score": 0.92
}

Response 202 (Escalation needed):
{
  "status": "escalation_required",
  "reason": "Complex case - manual review needed",
  "flags": ["low_confidence", "urgent"],
  "assigned_to_queue": "support_team",
  "estimated_response_time": "2 hours"
}
```

### Feedback Loop & ML Training

```
POST /reclamation/{id}/feedback
{
  "satisfaction_score": 4,
  "feedback": "Response was helpful but slow",
  "used_suggestion": true,
  "modified_before_send": true
}

→ SystemEvents:
  1. ReclamationResolvedEvent fired
  2. Listener records: satisfaction + suggestion performance
  3. ML dataset updated
  4. Analytics dashboard: "AI Satisfaction Rate: 84%"
```

### Dashboard Statistiques

```
Admin Dashboard - Reclamations IA:

├─ AI Accuracy
│  ├─ Classification accuracy: 92%
│  └─ vs Manual: 89% (AI better)
│
├─ Response Quality
│  ├─ Auto-generated responses: 145
│  ├─ Satisfaction avg: 4.1/5 ⭐
│  └─ Edited rate: 32% (agents modify)
│
├─ Performance
│  ├─ Avg resolution time: 4.2 hours
│  ├─ vs Manual: 12.8 hours (AI 3x faster!)
│  └─ Cost saved: €2,400/month
│
├─ Escalations
│  ├─ Total: 23 this month
│  ├─ Rate: 12%
│  └─ Reasons: [low_confidence, urgent]
│
└─ A/B Testing (optional)
   ├─ AI vs Human: 84% prefer AI
   ├─ Tone comparison: Empathetic wins
   └─ Solution proposals: 91% acceptance
```

### Fichiers à Créer

| Fichier | Type | Ligne |
|---------|------|-------|
| `src/Service/GeminiReclamationAiService.php` | NEW | ~300 |
| `src/Controller/Api/ReclamationApiController.php` | NEW | ~200 |
| `tests/Service/ReclamationAiServiceTest.php` | NEW | ~250 |
| `templates/admin/reclamation/suggest.html.twig` | NEW | ~80 |

---

## 📊 RÉSUMÉ MODULE RÉCLAMATIONS

| Aspect | Détail |
|--------|--------|
| **Points Totaux** | 30 pts (16 + 14) |
| **Durée Estimée** | 2 semaines |
| **Équipe** | 4 devs (1-2 backend, 1 frontend, 1 QA) |
| **Complexité** | Haute (State Machine + IA) |
| **Risques** | Gemini API rate limiting, workflow bugs |
| **Tests** | 60+ cas |
| **Performance** | 2-3 secondes AI generation |

