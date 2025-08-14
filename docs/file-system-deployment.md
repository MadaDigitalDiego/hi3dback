# 🚀 Guide de Déploiement - Système de Gestion de Fichiers

## 📋 Prérequis

### Serveur
- **PHP 8.1+** avec extensions : curl, fileinfo, gd, mbstring
- **Laravel 10+**
- **Base de données** : PostgreSQL/MySQL
- **Stockage** : Minimum 50GB pour les fichiers locaux
- **Mémoire** : 512MB minimum pour PHP
- **Connexion Internet** : Stable pour SwissTransfer

### Extensions PHP Requises
```bash
# Ubuntu/Debian
sudo apt-get install php8.1-curl php8.1-fileinfo php8.1-gd php8.1-mbstring

# CentOS/RHEL
sudo yum install php-curl php-fileinfo php-gd php-mbstring
```

## 🔧 Installation

### 1. Configuration des Variables d'Environnement

```env
# .env - Configuration de production
APP_ENV=production
APP_DEBUG=false

# Système de fichiers
FILE_LOCAL_STORAGE_LIMIT=10
FILE_MAX_UPLOAD_SIZE=500
FILE_ALLOWED_MIME_TYPES="image/jpeg,image/png,image/gif,image/webp,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/zip,application/x-rar-compressed,text/plain"

# SwissTransfer
SWISSTRANSFER_ENABLED=true
SWISSTRANSFER_BASE_URL=https://www.swisstransfer.com
SWISSTRANSFER_API_URL=https://www.swisstransfer.com/api
SWISSTRANSFER_MAX_FILE_SIZE=50000
SWISSTRANSFER_TIMEOUT=300

# Stockage
FILESYSTEM_DISK=public
```

### 2. Exécution des Migrations

```bash
# Exécuter les migrations
php artisan migrate --force

# Vérifier le statut
php artisan migrate:status
```

### 3. Configuration du Stockage

```bash
# Créer le lien symbolique pour le stockage public
php artisan storage:link

# Créer les répertoires nécessaires
mkdir -p storage/app/public/uploads
mkdir -p storage/logs

# Définir les permissions
chmod -R 755 storage/app/public
chmod -R 755 bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### 4. Configuration du Serveur Web

#### Apache (.htaccess)
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>

# Augmenter les limites d'upload
php_value upload_max_filesize 500M
php_value post_max_size 500M
php_value max_execution_time 300
php_value max_input_time 300
php_value memory_limit 512M
```

#### Nginx
```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/html/hi3d/backend/public;
    index index.php;

    # Augmenter les limites d'upload
    client_max_body_size 500M;
    client_body_timeout 300s;
    client_header_timeout 300s;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        
        # Timeouts pour les gros uploads
        fastcgi_read_timeout 300;
        fastcgi_send_timeout 300;
    }

    # Sécurité pour les fichiers uploadés
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|pdf|doc|docx|zip)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

### 5. Configuration PHP

```ini
; php.ini - Optimisations pour les uploads
upload_max_filesize = 500M
post_max_size = 500M
max_execution_time = 300
max_input_time = 300
memory_limit = 512M
max_file_uploads = 20

; Sécurité
file_uploads = On
allow_url_fopen = On
allow_url_include = Off
```

## 🔄 Tâches Cron

### Configuration du Nettoyage Automatique

```bash
# Éditer le crontab
crontab -e

# Ajouter les tâches
# Nettoyage des fichiers expirés (quotidien à 2h)
0 2 * * * cd /var/www/html/hi3d/backend && php artisan files:clean-expired >> /var/log/file-cleanup.log 2>&1

# Nettoyage des logs Laravel (hebdomadaire)
0 3 * * 0 cd /var/www/html/hi3d/backend && find storage/logs -name "*.log" -mtime +7 -delete

# Optimisation de la base de données (mensuel)
0 4 1 * * cd /var/www/html/hi3d/backend && php artisan optimize:clear
```

## 📊 Monitoring et Logs

### 1. Logs Personnalisés

```php
// config/logging.php - Ajouter un canal pour les fichiers
'channels' => [
    'files' => [
        'driver' => 'daily',
        'path' => storage_path('logs/files.log'),
        'level' => 'info',
        'days' => 14,
    ],
],
```

### 2. Métriques à Surveiller

```bash
# Espace disque utilisé
df -h /var/www/html/hi3d/backend/storage

# Nombre de fichiers par type de stockage
mysql -u user -p database -e "
SELECT storage_type, COUNT(*) as count, 
       SUM(size) as total_size 
FROM files 
GROUP BY storage_type;"

# Fichiers en erreur
mysql -u user -p database -e "
SELECT status, COUNT(*) as count 
FROM files 
WHERE status IN ('failed', 'expired') 
GROUP BY status;"
```

### 3. Alertes Recommandées

- **Espace disque < 10%** : Alerte critique
- **Taux d'échec upload > 5%** : Alerte warning
- **Fichiers expirés > 100/jour** : Alerte info
- **Temps de réponse API > 30s** : Alerte warning

## 🔒 Sécurité

### 1. Validation des Fichiers

```php
// Ajouter dans config/filesystems.php
'security' => [
    'scan_uploads' => env('SCAN_UPLOADS', true),
    'max_filename_length' => 255,
    'blocked_extensions' => ['exe', 'bat', 'cmd', 'scr', 'pif'],
    'require_extension_match' => true,
],
```

### 2. Protection contre les Attaques

```apache
# .htaccess - Protection supplémentaire
<Files "*.php">
    Order Deny,Allow
    Deny from all
</Files>

# Bloquer l'exécution de scripts dans uploads
<Directory "/var/www/html/hi3d/backend/storage/app/public/uploads">
    php_flag engine off
    AddType text/plain .php .php3 .phtml .pht
</Directory>
```

### 3. Rate Limiting

```php
// Dans RouteServiceProvider.php
RateLimiter::for('file-upload', function (Request $request) {
    return Limit::perMinute(10)->by($request->user()?->id ?: $request->ip())
                ->response(function () {
                    return response()->json([
                        'success' => false,
                        'message' => 'Too many upload attempts'
                    ], 429);
                });
});
```

## 🚨 Dépannage

### Problèmes Courants

#### 1. Erreur "File too large"
```bash
# Vérifier les limites PHP
php -i | grep -E "(upload_max_filesize|post_max_size|memory_limit)"

# Vérifier les limites du serveur web
# Nginx: client_max_body_size
# Apache: LimitRequestBody
```

#### 2. Erreur de permissions
```bash
# Corriger les permissions
sudo chown -R www-data:www-data storage/
sudo chmod -R 755 storage/
sudo chmod -R 644 storage/logs/
```

#### 3. SwissTransfer indisponible
```bash
# Vérifier la connectivité
curl -I https://www.swisstransfer.com

# Vérifier les logs
tail -f storage/logs/files.log | grep SwissTransfer
```

#### 4. Base de données pleine
```sql
-- Nettoyer les anciens fichiers
DELETE FROM files 
WHERE status = 'expired' 
AND updated_at < DATE_SUB(NOW(), INTERVAL 30 DAY);

-- Optimiser les tables
OPTIMIZE TABLE files;
```

## 📈 Optimisations de Performance

### 1. Cache Redis
```env
CACHE_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### 2. Queue pour les Gros Uploads
```env
QUEUE_CONNECTION=redis

# Traitement en arrière-plan
php artisan queue:work --queue=file-processing
```

### 3. CDN pour les Fichiers Locaux
```php
// config/filesystems.php
'public' => [
    'driver' => 'local',
    'root' => storage_path('app/public'),
    'url' => env('CDN_URL', env('APP_URL').'/storage'),
    'visibility' => 'public',
],
```

## ✅ Checklist de Déploiement

- [ ] Variables d'environnement configurées
- [ ] Migrations exécutées
- [ ] Permissions de stockage définies
- [ ] Lien symbolique créé
- [ ] Limites PHP ajustées
- [ ] Serveur web configuré
- [ ] Tâches cron programmées
- [ ] Logs configurés
- [ ] Tests d'upload effectués
- [ ] Monitoring en place
- [ ] Sauvegardes configurées

---

## 📞 Support

En cas de problème :
1. Vérifier les logs : `storage/logs/laravel.log`
2. Tester avec Postman : Collection fournie
3. Vérifier l'espace disque : `df -h`
4. Consulter la documentation API
5. Contacter l'équipe de développement
