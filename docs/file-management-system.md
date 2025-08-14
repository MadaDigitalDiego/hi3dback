# 📁 Système de Gestion de Fichiers avec SwissTransfer

## 🎯 Vue d'ensemble

Le système de gestion de fichiers Hi3D offre un upload intelligent qui choisit automatiquement entre le stockage local et SwissTransfer selon la taille des fichiers.

### ✨ Fonctionnalités Principales

- **Upload Intelligent** : Fichiers < 10MB → Stockage local, > 10MB → SwissTransfer
- **Support Multi-fichiers** : Upload de plusieurs fichiers simultanément
- **Validation Avancée** : Types MIME, taille, sécurité
- **Barre de Progression** : Suivi en temps réel des uploads
- **Gestion d'Expiration** : Nettoyage automatique des fichiers SwissTransfer expirés
- **Relations Polymorphiques** : Attachement aux modèles (Achievement, ServiceOffer, etc.)

## 🏗️ Architecture

### Modèles

#### File Model
```php
// Champs principaux
- original_name: Nom original du fichier
- filename: Nom généré unique
- mime_type: Type MIME
- size: Taille en bytes
- extension: Extension du fichier
- storage_type: 'local' ou 'swisstransfer'
- status: 'uploading', 'completed', 'failed', 'expired'

// Stockage local
- local_path: Chemin dans storage/app/public

// Stockage SwissTransfer
- swisstransfer_url: URL de partage
- swisstransfer_download_url: URL de téléchargement
- swisstransfer_delete_url: URL de suppression
- swisstransfer_expires_at: Date d'expiration

// Relations
- user_id: Propriétaire du fichier
- fileable_type/fileable_id: Relation polymorphique
```

### Services

#### FileManagerService
- **uploadFile()** : Upload intelligent d'un fichier
- **uploadMultipleFiles()** : Upload de plusieurs fichiers
- **deleteFile()** : Suppression sécurisée
- **getDownloadUrl()** : Génération d'URL de téléchargement
- **checkExpiredFiles()** : Nettoyage des fichiers expirés
- **getStorageStats()** : Statistiques de stockage

#### SwissTransferService
- **uploadFile()** : Upload vers SwissTransfer
- **deleteFile()** : Suppression depuis SwissTransfer
- **checkFileAvailability()** : Vérification de disponibilité
- **getFileInfo()** : Informations sur un fichier

## 🔧 Configuration

### Variables d'Environnement

```env
# Système de fichiers
FILE_LOCAL_STORAGE_LIMIT=10          # MB - Limite pour stockage local
FILE_MAX_UPLOAD_SIZE=500             # MB - Taille max d'upload
FILE_ALLOWED_MIME_TYPES="image/jpeg,image/png,application/pdf,..."

# SwissTransfer
SWISSTRANSFER_ENABLED=true
SWISSTRANSFER_BASE_URL=https://www.swisstransfer.com
SWISSTRANSFER_API_URL=https://www.swisstransfer.com/api
SWISSTRANSFER_MAX_FILE_SIZE=50000    # MB
SWISSTRANSFER_TIMEOUT=300            # secondes
```

### Types MIME Autorisés par Défaut

- **Images** : jpeg, png, gif, webp
- **Documents** : pdf, doc, docx, xls, xlsx
- **Archives** : zip, rar
- **Texte** : txt

## 📡 API Endpoints

### Upload de Fichiers

```http
POST /api/files/upload
Authorization: Bearer {token}
Content-Type: multipart/form-data

# Paramètres
files[]: File[]                    # Fichiers à uploader (requis)
fileable_type: string             # Type de modèle parent (optionnel)
fileable_id: integer              # ID du modèle parent (optionnel)
options[message]: string          # Message pour SwissTransfer (optionnel)
options[email_recipients]: string # Destinataires email (optionnel)
options[download_limit]: integer  # Limite de téléchargements (optionnel)
options[expiration_days]: integer # Jours avant expiration (optionnel)
```

**Réponse Succès (Single File):**
```json
{
  "success": true,
  "message": "File uploaded successfully",
  "data": {
    "id": 1,
    "original_name": "document.pdf",
    "filename": "document_abc123.pdf",
    "mime_type": "application/pdf",
    "size": 2048576,
    "human_size": "2.05 MB",
    "extension": "pdf",
    "storage_type": "local",
    "status": "completed",
    "download_url": "http://localhost/storage/uploads/document_abc123.pdf",
    "expires_at": null,
    "is_expired": false,
    "created_at": "2025-07-21T13:30:00.000Z",
    "updated_at": "2025-07-21T13:30:00.000Z"
  }
}
```

**Réponse Succès (Multiple Files):**
```json
{
  "success": true,
  "message": "Uploaded 2 of 3 files",
  "data": {
    "files": [...],
    "errors": [
      {
        "index": 2,
        "filename": "large_file.zip",
        "error": "File size exceeds maximum allowed size"
      }
    ],
    "statistics": {
      "total": 3,
      "successful": 2,
      "failed": 1
    }
  }
}
```

### Gestion des Fichiers

```http
# Liste des fichiers de l'utilisateur
GET /api/files?status=completed&storage_type=local&per_page=20
Authorization: Bearer {token}

# Détails d'un fichier
GET /api/files/{id}
Authorization: Bearer {token}

# URL de téléchargement
GET /api/files/{id}/download
Authorization: Bearer {token}

# Suppression
DELETE /api/files/{id}
Authorization: Bearer {token}

# Statistiques (admin seulement)
GET /api/files/admin/stats
Authorization: Bearer {token}
```

## 🔒 Sécurité

### Validation des Fichiers

1. **Taille** : Vérification contre FILE_MAX_UPLOAD_SIZE
2. **Type MIME** : Validation contre la liste autorisée
3. **Extension** : Vérification de cohérence avec le type MIME
4. **Contenu** : Validation que le fichier n'est pas corrompu

### Contrôle d'Accès

- **Propriétaire** : Accès complet à ses fichiers
- **Admin/Super-admin** : Accès à tous les fichiers
- **Autres** : Aucun accès par défaut

### Protection contre les Attaques

- **Rate Limiting** : Limitation des uploads par utilisateur
- **Validation stricte** : Types MIME et extensions
- **Noms de fichiers sécurisés** : Génération automatique
- **Isolation** : Stockage dans des répertoires sécurisés

## 🚀 Utilisation

### Upload Simple

```javascript
const formData = new FormData();
formData.append('files[]', file);

fetch('/api/files/upload', {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${token}`,
  },
  body: formData
})
.then(response => response.json())
.then(data => console.log(data));
```

### Upload avec Relation

```javascript
const formData = new FormData();
formData.append('files[]', file);
formData.append('fileable_type', 'App\\Models\\Achievement');
formData.append('fileable_id', '123');

// Upload vers SwissTransfer avec options
formData.append('options[message]', 'Fichiers du projet');
formData.append('options[expiration_days]', '30');
```

### Barre de Progression

```javascript
const xhr = new XMLHttpRequest();

xhr.upload.addEventListener('progress', (e) => {
  if (e.lengthComputable) {
    const percentComplete = (e.loaded / e.total) * 100;
    updateProgressBar(percentComplete);
  }
});

xhr.open('POST', '/api/files/upload');
xhr.setRequestHeader('Authorization', `Bearer ${token}`);
xhr.send(formData);
```

## 🛠️ Maintenance

### Commande de Nettoyage

```bash
# Nettoyer les fichiers expirés
php artisan files:clean-expired

# Programmer dans le cron (daily)
# 0 2 * * * cd /path/to/project && php artisan files:clean-expired
```

### Monitoring

```php
// Obtenir les statistiques
$stats = app(FileManagerService::class)->getStorageStats();

// Résultat
[
  'total_files' => 1250,
  'local_files' => 800,
  'swisstransfer_files' => 450,
  'completed_files' => 1200,
  'failed_files' => 30,
  'expired_files' => 20,
  'total_size_bytes' => 5368709120,
  'local_size_bytes' => 1073741824,
  'swisstransfer_size_bytes' => 4294967296
]
```

## 🔄 Intégration avec les Modèles Existants

### Achievement avec Fichiers

```php
// Dans le modèle Achievement
public function files()
{
    return $this->morphMany(File::class, 'fileable');
}

// Upload pour un achievement
$achievement = Achievement::find(1);
$file = $fileManagerService->uploadFile($uploadedFile, $user, $achievement);
```

### ServiceOffer avec Fichiers

```php
// Dans le modèle ServiceOffer
public function files()
{
    return $this->morphMany(File::class, 'fileable');
}
```

## 📊 Métriques et Analytics

- **Volume de stockage** par type
- **Taux de succès** des uploads
- **Utilisation SwissTransfer** vs local
- **Fichiers expirés** par période
- **Top types de fichiers** uploadés

## 🔧 Intégration React

### Composant d'Upload avec Barre de Progression

```jsx
import React, { useState } from 'react';
import axios from 'axios';

const FileUploader = ({ onUploadComplete, fileableType, fileableId }) => {
  const [files, setFiles] = useState([]);
  const [uploading, setUploading] = useState(false);
  const [progress, setProgress] = useState(0);

  const handleFileSelect = (e) => {
    setFiles(Array.from(e.target.files));
  };

  const uploadFiles = async () => {
    if (files.length === 0) return;

    setUploading(true);
    setProgress(0);

    const formData = new FormData();
    files.forEach(file => {
      formData.append('files[]', file);
    });

    if (fileableType && fileableId) {
      formData.append('fileable_type', fileableType);
      formData.append('fileable_id', fileableId);
    }

    try {
      const response = await axios.post('/api/files/upload', formData, {
        headers: {
          'Content-Type': 'multipart/form-data',
          'Authorization': `Bearer ${localStorage.getItem('token')}`
        },
        onUploadProgress: (progressEvent) => {
          const percentCompleted = Math.round(
            (progressEvent.loaded * 100) / progressEvent.total
          );
          setProgress(percentCompleted);
        }
      });

      onUploadComplete(response.data);
      setFiles([]);
      setProgress(0);
    } catch (error) {
      console.error('Upload failed:', error);
    } finally {
      setUploading(false);
    }
  };

  return (
    <div className="file-uploader">
      <input
        type="file"
        multiple
        onChange={handleFileSelect}
        disabled={uploading}
        accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.zip"
      />

      {files.length > 0 && (
        <div className="file-list">
          {files.map((file, index) => (
            <div key={index} className="file-item">
              <span>{file.name}</span>
              <span>{(file.size / 1024 / 1024).toFixed(2)} MB</span>
              <span>{file.size > 10 * 1024 * 1024 ? 'SwissTransfer' : 'Local'}</span>
            </div>
          ))}
        </div>
      )}

      {uploading && (
        <div className="progress-bar">
          <div
            className="progress-fill"
            style={{ width: `${progress}%` }}
          />
          <span>{progress}%</span>
        </div>
      )}

      <button
        onClick={uploadFiles}
        disabled={uploading || files.length === 0}
      >
        {uploading ? 'Uploading...' : 'Upload Files'}
      </button>
    </div>
  );
};

export default FileUploader;
```

### Hook personnalisé pour la gestion des fichiers

```jsx
import { useState, useEffect } from 'react';
import axios from 'axios';

export const useFileManager = () => {
  const [files, setFiles] = useState([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  const fetchFiles = async (filters = {}) => {
    setLoading(true);
    try {
      const params = new URLSearchParams(filters);
      const response = await axios.get(`/api/files?${params}`, {
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('token')}`
        }
      });
      setFiles(response.data.data.files);
    } catch (err) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  };

  const deleteFile = async (fileId) => {
    try {
      await axios.delete(`/api/files/${fileId}`, {
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('token')}`
        }
      });
      setFiles(files.filter(file => file.id !== fileId));
    } catch (err) {
      setError(err.message);
    }
  };

  const getDownloadUrl = async (fileId) => {
    try {
      const response = await axios.get(`/api/files/${fileId}/download`, {
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('token')}`
        }
      });
      return response.data.data.download_url;
    } catch (err) {
      setError(err.message);
      return null;
    }
  };

  return {
    files,
    loading,
    error,
    fetchFiles,
    deleteFile,
    getDownloadUrl
  };
};
```

---

## 🎉 Avantages du Système

✅ **Économie de stockage** : Gros fichiers sur SwissTransfer
✅ **Performance** : Petits fichiers en local pour un accès rapide
✅ **Scalabilité** : Pas de limite de stockage avec SwissTransfer
✅ **Sécurité** : Validation stricte et contrôle d'accès
✅ **Maintenance** : Nettoyage automatique des fichiers expirés
✅ **Flexibilité** : Support de tous types de fichiers
✅ **UX optimisée** : Barre de progression et feedback temps réel
✅ **Intégration facile** : API REST standard et composants React
