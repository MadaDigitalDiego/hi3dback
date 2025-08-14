# 🔍 Documentation - Recherche Globale avec Meilisearch

## 🎯 Vue d'ensemble

Cette documentation décrit l'implémentation de la recherche globale utilisant **Meilisearch** et **Laravel Scout** pour rechercher simultanément dans trois modèles : **ProfessionalProfile**, **ServiceOffer**, et **Achievement**.

## 🏗️ Architecture

### Technologies Utilisées
- **Laravel Scout** - Interface de recherche pour Laravel
- **Meilisearch** - Moteur de recherche rapide et pertinent
- **GlobalSearchService** - Service personnalisé pour la recherche multi-modèles

### Modèles Indexés

#### 1. ProfessionalProfile
- **Index** : `professional_profiles_index`
- **Champs searchables** : nom, titre, profession, bio, compétences, ville
- **Condition d'indexation** : `completion_percentage >= 50`

#### 2. ServiceOffer
- **Index** : `service_offers_index`
- **Champs searchables** : titre, description, catégories, prix
- **Condition d'indexation** : `status = 'active'` et `is_private = false`

#### 3. Achievement
- **Index** : `achievements_index`
- **Champs searchables** : titre, organisation, description
- **Condition d'indexation** : titre et organisation non vides

## 🚀 APIs Disponibles

### 1. Recherche Globale
```http
GET /api/search?q={query}&per_page={limit}&page={page}&types[]={types}&filters[key]=value
```

**Paramètres :**
- `q` (requis) : Terme de recherche (min: 2 caractères)
- `per_page` (optionnel) : Nombre de résultats par page (défaut: 15, max: 100)
- `page` (optionnel) : Numéro de page (défaut: 1)
- `types[]` (optionnel) : Types de modèles à rechercher
  - `professional_profiles`
  - `service_offers`
  - `achievements`
- `filters` (optionnel) : Filtres spécifiques par type

**Exemple :**
```bash
curl "http://localhost:8000/api/search?q=Laravel&per_page=10&types[]=professional_profiles&types[]=service_offers&filters[city]=Paris"
```

### 2. Recherche de Professionnels
```http
GET /api/search/professionals?q={query}&filters[key]=value
```

**Filtres disponibles :**
- `city` : Ville du professionnel
- `availability_status` : Statut de disponibilité (`available`, `unavailable`, `busy`)
- `min_experience` : Années d'expérience minimum
- `max_hourly_rate` : Tarif horaire maximum

### 3. Recherche de Services
```http
GET /api/search/services?q={query}&filters[key]=value
```

**Filtres disponibles :**
- `max_price` : Prix maximum
- `categories[]` : Catégories de services

### 4. Recherche de Réalisations
```http
GET /api/search/achievements?q={query}&filters[key]=value
```

**Filtres disponibles :**
- `organization` : Organisation émettrice
- `date_from` : Date minimum d'obtention

### 5. Suggestions de Recherche
```http
GET /api/search/suggestions?q={query}&limit={limit}
```

### 6. Statistiques de Recherche
```http
GET /api/search/stats
```

## 📊 Format des Réponses

### Recherche Globale
```json
{
  "success": true,
  "data": {
    "query": "Laravel",
    "total_count": 25,
    "results_by_type": {
      "professional_profiles": [...],
      "service_offers": [...],
      "achievements": [...]
    },
    "combined_results": {
      "data": [...],
      "current_page": 1,
      "per_page": 15,
      "total": 25
    },
    "pagination": {
      "current_page": 1,
      "per_page": 15,
      "total": 25,
      "last_page": 2
    }
  }
}
```

### Professionnel
```json
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
```

### Service
```json
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
```

### Réalisation
```json
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
```

## 🔧 Commandes Artisan

### Indexer les Modèles
```bash
# Indexer tous les modèles
php artisan search:index

# Indexer un modèle spécifique
php artisan search:index --model=professional_profiles

# Réindexer complètement (vider puis indexer)
php artisan search:index --fresh

# Mode verbeux
php artisan search:index --verbose
```

### Vider les Index
```bash
# Vider tous les index
php artisan search:flush

# Vider un index spécifique
php artisan search:flush --model=service_offers

# Confirmation automatique
php artisan search:flush --confirm
```

## ⚙️ Configuration

### Configuration Scout
```php
// config/scout.php
'driver' => env('SCOUT_DRIVER', 'meilisearch'),

'meilisearch' => [
    'host' => env('MEILISEARCH_HOST', 'http://localhost:7700'),
    'key' => env('MEILISEARCH_KEY', null),
],
```

### Variables d'Environnement
```env
SCOUT_DRIVER=meilisearch
MEILISEARCH_HOST=http://localhost:7700
MEILISEARCH_KEY=your_master_key_here
```

## 🎯 Score de Pertinence

Le système calcule un score de pertinence pour chaque résultat :

### Professionnels
- Note (rating) × 0.2
- Pourcentage de complétion × 0.01
- Années d'expérience × 0.1

### Services
- Note (rating) × 0.3
- Nombre de likes × 0.01
- Nombre de vues × 0.001

### Réalisations
- Score de base : 0.5

## 🧪 Tests

### Exécuter les Tests
```bash
# Tests de recherche globale
php artisan test --filter=GlobalSearchTest

# Tous les tests
php artisan test
```

### Tests Disponibles
- Test de l'endpoint de recherche globale
- Test des endpoints spécifiques par modèle
- Test des suggestions
- Test des statistiques
- Test de validation des paramètres
- Test des filtres

## 🚀 Utilisation Frontend

### Exemple JavaScript
```javascript
// Recherche globale
const searchResults = await fetch('/api/search?q=Laravel&per_page=10')
  .then(response => response.json());

// Suggestions en temps réel
const suggestions = await fetch('/api/search/suggestions?q=Lar&limit=5')
  .then(response => response.json());

// Recherche avec filtres
const filteredResults = await fetch('/api/search/professionals?q=Developer&filters[city]=Paris')
  .then(response => response.json());
```

### Exemple Vue.js
```vue
<template>
  <div>
    <input v-model="query" @input="search" placeholder="Rechercher...">
    <div v-for="result in results" :key="result.id">
      <h3>{{ result.title }}</h3>
      <p>{{ result.description }}</p>
      <span class="badge">{{ result.type }}</span>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      query: '',
      results: []
    }
  },
  methods: {
    async search() {
      if (this.query.length >= 2) {
        const response = await fetch(`/api/search?q=${this.query}`);
        const data = await response.json();
        this.results = data.data.combined_results.data;
      }
    }
  }
}
</script>
```

## 🔒 Sécurité et Performance

### Sécurité
- Validation stricte des paramètres d'entrée
- Limitation de la taille des requêtes
- Échappement automatique des caractères spéciaux

### Performance
- Index optimisés pour la recherche rapide
- Pagination pour éviter les surcharges
- Cache des résultats recommandé
- Limitation du nombre de résultats par requête

## 📝 Bonnes Pratiques

1. **Indexation** : Réindexer après des modifications importantes
2. **Monitoring** : Surveiller les performances des requêtes
3. **Cache** : Implémenter un cache pour les recherches fréquentes
4. **Filtres** : Utiliser les filtres pour affiner les résultats
5. **Pagination** : Toujours paginer les résultats

## 🛠️ Installation et Configuration

### 1. Installer Meilisearch
```bash
# Via Docker
docker run -it --rm -p 7700:7700 getmeili/meilisearch:latest

# Via binaire (Linux/macOS)
curl -L https://install.meilisearch.com | sh
./meilisearch
```

### 2. Configurer Laravel
```bash
# Variables d'environnement
echo "SCOUT_DRIVER=meilisearch" >> .env
echo "MEILISEARCH_HOST=http://localhost:7700" >> .env

# Indexer les données
php artisan search:index --fresh
```

### 3. Tester l'Installation
```bash
# Vérifier que Meilisearch fonctionne
curl http://localhost:7700/health

# Tester une recherche
curl "http://localhost:8000/api/search?q=test"
```

---

**Prochaine étape :** Consultez le [Guide de Test Postman](./postman-global-search-testing.md) pour tester les APIs de recherche.
