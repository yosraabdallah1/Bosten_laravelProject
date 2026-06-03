# Bosten 🌿

Plateforme e-commerce spécialisée dans les plantes et le jardinage, développée avec **Laravel 12**. Elle intègre un catalogue produits, un système de panier/commandes, un espace d'administration complet et un chatbot IA (Gemini) capable de répondre aux questions des clients en se basant sur les données réelles de la boutique.

---

## Fonctionnalités

### Espace client
- Catalogue produits avec filtrage par catégorie et recherche plein texte
- Fiche produit détaillée avec produits similaires
- Panier : ajout, modification de quantité, suppression
- Checkout et historique des commandes avec suivi de statut
- Profil utilisateur avec onglets : infos, mot de passe, commandes, suppression de compte
- **Chatbot Basma** — assistant IA alimenté par Gemini 2.0 Flash, avec analyse d'intention par regex, récupération dynamique de données en base (produits, commandes, stock, bestsellers) et fallback local intelligent si l'API est indisponible

### Espace administrateur
- Dashboard avec KPIs (produits actifs, commandes en attente, clients, chiffre d'affaires) et alertes stock faible
- CRUD complet des produits (nom, catégorie, prix, stock, image, visibilité) avec génération de slug unique automatique
- CRUD complet des catégories
- Gestion des commandes avec changement de statut inline
- Sidebar de navigation fixe avec badge commandes en attente

### Authentification
- Inscription / Connexion / Déconnexion via Laravel Breeze
- Vérification d'email
- Réinitialisation de mot de passe
- Middleware `EnsureIsAdmin` pour la protection des routes admin

---

## Stack technique

| Couche | Technologie |
|--------|-------------|
| Backend | Laravel 12, PHP 8.2+ |
| Base de données | MySQL (SQLite en test) |
| ORM | Eloquent |
| Authentification | Laravel Breeze |
| Frontend | Bootstrap 5.3, Bootstrap Icons CDN, Alpine.js 3.4 |
| Build | Vite 7, Tailwind CSS 3 |
| HTTP client | Guzzle 7 |
| IA chatbot | Google Gemini 2.0 Flash |
| Tests | PHPUnit 11 — 139 tests, 284 assertions |
| Qualité | SonarCloud + GitHub Actions |

---

## Structure du projet

```
bosten/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/
│   │   │   │   ├── AdminController.php       # Dashboard + KPIs
│   │   │   │   └── CategoryController.php    # CRUD catégories
│   │   │   ├── Auth/                         # Breeze (login, register...)
│   │   │   ├── CartController.php
│   │   │   ├── ChatbotController.php          # index / ask / clear
│   │   │   ├── OrderController.php
│   │   │   ├── ProductController.php          # public + admin CRUD
│   │   │   └── ProfileController.php
│   │   ├── Middleware/
│   │   │   └── EnsureIsAdmin.php
│   │   └── Requests/
│   ├── Models/
│   │   ├── CartItem.php
│   │   ├── Category.php                      # HasFactory
│   │   ├── Conversation.php
│   │   ├── Order.php                         # HasFactory
│   │   ├── OrderItem.php
│   │   ├── Product.php                       # HasFactory, isInStock()
│   │   └── User.php
│   ├── Policies/
│   │   ├── CartItemPolicy.php
│   │   └── OrderPolicy.php
│   └── Services/
│       ├── CartService.php
│       ├── GeminiChatbotService.php           # Moteur IA du chatbot
│       ├── OrderService.php
│       └── ProductService.php
├── bootstrap/
│   └── app.php                               # Routing web + api
├── database/
│   ├── factories/
│   │   ├── CategoryFactory.php
│   │   ├── OrderFactory.php
│   │   ├── ProductFactory.php
│   │   └── UserFactory.php                   # state admin()
│   ├── migrations/
│   └── seeders/
│       ├── CategorySeeder.php
│       └── ProductSeeder.php
├── resources/
│   ├── views/
│   │   ├── admin/                            # Dashboard, produits, catégories, commandes
│   │   ├── auth/                             # Login, register, reset...
│   │   ├── cart/
│   │   ├── chatbot/
│   │   ├── layouts/
│   │   │   ├── admin.blade.php               # Layout avec sidebar admin
│   │   │   ├── app.blade.php                 # Layout principal (Bootstrap CDN)
│   │   │   ├── guest.blade.php               # Layout auth
│   │   │   └── navigation.blade.php          # Navbar responsive
│   │   ├── orders/
│   │   ├── products/
│   │   └── profile/
│   ├── css/app.css
│   └── js/app.js
├── routes/
│   ├── api.php                               # POST /api/chatbot
│   └── web.php
├── tests/
│   ├── Feature/
│   │   ├── Auth/                             # Tests Breeze (25 tests)
│   │   ├── AuthTest.php                      # 13 tests
│   │   ├── CartFeatureTest.php               # 10 tests
│   │   ├── ChatbotEndpointTest.php           # 9 tests
│   │   ├── OrderFeatureTest.php              # 14 tests
│   │   └── ProductFeatureTest.php            # 13 tests
│   └── Unit/
│       ├── CartServiceTest.php               # 11 tests
│       ├── GeminiChatbotServiceTest.php      # 16 tests
│       ├── OrderServiceTest.php              # 8 tests
│       ├── ProductModelTest.php              # 7 tests
│       └── ProductServiceTest.php            # 13 tests
├── .github/
│   └── workflows/
│       └── sonar.yml                         # CI/CD GitHub Actions
└── sonar-project.properties                  # Config SonarCloud
```

---

## Installation

### Prérequis

- PHP 8.2+
- Composer
- Node.js 18+ et npm
- MySQL

### Étapes

```bash
# 1. Cloner le dépôt
git clone <repository-url>
cd bosten

# 2. Dépendances PHP
composer install

# 3. Environnement
cp .env.example .env
php artisan key:generate

# 4. Base de données — modifier .env puis :
php artisan migrate --seed

# 5. Assets frontend
npm install
npm run build

# 6. Lien storage pour les images uploadées
php artisan storage:link
```

### Configuration `.env`

```env
# Base de données
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bosten_db
DB_USERNAME=root
DB_PASSWORD=

# Chatbot IA (Gemini)
# Obtenir une clé sur https://aistudio.google.com/apikey
# La clé doit commencer par "AIza..."
GEMINI_API_KEY=AIzaSy...votre_cle...
```

### Créer un compte administrateur

```bash
php artisan tinker
```
```php
App\Models\User::create([
    'name'     => 'Admin',
    'email'    => 'admin@bosten.tn',
    'password' => bcrypt('motdepasse'),
    'is_admin' => true,
]);
```

### Lancer en développement

```bash
php artisan serve   # http://127.0.0.1:8000
npm run dev         # Vite HMR (optionnel)
```

---

## Routes

### Publiques
| Méthode | URI | Description |
|---------|-----|-------------|
| GET | `/` | Accueil — catalogue |
| GET | `/produits` | Liste des produits |
| GET | `/produits/{slug}` | Fiche produit |

### Authentifiées (clients)
| Méthode | URI | Description |
|---------|-----|-------------|
| GET/PATCH/DELETE | `/profile` | Gestion du profil |
| GET/POST/PATCH/DELETE | `/panier` | Panier |
| GET/POST | `/commandes` | Commandes |
| GET | `/commandes/{order}` | Détail commande |
| GET | `/checkout` | Checkout |
| GET | `/chatbot` | Interface chatbot |
| POST | `/chatbot` | Envoyer un message |
| POST | `/chatbot/clear` | Effacer l'historique |

### API
| Méthode | URI | Middleware | Description |
|---------|-----|------------|-------------|
| POST | `/api/chatbot` | `auth` | Endpoint API chatbot |

### Administrateur (`/admin/...`)
| Méthode | URI | Description |
|---------|-----|-------------|
| GET | `/admin/dashboard` | Tableau de bord |
| GET/POST | `/admin/products` | Liste + créer produit |
| GET/PUT/DELETE | `/admin/products/{product}` | Modifier / désactiver |
| GET/POST | `/admin/categories` | Liste + créer catégorie |
| GET/PUT/DELETE | `/admin/categories/{category}` | Modifier / supprimer |
| GET | `/admin/orders` | Liste des commandes |
| PATCH | `/admin/orders/{order}/status` | Changer le statut |

---

## Chatbot Basma — fonctionnement

### Architecture

```
Question utilisateur
      │
      ▼
detectIntent()         ← analyse par regex (stock, orders, catalogue,
      │                   bestsellers, price, advice)
      ▼
fetchContextData()     ← charge uniquement les données pertinentes
      │                   depuis la base de données via Eloquent
      ▼
buildPrompt()          ← construit le prompt : système + données
      │                   + historique (3 derniers échanges) + question
      ▼
callGemini()           ← appel HTTP Guzzle vers Gemini 2.0 Flash
      │
      ├─ Succès ──────→ réponse IA en langage naturel
      │
      └─ Erreur ──────→ localFallback() ← réponse construite
           (429/timeout)                   depuis les données DB
```

### Intents détectés automatiquement

| Intent | Mots-clés | Données chargées |
|--------|-----------|------------------|
| `stock` | stock, rupture, disponible, quantité | Produits avec stock < 5 |
| `orders` | commande, livraison, statut, suivi | 5 dernières commandes |
| `catalogue` | produits, plante, jardinage, liste | 8 premiers produits actifs |
| `bestsellers` | meilleure vente, populaire, top | Top 3 par volume vendu |
| `price` | prix, tarif, combien, promotion | Tous les produits avec prix |
| `advice` | conseil, entretien, arrosage, jardinage | Produits avec descriptions |

### Gestion des erreurs API

| Code | Comportement |
|------|-------------|
| `429` (quota/clé invalide) | `localFallback()` avec données DB |
| Timeout / ConnectException | Message "service indisponible" |
| `500` / `503` | Message "difficultés techniques" |
| Réponse vide | `localFallback()` |

> **Note :** La clé Gemini doit commencer par `AIza...`. Une clé invalide génère un HTTP 429 immédiat.

---

## Modèles de données

```
users
  id, name, email, password, is_admin, email_verified_at

categories
  id, name, slug, description

products
  id, category_id, name, slug, description, price, stock, image, is_active

orders
  id, user_id, status, total, address, phone
  status: pending | confirmed | shipped | delivered | cancelled

order_items
  id, order_id, product_id, quantity, unit_price

cart_items
  id, user_id, product_id, quantity

conversations
  id, user_id, message, reply
```

---

## Tests

**139 tests — 284 assertions — 100% pass ✅**

### Lancer les tests

```bash
# Toute la suite
php artisan test

# Suite unitaire seulement
php artisan test --testsuite=Unit

# Suite Feature seulement
php artisan test --testsuite=Feature

# Un fichier spécifique
php artisan test tests/Unit/ProductServiceTest.php
```

### Couverture de code (nécessite Xdebug)

```bash
# Activer Xdebug dans php.ini (XAMPP)
# Décommenter ou ajouter : zend_extension=xdebug

# Couverture dans le terminal
$env:XDEBUG_MODE="coverage"; php artisan test --coverage

# Rapport HTML complet
$env:XDEBUG_MODE="coverage"; php artisan test --coverage-html=reports/coverage-html

# Rapport Clover (pour SonarCloud)
$env:XDEBUG_MODE="coverage"; php artisan test --coverage-clover=reports/coverage.xml
```

### Détail des tests

| Suite | Fichier | Tests | Ce qui est couvert |
|-------|---------|-------|--------------------|
| Unit | `ProductServiceTest` | 13 | `getActiveProducts` (filtre, paginate, recherche), `getInStockProducts`, `getLowStock`, `getBestSellers` |
| Unit | `CartServiceTest` | 11 | `addItem` (création, incrément, défaut), `getCartWithTotal`, `updateItem`, `removeItem`, `clearCart` |
| Unit | `OrderServiceTest` | 8 | `createFromCart` (total, items, stock, panier vidé, exceptions, rollback) |
| Unit | `ProductModelTest` | 7 | `isInStock()`, relations Eloquent, factory states |
| Unit | `GeminiChatbotServiceTest` | 16 | `detectIntent` (9 cas), `buildPrompt` (3 cas), `callGemini` avec mock Guzzle (succès, 429, timeout, 500) |
| Feature | `AuthTest` | 13 | Inscription, connexion, déconnexion, accès admin/client/guest |
| Feature | `CartFeatureTest` | 10 | Panier CRUD via HTTP + policies d'autorisation |
| Feature | `OrderFeatureTest` | 14 | Checkout, création commande, stock décrémenté, autorisation, statuts admin |
| Feature | `ProductFeatureTest` | 13 | Catalogue public, filtres, CRUD admin avec image et slug unique |
| Feature | `ChatbotEndpointTest` | 9 | Endpoint `/chatbot`, validation, sauvegarde DB, clear |
| Feature | Auth/* (Breeze) | 25 | Login, register, password reset, email verification, profile |

### Bonnes pratiques appliquées

- **`RefreshDatabase`** sur chaque classe de test → base SQLite in-memory isolée par test
- **Factories** dédiées avec states (`admin()`, `inactive()`, `outOfStock()`, `lowStock()`, `pending()`...)
- **Mocks Guzzle** via `MockHandler` pour `callGemini()` sans appel réseau réel
- **`$this->mock()`** Laravel pour mocker `GeminiChatbotService` dans les tests Feature
- **`Storage::fake('public')`** pour les tests d'upload d'image
- **Assertions strictes** : `assertDatabaseHas`, `assertDatabaseMissing`, `assertDatabaseCount`

---

## Qualité du code — SonarCloud

### Configuration

Le fichier `sonar-project.properties` à la racine configure l'analyse :

```properties
sonar.projectKey=votre-username_bosten
sonar.organization=votre-username
sonar.sources=app,routes,database
sonar.tests=tests
sonar.php.coverage.reportPaths=reports/coverage.xml
sonar.exclusions=vendor/**,node_modules/**,public/**,storage/**,resources/views/**
```

### Setup SonarCloud

1. Créer un compte sur [sonarcloud.io](https://sonarcloud.io) et lier le dépôt GitHub
2. Récupérer le `Project Key` et l'`Organization` depuis le dashboard SonarCloud
3. Mettre à jour `sonar-project.properties` avec ces valeurs
4. Ajouter les secrets GitHub :
   - `SONAR_TOKEN` → token généré sur SonarCloud (Account > Security)
   - `SONAR_PROJECT_KEY` → clé du projet
   - `SONAR_ORGANIZATION` → organisation

### Pipeline GitHub Actions

Le fichier `.github/workflows/sonar.yml` s'exécute automatiquement à chaque `push` / `pull_request` sur `main` ou `develop` :

```
1. Checkout du code (fetch-depth: 0 pour SonarCloud)
2. Setup PHP 8.2 + Xdebug
3. Setup Node.js 20
4. Cache Composer + install dépendances
5. npm ci + npm run build
6. Configuration .env de test
7. php artisan test --coverage-clover=reports/coverage.xml
8. Upload artefacts (rapports coverage + JUnit)
9. SonarCloud Scan (SonarSource/sonarcloud-github-action@v2)
```

---

## Déploiement

```bash
# Optimiser pour la production
composer install --optimize-autoloader --no-dev
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Permissions (Linux/Mac)
chmod -R 775 storage bootstrap/cache
```

---

## Dépannage

**Assets non chargés** — relancer `npm run build` puis `php artisan view:clear`

**Chatbot répond "surchargé" en permanence** — la clé `GEMINI_API_KEY` est invalide ou expirée. Générer une nouvelle clé sur [aistudio.google.com/apikey](https://aistudio.google.com/apikey) (format `AIzaSy...`) puis `php artisan config:clear`

**`Route [dashboard] not defined`** — vider le cache : `php artisan route:clear && php artisan cache:clear`

**Images produits non affichées** — vérifier que le lien storage existe : `php artisan storage:link`

**Tests en échec après `git pull`** — `php artisan migrate:fresh` si les migrations ont changé

**Migrations en erreur** — `php artisan migrate:fresh --seed` (⚠️ efface toutes les données)

---

## Licence

MIT

---

*Bosten — Plantes & Jardinage en Tunisie 🌿*
