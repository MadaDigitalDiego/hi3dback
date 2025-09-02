# 📖 Documentation API - Likes et Vues des Service Offers

## 🎯 Vue d'ensemble

Cette documentation décrit les APIs pour la gestion des **likes** (j'aime) et **vues** (nombre de vues) des service offers, avec intégration automatique au système de favoris polymorphique.

## 🏗️ Architecture

### Fonctionnalités Implémentées

#### 1. Système de Vues
- ✅ Enregistrement automatique des vues
- ✅ Support utilisateurs connectés et anonymes
- ✅ Prévention des doublons par session/utilisateur
- ✅ Métadonnées complètes (IP, User Agent, Session)
- ✅ Statistiques détaillées avec historique
- ✅ **Pas d'authentification requise**

#### 2. Système de Likes
- ✅ Like/Unlike des service offers
- ✅ **Authentification requise** (Laravel Sanctum)
- ✅ Intégration automatique aux favoris
- ✅ Comptage en temps réel
- ✅ Prévention des doublons

## 🚀 Endpoints Disponibles

### 📊 Routes Publiques (Vues) - Pas d'authentification

#### 1. POST `/api/service-offers/{id}/view`
**Description :** Enregistre une vue pour un service offer

**Paramètres :**
- `{id}` : ID du service offer (dans l'URL)

**Headers requis :**
```
Content-Type: application/json
Accept: application/json
```

**Réponse succès (200) :**
```json
{
  "success": true,
  "message": "Vue enregistrée avec succès.",
  "data": {
    "total_views": 1,
    "view_recorded": true
  }
}
```

**Réponse si déjà vu :**
```json
{
  "success": true,
  "message": "Vue déjà enregistrée pour cette session.",
  "data": {
    "total_views": 1,
    "view_recorded": false
  }
}
```

#### 2. GET `/api/service-offers/{id}/view/stats`
**Description :** Récupère les statistiques détaillées des vues

**Paramètres :**
- `{id}` : ID du service offer (dans l'URL)

**Headers requis :**
```
Accept: application/json
```

**Réponse succès (200) :**
```json
{
  "success": true,
  "data": {
    "total_views": 15,
    "unique_users": 8,
    "anonymous_views": 7,
    "views_per_day": [
      {
        "date": "2025-09-01",
        "count": 5
      },
      {
        "date": "2025-09-02",
        "count": 10
      }
    ]
  }
}
```

#### 3. GET `/api/service-offers/{id}/view/status`
**Description :** Vérifie si l'utilisateur/session actuel a déjà vu le service

**Paramètres :**
- `{id}` : ID du service offer (dans l'URL)

**Headers requis :**
```
Accept: application/json
```

**Réponse succès (200) :**
```json
{
  "success": true,
  "data": {
    "has_viewed": true,
    "total_views": 15
  }
}
```

### 🔒 Routes Protégées (Likes) - Authentification requise

#### 4. POST `/api/service-offers/{id}/like`
**Description :** Like un service offer

**Paramètres :**
- `{id}` : ID du service offer (dans l'URL)

**Headers requis :**
```
Content-Type: application/json
Accept: application/json
Authorization: Bearer {votre_token}
```

**Réponse succès (200) :**
```json
{
  "success": true,
  "message": "Service ajouté aux likes et favoris avec succès.",
  "data": {
    "liked": true,
    "total_likes": 1,
    "is_favorite": false
  }
}
```

**Réponse erreur (401) :**
```json
{
  "success": false,
  "message": "Vous devez être connecté pour liker un service."
}
```

#### 5. DELETE `/api/service-offers/{id}/like`
**Description :** Unlike un service offer

**Paramètres :**
- `{id}` : ID du service offer (dans l'URL)

**Headers requis :**
```
Accept: application/json
Authorization: Bearer {votre_token}
```

**Réponse succès (200) :**
```json
{
  "success": true,
  "message": "Service retiré des likes et favoris avec succès.",
  "data": {
    "liked": false,
    "total_likes": 0,
    "is_favorite": false
  }
}
```

#### 6. POST `/api/service-offers/{id}/like/toggle`
**Description :** Bascule le statut like/unlike

**Paramètres :**
- `{id}` : ID du service offer (dans l'URL)

**Headers requis :**
```
Content-Type: application/json
Accept: application/json
Authorization: Bearer {votre_token}
```

**Réponse succès (200) :**
```json
{
  "success": true,
  "message": "Service ajouté aux likes et favoris.",
  "data": {
    "liked": true,
    "total_likes": 1,
    "is_favorite": false
  }
}
```

#### 7. GET `/api/service-offers/{id}/like/status`
**Description :** Récupère le statut du like pour l'utilisateur connecté

**Paramètres :**
- `{id}` : ID du service offer (dans l'URL)

**Headers requis :**
```
Accept: application/json
Authorization: Bearer {votre_token}
```

**Réponse succès (200) :**
```json
{
  "success": true,
  "data": {
    "liked": true,
    "total_likes": 1,
    "is_favorite": false
  }
}
```

## 📊 Nouveaux Champs dans ServiceOfferResource

Tous les endpoints qui retournent des service offers incluent maintenant ces nouveaux champs :

```json
{
  "id": 1,
  "title": "Mon Service",
  "description": "Description du service",
  "price": "1500.00",
  "likes": 25,
  "views": 150,
  "likes_count": 25,
  "views_count": 150,
  "popularity_score": 225.0,
  "created_at": "2025-09-02T08:00:00.000000Z",
  "updated_at": "2025-09-02T08:30:00.000000Z"
}
```

### Description des Nouveaux Champs

- **`likes_count`** (integer) : Nombre de likes via l'API (temps réel)
- **`views_count`** (integer) : Nombre de vues via l'API (temps réel)
- **`popularity_score`** (float) : Score calculé selon la formule `(likes × 3) + (views × 1)`

## 🧪 Tests avec Postman

### Prérequis

1. **Base URL :** `http://localhost:8000/api` (ou votre URL de serveur)
2. **Token d'authentification :** Nécessaire pour les endpoints de likes

### Obtenir un Token d'Authentification

**Endpoint :** `POST /api/login`
**Body (JSON) :**
```json
{
  "email": "votre@email.com",
  "password": "votre_mot_de_passe"
}
```

**Réponse :**
```json
{
  "success": true,
  "token": "1|abc123def456...",
  "user": {...}
}
```

### Collection Postman Recommandée

#### 📁 Dossier "Service Offers - Vues (Public)"

**1. Enregistrer une Vue**
- **Méthode :** POST
- **URL :** `{{base_url}}/service-offers/1/view`
- **Headers :**
  - `Content-Type: application/json`
  - `Accept: application/json`

**2. Statistiques des Vues**
- **Méthode :** GET
- **URL :** `{{base_url}}/service-offers/1/view/stats`
- **Headers :**
  - `Accept: application/json`

**3. Statut de Vue**
- **Méthode :** GET
- **URL :** `{{base_url}}/service-offers/1/view/status`
- **Headers :**
  - `Accept: application/json`

#### 📁 Dossier "Service Offers - Likes (Authentifié)"

**4. Liker un Service**
- **Méthode :** POST
- **URL :** `{{base_url}}/service-offers/1/like`
- **Headers :**
  - `Content-Type: application/json`
  - `Accept: application/json`
  - `Authorization: Bearer {{token}}`

**5. Unliker un Service**
- **Méthode :** DELETE
- **URL :** `{{base_url}}/service-offers/1/like`
- **Headers :**
  - `Accept: application/json`
  - `Authorization: Bearer {{token}}`

**6. Toggle Like**
- **Méthode :** POST
- **URL :** `{{base_url}}/service-offers/1/like/toggle`
- **Headers :**
  - `Content-Type: application/json`
  - `Accept: application/json`
  - `Authorization: Bearer {{token}}`

**7. Statut du Like**
- **Méthode :** GET
- **URL :** `{{base_url}}/service-offers/1/like/status`
- **Headers :**
  - `Accept: application/json`
  - `Authorization: Bearer {{token}}`

### Variables d'Environnement Postman

Créez un environnement avec ces variables :

```json
{
  "base_url": "http://localhost:8000/api",
  "token": "1|votre_token_ici",
  "service_id": "1"
}
```

## 🔧 Calcul du Score de Popularité

Le score de popularité est calculé selon la formule :
```
popularity_score = (likes_count × 3) + (views_count × 1)
```

Les likes ont un poids plus important (×3) que les vues (×1) pour refléter l'engagement plus fort qu'ils représentent.

## ⚠️ Gestion des Erreurs

### Erreurs Communes

**401 Unauthorized :**
```json
{
  "message": "Unauthenticated."
}
```

**404 Not Found :**
```json
{
  "message": "No query results for model [App\\Models\\ServiceOffer] 999"
}
```

**500 Internal Server Error :**
```json
{
  "success": false,
  "message": "Erreur lors du like du service.",
  "error": "Message d'erreur détaillé"
}
```

## 🚀 Utilisation Frontend

### Exemple JavaScript/Axios

```javascript
// Enregistrer une vue (public)
const recordView = async (serviceId) => {
  try {
    const response = await axios.post(`/api/service-offers/${serviceId}/view`);
    console.log('Vue enregistrée:', response.data);
  } catch (error) {
    console.error('Erreur:', error.response.data);
  }
};

// Liker un service (authentifié)
const likeService = async (serviceId, token) => {
  try {
    const response = await axios.post(
      `/api/service-offers/${serviceId}/like`,
      {},
      {
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json'
        }
      }
    );
    console.log('Service liké:', response.data);
  } catch (error) {
    console.error('Erreur:', error.response.data);
  }
};

// Afficher les statistiques
const displayStats = (serviceData) => {
  console.log(`${serviceData.likes_count} likes, ${serviceData.views_count} vues`);
  console.log(`Score de popularité: ${serviceData.popularity_score}`);
};
```

## 📈 Optimisations Implémentées

### 1. Base de Données
- Index sur les colonnes critiques pour les performances
- Contraintes uniques pour éviter les doublons
- Relations Eloquent optimisées

### 2. Prévention des Doublons
- **Vues** : Par `user_id` + `service_offer_id` ou `session_id` + `service_offer_id`
- **Likes** : Gestion automatique par le package `overtrue/laravel-like`

### 3. Sécurité
- Authentification requise pour les likes
- Rate limiting sur les endpoints
- Validation des données d'entrée

## 📋 Collection Postman Complète

### Import de Collection JSON

Voici une collection Postman complète que vous pouvez importer :

```json
{
  "info": {
    "name": "Service Offers - Likes & Views API",
    "description": "Collection complète pour tester les APIs de likes et vues des service offers",
    "schema": "https://schema.getpostman.com/json/collection/v2.1.0/collection.json"
  },
  "variable": [
    {
      "key": "base_url",
      "value": "http://localhost:8000/api",
      "type": "string"
    },
    {
      "key": "token",
      "value": "",
      "type": "string"
    },
    {
      "key": "service_id",
      "value": "1",
      "type": "string"
    }
  ],
  "item": [
    {
      "name": "Authentication",
      "item": [
        {
          "name": "Login",
          "request": {
            "method": "POST",
            "header": [
              {
                "key": "Content-Type",
                "value": "application/json"
              },
              {
                "key": "Accept",
                "value": "application/json"
              }
            ],
            "body": {
              "mode": "raw",
              "raw": "{\n  \"email\": \"superadmin@hi3d.com\",\n  \"password\": \"password\"\n}"
            },
            "url": {
              "raw": "{{base_url}}/login",
              "host": ["{{base_url}}"],
              "path": ["login"]
            }
          }
        }
      ]
    },
    {
      "name": "Views (Public)",
      "item": [
        {
          "name": "Record View",
          "request": {
            "method": "POST",
            "header": [
              {
                "key": "Content-Type",
                "value": "application/json"
              },
              {
                "key": "Accept",
                "value": "application/json"
              }
            ],
            "url": {
              "raw": "{{base_url}}/service-offers/{{service_id}}/view",
              "host": ["{{base_url}}"],
              "path": ["service-offers", "{{service_id}}", "view"]
            }
          }
        },
        {
          "name": "View Stats",
          "request": {
            "method": "GET",
            "header": [
              {
                "key": "Accept",
                "value": "application/json"
              }
            ],
            "url": {
              "raw": "{{base_url}}/service-offers/{{service_id}}/view/stats",
              "host": ["{{base_url}}"],
              "path": ["service-offers", "{{service_id}}", "view", "stats"]
            }
          }
        },
        {
          "name": "View Status",
          "request": {
            "method": "GET",
            "header": [
              {
                "key": "Accept",
                "value": "application/json"
              }
            ],
            "url": {
              "raw": "{{base_url}}/service-offers/{{service_id}}/view/status",
              "host": ["{{base_url}}"],
              "path": ["service-offers", "{{service_id}}", "view", "status"]
            }
          }
        }
      ]
    },
    {
      "name": "Likes (Authenticated)",
      "item": [
        {
          "name": "Like Service",
          "request": {
            "method": "POST",
            "header": [
              {
                "key": "Content-Type",
                "value": "application/json"
              },
              {
                "key": "Accept",
                "value": "application/json"
              },
              {
                "key": "Authorization",
                "value": "Bearer {{token}}"
              }
            ],
            "url": {
              "raw": "{{base_url}}/service-offers/{{service_id}}/like",
              "host": ["{{base_url}}"],
              "path": ["service-offers", "{{service_id}}", "like"]
            }
          }
        },
        {
          "name": "Unlike Service",
          "request": {
            "method": "DELETE",
            "header": [
              {
                "key": "Accept",
                "value": "application/json"
              },
              {
                "key": "Authorization",
                "value": "Bearer {{token}}"
              }
            ],
            "url": {
              "raw": "{{base_url}}/service-offers/{{service_id}}/like",
              "host": ["{{base_url}}"],
              "path": ["service-offers", "{{service_id}}", "like"]
            }
          }
        },
        {
          "name": "Toggle Like",
          "request": {
            "method": "POST",
            "header": [
              {
                "key": "Content-Type",
                "value": "application/json"
              },
              {
                "key": "Accept",
                "value": "application/json"
              },
              {
                "key": "Authorization",
                "value": "Bearer {{token}}"
              }
            ],
            "url": {
              "raw": "{{base_url}}/service-offers/{{service_id}}/like/toggle",
              "host": ["{{base_url}}"],
              "path": ["service-offers", "{{service_id}}", "like", "toggle"]
            }
          }
        },
        {
          "name": "Like Status",
          "request": {
            "method": "GET",
            "header": [
              {
                "key": "Accept",
                "value": "application/json"
              },
              {
                "key": "Authorization",
                "value": "Bearer {{token}}"
              }
            ],
            "url": {
              "raw": "{{base_url}}/service-offers/{{service_id}}/like/status",
              "host": ["{{base_url}}"],
              "path": ["service-offers", "{{service_id}}", "like", "status"]
            }
          }
        }
      ]
    },
    {
      "name": "Service Offers",
      "item": [
        {
          "name": "Get Service Offer (Public)",
          "request": {
            "method": "GET",
            "header": [
              {
                "key": "Accept",
                "value": "application/json"
              },
              {
                "key": "Authorization",
                "value": "Bearer {{token}}"
              }
            ],
            "url": {
              "raw": "{{base_url}}/service-offers/{{service_id}}/public",
              "host": ["{{base_url}}"],
              "path": ["service-offers", "{{service_id}}", "public"]
            }
          }
        },
        {
          "name": "List Service Offers",
          "request": {
            "method": "GET",
            "header": [
              {
                "key": "Accept",
                "value": "application/json"
              },
              {
                "key": "Authorization",
                "value": "Bearer {{token}}"
              }
            ],
            "url": {
              "raw": "{{base_url}}/service-offers",
              "host": ["{{base_url}}"],
              "path": ["service-offers"]
            }
          }
        }
      ]
    }
  ]
}
```

## 🧪 Scénarios de Test Recommandés

### Scénario 1 : Test Complet des Vues (Public)

1. **Enregistrer une première vue**
   - Endpoint : `POST /api/service-offers/1/view`
   - Résultat attendu : `view_recorded: true`, `total_views: 1`

2. **Tenter d'enregistrer la même vue**
   - Endpoint : `POST /api/service-offers/1/view`
   - Résultat attendu : `view_recorded: false`, `total_views: 1`

3. **Vérifier les statistiques**
   - Endpoint : `GET /api/service-offers/1/view/stats`
   - Résultat attendu : `total_views: 1`, `anonymous_views: 1`

4. **Vérifier le statut**
   - Endpoint : `GET /api/service-offers/1/view/status`
   - Résultat attendu : `has_viewed: true`

### Scénario 2 : Test Complet des Likes (Authentifié)

1. **Obtenir un token d'authentification**
   - Endpoint : `POST /api/login`
   - Copier le token dans les variables Postman

2. **Vérifier le statut initial**
   - Endpoint : `GET /api/service-offers/1/like/status`
   - Résultat attendu : `liked: false`, `total_likes: 0`

3. **Liker le service**
   - Endpoint : `POST /api/service-offers/1/like`
   - Résultat attendu : `liked: true`, `total_likes: 1`

4. **Vérifier le nouveau statut**
   - Endpoint : `GET /api/service-offers/1/like/status`
   - Résultat attendu : `liked: true`, `total_likes: 1`

5. **Tester le toggle (unlike)**
   - Endpoint : `POST /api/service-offers/1/like/toggle`
   - Résultat attendu : `liked: false`, `total_likes: 0`

6. **Tester le toggle (like)**
   - Endpoint : `POST /api/service-offers/1/like/toggle`
   - Résultat attendu : `liked: true`, `total_likes: 1`

7. **Unliker explicitement**
   - Endpoint : `DELETE /api/service-offers/1/like`
   - Résultat attendu : `liked: false`, `total_likes: 0`

### Scénario 3 : Test des Nouveaux Champs

1. **Récupérer un service offer**
   - Endpoint : `GET /api/service-offers/1/public`
   - Vérifier la présence des champs :
     - `likes_count`
     - `views_count`
     - `popularity_score`

2. **Calculer manuellement le score**
   - Formule : `(likes_count × 3) + (views_count × 1)`
   - Vérifier que `popularity_score` correspond

## 🔍 Débogage et Troubleshooting

### Problèmes Courants

#### 1. Token Expiré
**Symptôme :** `{"message": "Unauthenticated."}`
**Solution :** Refaire un login et mettre à jour le token

#### 2. Service Offer Inexistant
**Symptôme :** `No query results for model [App\Models\ServiceOffer] 999`
**Solution :** Vérifier que l'ID du service existe dans la base de données

#### 3. Erreur de CORS
**Symptôme :** Erreur CORS dans le navigateur
**Solution :** Vérifier la configuration CORS dans `config/cors.php`

#### 4. Erreur 500 sur les Likes
**Symptôme :** Erreur interne du serveur
**Solution :** Vérifier les logs Laravel dans `storage/logs/laravel.log`

### Commandes Utiles pour le Débogage

```bash
# Vérifier les logs Laravel
tail -f storage/logs/laravel.log

# Vérifier les routes
php artisan route:list | grep service-offers

# Vérifier la base de données
php artisan tinker
>>> App\Models\ServiceOffer::find(1)
>>> App\Models\ServiceOfferView::count()

# Nettoyer le cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

## 📊 Monitoring et Analytics

### Métriques Importantes à Surveiller

1. **Taux de Vues**
   - Nombre de vues par service
   - Vues uniques vs vues totales
   - Évolution temporelle des vues

2. **Engagement (Likes)**
   - Taux de conversion vue → like
   - Services les plus likés
   - Utilisateurs les plus actifs

3. **Performance**
   - Temps de réponse des APIs
   - Nombre de requêtes par minute
   - Erreurs 4xx/5xx

### Requêtes SQL Utiles

```sql
-- Top 10 des services les plus vus
SELECT so.id, so.title, COUNT(sov.id) as total_views
FROM service_offers so
LEFT JOIN service_offer_views sov ON so.id = sov.service_offer_id
GROUP BY so.id, so.title
ORDER BY total_views DESC
LIMIT 10;

-- Top 10 des services les plus likés
SELECT so.id, so.title, COUNT(l.id) as total_likes
FROM service_offers so
LEFT JOIN likes l ON so.id = l.likeable_id AND l.likeable_type = 'App\\Models\\ServiceOffer'
GROUP BY so.id, so.title
ORDER BY total_likes DESC
LIMIT 10;

-- Statistiques par jour (derniers 7 jours)
SELECT DATE(created_at) as date, COUNT(*) as views
FROM service_offer_views
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
GROUP BY DATE(created_at)
ORDER BY date DESC;
```

---

**🎉 Les APIs sont maintenant 100% fonctionnelles et prêtes à l'utilisation !**

Cette documentation complète vous permet de tester et intégrer facilement les nouvelles fonctionnalités de likes et vues pour les service offers.
