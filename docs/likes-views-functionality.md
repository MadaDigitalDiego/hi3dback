# 📖 Documentation - Fonctionnalités Likes et Vues

## 🎯 Vue d'ensemble

Ce document décrit l'implémentation des fonctionnalités **"Likes"** (j'aime) et **"Vues"** (nombre de vues) pour les profils professionnels, avec intégration automatique au système de favoris.

## 🏗️ Architecture

### Packages Utilisés
- **`overtrue/laravel-like`** (v5.4.0) - Gestion des likes
- **Laravel Sanctum** - Authentification API
- **Laravel Events** - Synchronisation likes ↔ favoris

### Tables de Base de Données

#### 1. `likes` (Package overtrue/laravel-like)
```sql
- id (bigint, primary key)
- user_id (bigint, foreign key)
- likeable_type (string) - Type polymorphique
- likeable_id (bigint) - ID polymorphique
- created_at, updated_at (timestamps)
```

#### 2. `professional_profile_views`
```sql
- id (bigint, primary key)
- professional_profile_id (bigint, foreign key)
- user_id (bigint, nullable, foreign key)
- session_id (string, nullable)
- ip_address (string, nullable)
- user_agent (string, nullable)
- created_at, updated_at (timestamps)
- UNIQUE(professional_profile_id, user_id, session_id)
```

#### 3. `user_favorites`
```sql
- id (bigint, primary key)
- user_id (bigint, foreign key)
- favoritable_type (string) - Type polymorphique
- favoritable_id (bigint) - ID polymorphique
- created_at, updated_at (timestamps)
- UNIQUE(user_id, favoritable_type, favoritable_id)
```

## 🔧 Fonctionnalités

### 1. Système de Likes

#### Caractéristiques
- ✅ Like/Unlike des profils professionnels
- ✅ Authentification requise (Laravel Sanctum)
- ✅ Ajout automatique aux favoris lors du like
- ✅ Suppression automatique des favoris lors de l'unlike
- ✅ Comptage en temps réel
- ✅ Prévention des doublons

#### Événements Laravel
- **`Liked`** → Déclenche `AddToFavoritesOnLike`
- **`Unliked`** → Déclenche `RemoveFromFavoritesOnUnlike`

### 2. Système de Vues

#### Caractéristiques
- ✅ Enregistrement automatique des vues
- ✅ Support utilisateurs connectés et anonymes
- ✅ Prévention des doublons par session/utilisateur
- ✅ Métadonnées : IP, User Agent, Session ID
- ✅ Statistiques détaillées
- ✅ Pas d'authentification requise

#### Prévention des Doublons
- **Utilisateurs connectés** : Par `user_id` + `professional_profile_id`
- **Utilisateurs anonymes** : Par `session_id` + `professional_profile_id`

## 🚀 APIs Disponibles

### Routes Publiques (Vues)
```
POST   /api/professionals/{id}/view          # Enregistrer une vue
GET    /api/professionals/{id}/view/stats    # Statistiques des vues
GET    /api/professionals/{id}/view/status   # Vérifier si déjà vu
```

### Routes Protégées (Likes)
```
POST   /api/professionals/{id}/like         # Liker un profil
DELETE /api/professionals/{id}/like         # Unliker un profil
POST   /api/professionals/{id}/like/toggle  # Basculer like/unlike
GET    /api/professionals/{id}/like/status  # Statut du like
```

## 📊 Méthodes Helper

### Sur le modèle `ProfessionalProfile`

#### Vues
```php
$profile->getTotalViewsAttribute()           // Nombre total de vues
$profile->getUniqueViewersCount()           // Nombre de viewers uniques
$profile->getViewsCountForPeriod(30)        // Vues sur une période
$profile->recordView($userId, $sessionId)   // Enregistrer une vue
$profile->isViewedBy($userId, $sessionId)   // Vérifier si vu
```

#### Likes
```php
$profile->getTotalLikesAttribute()          // Nombre total de likes
$profile->isLikedByUser($userId)           // Vérifie si liké par un user
$profile->likers()                         // Relation vers les users qui ont liké
```

#### Popularité
```php
$profile->getPopularityScore()             // Score basé sur likes + vues
ProfessionalProfile::mostLiked(10)        // Top 10 des plus likés
ProfessionalProfile::mostViewed(10)       // Top 10 des plus vus
ProfessionalProfile::orderByPopularity()  // Tri par popularité
```

### Sur le modèle `User`

#### Likes
```php
$user->like($profile)                      // Liker un profil
$user->unlike($profile)                    // Unliker un profil
$user->toggleLike($profile)                // Basculer like/unlike
$user->hasLiked($profile)                  // Vérifie si a liké
```

#### Favoris
```php
$user->addToFavorites($profile)            // Ajouter aux favoris
$user->removeFromFavorites($profile)       // Retirer des favoris
$user->hasFavorite($profile)               // Vérifie si en favoris
$user->favoriteProfessionalProfiles()      // Tous les profils favoris
```

## 🔒 Sécurité

### Authentification
- **Likes** : Token Sanctum requis
- **Vues** : Aucune authentification requise
- **Favoris** : Gérés automatiquement via les likes

### Validation
- Vérification de l'existence du profil professionnel
- Validation des tokens d'authentification
- Prévention des doublons automatique

### Rate Limiting
- Recommandé d'ajouter du rate limiting pour éviter le spam
- Exemple : 60 requêtes par minute par utilisateur

## 📈 Performance

### Optimisations
- **Index de base de données** sur les colonnes fréquemment utilisées
- **Contraintes uniques** pour éviter les doublons
- **Relations Eloquent** optimisées
- **Requêtes groupées** pour les statistiques

### Cache (Recommandé)
```php
// Cache des statistiques populaires
Cache::remember("profile_{$id}_stats", 3600, function() use ($profile) {
    return [
        'total_likes' => $profile->getTotalLikesAttribute(),
        'total_views' => $profile->getTotalViewsAttribute(),
        'popularity_score' => $profile->getPopularityScore()
    ];
});
```

## 🧪 Tests

### Tests Unitaires
- Couverture complète des fonctionnalités
- Tests d'intégration likes ↔ favoris
- Tests de sécurité et d'authentification

### Tests API
- Validation de toutes les routes
- Tests avec et sans authentification
- Vérification des réponses JSON

## 🔄 Workflow Complet

1. **Utilisateur visite un profil** → Vue enregistrée automatiquement
2. **Utilisateur like le profil** → Like + Ajout aux favoris automatique
3. **Utilisateur unlike le profil** → Unlike + Suppression des favoris automatique
4. **Statistiques mises à jour** → En temps réel

## 📝 Notes d'Implémentation

### Événements Laravel
Les événements `Liked` et `Unliked` du package `overtrue/laravel-like` sont automatiquement écoutés pour synchroniser les favoris.

### Sessions
Pour les utilisateurs non connectés, les vues sont trackées par session ID pour éviter les doublons.

### Polymorphisme
Le système est conçu pour être extensible à d'autres types d'objets (pas seulement les profils professionnels).

---

**Prochaine étape :** Consultez le [Guide de Test Postman](./postman-likes-views-testing.md) pour tester les APIs.
