# Laravel Forge Cron Jobs Configuration

## Tâches cron recommandées pour l'automatisation Meilisearch

### 1. Indexation quotidienne (recommandée)
```bash
# Réindexation complète tous les jours à 2h du matin
0 2 * * * cd /home/forge/hi3dback && php artisan forge:index --check-health
```

### 2. Indexation hebdomadaire avec nettoyage
```bash
# Réindexation complète avec nettoyage tous les dimanches à 3h du matin
0 3 * * 0 cd /home/forge/hi3dback && php artisan forge:index --check-health --force
```

### 3. Vérification de santé Meilisearch
```bash
# Vérification de la santé de Meilisearch toutes les heures
0 * * * * cd /home/forge/hi3dback && php artisan forge:index --check-health --notify=YOUR_WEBHOOK_URL
```

### 4. Indexation après déploiement (via webhook)
```bash
# Cette tâche sera déclenchée automatiquement par le script de déploiement
# Pas besoin de cron job pour celle-ci
```

## Configuration dans Laravel Forge

### Étape 1: Ajouter les tâches cron
1. Connectez-vous à votre tableau de bord Laravel Forge
2. Allez dans votre serveur > Sites > Votre site
3. Cliquez sur "Scheduler" dans le menu latéral
4. Ajoutez les tâches cron ci-dessus

### Étape 2: Variables d'environnement
Assurez-vous que ces variables sont définies dans votre fichier .env de production :

```env
SCOUT_DRIVER=meilisearch
MEILISEARCH_HOST=https://your-meilisearch-instance.com
MEILISEARCH_KEY=your-api-key

# Optionnel: URL de webhook pour notifications
FORGE_WEBHOOK_URL=https://hooks.slack.com/your-webhook-url
```

### Étape 3: Configuration du script de déploiement
Dans Laravel Forge, remplacez votre script de déploiement par le contenu du fichier `forge-deploy.sh`

### Étape 4: Configuration des queues (optionnel)
Si vous voulez utiliser les jobs en arrière-plan :

```bash
# Démarrer le worker de queue
php artisan queue:work --daemon --tries=3 --timeout=300
```

## Commandes disponibles

### Indexation manuelle
```bash
# Indexation complète avec vérification de santé
php artisan forge:index --check-health

# Indexation forcée (même si Meilisearch n'est pas disponible)
php artisan forge:index --force

# Indexation avec notification
php artisan forge:index --notify=https://your-webhook-url.com
```

### Via les jobs (asynchrone)
```bash
# Déclencher l'indexation en arrière-plan
php artisan tinker
>>> dispatch(new \App\Jobs\IndexSearchableModelsJob());

# Indexation d'un modèle spécifique
>>> dispatch(new \App\Jobs\IndexSearchableModelsJob('professional_profiles'));
```

### Via Composer
```bash
# Indexation rapide
composer run meilisearch:index

# Réindexation complète
composer run meilisearch:reindex

# Script de déploiement complet
composer run deploy:production
```

## Monitoring et logs

### Vérifier les logs
```bash
# Logs Laravel
tail -f storage/logs/laravel.log

# Logs spécifiques à l'indexation
grep "Meilisearch" storage/logs/laravel.log
```

### Vérifier l'état des index
```bash
# Via l'API Meilisearch
curl -H "Authorization: Bearer YOUR_API_KEY" \
     "https://your-meilisearch-instance.com/indexes"

# Via l'interface Hi3D
curl "https://your-domain.com/api/search/stats"
```

## Notifications

### Configuration Slack
1. Créez un webhook Slack
2. Ajoutez l'URL dans votre .env : `FORGE_WEBHOOK_URL=https://hooks.slack.com/...`
3. Les notifications seront envoyées automatiquement

### Configuration Discord
Remplacez l'URL Slack par une URL de webhook Discord dans les scripts.

## Dépannage

### Problèmes courants
1. **Timeout lors de l'indexation** : Augmentez la valeur `timeout` dans le job
2. **Mémoire insuffisante** : Réduisez la taille des chunks (actuellement 100)
3. **Meilisearch indisponible** : Vérifiez la configuration réseau et les clés API

### Commandes de diagnostic
```bash
# Test de connexion Meilisearch
php artisan tinker
>>> config('scout.meilisearch.host')
>>> config('scout.meilisearch.key')

# Vérifier les modèles indexables
php artisan scout:status
```
