# Tests Postman - Likes et Views pour Professionnels

## Collection Postman : Professionnels avec Likes/Views

### 1. Récupérer tous les professionnels avec statistiques

**Méthode** : `GET`  
**URL** : `{{base_url}}/api/professionals`  
**Headers** : Aucun (endpoint public)

**Test Script** :
```javascript
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

pm.test("Response has professionals array", function () {
    const jsonData = pm.response.json();
    pm.expect(jsonData).to.have.property('success', true);
    pm.expect(jsonData).to.have.property('professionals');
    pm.expect(jsonData.professionals).to.be.an('array');
});

pm.test("Each professional has likes and views data", function () {
    const jsonData = pm.response.json();
    if (jsonData.professionals.length > 0) {
        const professional = jsonData.professionals[0];
        pm.expect(professional).to.have.property('likes_count');
        pm.expect(professional).to.have.property('views_count');
        pm.expect(professional).to.have.property('popularity_score');
        pm.expect(professional.likes_count).to.be.a('number');
        pm.expect(professional.views_count).to.be.a('number');
        pm.expect(professional.popularity_score).to.be.a('number');
    }
});
```

### 2. Récupérer un professionnel spécifique avec statistiques

**Méthode** : `GET`  
**URL** : `{{base_url}}/api/professionals/1`  
**Headers** : Aucun (endpoint public)

**Test Script** :
```javascript
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

pm.test("Response has professional object with stats", function () {
    const jsonData = pm.response.json();
    pm.expect(jsonData).to.have.property('success', true);
    pm.expect(jsonData).to.have.property('professional');
    
    const professional = jsonData.professional;
    pm.expect(professional).to.have.property('id');
    pm.expect(professional).to.have.property('likes_count');
    pm.expect(professional).to.have.property('views_count');
    pm.expect(professional).to.have.property('popularity_score');
});

pm.test("Popularity score calculation is correct", function () {
    const jsonData = pm.response.json();
    const professional = jsonData.professional;
    const expectedScore = (professional.likes_count * 3) + (professional.views_count * 1);
    pm.expect(professional.popularity_score).to.equal(expectedScore);
});
```

### 3. Filtrer les professionnels avec statistiques

**Méthode** : `GET`  
**URL** : `{{base_url}}/api/professionals/filter?search=javascript`  
**Headers** : Aucun (endpoint public)

**Test Script** :
```javascript
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

pm.test("Filtered professionals have likes/views data", function () {
    const jsonData = pm.response.json();
    pm.expect(jsonData).to.have.property('professionals');
    
    if (jsonData.professionals.length > 0) {
        jsonData.professionals.forEach(professional => {
            pm.expect(professional).to.have.property('likes_count');
            pm.expect(professional).to.have.property('views_count');
            pm.expect(professional).to.have.property('popularity_score');
        });
    }
});
```

### 4. Enregistrer une vue (Public)

**Méthode** : `POST`  
**URL** : `{{base_url}}/api/professionals/1/view`  
**Headers** : 
```
Content-Type: application/json
```

**Test Script** :
```javascript
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

pm.test("View recorded successfully", function () {
    const jsonData = pm.response.json();
    pm.expect(jsonData).to.have.property('success', true);
    pm.expect(jsonData).to.have.property('data');
    pm.expect(jsonData.data).to.have.property('total_views');
    pm.expect(jsonData.data.total_views).to.be.a('number');
});
```

### 5. Liker un profil (Authentifié)

**Méthode** : `POST`  
**URL** : `{{base_url}}/api/professionals/1/like`  
**Headers** :
```
Authorization: Bearer {{auth_token}}
Content-Type: application/json
```

**Test Script** :
```javascript
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

pm.test("Like recorded successfully", function () {
    const jsonData = pm.response.json();
    pm.expect(jsonData).to.have.property('success', true);
    pm.expect(jsonData).to.have.property('data');
    pm.expect(jsonData.data).to.have.property('liked', true);
    pm.expect(jsonData.data).to.have.property('total_likes');
    pm.expect(jsonData.data.total_likes).to.be.a('number');
});
```

### 6. Vérifier le statut du like

**Méthode** : `GET`  
**URL** : `{{base_url}}/api/professionals/1/like/status`  
**Headers** :
```
Authorization: Bearer {{auth_token}}
```

**Test Script** :
```javascript
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

pm.test("Like status returned", function () {
    const jsonData = pm.response.json();
    pm.expect(jsonData).to.have.property('success', true);
    pm.expect(jsonData).to.have.property('data');
    pm.expect(jsonData.data).to.have.property('liked');
    pm.expect(jsonData.data.liked).to.be.a('boolean');
});
```

### 7. Statistiques des vues

**Méthode** : `GET`  
**URL** : `{{base_url}}/api/professionals/1/view/stats`  
**Headers** : Aucun (endpoint public)

**Test Script** :
```javascript
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

pm.test("View stats returned", function () {
    const jsonData = pm.response.json();
    pm.expect(jsonData).to.have.property('success', true);
    pm.expect(jsonData).to.have.property('data');
    pm.expect(jsonData.data).to.have.property('total_views');
    pm.expect(jsonData.data).to.have.property('unique_users');
    pm.expect(jsonData.data).to.have.property('anonymous_views');
});
```

## Variables d'environnement Postman

Créez un environnement Postman avec les variables suivantes :

```json
{
  "base_url": "http://localhost:8000",
  "auth_token": "your_bearer_token_here"
}
```

## Scénarios de test complets

### Scénario 1 : Vérification des données de base
1. Récupérer tous les professionnels
2. Vérifier que chaque professionnel a les champs `likes_count`, `views_count`, `popularity_score`
3. Récupérer un professionnel spécifique
4. Vérifier que le calcul du score de popularité est correct

### Scénario 2 : Test des interactions
1. Enregistrer une vue sur un profil
2. Vérifier que le compteur de vues a augmenté
3. Liker le profil (authentifié)
4. Vérifier que le compteur de likes a augmenté
5. Vérifier que le score de popularité a été recalculé

### Scénario 3 : Test de performance
1. Récupérer tous les professionnels
2. Mesurer le temps de réponse
3. Vérifier que les relations sont chargées efficacement (pas de requêtes N+1)

## Notes pour les tests

1. **Authentification** : Certains endpoints nécessitent un token Bearer valide
2. **Données de test** : Assurez-vous d'avoir des professionnels en base de données
3. **Sessions** : Les vues utilisent les sessions pour éviter les doublons
4. **Rate limiting** : Respectez les limites de taux si elles sont configurées

## Validation des performances

Ajoutez ces tests pour vérifier les performances :

```javascript
pm.test("Response time is less than 2000ms", function () {
    pm.expect(pm.response.responseTime).to.be.below(2000);
});

pm.test("No N+1 query issues", function () {
    // Vérifiez que le nombre de requêtes SQL reste constant
    // indépendamment du nombre de professionnels
});
```
