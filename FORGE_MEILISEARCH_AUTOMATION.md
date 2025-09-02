# 🚀 Automatisation Meilisearch avec Laravel Forge

Ce guide vous explique comment automatiser complètement l'indexation Meilisearch avec Laravel Forge.

## 📋 Vue d'ensemble

L'automatisation comprend :
- ✅ Scripts Composer pour déploiement
- ✅ Script de déploiement Forge optimisé
- ✅ Commande Artisan spécialisée
- ✅ Jobs en arrière-plan
- ✅ Tâches cron automatiques
- ✅ Middleware d'indexation temps réel
- ✅ Notifications et monitoring

## 🔧 Configuration Laravel Forge

### 1. Variables d'environnement (.env)

Ajoutez ces variables dans votre fichier .env de production :

```env
# Meilisearch (obligatoire)
SCOUT_DRIVER=meilisearch
MEILISEARCH_HOST=https://your-meilisearch-instance.com
MEILISEARCH_KEY=your-api-key

# Queue pour indexation asynchrone (recommandé)
QUEUE_CONNECTION=database
# ou
QUEUE_CONNECTION=redis

# Notifications (optionnel)
FORGE_WEBHOOK_URL=https://hooks.slack.com/your-webhook-url
```

### 2. Script de déploiement Forge

Dans votre tableau de bord Laravel Forge :
1. Allez dans **Sites** > **Votre site** > **Deployment Script**
2. Remplacez le contenu par celui du fichier `forge-deploy.sh`
3. Sauvegardez

### 3. Tâches cron

Dans **Scheduler**, ajoutez ces tâches :

```bash
# Indexation quotidienne à 2h du matin
0 2 * * * cd /home/forge/hi3dback && php artisan forge:index --check-health

# Vérification de santé toutes les 6 heures
0 */6 * * * cd /home/forge/hi3dback && php artisan forge:index --check-health --notify=$FORGE_WEBHOOK_URL
```

### 4. Configuration des queues

Si vous utilisez les jobs asynchrones :

```bash
# Dans Forge > Daemons, ajoutez :
php artisan queue:work --queue=indexation,default --tries=3 --timeout=300 --daemon
```

## 🎯 Méthodes d'indexation disponibles

### 1. Automatique lors du déploiement
```bash
# Via le script de déploiement Forge
# Se déclenche automatiquement à chaque push
```

### 2. Manuelle via Artisan
```bash
# Indexation complète avec vérifications
php artisan forge:index --check-health

# Indexation forcée
php artisan forge:index --force

# Avec notifications
php artisan forge:index --notify=https://your-webhook.com
```

### 3. Via Composer
```bash
# Indexation rapide
composer run meilisearch:index

# Réindexation complète
composer run meilisearch:reindex

# Déploiement complet
composer run deploy:production
```

### 4. Jobs asynchrones
```php
// Dans votre code PHP
use App\Jobs\IndexSearchableModelsJob;

// Indexation complète
IndexSearchableModelsJob::dispatch();

// Indexation d'un modèle spécifique
IndexSearchableModelsJob::dispatch('professional_profiles');
```

### 5. Indexation temps réel (optionnel)

Pour activer l'indexation automatique lors des modifications :

```php
// Dans app/Http/Kernel.php, ajoutez le middleware :
protected $middlewareGroups = [
    'api' => [
        // ... autres middlewares
        \App\Http\Middleware\AutoIndexMiddleware::class,
    ],
];
```

## 📊 Monitoring et vérification

### Vérifier l'état des index
```bash
# Via l'API Hi3D
curl "https://your-domain.com/api/search/stats"

# Via Meilisearch directement
curl -H "Authorization: Bearer YOUR_API_KEY" \
     "https://your-meilisearch-instance.com/indexes"
```

### Logs d'indexation
```bash
# Voir les logs d'indexation
tail -f storage/logs/laravel.log | grep "Meilisearch\|indexation"

# Logs des jobs
php artisan queue:failed
```

### Test de recherche
```bash
# Test simple
curl "https://your-domain.com/api/search?q=test"

# Test avec modèles spécifiques
curl -X POST "https://your-domain.com/api/search" \
  -H "Content-Type: application/json" \
  -d '{"query": "Laravel", "models": ["professional_profiles"]}'
```

## 🔔 Notifications

### Configuration Slack
1. Créez un webhook Slack dans votre workspace
2. Ajoutez l'URL dans votre .env : `FORGE_WEBHOOK_URL=https://hooks.slack.com/...`
3. Les notifications seront envoyées automatiquement

### Configuration Discord
Remplacez l'URL Slack par une URL de webhook Discord.

## 🚨 Dépannage

### Problèmes courants

**1. Timeout lors de l'indexation**
```bash
# Augmentez le timeout dans le job
# Ou indexez par petits chunks
php artisan forge:index --check-health
```

**2. Meilisearch indisponible**
```bash
# Vérifiez la connexion
curl https://your-meilisearch-instance.com/health

# Vérifiez les variables d'environnement
php artisan tinker
>>> config('scout.meilisearch.host')
>>> config('scout.meilisearch.key')
```

**3. Jobs qui échouent**
```bash
# Voir les jobs échoués
php artisan queue:failed

# Relancer un job échoué
php artisan queue:retry all
```

### Commandes de diagnostic
```bash
# Vérifier la configuration Scout
php artisan scout:status

# Tester l'indexation manuelle
php artisan scout:import "App\Models\ProfessionalProfile"

# Vider et réindexer
php artisan scout:flush "App\Models\ProfessionalProfile"
php artisan scout:import "App\Models\ProfessionalProfile"
```

## 📈 Optimisations recommandées

### 1. Configuration de production
```env
# Optimisations pour la production
SCOUT_CHUNK_SIZE=500
MEILISEARCH_TIMEOUT=30
```

### 2. Indexation différée
- Utilisez les jobs pour éviter de bloquer les requêtes utilisateur
- Configurez des queues dédiées pour l'indexation

### 3. Monitoring
- Configurez des alertes pour les échecs d'indexation
- Surveillez la santé de Meilisearch
- Loggez les performances d'indexation

## ✅ Checklist de déploiement

- [ ] Variables d'environnement configurées
- [ ] Script de déploiement Forge mis à jour
- [ ] Tâches cron ajoutées
- [ ] Queues configurées (si utilisées)
- [ ] Webhooks de notification configurés
- [ ] Tests d'indexation effectués
- [ ] Monitoring en place

## 🎉 Résultat

Avec cette configuration, votre indexation Meilisearch sera :
- ✅ **Automatique** lors des déploiements
- ✅ **Planifiée** via les tâches cron
- ✅ **Temps réel** pour les modifications importantes
- ✅ **Surveillée** avec notifications
- ✅ **Robuste** avec gestion d'erreurs
- ✅ **Scalable** avec jobs asynchrones
