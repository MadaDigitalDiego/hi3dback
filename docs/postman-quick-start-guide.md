# 🚀 Guide de Démarrage Rapide - Tests Postman

## 📥 **Installation et Configuration**

### **1. Importer la Collection**

1. **Ouvrir Postman**
2. **Cliquer sur "Import"** dans le coin supérieur gauche
3. **Sélectionner le fichier** `Hi3D-Global-Search-Complete.postman_collection.json`
4. **Confirmer l'importation**

### **2. Importer l'Environnement**

1. **Cliquer sur l'icône d'environnement** (⚙️) en haut à droite
2. **Cliquer sur "Import"**
3. **Sélectionner le fichier** `Hi3D-Global-Search.postman_environment.json`
4. **Sélectionner l'environnement** "Hi3D Global Search Environment"

### **3. Configuration Initiale**

Modifiez les variables d'environnement selon votre configuration :

```
base_url: http://localhost:8000/api  (ou votre URL)
auth_token: [votre-token-sanctum]    (si authentification requise)
```

---

## 🧪 **Tests de Base**

### **Ordre de Test Recommandé**

#### **1. Tests de Fonctionnalité (🔍 Search Endpoints)**
```
✅ Global Search          - Test de recherche globale
✅ Search Professionals   - Test recherche professionnels
✅ Search Services        - Test recherche services  
✅ Search Achievements    - Test recherche réalisations
✅ Search Suggestions     - Test suggestions temps réel
```

#### **2. Tests de Filtres (🎯 Filtered Searches)**
```
✅ Professionals by City  - Test filtre géographique
✅ Services by Price      - Test filtre prix
✅ Professionals by Rating - Test filtre rating
✅ Services by Category   - Test filtre catégorie
```

#### **3. Tests de Métriques (📊 Stats & Metrics)**
```
✅ Search Statistics      - Test statistiques générales
✅ Popular Searches       - Test recherches populaires
✅ Real-time Metrics      - Test métriques temps réel
✅ Detailed Metrics       - Test métriques détaillées
```

---

## ⚡ **Exécution des Tests**

### **Test Individuel**
1. **Sélectionner une requête** dans la collection
2. **Cliquer sur "Send"**
3. **Vérifier les résultats** dans l'onglet "Test Results"

### **Test de Dossier Complet**
1. **Clic droit sur un dossier** (ex: "🔍 Search Endpoints")
2. **Sélectionner "Run folder"**
3. **Configurer les options** d'exécution
4. **Cliquer sur "Run"**

### **Test de Collection Complète**
1. **Clic droit sur la collection** "Hi3D Global Search API"
2. **Sélectionner "Run collection"**
3. **Configurer** :
   - Iterations: 1
   - Delay: 100ms entre les requêtes
   - Data file: (optionnel)
4. **Cliquer sur "Run Hi3D Global Search API"**

---

## 📊 **Interprétation des Résultats**

### **Codes de Statut Attendus**

| Endpoint | Statut | Description |
|----------|--------|-------------|
| Recherche valide | 200 | Succès avec résultats |
| Paramètre manquant | 422 | Erreur de validation |
| Query trop courte | 422 | Validation échouée |
| Type invalide | 422 | Type non supporté |
| Rate limit dépassé | 429 | Trop de requêtes |

### **Structure de Réponse Standard**

```json
{
  "success": true,
  "data": {
    "query": "Laravel",
    "total_count": 5,
    "results_by_type": {
      "professional_profiles": [...],
      "service_offers": [...],
      "achievements": [...]
    },
    "combined_results": {
      "data": [...],
      "pagination": {...}
    }
  },
  "meta": {
    "execution_time": "0.15s",
    "cached": false
  }
}
```

### **Tests Automatiques Inclus**

Chaque requête inclut des tests automatiques qui vérifient :

- ✅ **Code de statut** correct
- ✅ **Structure de réponse** valide
- ✅ **Types de données** appropriés
- ✅ **Logique métier** respectée
- ✅ **Performance** acceptable

---

## 🔧 **Personnalisation des Tests**

### **Modifier les Variables**

Dans l'environnement, vous pouvez ajuster :

```javascript
search_query: "votre-terme"     // Terme de recherche principal
test_city: "Lyon"               // Ville pour les filtres
test_rating: "4.0"              // Rating minimum
test_price: "2000"              // Prix maximum
pagination_size: "15"           // Taille de page
```

### **Ajouter des Tests Personnalisés**

Dans l'onglet "Tests" d'une requête :

```javascript
// Test personnalisé
pm.test("Mon test spécifique", function () {
    const jsonData = pm.response.json();
    pm.expect(jsonData.data.total_count).to.be.greaterThan(0);
});

// Sauvegarder une valeur pour les tests suivants
pm.globals.set("first_result_id", jsonData.data.combined_results.data[0].id);
```

---

## 📈 **Tests de Performance**

### **Tests Inclus**

La collection inclut des tests de performance automatiques :

```javascript
// Test de temps de réponse global
pm.test("Response time is acceptable", function () {
    pm.expect(pm.response.responseTime).to.be.below(3000);
});

// Test de performance spécifique
pm.test("Search performs well", function () {
    pm.expect(pm.response.responseTime).to.be.below(2000);
});
```

### **Métriques Surveillées**

- ⏱️ **Temps de réponse** < 3 secondes
- 🔄 **Gestion de charge** avec requêtes concurrentes
- 🛡️ **Rate limiting** fonctionnel
- 💾 **Cache** efficace

---

## 🐛 **Dépannage**

### **Erreurs Communes**

#### **Connection Refused**
```
Error: connect ECONNREFUSED 127.0.0.1:8000
```
**Solution** : Vérifiez que Laravel est démarré (`php artisan serve`)

#### **404 Not Found**
```
Status: 404 Not Found
```
**Solution** : Vérifiez l'URL de base dans l'environnement

#### **422 Validation Error**
```
{
  "message": "The given data was invalid.",
  "errors": {...}
}
```
**Solution** : Vérifiez les paramètres de la requête

#### **429 Too Many Requests**
```
{
  "message": "Too Many Attempts."
}
```
**Solution** : Attendez quelques secondes avant de relancer

### **Vérifications de Base**

1. ✅ **Laravel est démarré** : `php artisan serve`
2. ✅ **Meilisearch fonctionne** : Données indexées
3. ✅ **Variables d'environnement** : URL correcte
4. ✅ **Authentification** : Token valide si requis

---

## 📋 **Checklist de Test Complet**

### **Tests Fonctionnels**
- [ ] Recherche globale fonctionne
- [ ] Recherche par type fonctionne
- [ ] Suggestions en temps réel
- [ ] Filtres géographiques
- [ ] Filtres de prix
- [ ] Filtres de rating
- [ ] Filtres de catégorie

### **Tests de Validation**
- [ ] Paramètre manquant rejeté
- [ ] Query trop courte rejetée
- [ ] Type invalide rejeté
- [ ] Pagination fonctionne

### **Tests de Performance**
- [ ] Temps de réponse < 3s
- [ ] Rate limiting actif
- [ ] Cache fonctionnel
- [ ] Gestion de charge

### **Tests de Métriques**
- [ ] Statistiques disponibles
- [ ] Recherches populaires trackées
- [ ] Métriques temps réel
- [ ] Métriques détaillées

---

## 🎯 **Résultats Attendus**

### **Collection Complète**
```
✅ Tests: 45+ passed
⏱️ Duration: ~30-60 seconds
📊 Coverage: 100% des endpoints
🎯 Success Rate: 95%+
```

### **Performance Cible**
```
🔍 Recherche globale: < 1.5s
🎯 Recherche filtrée: < 1.0s
💡 Suggestions: < 0.5s
📊 Statistiques: < 0.3s
```

---

## 🚀 **Prochaines Étapes**

Après avoir validé tous les tests :

1. **Intégrer dans CI/CD** : Automatiser les tests
2. **Monitoring production** : Surveiller les métriques
3. **Tests de charge** : Utiliser Newman pour les tests automatisés
4. **Documentation équipe** : Former les développeurs

### **Commande Newman (Optionnel)**
```bash
# Installer Newman
npm install -g newman

# Exécuter la collection
newman run Hi3D-Global-Search-Complete.postman_collection.json \
  -e Hi3D-Global-Search.postman_environment.json \
  --reporters cli,html \
  --reporter-html-export results.html
```

**🎉 Votre API de recherche globale est maintenant entièrement testée et validée !**
