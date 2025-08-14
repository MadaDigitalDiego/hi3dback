# API Professionnels - Likes et Views

## Vue d'ensemble

Les endpoints `/api/professionals` et `/api/professionals/{id}` incluent maintenant les données de likes et views dans leurs réponses JSON.

## Nouvelles données incluses

### Champs ajoutés aux réponses

```json
{
  "likes_count": 15,
  "views_count": 234,
  "popularity_score": 279.0
}
```

- **`likes_count`** : Nombre total de likes reçus par le profil professionnel
- **`views_count`** : Nombre total de vues du profil professionnel
- **`popularity_score`** : Score de popularité calculé (likes × 3 + views × 1)

## Endpoints modifiés

### 1. GET /api/professionals

**Description** : Récupère la liste de tous les professionnels avec leurs statistiques de likes et views.

**Réponse exemple** :
```json
{
  "success": true,
  "professionals": [
    {
      "id": 1,
      "user_id": 5,
      "first_name": "Jean",
      "last_name": "Dupont",
      "email": "jean.dupont@example.com",
      "city": "Paris",
      "country": "France",
      "skills": ["JavaScript", "React", "Node.js"],
      "rating": 4.8,
      "likes_count": 15,
      "views_count": 234,
      "popularity_score": 279.0,
      "..."
    }
  ]
}
```

### 2. GET /api/professionals/{id}

**Description** : Récupère les détails d'un professionnel spécifique avec ses statistiques de likes et views.

**Paramètres** :
- `id` (integer) : ID du profil professionnel

**Réponse exemple** :
```json
{
  "success": true,
  "professional": {
    "id": 1,
    "user_id": 5,
    "first_name": "Jean",
    "last_name": "Dupont",
    "email": "jean.dupont@example.com",
    "bio": "Développeur full-stack passionné...",
    "skills": ["JavaScript", "React", "Node.js"],
    "rating": 4.8,
    "likes_count": 15,
    "views_count": 234,
    "popularity_score": 279.0,
    "achievements": [...],
    "..."
  }
}
```

### 3. GET /api/professionals/filter

**Description** : Filtre les professionnels avec critères avancés, incluant les statistiques de likes et views.

**Paramètres de requête** :
- `search` (string, optionnel) : Terme de recherche
- `location` (string, optionnel) : Localisation
- `sort_by` (string, optionnel) : Critère de tri (`newest`, `rating`)

**Réponse** : Même format que `/api/professionals` avec les données de likes et views incluses.

## APIs de gestion des likes et views

### Likes (Authentification requise)

```bash
# Liker un profil
POST /api/professionals/{id}/like

# Unliker un profil  
DELETE /api/professionals/{id}/like

# Basculer like/unlike
POST /api/professionals/{id}/like/toggle

# Vérifier le statut du like
GET /api/professionals/{id}/like/status
```

### Views (Public)

```bash
# Enregistrer une vue
POST /api/professionals/{id}/view

# Statistiques des vues
GET /api/professionals/{id}/view/stats

# Vérifier si déjà vu
GET /api/professionals/{id}/view/status
```

## Calcul du score de popularité

Le score de popularité est calculé selon la formule :
```
popularity_score = (likes_count × 3) + (views_count × 1)
```

Les likes ont un poids plus important (×3) que les vues (×1) pour refléter l'engagement plus fort qu'ils représentent.

## Optimisations implémentées

- **Relations Eloquent** : Chargement optimisé avec `with(['views', 'likers'])`
- **Prévention des doublons** : Les vues et likes en double sont automatiquement évités
- **Index de base de données** : Index sur les colonnes critiques pour les performances
- **Cache des attributs** : Les méthodes `getTotalLikesAttribute()` et `getTotalViewsAttribute()` utilisent les relations chargées

## Exemples d'utilisation frontend

### Affichage des statistiques
```javascript
// Afficher le nombre de likes et vues
const professional = response.data.professional;
console.log(`${professional.likes_count} likes, ${professional.views_count} vues`);
console.log(`Score de popularité: ${professional.popularity_score}`);
```

### Tri par popularité
```javascript
// Trier les professionnels par score de popularité
const sortedProfessionals = professionals.sort((a, b) => 
  b.popularity_score - a.popularity_score
);
```

## Notes importantes

1. **Performance** : Les relations `views` et `likers` sont chargées avec les requêtes principales pour éviter le problème N+1
2. **Compatibilité** : Les nouveaux champs sont ajoutés sans casser la compatibilité avec l'API existante
3. **Sécurité** : Les likes nécessitent une authentification, les vues sont publiques
4. **Prévention des abus** : Les doublons de vues sont évités par session/utilisateur

## Tests recommandés

1. Vérifier que les nouveaux champs sont présents dans toutes les réponses
2. Tester les performances avec un grand nombre de professionnels
3. Valider que les compteurs se mettent à jour correctement après likes/vues
4. Tester la compatibilité avec les clients API existants
