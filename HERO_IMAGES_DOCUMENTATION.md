# 🖼️ Système de Gestion des Images Hero

## 📋 Vue d'ensemble

Le système de gestion des images Hero permet aux administrateurs de gérer les images affichées dans le Hero de la page d'accueil via le back-office Filament.

## ✨ Fonctionnalités

### 🔧 Back-office (Filament)
- **Upload d'images** : Support des formats JPEG, PNG, WebP
- **Éditeur d'images intégré** : Recadrage avec ratios prédéfinis (16:9, 21:9, libre)
- **Miniatures automatiques** : Génération optionnelle de thumbnails
- **Activation/Désactivation** : Toggle ON/OFF pour chaque image
- **Gestion de l'ordre** : Réorganisation par drag & drop ou champ position
- **Aperçu en temps réel** : Visualisation des images dans le tableau
- **Actions en lot** : Activation/désactivation multiple
- **Badge de navigation** : Affichage du nombre d'images actives

### 🌐 API Frontend
- **Images actives** : Récupération des images activées triées par position
- **Statistiques** : Compteurs total/actif/inactif
- **Image spécifique** : Récupération d'une image par ID
- **URLs complètes** : Génération automatique des URLs d'images

## 🚀 Utilisation

### Accès au Back-office
```
URL: http://localhost:8000/admin
Section: Gestion du contenu > Images Hero
```

### Endpoints API

#### Récupérer les images Hero actives
```http
GET /api/hero-images
```
**Réponse :**
```json
{
  "data": [
    {
      "id": 1,
      "title": "Architecture 3D Moderne",
      "description": "Description de l'image",
      "alt_text": "Texte alternatif",
      "image_url": "https://example.com/image.jpg",
      "thumbnail_url": "https://example.com/thumb.jpg",
      "position": 1,
      "is_active": true,
      "created_at": "2025-08-22T05:28:30.000000Z",
      "updated_at": "2025-08-22T05:28:30.000000Z"
    }
  ]
}
```

#### Statistiques des images
```http
GET /api/hero-images/stats
```
**Réponse :**
```json
{
  "total": 6,
  "active": 4,
  "inactive": 2
}
```

#### Image spécifique
```http
GET /api/hero-images/{id}
```

## 🗄️ Structure de la Base de Données

### Table `hero_images`
| Champ | Type | Description |
|-------|------|-------------|
| `id` | bigint | Identifiant unique |
| `title` | string | Titre optionnel |
| `image_path` | string | Chemin vers l'image |
| `thumbnail_path` | string | Chemin vers la miniature |
| `is_active` | boolean | Image activée |
| `position` | integer | Position d'affichage |
| `alt_text` | string | Texte alternatif |
| `description` | text | Description |
| `created_at` | timestamp | Date de création |
| `updated_at` | timestamp | Date de modification |

## 📁 Organisation des Fichiers

### Modèles et Contrôleurs
- `app/Models/HeroImage.php` - Modèle Eloquent
- `app/Http/Controllers/Api/HeroImageController.php` - Contrôleur API
- `app/Http/Resources/HeroImageResource.php` - Ressource API

### Back-office Filament
- `app/Filament/Resources/HeroImageResource.php` - Ressource Filament

### Base de données
- `database/migrations/2025_08_22_052221_create_hero_images_table.php` - Migration
- `database/seeders/HeroImageSeeder.php` - Seeder avec exemples

### Stockage
- `storage/app/public/hero-images/` - Images principales
- `storage/app/public/hero-images/thumbnails/` - Miniatures

## 🔒 Sécurité et Permissions

- **Upload** : Limité aux administrateurs connectés
- **Taille max** : 5MB pour les images principales, 2MB pour les miniatures
- **Formats** : JPEG, PNG, WebP uniquement
- **API publique** : Lecture seule pour les images actives
- **API admin** : Accès complet avec authentification

## 🎯 Intégration Frontend

### Exemple d'utilisation React/Vue
```javascript
// Récupérer les images Hero
const fetchHeroImages = async () => {
  const response = await fetch('/api/hero-images');
  const data = await response.json();
  return data.data; // Array des images actives
};

// Utilisation dans un composant
const heroImages = await fetchHeroImages();
heroImages.forEach(image => {
  console.log(`Image: ${image.title} - URL: ${image.image_url}`);
});
```

## 🧪 Tests

Exécuter le script de test :
```bash
php test_hero_images_api.php
```

## 📊 Monitoring

- **Badge navigation** : Nombre d'images actives visible dans le menu
- **Statistiques** : Endpoint `/api/hero-images/stats` pour monitoring
- **Logs** : Suppression automatique des fichiers lors de la suppression des enregistrements

## 🔄 Maintenance

### Commandes utiles
```bash
# Réexécuter les migrations
php artisan migrate:fresh --seed

# Seeder uniquement les images Hero
php artisan db:seed --class=HeroImageSeeder

# Nettoyer le cache
php artisan cache:clear
php artisan config:clear
```

## 🎨 Personnalisation

Le système est entièrement personnalisable :
- Modifier les ratios d'images dans `HeroImageResource.php`
- Ajuster les tailles max dans la configuration
- Personnaliser les champs selon les besoins
- Étendre l'API avec de nouveaux endpoints
