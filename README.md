# Pharmax - Plateforme de Gestion de Pharmacie en Ligne

Pharmax est une application web  de gestion de pharmacie en ligne, developpee dans le cadre du projet PIDEV (Projet Integre de Developpement) - 3eme annee ingenierie, **Esprit School of Engineering** (Annee universitaire 2025-2026).

---

## Description generale

Pharmax permet aux clients de parcourir un catalogue de produits pharmaceutiques, de gerer leur panier, de passer des commandes avec suivi de livraison, d'interagir avec un blog sante et de soumettre des reclamations. Une interface d'administration complete permet la gestion back-office de l'ensemble des fonctionnalites.

---

## Stack technique

| Composant | Technologie |
|-----------|-------------|
| Langage | PHP 8.x |
| Framework | Symfony 6.4 |
| Templating | Twig |
| Base de donnees | MariaDB 10.4 (MySQL) |
| ORM | Doctrine ORM 3.6 |
| Frontend | HTML5, CSS3, JavaScript, Bootstrap |
| Theme admin | Sneat Admin Dashboard |
| Auth API | Lexik JWT + Firebase PHP-JWT |
| Paiement | Stripe |
| IA / Chatbot | Ollama (mistral, llama2), Google Gemini |
| Emails | Symfony Mailer (Gmail SMTP) |
| Temps reel | Symfony Mercure |
| Tests | PHPUnit 9.5 |
| Analyse statique | PHPStan 2.1 |

---

## Fonctionnalites

### Espace client (frontend)
- Catalogue de produits avec filtres par categorie, prix et statut
- Panier d'achat et gestion des quantites
- Passage de commande avec paiement en ligne (Stripe)
- Suivi de livraison avec generation de QR code
- Telechargement de facture PDF
- Blog sante : articles, commentaires, likes
- Depot et suivi de reclamations avec reponses admin
- Notifications en temps reel (Mercure)
- Chatbot IA integre (Ollama / Gemini)
- Traduction multilingue francais / anglais (Google Translate)
- Systeme de recommandations de produits personnalisees

### Espace administrateur
- Tableau de bord avec statistiques et reporting
- Gestion des produits, categories, stocks et dates d'expiration
- Gestion des commandes et des livraisons
- Gestion des reclamations et envoi de reponses
- Gestion des utilisateurs et des roles
- Moderation automatique des commentaires (HuggingFace)
- Detection de fraude sur les commandes
- Digest email quotidien / hebdomadaire

### Securite et authentification
- Connexion formulaire (email / mot de passe)
- Authentification JWT pour les API REST
- Connexion via Google OAuth2
- Double authentification (2FA - Google Authenticator)
- Authentification biometrique (Face Auth)
- Roles : `ROLE_USER`, `ROLE_ADMIN`
- Blocage / deblocage de comptes
- ReCAPTCHA v3 sur les formulaires publics
- Reinitialisation de mot de passe par email

---

## Architecture du projet

```
pharmax/
├── src/
│   ├── Controller/          # Controleurs (~30 : frontend, admin, API)
│   ├── Entity/              # Entites Doctrine (13 modeles)
│   ├── Form/                # Types de formulaires Symfony (15)
│   ├── Repository/          # Couche acces aux donnees (15)
│   ├── Service/             # Logique metier (22 services)
│   ├── Security/            # Authentificateurs (Login, JWT, Google, 2FA)
│   ├── Command/             # Commandes console Symfony (9)
│   ├── EventListener/       # Listeners Doctrine / Symfony
│   └── Twig/                # Extensions Twig personnalisees
├── config/
│   ├── jwt/                 # Cles RSA (private.pem, public.pem)
│   ├── packages/            # Configuration des bundles
│   └── services.yaml        # Injection de dependances & cles API
├── templates/               # Vues Twig (20+ sous-dossiers)
├── migrations/              # Migrations Doctrine (6 versions)
├── tests/                   # Suite de tests PHPUnit
├── public/                  # Racine web (index.php, assets statiques)
├── translations/            # Fichiers i18n (fr, en)
├── .env                     # Variables d'environnement
└── composer.json            # Dependances PHP
```

---

## Entites principales (Doctrine)

| Entite | Role |
|--------|------|
| `User` | Utilisateurs, roles, 2FA, points fidelite |
| `Produit` | Produits pharmaceutiques (prix, stock, expiration) |
| `Categorie` | Categories de produits |
| `Commande` | Commandes clients |
| `LigneCommande` | Lignes de detail des commandes |
| `Livraison` | Informations de livraison |
| `Reclamation` | Reclamations clients |
| `Reponse` | Reponses admin aux reclamations |
| `Article` | Articles du blog sante |
| `Commentaire` | Commentaires sur articles et produits |
| `Notification` | Notifications utilisateurs |
| `ResetPasswordRequest` | Tokens de reinitialisation de mot de passe |

---

## Services metier

| Service | Responsabilite |
|---------|----------------|
| `StripeService` | Paiement en ligne |
| `InvoiceService` | Generation de factures PDF (DomPDF) |
| `CommandeQrCodeService` | Generation de QR codes de commande |
| `OllamaService` | Integration IA locale (Ollama) |
| `GeminiService` | Integration Google Gemini |
| `ChatBotService` | Chatbot conversationnel IA |
| `CommentModerationService` | Moderation automatique (HuggingFace) |
| `ProfanityDetectorService` | Filtre de grossieretes |
| `FraudDetectionService` | Scoring de risque fraude sur commandes |
| `EmailService` | Notifications et confirmations email |
| `AdminEmailDigestService` | Rapports email administrateur |
| `ReportingService` | Analytiques et statistiques ventes |
| `ProductRecommender` | Recommandations produits personnalisees |
| `TranslateService` | Traduction via Google Translate |
| `JwtTokenService` | Gestion des tokens JWT |

---

## API REST

Le projet expose une API REST protegee par JWT sous le prefixe `/api/` :

| Endpoint | Description |
|----------|-------------|
| `POST /api/auth/login` | Connexion et obtention du token JWT |
| `POST /api/auth/refresh` | Renouvellement du token |
| `GET /api/articles` | Liste des articles du blog |
| `GET /POST /api/commentaires` | Gestion des commentaires |
| `POST /api/chatbot` | Interaction avec le chatbot IA |

---

## Commandes console

```bash
php bin/console app:create-admin-user          # Creer un administrateur
php bin/console app:generate-jwt-keys          # Generer les cles JWT RSA
php bin/console app:check-expiration           # Verifier les produits expires
php bin/console app:send-admin-digest          # Envoyer le rapport email admin
php bin/console app:create-test-products       # Inserer des produits de test
php bin/console app:cleanup-orphans            # Nettoyer les donnees orphelines
php bin/console app:test-mailer                # Tester la configuration email
php bin/console app:show-notifications         # Afficher les notifications
php bin/console app:verify-jwt-integration     # Valider l'integration JWT
```

---

## Installation

### Prerequis

- PHP >= 8.1
- Composer
- MariaDB 10.4 / MySQL 8
- Symfony CLI (optionnel)

### Etapes

```bash
# 1. Cloner le depot
git clone https://github.com/NayrouzDaikhi/pharmax.git
cd pharmax

# 2. Installer les dependances PHP
composer install

# 3. Configurer l'environnement
cp .env .env.local
# Editer .env.local avec vos valeurs (BDD, API keys, SMTP...)

# 4. Creer la base de donnees et executer les migrations
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate

# 5. Generer les cles JWT
php bin/console lexik:jwt:generate-keypair

# 6. Creer le premier administrateur
php bin/console app:create-admin-user

# 7. Lancer le serveur de developpement
symfony server:start
# ou
php -S 127.0.0.1:8000 -t public
```

### Variables d'environnement cles (.env.local)

```dotenv
DATABASE_URL="mysql://user:password@127.0.0.1:3306/pharmjavaa"
MAILER_DSN=gmail://user:app_password@default

JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
JWT_PASSPHRASE=your_passphrase

STRIPE_SECRET_KEY=sk_test_...
STRIPE_PUBLIC_KEY=pk_test_...

GOOGLE_CLIENT_ID=...
GOOGLE_CLIENT_SECRET=...

GEMINI_API_KEY=...
HUGGINGFACE_API_KEY=...

RECAPTCHA_SITE_KEY=...
RECAPTCHA_SECRET_KEY=...

OLLAMA_URL=http://localhost:11434
OLLAMA_MODEL=mistral

MERCURE_URL=http://localhost:3000/.well-known/mercure
```

---

## Tests et qualite

```bash
# Lancer la suite de tests PHPUnit
php bin/phpunit

# Analyse statique PHPStan (niveau 5)
vendor/bin/phpstan analyse src --level=5
```

---

## Contexte academique

Developpe a **Esprit School of Engineering - Tunisie**  
**PIDEV - 3eme annee ingenierie** | Annee universitaire **2025-2026**

Ce projet integre les bonnes pratiques du developpement full-stack : architecture MVC, ORM, migrations versionnees, tests unitaires, analyse statique, securite avancee et integration de services tiers (paiement, IA, traduction, temps reel).

---

## Remerciements

- **Esprit School of Engineering** - Cadre pedagogique et encadrement
- **Symfony** - Framework PHP
- **Doctrine ORM** - Gestion de la persistance
- Tous les encadrants et tuteurs ayant supervise ce projet
