# Intégration Meilisearch avec Temps de Recherche

## Vue d'ensemble

Cette documentation décrit l'intégration de Meilisearch dans les contrôleurs `ExplorerController` pour afficher et retourner les temps de recherche. Les méthodes `getProfessionals` et `getServices` utilisent maintenant Meilisearch pour les recherches textuelles et retournent des informations détaillées sur les performances.

## Fonctionnalités Ajoutées

### 1. Recherche Hybride
- **Meilisearch** : Utilisé quand un paramètre `search` est fourni (minimum 2 caractères)
- **Eloquent** : Utilisé pour les filtres sans recherche textuelle

### 2. Métriques de Performance
Chaque réponse inclut maintenant une section `performance` avec :
- `total_execution_time_ms` : Temps total d'exécution de la requête
- `search_method` : Méthode utilisée (`meilisearch` ou `eloquent`)
- `meilisearch_time_ms` : Temps spécifique de la recherche Meilisearch (si applicable)
- `search_query` : Requête de recherche utilisée (si applicable)

## Endpoints Modifiés

### GET /api/explorer/professionals
**Paramètres de recherche :**
- `search` : Recherche textuelle (utilise Meilisearch si ≥ 2 caractères)
- `skills`, `city`, `country`, `min_rate`, `max_rate`, `availability` : Filtres

**Exemple de réponse avec Meilisearch :**
```json
{
  "success": true,
  "professionals": [...],
  "pagination": {
    "total": 25,
    "per_page": 10,
    "current_page": 1,
    "last_page": 3
  },
  "performance": {
    "total_execution_time_ms": 45.67,
    "search_method": "meilisearch",
    "meilisearch_time_ms": 12.34,
    "search_query": "développeur Laravel"
  }
}
```

### GET /api/explorer/services
**Paramètres de recherche :**
- `search` : Recherche textuelle (utilise Meilisearch si ≥ 2 caractères)
- `category`, `min_price`, `max_price`, `execution_time`, `sort_by` : Filtres

**Exemple de réponse avec Eloquent :**
```json
{
  "success": true,
  "services": [...],
  "pagination": {
    "total": 15,
    "per_page": 10,
    "current_page": 1,
    "last_page": 2
  },
  "performance": {
    "total_execution_time_ms": 23.45,
    "search_method": "eloquent"
  }
}
```

## Nouvel Endpoint

### GET /api/explorer/search-stats
Retourne des statistiques détaillées sur la configuration et les performances de Meilisearch.

**Exemple de réponse :**
```json
{
  "success": true,
  "stats": {
    "configuration": {
      "scout_driver": "meilisearch",
      "meilisearch_host": "http://localhost:7700",
      "meilisearch_configured": true
    },
    "models": {
      "professional_profiles": {
        "total_records": 150,
        "searchable_records": 120,
        "index_name": "professional_profiles_index"
      },
      "service_offers": {
        "total_records": 75,
        "searchable_records": 65,
        "index_name": "service_offers_index"
      }
    },
    "performance": {
      "test_search_time_ms": 8.45,
      "meilisearch_available": true,
      "stats_generation_time_ms": 15.67
    }
  }
}
```

## Tests et Utilisation

### 1. Test de Recherche avec Meilisearch
```bash
# Recherche de professionnels
curl "http://localhost:8000/api/explorer/professionals?search=Laravel&per_page=5"

# Recherche de services
curl "http://localhost:8000/api/explorer/services?search=développement&category=web"
```

### 2. Test de Filtrage sans Recherche (Eloquent)
```bash
# Filtrage de professionnels
curl "http://localhost:8000/api/explorer/professionals?city=Paris&min_rate=50"

# Filtrage de services
curl "http://localhost:8000/api/explorer/services?min_price=100&max_price=500"
```

### 3. Vérification des Statistiques
```bash
curl "http://localhost:8000/api/explorer/search-stats"
```

## Configuration Requise

### 1. Meilisearch en Cours d'Exécution
```bash
# Avec Docker
docker run -it --rm -p 7700:7700 getmeili/meilisearch:latest

# Vérifier que Meilisearch fonctionne
curl http://localhost:7700/health
```

### 2. Variables d'Environnement (.env)
```env
SCOUT_DRIVER=meilisearch
MEILISEARCH_HOST=http://localhost:7700
MEILISEARCH_KEY=
```

### 3. Indexation des Données
```bash
# Indexer tous les modèles
php artisan scout:import "App\Models\ProfessionalProfile"
php artisan scout:import "App\Models\ServiceOffer"

# Ou utiliser la commande personnalisée
php artisan meilisearch:reindex --show-progress
```

## Avantages de l'Implémentation

1. **Performance Transparente** : Les temps de recherche sont automatiquement mesurés et retournés
2. **Recherche Hybride** : Utilise la meilleure méthode selon le contexte
3. **Compatibilité** : Fonctionne même si Meilisearch n'est pas disponible
4. **Monitoring** : Permet de surveiller les performances de recherche
5. **Debugging** : Facilite l'identification des problèmes de performance

## Dépannage

### Meilisearch Non Disponible
Si Meilisearch n'est pas disponible, l'API basculera automatiquement sur Eloquent sans erreur.

### Performances Lentes
Utilisez l'endpoint `/api/explorer/search-stats` pour diagnostiquer les problèmes de performance.

### Données Non Indexées
Vérifiez l'indexation avec :
```bash
php artisan scout:status
```
