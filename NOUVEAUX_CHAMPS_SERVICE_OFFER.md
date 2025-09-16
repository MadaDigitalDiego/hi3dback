# Nouveaux Champs pour ServiceOffer

## Résumé des modifications

Quatre nouveaux champs ont été ajoutés au modèle `ServiceOffer` pour permettre aux utilisateurs de fournir des informations plus détaillées sur leurs services :

1. **`what_you_get`** - Ce que vous obtenez
2. **`who_is_this_for`** - Pour qui est ce produit
3. **`delivery_method`** - Méthode de livraison
4. **`why_choose_me`** - Pourquoi me choisir

## Détails techniques

### Base de données

**Migration créée :** `2025_09_16_040848_add_additional_fields_to_service_offers_table.php`

```sql
ALTER TABLE service_offers ADD COLUMN what_you_get TEXT NULL;
ALTER TABLE service_offers ADD COLUMN who_is_this_for TEXT NULL;
ALTER TABLE service_offers ADD COLUMN delivery_method TEXT NULL;
ALTER TABLE service_offers ADD COLUMN why_choose_me TEXT NULL;
```

### Modèle ServiceOffer

Les nouveaux champs ont été ajoutés à :
- `$fillable` array pour permettre l'assignation de masse
- `toSearchableArray()` pour inclure dans la recherche Meilisearch

### Validation

**StoreServiceOfferRequest :**
```php
'what_you_get' => 'nullable|string',
'who_is_this_for' => 'nullable|string',
'delivery_method' => 'nullable|string',
'why_choose_me' => 'nullable|string',
```

**UpdateServiceOfferRequest :**
```php
'what_you_get' => 'sometimes|string',
'who_is_this_for' => 'sometimes|string',
'delivery_method' => 'sometimes|string',
'why_choose_me' => 'sometimes|string',
```

### API Response

Les nouveaux champs sont inclus dans `ServiceOfferResource` et retournés dans toutes les réponses API.

## Utilisation

### Création d'une offre de service

```json
POST /api/service-offers
{
    "title": "Modélisation 3D Architecture",
    "description": "Service de modélisation 3D professionnel",
    "price": 1500,
    "price_unit": "par projet",
    "execution_time": "1 semaine",
    "concepts": "3",
    "revisions": "2",
    "categories": ["Architecture", "Modélisation 3D"],
    "status": "published",
    "what_you_get": "• Modélisation 3D haute qualité\n• Rendus photoréalistes\n• Fichiers sources inclus",
    "who_is_this_for": "Architectes, promoteurs immobiliers, designers d'intérieur",
    "delivery_method": "Livraison numérique via plateforme sécurisée",
    "why_choose_me": "Plus de 5 ans d'expérience, portfolio de 200+ projets"
}
```

### Mise à jour d'une offre

```json
PUT /api/service-offers/{id}
{
    "what_you_get": "Contenu mis à jour",
    "who_is_this_for": "Public cible mis à jour",
    "delivery_method": "Méthode de livraison mise à jour",
    "why_choose_me": "Raisons mises à jour"
}
```

### Réponse API

```json
{
    "data": {
        "id": 1,
        "title": "Modélisation 3D Architecture",
        "description": "Service de modélisation 3D professionnel",
        "price": 1500,
        "what_you_get": "• Modélisation 3D haute qualité\n• Rendus photoréalistes",
        "who_is_this_for": "Architectes, promoteurs immobiliers",
        "delivery_method": "Livraison numérique via plateforme sécurisée",
        "why_choose_me": "Plus de 5 ans d'expérience",
        "created_at": "2025-09-16T04:08:48.000000Z",
        "updated_at": "2025-09-16T04:08:48.000000Z"
    }
}
```

## Tests

Un seeder `ServiceOfferSeeder` a été créé pour générer des données d'exemple avec les nouveaux champs.

Des tests unitaires sont disponibles dans `ServiceOfferAdditionalFieldsTest` pour vérifier :
- La création d'offres avec les nouveaux champs
- La mise à jour des champs existants
- La présence des champs dans les réponses API

## Migration

Pour appliquer les modifications :

```bash
php artisan migrate
```

Pour générer des données d'exemple :

```bash
php artisan db:seed --class=ServiceOfferSeeder
```

## Notes importantes

- Tous les nouveaux champs sont **optionnels** (nullable)
- Les champs sont stockés en format **TEXT** pour permettre du contenu long
- Les champs sont inclus dans la recherche Meilisearch
- Compatibilité ascendante maintenue avec les offres existantes
