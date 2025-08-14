# 🚀 Guide de Démarrage Rapide - Likes et Vues

## ⚡ Configuration en 5 Minutes

### 1. Prérequis
```bash
# Vérifier que le serveur Laravel fonctionne
php artisan serve

# Vérifier que les migrations sont appliquées
php artisan migrate:status
```

### 2. Créer des Données de Test
```bash
cd backend
php test_likes_views.php
```

### 3. Obtenir un Token d'Authentification
```bash
php test_api_likes_views.php
```
**Copiez le token affiché** pour l'utiliser dans Postman.

## 📥 Import Postman

### 1. Importer la Collection
1. Ouvrir Postman
2. Cliquer sur **"Import"**
3. Sélectionner le fichier `docs/postman-likes-views-collection.json`

### 2. Importer l'Environnement
1. Cliquer sur **"Import"**
2. Sélectionner le fichier `docs/postman-likes-views-environment.json`
3. Sélectionner l'environnement **"Likes & Views Environment"**

### 3. Configurer le Token
1. Dans l'environnement, modifier la variable `auth_token`
2. Coller le token obtenu à l'étape 3 ci-dessus

## 🧪 Tests Rapides

### Test 1 : Vérifier la Santé de l'API
```
GET {{base_url}}/health-check
```
**Résultat attendu :** `{"status":"ok"}`

### Test 2 : Enregistrer une Vue (Sans Auth)
```
POST {{base_url}}/professionals/1/view
```
**Résultat attendu :** Vue enregistrée avec succès

### Test 3 : Liker un Profil (Avec Auth)
```
POST {{base_url}}/professionals/1/like
Authorization: Bearer {{auth_token}}
```
**Résultat attendu :** Like + Ajout aux favoris automatique

### Test 4 : Vérifier les Statistiques
```
GET {{base_url}}/professionals/1/view/stats
```
**Résultat attendu :** Statistiques détaillées des vues

## 📊 Workflow de Test Complet

### Scénario : Utilisateur Découvre et Like un Profil

1. **Vue du profil** (automatique lors de la visite)
   ```
   POST /professionals/1/view
   ```

2. **Vérification des stats avant like**
   ```
   GET /professionals/1/view/stats
   GET /professionals/1/like/status
   ```

3. **Like du profil** (ajoute automatiquement aux favoris)
   ```
   POST /professionals/1/like
   ```

4. **Vérification après like**
   ```
   GET /professionals/1/like/status
   # Doit montrer: liked=true, is_favorite=true
   ```

5. **Unlike du profil** (retire automatiquement des favoris)
   ```
   DELETE /professionals/1/like
   ```

6. **Vérification finale**
   ```
   GET /professionals/1/like/status
   # Doit montrer: liked=false, is_favorite=false
   ```

## 🔧 Dépannage Rapide

### Problème : Token Invalide
**Erreur :** `{"message": "Unauthenticated."}`
**Solution :** Régénérer le token avec `php test_api_likes_views.php`

### Problème : Profil Non Trouvé
**Erreur :** `No query results for model [App\Models\ProfessionalProfile] X`
**Solution :** Vérifier que le profil existe ou utiliser l'ID 1

### Problème : Serveur Non Accessible
**Erreur :** Connexion refusée
**Solution :** Démarrer le serveur avec `php artisan serve`

### Problème : Base de Données
**Erreur :** Table doesn't exist
**Solution :** Exécuter `php artisan migrate`

## 📈 Réponses Types

### Vue Enregistrée
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

### Like Réussi
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

### Statistiques de Vues
```json
{
  "success": true,
  "data": {
    "total_views": 5,
    "unique_users": 2,
    "anonymous_views": 3,
    "views_per_day": [
      {"date": "2025-07-08", "count": 5}
    ]
  }
}
```

## 🎯 Points Clés à Retenir

1. **Vues** = Pas d'authentification requise
2. **Likes** = Authentification Sanctum requise
3. **Like automatique** = Ajout aux favoris
4. **Unlike automatique** = Suppression des favoris
5. **Doublons prévenus** = Par utilisateur/session

## 📝 Checklist de Validation

- [ ] ✅ Health check fonctionne
- [ ] ✅ Vue enregistrée sans auth
- [ ] ✅ Like nécessite auth
- [ ] ✅ Like ajoute aux favoris
- [ ] ✅ Unlike retire des favoris
- [ ] ✅ Statistiques correctes
- [ ] ✅ Doublons prévenus
- [ ] ✅ Erreurs bien gérées

## 🔗 Liens Utiles

- [Documentation Complète](./likes-views-functionality.md)
- [Guide de Test Détaillé](./postman-likes-views-testing.md)
- [Collection Postman](./postman-likes-views-collection.json)
- [Environnement Postman](./postman-likes-views-environment.json)

---

**🎉 Prêt à tester !** Suivez ce guide et vous devriez avoir un environnement de test fonctionnel en moins de 5 minutes.
