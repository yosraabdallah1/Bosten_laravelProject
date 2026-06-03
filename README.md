# Bosten - Plateforme E-commerce Laravel

Bosten est une plateforme e-commerce moderne développée avec Laravel 12, offrant une expérience complète d'achat en ligne avec gestion des produits, panier, commandes, et un chatbot intégré.

## 📋 Fonctionnalités

### Pour les clients
- ✅ **Catalogue produits** - Navigation et filtrage des produits
- ✅ **Système de panier** - Ajout, modification et suppression d'articles
- ✅ **Passage de commande** - Processus de checkout sécurisé
- ✅ **Gestion des commandes** - Historique et suivi des commandes
- ✅ **Chatbot intégré** - Assistant virtuel pour répondre aux questions
- ✅ **Profil utilisateur** - Gestion des informations personnelles

### Pour les administrateurs
- ✅ **Dashboard admin** - Vue d'ensemble des statistiques
- ✅ **Gestion des produits** - CRUD complet des produits
- ✅ **Gestion des catégories** - Organisation des produits
- ✅ **Gestion des commandes** - Suivi et mise à jour du statut
- ✅ **Interface sécurisée** - Accès restreint aux administrateurs

## 🛠️ Technologies utilisées

### Backend
- **Laravel 12** - Framework PHP moderne
- **PHP 8.2+** - Langage de programmation
- **MySQL/SQLite** - Base de données
- **Eloquent ORM** - Mapping objet-relationnel
- **Artisan CLI** - Outils de développement

### Frontend
- **Tailwind CSS 3.1** - Framework CSS utilitaire
- **Alpine.js 3.4** - Framework JavaScript minimaliste
- **Bootstrap 5.3** - Composants d'interface
- **Vite 7.0** - Outil de build et développement
- **Axios** - Client HTTP pour les requêtes AJAX

### Authentification & Sécurité
- **Laravel Breeze** - Kit d'authentification
- **Middleware admin** - Contrôle d'accès personnalisé
- **Validation des données** - Sécurité des entrées utilisateur
- **Protection CSRF** - Sécurité des formulaires

## 📁 Structure du projet

```
bosten/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/          # Contrôleurs administrateur
│   │   │   ├── Auth/           # Contrôleurs d'authentification
│   │   │   ├── CartController.php
│   │   │   ├── ChatbotController.php
│   │   │   ├── OrderController.php
│   │   │   └── ProductController.php
│   │   ├── Middleware/
│   │   │   └── EnsureIsAdmin.php
│   │   └── Requests/
│   ├── Models/                 # Modèles Eloquent
│   ├── Policies/               # Politiques d'autorisation
│   ├── Providers/
│   ├── Services/               # Services métier
│   └── View/Components/
├── bootstrap/                  # Configuration Laravel
├── config/                     # Fichiers de configuration
├── database/
│   ├── migrations/            # Migrations de base de données
│   ├── seeders/              # Données de test
│   └── factories/
├── public/                    # Assets publics
├── resources/
│   ├── views/                # Templates Blade
│   └── css/                  # Styles CSS
├── routes/                    # Routes web et API
├── storage/                   # Fichiers uploadés
└── tests/                     # Tests unitaires
```

## 🚀 Installation et configuration

### Prérequis
- PHP 8.2 ou supérieur
- Composer
- Node.js 18+ et npm
- Base de données (MySQL, PostgreSQL ou SQLite)

### Étapes d'installation

1. **Cloner le dépôt**
   ```bash
   git clone <repository-url>
   cd bosten
   ```

2. **Installer les dépendances PHP**
   ```bash
   composer install
   ```

3. **Configurer l'environnement**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Configurer la base de données**
   Modifier le fichier `.env` avec vos informations de base de données :
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=bosten
   DB_USERNAME=root
   DB_PASSWORD=
   ```

5. **Exécuter les migrations et les seeders**
   ```bash
   php artisan migrate --seed
   ```

6. **Installer les dépendances frontend**
   ```bash
   npm install
   npm run build
   ```

7. **Créer un compte administrateur**
   ```bash
   php artisan tinker
   ```
   ```php
   $user = App\Models\User::create([
       'name' => 'Admin',
       'email' => 'admin@example.com',
       'password' => bcrypt('password123'),
       'is_admin' => true,
   ]);
   ```

### Développement local

Pour le développement, vous pouvez utiliser :

```bash
# Lancer le serveur de développement
php artisan serve

# Lancer Vite pour les assets frontend
npm run dev

# Ou utiliser le script de développement intégré
composer run dev
```

## 🧪 Modèles de base de données

### User
- `id` - Identifiant unique
- `name` - Nom complet
- `email` - Adresse email
- `password` - Mot de passe hashé
- `is_admin` - Statut administrateur
- Relations : `orders`, `cartItems`, `conversations`

### Product
- `id` - Identifiant unique
- `name` - Nom du produit
- `slug` - Slug URL
- `description` - Description détaillée
- `price` - Prix du produit
- `stock` - Quantité en stock
- `category_id` - Référence à la catégorie

### Category
- `id` - Identifiant unique
- `name` - Nom de la catégorie
- `slug` - Slug URL
- `description` - Description de la catégorie

### Order
- `id` - Identifiant unique
- `user_id` - Référence à l'utilisateur
- `status` - Statut de la commande
- `total_amount` - Montant total
- Relations : `user`, `orderItems`

### CartItem
- `id` - Identifiant unique
- `user_id` - Référence à l'utilisateur
- `product_id` - Référence au produit
- `quantity` - Quantité dans le panier

### Conversation
- `id` - Identifiant unique
- `user_id` - Référence à l'utilisateur
- `question` - Question posée
- `answer` - Réponse du chatbot
- `timestamp` - Date et heure

## 📊 Routes principales

### Routes publiques
- `GET /` - Page d'accueil (liste des produits)
- `GET /produits` - Catalogue des produits
- `GET /produits/{slug}` - Détail d'un produit

### Routes authentifiées (clients)
- `GET /profile` - Gestion du profil
- `GET /panier` - Vue du panier
- `POST /panier` - Ajouter au panier
- `GET /commandes` - Historique des commandes
- `GET /chatbot` - Interface du chatbot

### Routes administrateur
- `GET /admin/dashboard` - Tableau de bord admin
- `GET /admin/products` - Gestion des produits
- `GET /admin/categories` - Gestion des catégories
- `GET /admin/orders` - Gestion des commandes

## 🤖 Chatbot

Le chatbot intégré utilise une approche simple de correspondance de mots-clés pour répondre aux questions courantes des clients :

- Questions sur les produits
- Informations sur les commandes
- Support client
- Questions sur les livraisons

## 🔒 Sécurité

- **Middleware admin** : Vérification du statut `is_admin` pour l'accès aux zones admin
- **Validation des formulaires** : Validation Laravel pour toutes les entrées
- **Protection CSRF** : Jetons pour tous les formulaires
- **Hashing des mots de passe** : Utilisation de bcrypt
- **Sessions sécurisées** : Gestion sécurisée des sessions

## 🧪 Tests

Pour exécuter les tests :

```bash
# Exécuter tous les tests
composer run test

# Exécuter les tests PHPUnit
php artisan test

# Exécuter les tests avec couverture de code
php artisan test --coverage
```

## 🚀 Déploiement

### Production
1. Configurer les variables d'environnement de production
2. Exécuter `npm run build` pour les assets optimisés
3. Configurer le serveur web (Apache/Nginx)
4. Configurer les permissions des dossiers storage et bootstrap/cache

### Optimisations
```bash
# Optimiser l'autoload Composer
composer install --optimize-autoloader --no-dev

# Optimiser le cache de configuration
php artisan config:cache

# Optimiser le cache de routes
php artisan route:cache

# Optimiser le cache des vues
php artisan view:cache
```

## 📝 Scripts disponibles

```bash
# Installation complète
composer run setup

# Développement (serveur + Vite + logs)
composer run dev

# Build des assets de production
npm run build

# Exécution des tests
composer run test
```

## 🐛 Dépannage

### Problèmes courants

1. **Permissions de dossiers**
   ```bash
   chmod -R 775 storage bootstrap/cache
   ```

2. **Erreurs de cache**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   php artisan view:clear
   ```

3. **Problèmes de migration**
   ```bash
   php artisan migrate:fresh --seed
   ```

## 📄 Licence

Ce projet est sous licence MIT. Voir le fichier `LICENSE` pour plus de détails.

## 👥 Contribution

Les contributions sont les bienvenues ! Veuillez :

1. Fork le projet
2. Créer une branche de fonctionnalité
3. Commiter vos changements
4. Pousser vers la branche
5. Ouvrir une Pull Request

## 📞 Support

Pour les problèmes techniques, veuillez créer une issue sur le dépôt GitHub.

---

**Bosten** - E-commerce moderne avec Laravel 12 • Développé avec ❤️