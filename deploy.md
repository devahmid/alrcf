# Guide de Déploiement - ALRCF Association

## 🚀 Déploiement sur un hébergement mutualisé

### Prérequis
- Hébergement web avec PHP 8.0+
- Base de données MySQL 5.7+
- Accès FTP/SFTP ou panneau de contrôle
- Nom de domaine configuré

### Étapes de déploiement

#### 1. Préparation des fichiers

```bash
# Build de production
./build-prod.sh

# Ou manuellement
npm run build
```

#### 2. Upload des fichiers

**Frontend (Angular) :**
- Uploadez le contenu du dossier `dist/alrcf-association/` à la racine de votre site web
- Assurez-vous que `index.html` est à la racine

**Backend (PHP) :**
- Uploadez le dossier `api/` à la racine de votre site web
- Vérifiez que les permissions sont correctes (755 pour les dossiers, 644 pour les fichiers)

#### 3. Configuration de la base de données

1. Créez une base de données MySQL via votre panneau de contrôle
2. Modifiez `api/config/database.php` avec vos paramètres :
   ```php
   private $host = "localhost"; // ou l'IP de votre serveur
   private $db_name = "votre_nom_de_base";
   private $username = "votre_utilisateur";
   private $password = "votre_mot_de_passe";
   ```
3. Exécutez le script d'installation : `http://votre-domaine.com/api/database/install.php`

#### 4. Configuration Angular

Modifiez `src/environments/environment.prod.ts` :
```typescript
export const environment = {
  production: true,
  apiUrl: 'https://votre-domaine.com/api/',
  appName: 'ALRCF Association',
  version: '1.0.0'
};
```

Puis rebuilder l'application :
```bash
npm run build
```

#### 5. Configuration du serveur web

**Apache (.htaccess) :**
```apache
RewriteEngine On

# Redirection vers Angular pour les routes SPA
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_URI} !^/api/
RewriteRule ^(.*)$ /index.html [L]

# Configuration API
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^api/(.*)$ /api/$1 [L]
```

**Nginx :**
```nginx
server {
    listen 80;
    server_name votre-domaine.com;
    root /var/www/html;
    index index.html;

    # API routes
    location /api/ {
        try_files $uri $uri/ /api/index.php?$query_string;
    }

    # Angular routes
    location / {
        try_files $uri $uri/ /index.html;
    }
}
```

### 🔧 Configuration avancée

#### Variables d'environnement
Créez un fichier `.env` dans le dossier `api/` :
```env
DB_HOST=localhost
DB_NAME=alrcf_association
DB_USER=votre_utilisateur
DB_PASS=votre_mot_de_passe
JWT_SECRET=votre_secret_jwt
```

#### Sécurité
1. Changez le mot de passe admin par défaut
2. Configurez HTTPS (certificat SSL)
3. Limitez l'accès aux fichiers sensibles
4. Configurez les en-têtes de sécurité

#### Performance
1. Activez la compression GZIP
2. Configurez la mise en cache
3. Optimisez les images
4. Utilisez un CDN si nécessaire

### 🐛 Dépannage

#### Erreurs courantes

**Erreur 500 :**
- Vérifiez les permissions des fichiers
- Vérifiez la configuration PHP
- Consultez les logs d'erreur

**Erreur CORS :**
- Vérifiez la configuration CORS dans `api/config/cors.php`
- Vérifiez l'URL de l'API dans Angular

**Erreur de base de données :**
- Vérifiez les paramètres de connexion
- Vérifiez que la base de données existe
- Vérifiez les permissions de l'utilisateur

#### Logs
- Logs PHP : `/var/log/apache2/error.log` ou `/var/log/nginx/error.log`
- Logs Angular : Console du navigateur
- Logs de l'application : `api/logs/` (si configuré)

### 📱 Test de déploiement

1. **Test de l'API :**
   - `https://votre-domaine.com/api/` → Doit retourner les informations de l'API

2. **Test de l'application :**
   - `https://votre-domaine.com/` → Doit afficher l'application Angular

3. **Test de connexion :**
   - Essayez de vous connecter avec le compte admin
   - Testez les fonctionnalités principales

### 🔄 Mise à jour

Pour mettre à jour l'application :

1. Téléchargez les nouveaux fichiers
2. Remplacez les fichiers existants
3. Exécutez les migrations de base de données si nécessaire
4. Videz le cache du navigateur

### 📞 Support

En cas de problème :
1. Vérifiez les logs d'erreur
2. Consultez la documentation
3. Contactez l'équipe de développement

---

**Bon déploiement ! 🚀**
