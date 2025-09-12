# ALRCF - Application Web d'Association

Application web moderne pour la gestion d'une association avec Angular frontend et PHP backend.

## 🚀 Fonctionnalités

### Pages Publiques
- **Page d'accueil** : Informations récentes avec animations et design moderne
- **À propos** : Présentation de l'association, équipe, histoire et valeurs
- **Contact** : Formulaire de contact, coordonnées et FAQ

### Espace Adhérent
- **Profil personnel** : Gestion des informations personnelles
- **Cotisations** : Consultation de l'historique des paiements
- **Signalements** : Création et suivi des signalements
- **Messages** : Communication avec l'administration

### Espace Administration
- **Tableau de bord** : Statistiques et vue d'ensemble
- **Gestion des adhérents** : CRUD complet des membres
- **Actualités** : Création et gestion des actualités
- **Événements** : Planification et gestion des événements
- **Messages** : Gestion des messages de contact
- **Signalements** : Traitement des signalements
- **Cotisations** : Suivi des paiements

## 🛠️ Technologies Utilisées

### Frontend
- **Angular 17** : Framework principal
- **Bootstrap 5** : Framework CSS
- **Font Awesome** : Icônes
- **AOS (Animate On Scroll)** : Animations
- **SCSS** : Préprocesseur CSS

### Backend
- **PHP 8+** : Langage serveur
- **MySQL** : Base de données
- **PDO** : Accès aux données
- **API REST** : Architecture

## 📋 Prérequis

- **Node.js** 18+ et npm
- **PHP** 8.0+
- **MySQL** 5.7+ ou MariaDB
- **Serveur web** (Apache/Nginx)

## 🚀 Installation

### 1. Cloner le projet
```bash
git clone [url-du-repo]
cd alrcf
```

### 2. Installation du frontend Angular
```bash
# Installer les dépendances
npm install

# Démarrer le serveur de développement
npm start
```

### 3. Configuration de la base de données

#### Option A : Installation automatique
1. Accédez à `http://localhost/api/database/install.php` dans votre navigateur
2. Suivez les instructions à l'écran

#### Option B : Installation manuelle
1. Créez une base de données MySQL nommée `alrcf_association`
2. Importez le fichier `api/database/schema.sql`

### 4. Configuration PHP

Modifiez le fichier `api/config/database.php` avec vos paramètres de base de données :

```php
private $host = "localhost";
private $db_name = "alrcf_association";
private $username = "votre_utilisateur";
private $password = "votre_mot_de_passe";
```

### 5. Configuration Angular

Modifiez le fichier `src/app/services/auth.service.ts` et `src/app/services/association.service.ts` pour pointer vers votre API :

```typescript
private apiUrl = 'http://votre-domaine/api/';
```

## 🔧 Configuration du serveur

### Apache (.htaccess)
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^api/(.*)$ api/$1 [L]
```

### Nginx
```nginx
location /api/ {
    try_files $uri $uri/ /api/index.php?$query_string;
}
```

## 👤 Comptes par défaut

### Administrateur
- **Email** : admin@alrcf.fr
- **Mot de passe** : password

## 📁 Structure du projet

```
alrcf/
├── src/                    # Code source Angular
│   ├── app/
│   │   ├── components/     # Composants Angular
│   │   ├── services/       # Services Angular
│   │   ├── models/         # Modèles TypeScript
│   │   └── guards/         # Guards de sécurité
├── api/                    # Backend PHP
│   ├── auth/              # APIs d'authentification
│   ├── news/              # APIs des actualités
│   ├── events/            # APIs des événements
│   ├── contact/           # APIs de contact
│   ├── config/            # Configuration
│   └── database/          # Scripts de base de données
└── assets/                # Ressources statiques
```

## 🎨 Personnalisation

### Couleurs
Modifiez les variables CSS dans `src/styles.scss` :

```scss
:root {
  --primary-color: #2c3e50;
  --secondary-color: #3498db;
  --accent-color: #e74c3c;
  // ...
}
```

### Logo et images
Remplacez les images dans le dossier `src/assets/images/`

## 🔒 Sécurité

- Mots de passe hashés avec `password_hash()`
- Protection CORS configurée
- Validation des données côté serveur
- Guards Angular pour la protection des routes

## 📱 Responsive Design

L'application est entièrement responsive et s'adapte à tous les écrans :
- Desktop (1200px+)
- Tablet (768px - 1199px)
- Mobile (< 768px)

## 🚀 Déploiement

### Frontend (Angular)
```bash
# Build de production
npm run build

# Les fichiers sont dans dist/alrcf-association/
```

### Backend (PHP)
1. Uploadez le dossier `api/` sur votre serveur
2. Configurez la base de données
3. Assurez-vous que PHP a les permissions d'écriture

## 🐛 Dépannage

### Erreurs courantes

1. **Erreur CORS** : Vérifiez la configuration CORS dans `api/config/cors.php`
2. **Erreur de base de données** : Vérifiez les paramètres dans `api/config/database.php`
3. **Erreur 404** : Vérifiez la configuration du serveur web

### Logs
- Logs PHP : Vérifiez les logs d'erreur de votre serveur
- Logs Angular : Console du navigateur

## 📞 Support

Pour toute question ou problème :
- Créez une issue sur le repository
- Contactez l'équipe de développement

## 📄 Licence

Ce projet est sous licence MIT. Voir le fichier `LICENSE` pour plus de détails.

## 🤝 Contribution

Les contributions sont les bienvenues ! N'hésitez pas à :
1. Fork le projet
2. Créer une branche pour votre fonctionnalité
3. Commiter vos changements
4. Pousser vers la branche
5. Ouvrir une Pull Request

---

**Développé avec ❤️ pour l'ALRCF**
