# 🚀 Guide de Déploiement - Recherche Globale en Production

## 🎯 Vue d'ensemble

Ce guide détaille le déploiement de la recherche globale avec Meilisearch en production, incluant la configuration Docker, la sécurité, et les bonnes pratiques.

## 🏗️ Architecture de Production

### Composants
- **Laravel Application** - API de recherche
- **Meilisearch** - Moteur de recherche
- **Redis** - Cache et métriques
- **Nginx** - Reverse proxy et load balancer
- **PostgreSQL/MySQL** - Base de données principale

### Diagramme d'Architecture
```
[Load Balancer] → [Nginx] → [Laravel App] → [Meilisearch]
                                ↓
                           [Redis Cache]
                                ↓
                           [PostgreSQL]
```

## 🐳 Déploiement avec Docker

### 1. Docker Compose Production

Créez un fichier `docker-compose.prod.yml` :

```yaml
version: '3.8'

services:
  # Application Laravel
  app:
    build:
      context: .
      dockerfile: Dockerfile.prod
    environment:
      - APP_ENV=production
      - SCOUT_DRIVER=meilisearch
      - MEILISEARCH_HOST=http://meilisearch:7700
      - MEILISEARCH_KEY=${MEILISEARCH_MASTER_KEY}
      - REDIS_HOST=redis
      - CACHE_DRIVER=redis
    depends_on:
      - meilisearch
      - redis
      - db
    networks:
      - app-network

  # Meilisearch
  meilisearch:
    image: getmeili/meilisearch:v1.5
    environment:
      - MEILI_MASTER_KEY=${MEILISEARCH_MASTER_KEY}
      - MEILI_ENV=production
      - MEILI_DB_PATH=/meili_data
      - MEILI_HTTP_ADDR=0.0.0.0:7700
      - MEILI_LOG_LEVEL=INFO
    volumes:
      - meilisearch_data:/meili_data
    ports:
      - "7700:7700"
    networks:
      - app-network
    restart: unless-stopped

  # Redis pour cache et métriques
  redis:
    image: redis:7-alpine
    command: redis-server --appendonly yes --requirepass ${REDIS_PASSWORD}
    volumes:
      - redis_data:/data
    ports:
      - "6379:6379"
    networks:
      - app-network
    restart: unless-stopped

  # Base de données
  db:
    image: postgres:15
    environment:
      - POSTGRES_DB=${DB_DATABASE}
      - POSTGRES_USER=${DB_USERNAME}
      - POSTGRES_PASSWORD=${DB_PASSWORD}
    volumes:
      - postgres_data:/var/lib/postgresql/data
    ports:
      - "5432:5432"
    networks:
      - app-network
    restart: unless-stopped

  # Nginx reverse proxy
  nginx:
    image: nginx:alpine
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./nginx.conf:/etc/nginx/nginx.conf
      - ./ssl:/etc/nginx/ssl
    depends_on:
      - app
    networks:
      - app-network
    restart: unless-stopped

volumes:
  meilisearch_data:
  redis_data:
  postgres_data:

networks:
  app-network:
    driver: bridge
```

### 2. Configuration Nginx

Créez `nginx.conf` :

```nginx
events {
    worker_connections 1024;
}

http {
    upstream laravel {
        server app:9000;
    }

    # Rate limiting
    limit_req_zone $binary_remote_addr zone=search:10m rate=10r/s;
    limit_req_zone $binary_remote_addr zone=suggestions:10m rate=20r/s;

    server {
        listen 80;
        server_name your-domain.com;
        return 301 https://$server_name$request_uri;
    }

    server {
        listen 443 ssl http2;
        server_name your-domain.com;

        ssl_certificate /etc/nginx/ssl/cert.pem;
        ssl_certificate_key /etc/nginx/ssl/key.pem;

        location /api/search {
            limit_req zone=search burst=20 nodelay;
            proxy_pass http://laravel;
            proxy_set_header Host $host;
            proxy_set_header X-Real-IP $remote_addr;
            proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        }

        location /api/search/suggestions {
            limit_req zone=suggestions burst=50 nodelay;
            proxy_pass http://laravel;
            proxy_set_header Host $host;
            proxy_set_header X-Real-IP $remote_addr;
            proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        }

        location / {
            proxy_pass http://laravel;
            proxy_set_header Host $host;
            proxy_set_header X-Real-IP $remote_addr;
            proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        }
    }
}
```

### 3. Dockerfile de Production

Créez `Dockerfile.prod` :

```dockerfile
FROM php:8.2-fpm

# Install dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    redis-tools

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy application
COPY . .

# Install dependencies
RUN composer install --optimize-autoloader --no-dev

# Set permissions
RUN chown -R www-data:www-data /var/www
RUN chmod -R 755 /var/www/storage

EXPOSE 9000

CMD ["php-fpm"]
```

## 🔧 Configuration de Production

### 1. Variables d'Environnement

Créez `.env.production` :

```env
APP_ENV=production
APP_DEBUG=false
APP_KEY=your-32-character-secret-key

# Database
DB_CONNECTION=pgsql
DB_HOST=db
DB_PORT=5432
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_secure_password

# Cache
CACHE_DRIVER=redis
REDIS_HOST=redis
REDIS_PASSWORD=your_redis_password
REDIS_PORT=6379

# Search
SCOUT_DRIVER=meilisearch
MEILISEARCH_HOST=http://meilisearch:7700
MEILISEARCH_KEY=your_meilisearch_master_key

# Queue
QUEUE_CONNECTION=redis

# Session
SESSION_DRIVER=redis
SESSION_LIFETIME=120
```

### 2. Configuration Meilisearch

Créez `meilisearch.yml` :

```yaml
# Configuration Meilisearch pour production
db_path: "/meili_data"
env: "production"
master_key: "your_secure_master_key_32_chars_min"
http_addr: "0.0.0.0:7700"
log_level: "INFO"
max_indexing_memory: "2Gb"
max_indexing_threads: 4

# Sécurité
cors:
  allow_all_origins: false
  allowed_origins: ["https://your-domain.com"]
  allowed_methods: ["GET", "POST"]
  allowed_headers: ["*"]
```

## 🔒 Sécurité

### 1. Meilisearch Security

```bash
# Générer une clé maître sécurisée
openssl rand -base64 32

# Configurer les API keys
curl -X POST 'http://meilisearch:7700/keys' \
  -H 'Authorization: Bearer YOUR_MASTER_KEY' \
  -H 'Content-Type: application/json' \
  --data-binary '{
    "description": "Search API key",
    "actions": ["search"],
    "indexes": ["*"],
    "expiresAt": null
  }'
```

### 2. Firewall Rules

```bash
# Autoriser seulement les ports nécessaires
ufw allow 22    # SSH
ufw allow 80    # HTTP
ufw allow 443   # HTTPS
ufw deny 7700   # Meilisearch (accès interne seulement)
ufw deny 6379   # Redis (accès interne seulement)
ufw deny 5432   # PostgreSQL (accès interne seulement)
```

### 3. SSL/TLS Configuration

```bash
# Générer certificats avec Let's Encrypt
certbot --nginx -d your-domain.com

# Ou utiliser des certificats personnalisés
openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
  -keyout /etc/nginx/ssl/key.pem \
  -out /etc/nginx/ssl/cert.pem
```

## 📊 Monitoring et Métriques

### 1. Health Checks

Créez `docker-compose.monitoring.yml` :

```yaml
version: '3.8'

services:
  # Prometheus pour les métriques
  prometheus:
    image: prom/prometheus
    ports:
      - "9090:9090"
    volumes:
      - ./prometheus.yml:/etc/prometheus/prometheus.yml
    networks:
      - app-network

  # Grafana pour la visualisation
  grafana:
    image: grafana/grafana
    ports:
      - "3000:3000"
    environment:
      - GF_SECURITY_ADMIN_PASSWORD=admin
    volumes:
      - grafana_data:/var/lib/grafana
    networks:
      - app-network

volumes:
  grafana_data:
```

### 2. Configuration Prometheus

Créez `prometheus.yml` :

```yaml
global:
  scrape_interval: 15s

scrape_configs:
  - job_name: 'meilisearch'
    static_configs:
      - targets: ['meilisearch:7700']
    metrics_path: '/metrics'

  - job_name: 'laravel'
    static_configs:
      - targets: ['app:9000']
    metrics_path: '/api/search/metrics'
```

## 🚀 Déploiement

### 1. Script de Déploiement

Créez `deploy.sh` :

```bash
#!/bin/bash

echo "🚀 Déploiement de la recherche globale..."

# Variables
COMPOSE_FILE="docker-compose.prod.yml"
ENV_FILE=".env.production"

# Vérifications préalables
echo "📋 Vérifications préalables..."
if [ ! -f "$ENV_FILE" ]; then
    echo "❌ Fichier $ENV_FILE manquant"
    exit 1
fi

# Arrêter les services existants
echo "🛑 Arrêt des services existants..."
docker-compose -f $COMPOSE_FILE down

# Construire les images
echo "🔨 Construction des images..."
docker-compose -f $COMPOSE_FILE build --no-cache

# Démarrer les services
echo "▶️  Démarrage des services..."
docker-compose -f $COMPOSE_FILE up -d

# Attendre que les services soient prêts
echo "⏳ Attente de la disponibilité des services..."
sleep 30

# Vérifier la santé des services
echo "🏥 Vérification de la santé des services..."
curl -f http://localhost:7700/health || exit 1
curl -f http://localhost/api/search/stats || exit 1

# Indexer les données
echo "📊 Indexation des données..."
docker-compose -f $COMPOSE_FILE exec app php artisan search:index --fresh

# Vérifier l'indexation
echo "✅ Vérification de l'indexation..."
curl -f "http://localhost/api/search?q=test" || exit 1

echo "🎉 Déploiement terminé avec succès !"
```

### 2. Commandes de Maintenance

```bash
# Sauvegarder les données Meilisearch
docker-compose exec meilisearch curl -X POST 'http://localhost:7700/dumps'

# Restaurer les données
docker-compose exec meilisearch curl -X POST 'http://localhost:7700/dumps/import' \
  -H 'Content-Type: application/json' \
  --data-binary @dump.json

# Réindexer les données
docker-compose exec app php artisan search:index --fresh

# Nettoyer les métriques anciennes
docker-compose exec app php artisan schedule:run
```

## 📈 Optimisations de Performance

### 1. Configuration Meilisearch

```yaml
# Optimisations pour gros volumes
max_indexing_memory: "4Gb"
max_indexing_threads: 8
http_payload_size_limit: "100MB"

# Cache des résultats
search_cutoff_ms: 150
```

### 2. Configuration Redis

```conf
# redis.conf optimisé
maxmemory 2gb
maxmemory-policy allkeys-lru
save 900 1
save 300 10
save 60 10000
```

### 3. Configuration Laravel

```php
// config/cache.php
'stores' => [
    'redis' => [
        'driver' => 'redis',
        'connection' => 'cache',
        'lock_connection' => 'default',
    ],
],

// config/queue.php
'connections' => [
    'redis' => [
        'driver' => 'redis',
        'connection' => 'default',
        'queue' => env('REDIS_QUEUE', 'default'),
        'retry_after' => 90,
        'block_for' => null,
    ],
],
```

## 🔧 Maintenance

### 1. Tâches Cron

```bash
# Crontab pour maintenance automatique
0 2 * * * docker-compose exec app php artisan search:index
0 3 * * * docker-compose exec app php artisan cache:clear
0 4 * * 0 docker-compose exec app php artisan search:metrics:clean
```

### 2. Monitoring des Logs

```bash
# Surveiller les logs en temps réel
docker-compose logs -f meilisearch
docker-compose logs -f app
docker-compose logs -f redis

# Analyser les métriques
curl http://localhost/api/search/metrics/realtime
```

## ✅ Checklist de Déploiement

- [ ] ✅ Variables d'environnement configurées
- [ ] ✅ Certificats SSL installés
- [ ] ✅ Firewall configuré
- [ ] ✅ Services Docker démarrés
- [ ] ✅ Base de données migrée
- [ ] ✅ Données indexées dans Meilisearch
- [ ] ✅ Tests de santé passés
- [ ] ✅ Monitoring configuré
- [ ] ✅ Sauvegardes programmées
- [ ] ✅ Documentation mise à jour

## 🆘 Dépannage

### Problèmes Courants

**Meilisearch inaccessible**
```bash
docker-compose logs meilisearch
docker-compose restart meilisearch
```

**Erreurs d'indexation**
```bash
docker-compose exec app php artisan search:flush --confirm
docker-compose exec app php artisan search:index --fresh
```

**Performance dégradée**
```bash
# Vérifier les métriques
curl http://localhost/api/search/metrics/realtime

# Nettoyer le cache
docker-compose exec app php artisan cache:clear
```

---

**🎉 Déploiement réussi !** Votre recherche globale est maintenant en production avec Meilisearch, prête à gérer des milliers de requêtes par seconde.
