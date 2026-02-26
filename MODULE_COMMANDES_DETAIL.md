# 🛒 MODULE COMMANDES - SPRINT 3

**Status**: 📋 En Planification  
**User Stories**: US#9 + US#10  
**Points Totaux**: 32 pts (18 + 14)  
**Durée Estimée**: 2-3 semaines

---

## 📦 USER STORY #9: CRUD COMMANDES (18 pts)

### Description
En tant que **client**, je veux **créer et gérer mes commandes** et en tant qu'**admin**, **gérer tout le cycle de vie des commandes** avec facturation et suivi.

### Critères d'Acceptation

```
✓ CLIENT: Créer commande (Checkout)
  - Ajouter produits au panier (quantité)
  - Voir total, TVA (20%), frais port (calcul auto)
  - Appliquer coupon code (validation date/usage)
  - Choisir adresse livraison (saved ou nouvelle)
  - Sauvegarder panier (persistant BD)
  - Validations: Quantités disponibles, stock

✓ CLIENT: Voir panier
  - Page /panier
  - Produits listés avec prix unitaire, quantité
  - Modifier quantité dans le panier
  - Supprimer produit du panier
  - Total dynamique (prix + TVA + port)
  - Coupon appliqué visible
  - Bouton "Commencer paiement" (checkout)

✓ CLIENT: Checkout + Paiement
  - Formulaire: adresse, téléphone, email
  - Payment gateway integration (Stripe ou PayPal)
  - Confirmation payement reçue
  - Email avec détails + numéro commande (#CMD-XXXXX)
  - Redirection suivre commande

✓ CLIENT: Voir mes commandes
  - Page /commandes
  - Liste toutes les commandes (client)
  - Timestamp création, statut, total
  - Tri par date desc, filtrer statut
  - Clic → afficher détail

✓ CLIENT: Détail commande
  - Numéro commande + Date
  - Produits achetés (avec prix à l'époque)
  - Total + calculé breakdown (TVA, port)
  - Statut courant + historique transitions
  - Timeline: Confirmée → Livrée
  - Bouton: Annuler (si avant paiement)
  - Bouton: Télécharger facture PDF
  - Support contact si problème

✓ CLIENT: Annuler commande
  - Avant livraison uniquement
  - Appel API Stripe pour remboursement
  - Email: Remboursement confirmé
  - Historique: Marquer CANCELLED
  - Refund: Crédité 5-7 jours

✓ ADMIN: Dashboard commandes
  - Toutes les commandes (tous les clients)
  - Filtres: Statut, Date range, Montant min/max
  - Tri: Par date desc, par montant
  - Bulk actions: Marquer livré, refunder, etc.
  - Graphique: Commandes par jour (7 derniers jours)
  - Revenue total + avg order

✓ ADMIN: Détail commande (Admin)
  - Infos client: Nom, Email, Téléphone
  - Produits: Avec prix vente, quantité, montant
  - Calcul breakdown: Sous-total, TVA, Port, Total
  - Timeline complète des changements
  - Bouton Mark Shipped / Delivered / Cancelled
  - Refund historique (si applicable)
  - Notes internes (admin only)

✓ ADMIN: Modifier commande
  - Avant paiement confirmé: Edit adresse, coupon
  - Après paiement: Read-only (audit)
  - Nota: Modifier => Creates audit trail

✓ ADMIN: Remboursement
  - Refund partiel ou total
  - Appel API Stripe
  - Automate email au client
  - Timeline met à jour
  - Stock restauré (si applicable)
```

### Tâches Techniques

```
ENTITIES/MODELS:

[ ] Améliorer Commande Entity
    ├─ Ajouter: numero_commande (unique: #CMD-20260215-0001)
    ├─ Ajouter: statut (enum: PANIER, PAYEE, TRAITEE, EXPEDIEE, LIVREE, CANCELLED)
    ├─ Ajouter: sous_total (float)
    ├─ Ajouter: tva_amount (float computed)
    ├─ Ajouter: frais_port (float)
    ├─ Ajouter: total_amount (float = sous_total + tva + port)
    ├─ Ajouter: coupon_code (string nullable)
    ├─ Ajouter: coupon_discount (float nullable)
    ├─ Ajouter: adresse_livraison (text)
    ├─ Ajouter: telephone_livraison (string)
    ├─ Ajouter: notes_internes (text, admin only)
    ├─ Ajouter: stripe_payment_id (string nullable)
    ├─ Ajouter: refund_amount (float nullable)
    ├─ Ajouter: refund_date (datetime nullable)
    ├─ Ajouter: cancelled_at (datetime nullable)
    ├─ Ajouter: shipped_at (datetime nullable)
    ├─ Ajouter: delivered_at (datetime nullable)
    └─ Ajouter: notes (relation to CommandeNote for audit)

[ ] NEW: LigneCommande (already exists, ensure fields)
    ├─ id, commande_id (FK), produit_id (FK)
    ├─ quantite, prix_unitaire (snapshot at purchase)
    ├─ sous_total (quantite * prix_unitaire)
    └─ created_at

[ ] NEW: CommandeNote (Audit trail)
    ├─ id, commande_id (FK), author_id (FK)
    ├─ content (text), action (created, modified, refunded, completed)
    ├─ old_value, new_value (for auditing changes)
    └─ created_at (datetime)

[ ] NEW: Coupon Entity
    ├─ id, code (unique, string)
    ├─ discount_percent (int 0-100)
    ├─ discount_amount (float, alternative)
    ├─ max_usage (int)
    ├─ usage_count (int, computed from DB)
    ├─ valid_from (datetime)
    ├─ valid_until (datetime)
    ├─ status (ACTIVE, DISABLED, EXPIRED)
    ├─ min_order_amount (float nullable)
    └─ tags (array, for categorization)

[ ] NEW: Adresse Entity
    ├─ id, utilisateur_id (FK)
    ├─ type (LIVRAISON, FACTURATION)
    ├─ rue, code_postal, ville, pays
    ├─ nom (label, ex: "Domicile")
    ├─ is_default (bool)
    └─ created_at

CONTROLLERS:

[ ] PanierController (Client)
    ├─ GET /panier → Afficher panier
    ├─ POST /panier/add → Ajouter produit
    ├─ POST /panier/update → Modifier quantité
    ├─ POST /panier/remove → Supprimer produit
    ├─ POST /panier/coupon → Appliquer coupon
    ├─ GET /checkout → Formulaire checkout
    └─ POST /checkout/process → Traiter commande (Stripe)

[ ] CommandeController (Client)
    ├─ GET /commandes → Lister mes commandes
    ├─ GET /commandes/{id} → Détail commande
    ├─ POST /commandes/{id}/cancel → Annuler
    ├─ POST /commandes/{id}/invoice/download → PDF facture
    ├─ POST /commandes/{id}/refund → Demander remboursement
    └─ GET /commandes/tracking/{numero} → Public tracking

[ ] Admin/AdminCommandeController
    ├─ GET /admin/commandes → Dashboard
    ├─ GET /admin/commandes/{id} → Détail
    ├─ PUT /admin/commandes/{id}/status → Changer statut
    ├─ PUT /admin/commandes/{id}/note → Ajouter note
    ├─ POST /admin/commandes/{id}/refund → Refunder
    ├─ POST /admin/commandes/{id}/ship → Marquer expédié
    ├─ GET /admin/commandes/stats → Stats
    └─ POST /admin/commandes/bulk/status → Bulk change

[ ] Admin/CouponController
    ├─ GET /admin/coupons → Lister coupons
    ├─ POST /admin/coupons → Créer coupon
    ├─ PUT /admin/coupons/{code} → Modifier
    ├─ DELETE /admin/coupons/{code} → Supprimer
    └─ GET /admin/coupons/stats → Usage statistics

SERVICES:

[ ] PanierService
    ├─ addProduct(Session, $produitId, $quantity)
    ├─ removeProduct(Session, $produitId)
    ├─ updateQuantity(Session, $produitId, $quantity)
    ├─ applyCoupon(Session, $code)
    ├─ calculateTotals(Session) → Array avec TVA, port, etc.
    ├─ savePanier(Session, User) → Persiste en BD
    ├─ restorePanier(User) → Charge panier from BD
    └─ clearPanier(Session)

[ ] CommandeService
    ├─ createCommande(User, $panier, $adresse, $coupon)
    ├─ generateNumeroCommande() → #CMD-20260215-0001
    ├─ calculatePrice($sousTotal, $coupon) → with TVA
    ├─ applyDiscount(Commande, Coupon)
    ├─ cancelCommande(Commande, $reason)
    ├─ refundCommande(Commande, $amount)
    ├─ markShipped(Commande)
    ├─ markDelivered(Commande)
    └─ recordStatusChange(Commande, $from, $to)

[ ] PaymentService
    ├─ processPayment(Commande, Stripe token)
    ├─ getStripeStatus($stripeId)
    ├─ refundPayment(Commande, $amount)
    └─ webhookPaymentStatus($event)

[ ] FactureService
    ├─ generatePDF(Commande) → PDF binary
    ├─ getHTMLTemplate(Commande) → HTML réutilisable
    ├─ emailFacture(Commande, User)
    └─ storeFacture($filename, $binary)

[ ] CouponService
    ├─ validateCoupon(string $code, float $montant) → bool
    ├─ getDiscount(Coupon, $montant) → float
    ├─ applyUsage(Coupon)
    ├─ getRedeemedCount(Coupon) → int
    └─ expireOldCoupons()

[ ] NotificationService (ENHANCE)
    ├─ Ajouter: notifyOrderCreated(Commande)
    ├─ Ajouter: notifyOrderShipped(Commande)
    ├─ Ajouter: notifyOrderDelivered(Commande)
    ├─ Ajouter: notifyRefunded(Commande)
    └─ Ajouter: notifyOrderCancelled(Commande)

TEMPLATES:

[ ] Client UI
    ├─ templates/panier/index.html.twig → Page panier
    ├─ templates/panier/checkout.html.twig → Checkout form
    ├─ templates/commande/index.html.twig → Mes commandes
    ├─ templates/commande/show.html.twig → Détail commande
    ├─ templates/facture/invoice.html.twig → Template facture PDF
    └─ templates/email/order_confirmation.html.twig → Email

[ ] Admin UI
    ├─ templates/admin/commande/index.html.twig → Dashboard
    ├─ templates/admin/commande/show.html.twig → Détail
    ├─ templates/admin/coupon/index.html.twig → Gestion coupons
    └─ templates/admin/commande/stats.html.twig → Stats

TESTS:

[ ] PanierTest (30+ cases)
[ ] CommandeTest (25+ cases)
[ ] CouponTest (20+ cases)
[ ] PaymentTest (15+ cases - with mocked Stripe)
[ ] FactureTest (10+ cases)
```

### Base de Données

```sql
-- Commande (amélioration)
ALTER TABLE commandes
  ADD COLUMN numero_commande VARCHAR(50) UNIQUE,
  ADD COLUMN statut VARCHAR(50) DEFAULT 'PANIER',
  ADD COLUMN sous_total FLOAT,
  ADD COLUMN tva_amount FLOAT,
  ADD COLUMN frais_port FLOAT DEFAULT 5.99,
  ADD COLUMN coupon_code VARCHAR(50),
  ADD COLUMN coupon_discount FLOAT DEFAULT 0,
  ADD COLUMN adresse_livraison LONGTEXT,
  ADD COLUMN telephone_livraison VARCHAR(20),
  ADD COLUMN notes_internes TEXT,
  ADD COLUMN stripe_payment_id VARCHAR(255),
  ADD COLUMN refund_amount FLOAT,
  ADD COLUMN refund_date DATETIME,
  ADD COLUMN cancelled_at DATETIME,
  ADD COLUMN shipped_at DATETIME,
  ADD COLUMN delivered_at DATETIME;

-- Adresse (NEW)
CREATE TABLE adresse (
  id INT PRIMARY KEY AUTO_INCREMENT,
  utilisateur_id INT NOT NULL,
  type VARCHAR(50),
  rue VARCHAR(255),
  code_postal VARCHAR(10),
  ville VARCHAR(100),
  pays VARCHAR(100),
  nom VARCHAR(100),
  is_default BOOLEAN DEFAULT FALSE,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (utilisateur_id) REFERENCES `user`(id) ON DELETE CASCADE
);

-- Coupon (NEW)
CREATE TABLE coupon (
  id INT PRIMARY KEY AUTO_INCREMENT,
  code VARCHAR(50) UNIQUE NOT NULL,
  discount_percent INT,
  discount_amount FLOAT,
  max_usage INT,
  usage_count INT DEFAULT 0,
  valid_from DATETIME,
  valid_until DATETIME,
  status VARCHAR(50) DEFAULT 'ACTIVE',
  min_order_amount FLOAT,
  tags JSON,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP
);

-- CommandeNote (NEW - Audit trail)
CREATE TABLE commande_note (
  id INT PRIMARY KEY AUTO_INCREMENT,
  commande_id INT NOT NULL,
  author_id INT,
  content LONGTEXT,
  action VARCHAR(100),
  old_value TEXT,
  new_value TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (commande_id) REFERENCES commandes(id) ON DELETE CASCADE,
  FOREIGN KEY (author_id) REFERENCES `user`(id) ON DELETE SET NULL
);

-- INDEX
CREATE INDEX idx_commande_numero ON commandes(numero_commande);
CREATE INDEX idx_commande_utilisateur ON commandes(utilisateur_id, created_at DESC);
CREATE INDEX idx_commande_statut ON commandes(statut);
CREATE INDEX idx_commande_date ON commandes(created_at DESC);
CREATE INDEX idx_coupon_code ON coupon(code);
CREATE INDEX idx_coupon_valid ON coupon(valid_from, valid_until);
```

### Workflow Commande

```
┌──────────────────┐
│   PANIER         │
│ (Accumulation)   │
└────────┬─────────┘
         │
    Client checkout
         │
    ┌────▼─────────────────────┐
    │  PAYEE (Confirmer paiement)│
    │  Stripe webhook received  │
    └────┬──────────────────────┘
         │
  Admin traite commande
         │
    ┌────▼──────────────────┐
    │  TRAITEE (Préparée)    │
    │  Admin: "Picking"      │
    └────┬───────────────────┘
         │
  Admin marque expédié
         │
    ┌────▼──────────────────────┐
    │  EXPEDIEE (En transit)     │
    │ Email: Tracking number    │
    └────┬───────────────────────┘
         │
  Colis livré
         │
    ┌────▼──────────────────┐
    │  LIVREE ✓             │
    │ Email confirmation    │
    └───────────────────────┘
    
    SPECIAL STATES:
    - CANCELLED: Before PAYEE status
    - REFUNDED: After payment, before shipped
```

### Cas de Test

```php
// Test 1: Ajouter produit au panier
POST /panier/add
{
  "produit_id": 5,
  "quantite": 2
}
→ 200 OK
→ Session['panier'] = [{id: 5, qty: 2, prix: 9.99, ...}]
→ Total: 2 x 9.99 = 19.98

// Test 2: Appliquer coupon
POST /panier/coupon
{
  "code": "PROMO20"
}
→ 200 OK
→ Coupon = 20% off
→ Total: 19.98 - 3.996 = €15.98 (avant TVA)

// Test 3: Checkout
GET /checkout
→ Formulaire pré-rempli avec adresses sauvegardées

POST /checkout/process
{
  "adresse_id": 1,
  "stripe_token": "tok_visa"
}
→ Stripe.charge(15.98 EUR)
→ → Stripe webhook confirms payment
→ → Status: PAYEE
→ → Numero: #CMD-20260215-0001
→ → Email sent + PDF facture

// Test 4: Voir détail commande
GET /commandes/1
→ Affiche:
  - Numero #CMD-20260215-0001
  - Produits: 2x Produit (9.99 € ea)
  - Sous-total: 19.98
  - TVA (20%): 3.996
  - Port: 5.99
  - Total: 29.966
  - Status timeline: Payée → Traitée → Expédiée

// Test 5: Annuler commande
POST /commandes/1/cancel
→ IF status = PAYEE (avant expédition):
  → Status: CANCELLED
  → Stripe refund called
  → Email: "Remboursement confirmé, 5-7j"
→ ELSE: 403 Forbidden (déjà expédiée)

// Test 6: Admin refunder
POST /admin/commandes/1/refund
{
  "amount": 29.966
}
→ Stripe refund processed
→ CommandeNote: "Remboursement de 29.966€"
→ Email client confirme
→ Status: REFUNDED

// Test 7: Télécharger facture
GET /commandes/1/invoice/download
→ 200 OK
→ Content-Type: application/pdf
→ PDF: Facture avec tous les détails commande

// Test 8: Marquer livré (Admin)
PUT /admin/commandes/1/status
{
  "new_status": "LIVREE"
}
→ 200 OK
→ delivered_at = NOW()
→ CommandeNote logged
→ Email client: "Votre commande est livrée!"
```

---

## 🚚 USER STORY #10: API AVANCÉE - TRACKING COMMANDES (14 pts)

### Description
En tant que **client**, je veux suivre ma commande en temps réel avec **updates GPS, estimée de livraison** et **notifications push**, afin de **savoir quand mon colis arrive**.

### Critères d'Acceptation

```
✓ API Tracking
  - Endpoint GET /api/commandes/{numero}/tracking
  - Retourne: statut, étapes, estimée, GPS (if available)
  - Updates en temps réel (websocket optionnel)
  - Accessible publiquement avec juste numero_commande

✓ Timeline Étapes
  - Étape 1: "Confirmée" ✓
  - Étape 2: "Préparée en entrepôt"
  - Étape 3: "En cours de livraison"
  - Étape 4: "Livrée" ✓
  - Chaque étape: date/heure exacte

✓ Notifications
  - Push notification (optionnel)
  - SMS (optionnel via Twilio)
  - Email à changement statut
  - In-app notification dans dashboard

✓ GPS Tracking (optionel)
  - Intégration transporteur API
  - Latitude/Longitude du colis (si dispo)
  - Route map display
  - Estimée d'arrivée

✓ Webhooks
  - Pour intégrations tierces
  - POST https://partner.com/webhook
  - Event: order.shipped, order.delivered, etc.
```

### Endpoints API

```
GET /api/commandes/{numero}/tracking
  - numero: #CMD-20260215-0001 (public, peut pas voir autres)
  - Returns: full tracking info
  - Cached 5 minutes

GET /api/commandes/{numero}/tracking/timeline
  - Returns: Array of status changes with timestamps

GET /api/commandes/{numero}/tracking/position
  - Returns: { lat, lng, accuracy, last_update }
  - If available from carrier

POST /api/commandes/{id}/notifications/subscribe
  - user_id, notification_type (push, email, sms)
  - Returns: subscription confirmed

GET /api/user/commandes/tracked
  - Authenticated endpoint
  - Returns: All user orders with tracking
```

### Response Examples

```json
GET /api/commandes/CMD-20260215-0001/tracking

{
  "numero": "CMD-20260215-0001",
  "statut": "En cours de livraison",
  "progression": 75,
  "created_at": "2026-02-15T10:30:00Z",
  "estimated_delivery": "2026-02-16T18:00:00Z",
  "carrier": "DHL",
  "carrier_tracking": "1234567890ABC",
  
  "timeline": [
    {
      "step": 1,
      "label": "Confirmée",
      "timestamp": "2026-02-15T10:30:00Z",
      "completed": true,
      "icon": "check"
    },
    {
      "step": 2,
      "label": "Préparée en entrepôt",
      "timestamp": "2026-02-15T14:15:00Z",
      "completed": true,
      "icon": "box"
    },
    {
      "step": 3,
      "label": "En cours de livraison",
      "timestamp": "2026-02-16T08:00:00Z",
      "completed": true,
      "current": true,
      "icon": "truck"
    },
    {
      "step": 4,
      "label": "Livrée",
      "timestamp": null,
      "completed": false,
      "estimated": "2026-02-16T18:00:00Z",
      "icon": "home"
    }
  ],
  
  "position": {
    "latitude": 48.8566,
    "longitude": 2.3522,
    "accuracy": 50,
    "last_update": "2026-02-16T10:45:00Z",
    "address": "Paris 1er arrondissement"
  },
  
  "driver": {
    "name": "Jean Dupont",
    "phone": "+33612345678",
    "vehicle": "Sprinter DHL",
    "rating": 4.8
  },
  
  "notifications": {
    "email": true,
    "push": false,
    "sms": false
  }
}
```

### Tâches Techniques

```
[ ] Api/TrackingApiController
    ├─ getTracking(string $numero)
    ├─ getTimeline(string $numero)
    ├─ getPosition(string $numero)
    ├─ subscribeNotifications()
    └─ getMyCommandes() [authenticated]

[ ] TrackingService
    ├─ getTrackingInfo(Commande) → Full tracking
    ├─ getEstimatedDelivery(Commande) → DateTime
    ├─ syncCarrierData(Commande) → Pull from DHL/UPS/etc
    ├─ calculateProgression(Commande) → 0-100%
    └─ formatTimeline(Commande) → Array of steps

[ ] CarrierIntegrationService
    ├─ AbstractCarrierAPI (base class)
    ├─ DHLCarrierAPI extends AbstractCarrierAPI
    ├─ UPSCarrierAPI extends AbstractCarrierAPI
    ├─ FedexCarrierAPI extends AbstractCarrierAPI
    └─ Methods: getTracking(), getPosition(), getStatus()

[ ] NotificationService (ENHANCE)
    ├─ subscribeToTracking(User, $type)
    ├─ sendTrackingUpdate(Commande, $event)
    ├─ sendPushNotification(User, $message)
    ├─ sendSmsTracking(User, $message) [opt.]
    └─ Batch send from queue

[ ] Webhook System
    ├─ WebhookController (receive carrier events)
    ├─ WebhookService (process, validate, trigger events)
    ├─ Supported events: shipped, in_transit, delivered, failed
    └─ Retry logic (exponential backoff)

[ ] Tests
    ├─ TrackingApiTest (30+ cases)
    ├─ CarrierIntegrationTest (with mocks)
    ├─ NotificationTest
    └─ Webhook integration test

[ ] Real-time (Optional)
    ├─ Websocket support (Socket.io or Symfony Messenger)
    ├─ Push updates as status changes
    ├─ Client: JavaScript listener
```

### Database - Tracking Tables

```sql
-- Store tracking updates
CREATE TABLE commande_tracking_event (
  id INT PRIMARY KEY AUTO_INCREMENT,
  commande_id INT NOT NULL,
  event_type VARCHAR(100),
  event_data JSON,
  latitude DECIMAL(10, 8),
  longitude DECIMAL(11, 8),
  address VARCHAR(255),
  timestamp DATETIME,
  carrier_reference VARCHAR(255),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (commande_id) REFERENCES commandes(id) ON DELETE CASCADE,
  INDEX idx_commande_type (commande_id, event_type)
);

-- Notification preferences
ALTER TABLE `user`
  ADD COLUMN notify_order_shipped BOOLEAN DEFAULT TRUE,
  ADD COLUMN notify_order_delivered BOOLEAN DEFAULT TRUE,
  ADD COLUMN notify_order_push BOOLEAN DEFAULT FALSE,
  ADD COLUMN notify_order_sms BOOLEAN DEFAULT FALSE;

-- Webhooks sent (audit)
CREATE TABLE webhook_log (
  id INT PRIMARY KEY AUTO_INCREMENT,
  commande_id INT,
  event_type VARCHAR(100),
  payload JSON,
  status_code INT,
  response_body TEXT,
  retry_count INT DEFAULT 0,
  sent_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
```

### Intégrations Transporteurs

```php
// CarrierIntegrationService - Pattern Strategy

interface CarrierInterface {
  public function getTracking(string $trackingNumber);
  public function getPosition(string $trackingNumber);
  public function getEstimatedDelivery(string $trackingNumber);
}

class DHLCarrier implements CarrierInterface {
  private $apiKey = env('DHL_API_KEY');
  private $apiUrl = 'https://api.dhl.com/tracking';
  
  public function getTracking($trackingNumber) {
    $response = $this->client->get("{$this->apiUrl}/{$trackingNumber}", [
      'headers' => ['Authorization' => "Bearer {$this->apiKey}"]
    ]);
    
    return [
      'status' => $response['status'],
      'events' => array_map(fn($e) => [
        'timestamp' => $e['date'],
        'location' => $e['location'],
        'description' => $e['description']
      ], $response['events'])
    ];
  }
}

// Factory pattern
$carrier = CarrierFactory::create('DHL');
$tracking = $carrier->getTracking('#CMD-12345');
```

### Performance & Caching

```php
// TrackingService caching strategy

public function getTrackingInfo(Commande $cmd): array {
  $cacheKey = "tracking_{$cmd->getId()}";
  
  // Check cache (5 min TTL)
  $cached = $this->cache->get($cacheKey);
  if ($cached) {
    return $cached;
  }
  
  // Fetch from carrier (API call)
  $carrier = CarrierFactory::create($cmd->getCarrier());
  $tracking = $carrier->getTracking($cmd->getCarrierTrackingId());
  
  // Cache result
  $this->cache->set($cacheKey, $tracking, 300); // 5 mins
  
  return $tracking;
}
```

### Cas de Test Tracking

```php
// Test 1: Public tracking (sans authentification)
GET /api/commandes/CMD-20260215-0001/tracking
→ 200 OK
→ Returns full tracking info
→ No auth required!

// Test 2: Timeline
GET /api/commandes/CMD-20260215-0001/tracking/timeline
→ 200 OK
→ Array of 4 steps with timestamps

// Test 3: Position (GPS)
GET /api/commandes/CMD-20260215-0001/tracking/position
→ 200 OK
→ { lat: 48.8566, lng: 2.3522, accuracy: 50m }

// Test 4: Subscribe notifications
POST /api/commandes/CMD-20260215-0001/notifications/subscribe
{
  "user_id": 5,
  "types": ["email", "push"]
}
→ 201 Created
→ User will get notifications on status change

// Test 5: Webhook from DHL
POST /webhook/dhl
{
  "tracking_id": "1234567890",
  "event": "delivered",
  "timestamp": "2026-02-16T18:30:00Z",
  "location": "Paris",
  "signature": "xyz123" // HMAC validation
}
→ 200 OK
→ System updates commande statut
→ Sends email to customer

// Test 6: Real-time push (WebSocket)
WS /ws/tracking/CMD-20260215-0001
→ Client connected
→ When status changes → Server pushes JSON update
→ Client updates UI in real-time
```

### Dashboard Tracking (Client)

```html
<!-- /commandes/CMD-20260215-0001/tracking -->

<div class="tracking-container">
  <h2>Suivi Commande: #CMD-20260215-0001</h2>
  
  <div class="progress-bar">
    75% [████████░░] Livraison en cours
  </div>
  
  <timeline>
    ✓ Confirmée (15/02 10:30)
    ✓ Préparée (15/02 14:15)
    ● En cours (16/02 08:00) ← Current
    → Livrée (Estimée: 16/02 18:00)
  </timeline>
  
  <map> [Affiche position GPS + route] </map>
  
  <driver-info>
    Chauffeur: Jean Dupont
    Rating: ⭐⭐⭐⭐⭐ 4.8/5
    Phone: +33612345678
    Vehicle: Sprinter DHL
  </driver-info>
  
  <notifications>
    ☑ Email ☐ SMS ☑ Push
    [Settings]
  </notifications>
</div>
```

---

## 📊 RÉSUMÉ MODULE COMMANDES

| Aspect | Détail |
|--------|--------|
| **Points Totaux** | 32 pts (18 + 14) |
| **Durée Estimée** | 2-3 semaines |
| **Équipe** | 4-5 devs (2 backend, 1 frontend, 1 QA, 1 DevOps) |
| **Complexité** | Très Haute (Paiement, Tracking, Notifications) |
| **Intégrations** | Stripe, DHL, UPS, WebSockets, SMS (opt.) |
| **Risques** | Paiement bugs, Carrier API reliability |
| **Tests** | 80+ cas de test |
| **Performance** | < 500ms API, cache 5 min |

