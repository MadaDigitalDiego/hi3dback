# 🧪 Guide de Test Postman - Likes et Vues

## 🚀 Configuration Initiale

### 1. Variables d'Environnement Postman

Créez un environnement Postman avec les variables suivantes :

```json
{
  "base_url": "http://localhost:8000/api",
  "auth_token": "",
  "user_email": "client@test.com",
  "user_password": "password",
  "professional_profile_id": "1"
}
```

### 2. Prérequis

1. **Serveur Laravel démarré** : `php artisan serve`
2. **Base de données migrée** : `php artisan migrate`
3. **Données de test créées** : Exécuter `php test_likes_views.php`

## 🔐 Authentification

### Obtenir un Token d'Authentification

**Méthode 1 : Via Script PHP**
```bash
cd backend
php test_api_likes_views.php
```
Copiez le token affiché dans la variable `auth_token` de Postman.

**Méthode 2 : Via API Login (si disponible)**
```http
POST {{base_url}}/login
Content-Type: application/json

{
  "email": "{{user_email}}",
  "password": "{{user_password}}"
}
```

## 📊 Tests des APIs de Vues (Publiques)

### 1. Enregistrer une Vue

```http
POST {{base_url}}/professionals/{{professional_profile_id}}/view
Content-Type: application/json
```

**Réponse Attendue :**
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

### 2. Obtenir les Statistiques de Vues

```http
GET {{base_url}}/professionals/{{professional_profile_id}}/view/stats
Content-Type: application/json
```

**Réponse Attendue :**
```json
{
  "success": true,
  "data": {
    "total_views": 2,
    "unique_users": 1,
    "anonymous_views": 1,
    "views_per_day": [
      {
        "date": "2025-07-08",
        "count": 2
      }
    ]
  }
}
```

### 3. Vérifier le Statut de Vue

```http
GET {{base_url}}/professionals/{{professional_profile_id}}/view/status
Content-Type: application/json
```

**Réponse Attendue :**
```json
{
  "success": true,
  "data": {
    "has_viewed": false,
    "total_views": 2
  }
}
```

## ❤️ Tests des APIs de Likes (Protégées)

### Configuration de l'Authentification

Pour toutes les requêtes de likes, ajoutez l'en-tête :
```
Authorization: Bearer {{auth_token}}
```

### 1. Liker un Profil

```http
POST {{base_url}}/professionals/{{professional_profile_id}}/like
Authorization: Bearer {{auth_token}}
Content-Type: application/json
```

**Réponse Attendue :**
```json
{
  "success": true,
  "message": "Profil ajouté aux likes et favoris avec succès.",
  "data": {
    "liked": true,
    "total_likes": 1,
    "is_favorite": true
  }
}
```

### 2. Vérifier le Statut du Like

```http
GET {{base_url}}/professionals/{{professional_profile_id}}/like/status
Authorization: Bearer {{auth_token}}
Content-Type: application/json
```

**Réponse Attendue :**
```json
{
  "success": true,
  "data": {
    "liked": true,
    "total_likes": 1,
    "is_favorite": true
  }
}
```

### 3. Toggle Like (Basculer)

```http
POST {{base_url}}/professionals/{{professional_profile_id}}/like/toggle
Authorization: Bearer {{auth_token}}
Content-Type: application/json
```

**Réponse Attendue (si était liké) :**
```json
{
  "success": true,
  "message": "Profil retiré des likes et favoris.",
  "data": {
    "liked": false,
    "total_likes": 0,
    "is_favorite": false
  }
}
```

### 4. Unliker un Profil

```http
DELETE {{base_url}}/professionals/{{professional_profile_id}}/like
Authorization: Bearer {{auth_token}}
Content-Type: application/json
```

**Réponse Attendue :**
```json
{
  "success": true,
  "message": "Profil retiré des likes et favoris avec succès.",
  "data": {
    "liked": false,
    "total_likes": 0,
    "is_favorite": false
  }
}
```

## 🔒 Tests de Sécurité

### 1. Test sans Authentification (Likes)

```http
POST {{base_url}}/professionals/{{professional_profile_id}}/like
Content-Type: application/json
```

**Réponse Attendue :**
```json
{
  "message": "Unauthenticated."
}
```
**Status Code :** `401`

### 2. Test avec Token Invalide

```http
POST {{base_url}}/professionals/{{professional_profile_id}}/like
Authorization: Bearer invalid-token-here
Content-Type: application/json
```

**Réponse Attendue :**
```json
{
  "message": "Unauthenticated."
}
```
**Status Code :** `401`

### 3. Test avec Profil Inexistant

```http
POST {{base_url}}/professionals/99999/like
Authorization: Bearer {{auth_token}}
Content-Type: application/json
```

**Réponse Attendue :**
```json
{
  "message": "No query results for model [App\\Models\\ProfessionalProfile] 99999"
}
```
**Status Code :** `404`

## 📋 Collection Postman Complète

### Structure de la Collection

```
📁 Likes & Views API Tests
├── 📁 Authentication
│   └── 🔑 Get Auth Token
├── 📁 Views (Public)
│   ├── 👁️ Record View
│   ├── 📊 Get View Stats
│   └── ✅ Check View Status
├── 📁 Likes (Protected)
│   ├── ❤️ Like Profile
│   ├── 💔 Unlike Profile
│   ├── 🔄 Toggle Like
│   └── ✅ Check Like Status
└── 📁 Security Tests
    ├── 🚫 Like without Auth
    ├── 🚫 Like with Invalid Token
    └── 🚫 Like Non-existent Profile
```

## 🧪 Scénarios de Test Recommandés

### Scénario 1 : Workflow Complet Utilisateur
1. Enregistrer une vue du profil
2. Liker le profil (vérifier ajout aux favoris)
3. Vérifier les statistiques
4. Unliker le profil (vérifier suppression des favoris)

### Scénario 2 : Test des Doublons
1. Enregistrer une vue
2. Tenter d'enregistrer la même vue (doit être ignorée)
3. Liker un profil
4. Tenter de liker à nouveau (doit être ignoré)

### Scénario 3 : Test Multi-utilisateurs
1. Créer plusieurs utilisateurs
2. Chaque utilisateur like le même profil
3. Vérifier l'incrémentation du compteur
4. Vérifier que chaque utilisateur a le profil en favoris

## 📈 Tests de Performance

### Test de Charge (Optionnel)
Utilisez l'outil "Runner" de Postman pour :
- Exécuter 100 requêtes de vues simultanées
- Vérifier que les doublons sont bien gérés
- Mesurer les temps de réponse

## 🐛 Débogage

### Logs Laravel
Surveillez les logs Laravel pendant les tests :
```bash
tail -f storage/logs/laravel.log
```

### Base de Données
Vérifiez directement les tables :
```sql
SELECT * FROM likes WHERE likeable_type = 'App\\Models\\ProfessionalProfile';
SELECT * FROM professional_profile_views;
SELECT * FROM user_favorites WHERE favoritable_type = 'App\\Models\\ProfessionalProfile';
```

## ✅ Checklist de Validation

- [ ] Toutes les APIs de vues fonctionnent sans authentification
- [ ] Toutes les APIs de likes nécessitent une authentification
- [ ] Les likes ajoutent automatiquement aux favoris
- [ ] Les unlikes retirent automatiquement des favoris
- [ ] Les doublons de vues sont prévenus
- [ ] Les statistiques sont correctes
- [ ] Les erreurs retournent les bons codes de statut
- [ ] Les réponses JSON sont bien formatées

---

**Prochaine étape :** Importez la [Collection Postman](./postman-likes-views-collection.json) pour commencer les tests rapidement.
