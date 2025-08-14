# 🔍 README - Recherche Globale avec Meilisearch

## 🎯 Vue d'ensemble

Implémentation complète d'une **recherche globale** utilisant **Meilisearch** et **Laravel Scout** pour rechercher simultanément dans trois modèles :
- **ProfessionalProfile** (Professionnels)
- **ServiceOffer** (Services)
- **Achievement** (Réalisations)

## ✅ Fonctionnalités Implémentées

### 🔍 **Recherche Multi-Modèles**
- Recherche globale dans tous les modèles
- Recherche spécifique par type de modèle
- Filtres avancés par modèle
- Suggestions de recherche en temps réel
- Statistiques de recherche

### 📊 **APIs REST Complètes**
- **7 endpoints** de recherche
- Validation des paramètres
- Pagination automatique
- Gestion d'erreurs robuste
- Réponses JSON standardisées

### 🛠️ **Outils de Gestion**
- Commandes Artisan pour l'indexation
- Scripts de test automatisés
- Documentation complète
- Collection Postman prête à l'emploi

## 🏗️ Architecture

### **Modèles Configurés**
```php
// ProfessionalProfile
- Index: professional_profiles_index
- 21 champs indexés
- Condition: completion_percentage >= 50

// ServiceOffer  
- Index: service_offers_index
- 15 champs indexés
- Condition: status = 'active' && !is_private

// Achievement
- Index: achievements_index
- 9 champs indexés
- Condition: title && organization non vides
```

### **Service de Recherche**
```php
GlobalSearchService
├── search() - Recherche globale
├── searchProfessionalProfiles() - Recherche professionnels
├── searchServiceOffers() - Recherche services
├── searchAchievements() - Recherche réalisations
└── getSuggestions() - Suggestions
```

### **Contrôleur API**
```php
SearchController
├── globalSearch() - GET /api/search
├── searchProfessionals() - GET /api/search/professionals
├── searchServices() - GET /api/search/services
├── searchAchievements() - GET /api/search/achievements
├── suggestions() - GET /api/search/suggestions
└── stats() - GET /api/search/stats
```

## 🚀 Démarrage Rapide

### 1. **Démarrer Meilisearch**
```bash
# Avec Docker (recommandé)
docker run -it --rm -p 7700:7700 getmeili/meilisearch:latest

# Vérifier que Meilisearch fonctionne
curl http://localhost:7700/health
```

### 2. **Configurer Laravel**
```bash
# Variables d'environnement (.env)
SCOUT_DRIVER=meilisearch
MEILISEARCH_HOST=http://localhost:7700
MEILISEARCH_KEY=

# Démarrer le serveur Laravel
php artisan serve
```

### 3. **Indexer les Données**
```bash
# Indexer tous les modèles
php artisan search:index --fresh --verbose

# Vérifier l'indexation
curl "http://localhost:8000/api/search/stats"
```

### 4. **Tester la Recherche**
```bash
# Test simple
curl "http://localhost:8000/api/search?q=Laravel"

# Test avec filtres
curl "http://localhost:8000/api/search/professionals?q=Developer&filters[city]=Paris"
```

## 📋 APIs Disponibles

### **Recherche Globale**
```http
GET /api/search?q={query}&per_page={limit}&types[]={types}&filters[key]=value
```

### **Recherche Spécifique**
```http
GET /api/search/professionals?q={query}&filters[city]=Paris
GET /api/search/services?q={query}&filters[max_price]=1000
GET /api/search/achievements?q={query}&filters[organization]=Laravel
```

### **Utilitaires**
```http
GET /api/search/suggestions?q={query}&limit=5
GET /api/search/stats
```

## 🔧 Commandes Artisan

### **Indexation**
```bash
# Indexer tous les modèles
php artisan search:index

# Indexer un modèle spécifique
php artisan search:index --model=professional_profiles

# Réindexer complètement
php artisan search:index --fresh

# Mode verbeux
php artisan search:index --verbose
```

### **Gestion des Index**
```bash
# Vider tous les index
php artisan search:flush

# Vider un index spécifique
php artisan search:flush --model=service_offers

# Confirmation automatique
php artisan search:flush --confirm
```

## 🧪 Tests

### **Tests Automatisés**
```bash
# Tests de recherche globale
php artisan test --filter=GlobalSearchTest

# Vérifier la configuration
php test_search_config.php

# Test complet (nécessite Meilisearch)
php test_global_search.php
```

### **Tests Postman**
1. Importer `docs/postman-global-search-collection.json`
2. Configurer l'environnement avec `base_url=http://localhost:8000/api`
3. Exécuter les tests de la collection

## 📊 Exemples de Réponses

### **Recherche Globale**
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
    }
  }
}
```

### **Professionnel**
```json
{
  "id": 1,
  "type": "professional_profile",
  "title": "Full Stack Developer",
  "name": "John Doe",
  "location": "Paris, France",
  "skills": ["PHP", "Laravel", "React"],
  "hourly_rate": 50.0,
  "rating": 4.8,
  "url": "/professionals/1"
}
```

## 📚 Documentation

### **Documents Disponibles**
- **[Documentation Technique](./global-search-documentation.md)** - Guide complet
- **[Guide de Test Postman](./postman-global-search-testing.md)** - Tests détaillés
- **[Collection Postman](./postman-global-search-collection.json)** - Requêtes prêtes

### **Scripts Utilitaires**
- **`test_search_config.php`** - Vérification de la configuration
- **`test_global_search.php`** - Test complet avec données

## 🔒 Sécurité et Performance

### **Sécurité**
- Validation stricte des paramètres
- Échappement automatique des caractères spéciaux
- Limitation de la taille des requêtes
- Gestion d'erreurs sécurisée

### **Performance**
- Index optimisés pour la recherche rapide
- Pagination automatique
- Score de pertinence calculé
- Cache recommandé pour les recherches fréquentes

## 🛠️ Maintenance

### **Monitoring**
```bash
# Vérifier la santé de Meilisearch
curl http://localhost:7700/health

# Statistiques des index
curl http://localhost:7700/indexes

# Logs Laravel
tail -f storage/logs/laravel.log
```

### **Réindexation Périodique**
```bash
# Script de réindexation (à programmer en cron)
php artisan search:flush --confirm
php artisan search:index --fresh --verbose
```

## 🎯 Utilisation Frontend

### **JavaScript/Vue.js**
```javascript
// Recherche globale
const results = await fetch('/api/search?q=Laravel').then(r => r.json());

// Suggestions en temps réel
const suggestions = await fetch('/api/search/suggestions?q=Lar').then(r => r.json());

// Recherche avec filtres
const filtered = await fetch('/api/search/professionals?q=Dev&filters[city]=Paris').then(r => r.json());
```

## 🐛 Dépannage

### **Problèmes Courants**

**Meilisearch non accessible**
```bash
# Vérifier que Meilisearch fonctionne
curl http://localhost:7700/health
# Redémarrer si nécessaire
docker run -p 7700:7700 getmeili/meilisearch:latest
```

**Aucun résultat de recherche**
```bash
# Vérifier l'indexation
php artisan search:index --fresh --verbose
curl "http://localhost:8000/api/search/stats"
```

**Erreurs de validation**
```bash
# Vérifier les paramètres de requête
# q doit faire au moins 2 caractères
# types[] doit être valide
```

## ✅ Checklist de Validation

- [ ] ✅ Meilisearch démarré et accessible
- [ ] ✅ Configuration Scout correcte
- [ ] ✅ Modèles avec trait Searchable
- [ ] ✅ Données indexées
- [ ] ✅ Routes API fonctionnelles
- [ ] ✅ Tests passent
- [ ] ✅ Documentation complète
- [ ] ✅ Collection Postman importée

## 🎉 Conclusion

L'implémentation de la recherche globale est **complète et prête pour la production** :

- ✅ **3 modèles** indexés et searchables
- ✅ **7 endpoints** API documentés
- ✅ **Service robuste** avec filtres avancés
- ✅ **Commandes Artisan** pour la gestion
- ✅ **Tests automatisés** et validation
- ✅ **Documentation exhaustive**
- ✅ **Collection Postman** prête à l'emploi

**🚀 Prêt à utiliser !** Démarrez Meilisearch, indexez vos données et commencez à rechercher !

---

**📞 Support :** Consultez la documentation technique pour plus de détails ou contactez l'équipe de développement.
