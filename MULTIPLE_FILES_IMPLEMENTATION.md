# Implémentation des Fichiers Multiples pour Achievements et Services

## Résumé des Modifications

Cette implémentation permet aux utilisateurs d'ajouter plusieurs photos/fichiers dans les sections **Achievement** et **Service** de l'application.

## Achievements - Nouvelles Fonctionnalités

### 1. Structure de Base de Données

- **Nouveau champ** : `files` (JSON) - pour stocker plusieurs fichiers
- **Ancien champ maintenu** : `file_path` (string) - pour la rétrocompatibilité
- **Migration automatique** : Les données existantes sont migrées du format `file_path` vers `files`

### 2. Format des Fichiers

```json
{
  "files": [
    {
      "path": "achievement_files/filename.jpg",
      "original_name": "original_filename.jpg",
      "mime_type": "image/jpeg",
      "size": 12345
    },
    {
      "path": "achievement_files/document.pdf",
      "original_name": "certificate.pdf",
      "mime_type": "application/pdf",
      "size": 67890
    }
  ]
}
```

### 3. API Endpoints

#### Créer un Achievement avec Plusieurs Fichiers
```http
POST /api/achievements
Content-Type: multipart/form-data

{
  "title": "Mon Achievement",
  "description": "Description",
  "files[]": [file1, file2, file3, ...]
}
```

#### Créer un Achievement avec Un Seul Fichier (Rétrocompatibilité)
```http
POST /api/achievements
Content-Type: multipart/form-data

{
  "title": "Mon Achievement",
  "description": "Description",
  "file": file
}
```

#### Télécharger un Fichier d'Achievement
```http
GET /api/achievements/{id}/download?file_index=0
```

### 4. Validation

- **Types de fichiers supportés** : PDF, DOC, DOCX, JPEG, PNG, JPG, GIF, SVG, WEBP
- **Taille maximale** : 2MB par fichier
- **Nombre de fichiers** : Illimité (limité par la validation du serveur)

### 5. Rétrocompatibilité

L'implémentation maintient une compatibilité complète avec l'ancien système :
- Les achievements existants avec `file_path` continuent de fonctionner
- L'API accepte toujours le champ `file` pour un seul fichier
- Migration automatique des données existantes

## Services - Fonctionnalités Existantes

Les services supportaient déjà les fichiers multiples avec la même structure :

### Format des Fichiers Services
```json
{
  "files": [
    {
      "path": "service_offer_files/image.jpg",
      "original_name": "portfolio_image.jpg",
      "mime_type": "image/jpeg",
      "size": 54321
    }
  ]
}
```

### API Endpoints Services
```http
POST /api/service-offers
GET /api/service-offers/{id}/download?file_index=0
```

## Exemples d'Utilisation

### Frontend - Upload Multiple Files pour Achievement

```javascript
const formData = new FormData();
formData.append('title', 'Mon Achievement');
formData.append('description', 'Description');

// Ajouter plusieurs fichiers
files.forEach((file, index) => {
  formData.append('files[]', file);
});

fetch('/api/achievements', {
  method: 'POST',
  body: formData,
  headers: {
    'Authorization': 'Bearer ' + token
  }
});
```

### Frontend - Afficher les Fichiers

```javascript
// Pour un achievement avec plusieurs fichiers
achievement.files?.forEach((file, index) => {
  const downloadUrl = `/api/achievements/${achievement.id}/download?file_index=${index}`;
  // Créer un lien de téléchargement
});

// Pour un achievement avec l'ancien format
if (achievement.file_path && !achievement.files) {
  const downloadUrl = `/api/achievements/${achievement.id}/download?file_index=0`;
}
```

## Tests

### Test de Création
```bash
# Via Tinker
php artisan tinker
$achievement = new App\Models\Achievement([
  'title' => 'Test',
  'files' => [
    ['path' => 'test.jpg', 'original_name' => 'test.jpg', 'mime_type' => 'image/jpeg', 'size' => 12345]
  ]
]);
```

### Test API avec cURL
```bash
curl -X POST http://localhost:8000/api/achievements \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "title=Test Achievement" \
  -F "description=Test Description" \
  -F "files[]=@/path/to/file1.jpg" \
  -F "files[]=@/path/to/file2.pdf"
```

## Migration et Déploiement

1. **Exécuter les migrations** :
   ```bash
   php artisan migrate
   ```

2. **Vérifier la migration** :
   - Les données existantes sont automatiquement migrées
   - Aucune perte de données
   - Compatibilité maintenue

3. **Tests recommandés** :
   - Créer un achievement avec plusieurs fichiers
   - Vérifier le téléchargement des fichiers
   - Tester la rétrocompatibilité

## Avantages

1. **Flexibilité** : Les utilisateurs peuvent ajouter plusieurs preuves/photos
2. **Rétrocompatibilité** : Aucune rupture avec l'existant
3. **Cohérence** : Même système que les services
4. **Extensibilité** : Facile d'ajouter de nouvelles fonctionnalités

## Prochaines Étapes Suggérées

1. Mettre à jour l'interface utilisateur pour supporter l'upload multiple
2. Ajouter une prévisualisation des images
3. Implémenter la réorganisation des fichiers (drag & drop)
4. Ajouter des métadonnées supplémentaires (descriptions par fichier)
