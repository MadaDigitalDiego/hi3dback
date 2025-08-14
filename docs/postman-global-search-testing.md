# 🧪 Guide de Test Postman - Recherche Globale

## 🚀 Configuration Initiale

### 1. Variables d'Environnement Postman

Créez un environnement Postman avec les variables suivantes :

```json
{
  "base_url": "http://localhost:8000/api",
  "search_query": "Laravel",
  "professional_id": "1",
  "service_id": "1",
  "achievement_id": "1"
}
```

### 2. Prérequis

1. **Meilisearch démarré** : `docker run -p 7700:7700 getmeili/meilisearch:latest`
2. **Serveur Laravel démarré** : `php artisan serve`
3. **Données indexées** : `php artisan search:index --fresh`

## 🔍 Tests de Recherche Globale

### 1. Recherche Globale Simple

```http
GET {{base_url}}/search?q={{search_query}}
Content-Type: application/json
```

**Réponse Attendue :**
```json
{
  "success": true,
  "data": {
    "query": "Laravel",
    "total_count": 15,
    "results_by_type": {
      "professional_profiles": [...],
      "service_offers": [...],
      "achievements": [...]
    },
    "combined_results": {
      "data": [...],
      "current_page": 1,
      "per_page": 15,
      "total": 15
    },
    "pagination": {
      "current_page": 1,
      "per_page": 15,
      "total": 15,
      "last_page": 1
    }
  }
}
```

### 2. Recherche avec Pagination

```http
GET {{base_url}}/search?q={{search_query}}&per_page=5&page=2
Content-Type: application/json
```

### 3. Recherche avec Types Spécifiques

```http
GET {{base_url}}/search?q={{search_query}}&types[]=professional_profiles&types[]=service_offers
Content-Type: application/json
```

### 4. Recherche avec Filtres

```http
GET {{base_url}}/search?q=Developer&filters[city]=Paris&filters[availability_status]=available&filters[max_hourly_rate]=100
Content-Type: application/json
```

## 👨‍💼 Tests de Recherche de Professionnels

### 1. Recherche Simple de Professionnels

```http
GET {{base_url}}/search/professionals?q=Developer
Content-Type: application/json
```

**Réponse Attendue :**
```json
{
  "success": true,
  "data": {
    "query": "Developer",
    "count": 5,
    "results": [
      {
        "id": 1,
        "type": "professional_profile",
        "title": "Full Stack Developer",
        "name": "John Doe",
        "description": "Experienced developer...",
        "location": "Paris, France",
        "skills": ["PHP", "Laravel", "React"],
        "hourly_rate": 50.0,
        "rating": 4.8,
        "availability_status": "available",
        "url": "/professionals/1",
        "relevance_score": 2.5
      }
    ]
  }
}
```

### 2. Recherche avec Filtres Professionnels

```http
GET {{base_url}}/search/professionals?q=Developer&filters[city]=Paris&filters[min_experience]=3&filters[max_hourly_rate]=80
Content-Type: application/json
```

### 3. Recherche par Compétences

```http
GET {{base_url}}/search/professionals?q=Laravel PHP React
Content-Type: application/json
```

## 🛠️ Tests de Recherche de Services

### 1. Recherche Simple de Services

```http
GET {{base_url}}/search/services?q=Laravel
Content-Type: application/json
```

**Réponse Attendue :**
```json
{
  "success": true,
  "data": {
    "query": "Laravel",
    "count": 3,
    "results": [
      {
        "id": 1,
        "type": "service_offer",
        "title": "Laravel Web Application",
        "description": "Custom Laravel development...",
        "price": 500.0,
        "execution_time": "7 days",
        "categories": ["Web Development", "Laravel"],
        "rating": 4.9,
        "user_name": "John Doe",
        "url": "/services/1",
        "relevance_score": 2.8
      }
    ]
  }
}
```

### 2. Recherche avec Filtres de Prix

```http
GET {{base_url}}/search/services?q=Development&filters[max_price]=1000
Content-Type: application/json
```

### 3. Recherche par Catégories

```http
GET {{base_url}}/search/services?q=Web&filters[categories][]=Web Development&filters[categories][]=Laravel
Content-Type: application/json
```

## 🏆 Tests de Recherche de Réalisations

### 1. Recherche Simple de Réalisations

```http
GET {{base_url}}/search/achievements?q=Laravel
Content-Type: application/json
```

**Réponse Attendue :**
```json
{
  "success": true,
  "data": {
    "query": "Laravel",
    "count": 2,
    "results": [
      {
        "id": 1,
        "type": "achievement",
        "title": "Laravel Certified Developer",
        "organization": "Laravel",
        "description": "Advanced Laravel certification",
        "date_obtained": "2023-06-15",
        "professional_name": "John Doe",
        "url": "/achievements/1",
        "relevance_score": 1.5
      }
    ]
  }
}
```

### 2. Recherche avec Filtres d'Organisation

```http
GET {{base_url}}/search/achievements?q=Certified&filters[organization]=Laravel
Content-Type: application/json
```

### 3. Recherche avec Filtre de Date

```http
GET {{base_url}}/search/achievements?q=Developer&filters[date_from]=2023-01-01
Content-Type: application/json
```

## 💡 Tests de Suggestions

### 1. Suggestions de Recherche

```http
GET {{base_url}}/search/suggestions?q=Lar&limit=5
Content-Type: application/json
```

**Réponse Attendue :**
```json
{
  "success": true,
  "data": {
    "query": "Lar",
    "suggestions": [
      "Laravel Web Application",
      "Laravel Certified Developer",
      "Laravel Development",
      "Laravel API",
      "Laravel Expert"
    ]
  }
}
```

### 2. Suggestions avec Limite Personnalisée

```http
GET {{base_url}}/search/suggestions?q=Dev&limit=3
Content-Type: application/json
```

## 📊 Tests de Statistiques

### 1. Statistiques Globales

```http
GET {{base_url}}/search/stats
Content-Type: application/json
```

**Réponse Attendue :**
```json
{
  "success": true,
  "data": {
    "total_professionals": 150,
    "total_services": 89,
    "total_achievements": 45,
    "searchable_professionals": 120,
    "active_services": 67
  }
}
```

## 🔒 Tests de Validation

### 1. Test sans Paramètre de Recherche

```http
GET {{base_url}}/search
Content-Type: application/json
```

**Réponse Attendue :**
```json
{
  "message": "The q field is required.",
  "errors": {
    "q": ["The q field is required."]
  }
}
```
**Status Code :** `422`

### 2. Test avec Requête Trop Courte

```http
GET {{base_url}}/search?q=a
Content-Type: application/json
```

**Réponse Attendue :**
```json
{
  "message": "The q field must be at least 2 characters.",
  "errors": {
    "q": ["The q field must be at least 2 characters."]
  }
}
```
**Status Code :** `422`

### 3. Test avec Type Invalide

```http
GET {{base_url}}/search?q=test&types[]=invalid_type
Content-Type: application/json
```

**Réponse Attendue :**
```json
{
  "message": "The selected types.0 is invalid.",
  "errors": {
    "types.0": ["The selected types.0 is invalid."]
  }
}
```
**Status Code :** `422`

### 4. Test avec Pagination Invalide

```http
GET {{base_url}}/search?q=test&per_page=150
Content-Type: application/json
```

**Réponse Attendue :**
```json
{
  "message": "The per page field must not be greater than 100.",
  "errors": {
    "per_page": ["The per page field must not be greater than 100."]
  }
}
```
**Status Code :** `422`

## 📋 Collection Postman Complète

### Structure de la Collection

```
📁 Global Search API Tests
├── 📁 Global Search
│   ├── 🔍 Global Search Simple
│   ├── 📄 Global Search with Pagination
│   ├── 🎯 Global Search with Types
│   └── 🔧 Global Search with Filters
├── 📁 Professional Search
│   ├── 👨‍💼 Search Professionals
│   ├── 🔧 Search with Filters
│   └── 🎯 Search by Skills
├── 📁 Service Search
│   ├── 🛠️ Search Services
│   ├── 💰 Search with Price Filter
│   └── 📂 Search by Categories
├── 📁 Achievement Search
│   ├── 🏆 Search Achievements
│   ├── 🏢 Search by Organization
│   └── 📅 Search by Date
├── 📁 Suggestions & Stats
│   ├── 💡 Get Suggestions
│   └── 📊 Get Statistics
└── 📁 Validation Tests
    ├── ❌ Missing Query
    ├── ❌ Short Query
    ├── ❌ Invalid Type
    └── ❌ Invalid Pagination
```

## 🧪 Scénarios de Test Recommandés

### Scénario 1 : Recherche Complète
1. Recherche globale simple
2. Affiner avec des filtres
3. Paginer les résultats
4. Obtenir des suggestions

### Scénario 2 : Recherche Spécialisée
1. Rechercher des professionnels par compétences
2. Filtrer par localisation et tarif
3. Rechercher des services dans une catégorie
4. Vérifier les réalisations d'un professionnel

### Scénario 3 : Test de Performance
1. Recherche avec beaucoup de résultats
2. Pagination sur plusieurs pages
3. Recherche avec filtres multiples
4. Suggestions en temps réel

## 🐛 Débogage

### Logs Laravel
```bash
tail -f storage/logs/laravel.log
```

### Vérifier Meilisearch
```bash
# Santé de Meilisearch
curl http://localhost:7700/health

# Index existants
curl http://localhost:7700/indexes

# Statistiques d'un index
curl http://localhost:7700/indexes/professional_profiles_index/stats
```

### Réindexer si Nécessaire
```bash
php artisan search:flush --confirm
php artisan search:index --fresh --verbose
```

## ✅ Checklist de Validation

- [ ] Recherche globale fonctionne
- [ ] Pagination correcte
- [ ] Filtres appliqués correctement
- [ ] Suggestions pertinentes
- [ ] Statistiques exactes
- [ ] Validation des erreurs
- [ ] Performance acceptable
- [ ] Résultats formatés correctement

---

**Prochaine étape :** Importez la [Collection Postman](./postman-global-search-collection.json) pour commencer les tests rapidement.
