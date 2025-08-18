# Configuration CORS - Hi3D Backend

## Problème résolu

L'erreur CORS que vous rencontriez était causée par plusieurs problèmes :

1. **En-tête `x-requested-with` non autorisé** : L'en-tête n'était pas explicitement listé dans `allowed_headers`
2. **Conflit entre middlewares** : Deux middlewares CORS différents étaient configurés
3. **Configuration .htaccess conflictuelle** : Apache gérait CORS en parallèle de Laravel
4. **Domaine manquant** : `https://dev-backend.hi-3d.com` n'était pas dans les origines autorisées

## Solutions appliquées

### 1. Configuration CORS (config/cors.php)

```php
'allowed_origins' => [
    // ... autres domaines
    'https://dev-backend.hi-3d.com', // Ajouté
],

'allowed_origins_patterns' => [
    '/^https?:\/\/.*\.mada-digital\.xyz$/',
    '/^https?:\/\/.*\.hi-3d\.com$/', // Ajouté
    '/^https?:\/\/localhost(:\d+)?$/',
],

'allowed_headers' => [
    'Accept',
    'Authorization',
    'Content-Type',
    'X-Requested-With', // Explicitement ajouté
    'X-CSRF-TOKEN',
    'X-XSRF-TOKEN',
    'Origin',
    'Cache-Control',
    'Pragma',
],
```

### 2. Middleware CORS (app/Http/Kernel.php)

```php
protected $middleware = [
    \App\Http\Middleware\TrustProxies::class,
    \App\Http\Middleware\HandleCorsOptions::class, // Nouveau middleware pour OPTIONS
    \Fruitcake\Cors\HandleCors::class, // Middleware principal CORS
    // ... autres middlewares
];
```

### 3. Configuration Sanctum (config/sanctum.php)

```php
'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', sprintf(
    '%s%s',
    'localhost,localhost:3000,localhost:3001,127.0.0.1,127.0.0.1:3000,127.0.0.1:3001,127.0.0.1:8000,::1,hi3d.mada-digital.xyz,hi-3d.salon.mada-digital.xyz,backhi3d.mada-digital.xyz,dev-backend.hi-3d.com,dev2.mada-digital.xyz',
    Sanctum::currentApplicationUrlWithPort()
))),
```

### 4. Désactivation CORS Apache (public/.htaccess)

Les règles CORS Apache ont été commentées pour éviter les conflits avec Laravel.

## Tests et diagnostic

### Exécuter les tests CORS

```bash
php artisan test tests/Feature/CorsTest.php
```

### Diagnostic de la configuration

```bash
php artisan cors:diagnose
```

## Configuration pour la production

### Variables d'environnement requises

```env
APP_URL=https://dev-backend.hi-3d.com
FRONTEND_URL=https://your-frontend-domain.com
SANCTUM_STATEFUL_DOMAINS=your-frontend-domain.com,dev-backend.hi-3d.com
SESSION_DOMAIN=.hi-3d.com
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=none
```

## Vérification côté client

Assurez-vous que votre client JavaScript :

1. **Inclut les en-têtes requis** :
   ```javascript
   headers: {
     'Content-Type': 'application/json',
     'X-Requested-With': 'XMLHttpRequest',
     'Accept': 'application/json'
   }
   ```

2. **Configure les credentials si nécessaire** :
   ```javascript
   credentials: 'include' // Pour les cookies de session
   ```

3. **Gère correctement les requêtes OPTIONS** :
   Les requêtes preflight OPTIONS sont maintenant gérées automatiquement.

## Dépannage

### Si l'erreur persiste :

1. **Vérifiez les logs Laravel** :
   ```bash
   tail -f storage/logs/laravel.log
   ```

2. **Testez avec curl** :
   ```bash
   curl -X OPTIONS https://dev-backend.hi-3d.com/api/register \
     -H "Origin: https://your-frontend-domain.com" \
     -H "Access-Control-Request-Method: POST" \
     -H "Access-Control-Request-Headers: Content-Type, X-Requested-With" \
     -v
   ```

3. **Vérifiez la configuration du serveur web** :
   - Nginx : Assurez-vous qu'aucune règle CORS n'est définie
   - Apache : Vérifiez que mod_headers ne surcharge pas les en-têtes Laravel

### Erreurs communes

- **"Access-Control-Allow-Origin" header contains multiple values** : Conflit entre Apache et Laravel
- **"x-requested-with" not allowed** : En-tête manquant dans allowed_headers
- **Credentials not allowed** : Problème de configuration supports_credentials

## Sécurité

⚠️ **Important** : En production, ne jamais utiliser `'*'` pour :
- `allowed_origins`
- `allowed_headers` (sauf si absolument nécessaire)

Toujours spécifier explicitement les domaines et en-têtes autorisés.
