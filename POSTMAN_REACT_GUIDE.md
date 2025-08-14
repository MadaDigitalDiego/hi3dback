# Guide Complet : Tests Postman et Implémentation React

## 📋 Table des Matières
1. [Tests Postman pour Achievements](#tests-postman-achievements)
2. [Tests Postman pour Services](#tests-postman-services)
3. [Implémentation React pour Achievements](#react-achievements)
4. [Implémentation React pour Services](#react-services)
5. [Composants Réutilisables](#composants-reutilisables)

---

## 🧪 Tests Postman pour Achievements {#tests-postman-achievements}

### 1. Authentification (Prérequis)

```http
POST {{base_url}}/api/login
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "password"
}
```

**Réponse attendue :**
```json
{
  "success": true,
  "user": {
    "id": 1,
    "email": "user@example.com",
    "name": "User Name"
  },
  "token": "1|abc123def456...",
  "message": "Connexion réussie"
}
```

### 2. Créer un Achievement avec Plusieurs Fichiers

```http
POST {{base_url}}/api/achievements
Authorization: Bearer {{token}}
Content-Type: multipart/form-data

Form Data:
- title: "Certification AWS Solutions Architect"
- organization: "Amazon Web Services"
- date_obtained: "2024-01-15"
- description: "Certification professionnelle en architecture cloud"
- achievement_url: "https://aws.amazon.com/certification/"
- files[]: [Sélectionner fichier 1 - certificate.pdf]
- files[]: [Sélectionner fichier 2 - badge.png]
- files[]: [Sélectionner fichier 3 - verification.jpg]
```

**Réponse attendue :**
```json
{
    "achievement": {
        "title": "Certification AWS Solutions Architect",
        "organization": "Amazon Web Services",
        "description": "Certification professionnelle en architecture cloud",
        "achievement_url": "https://aws.amazon.com/certification/",
        "files": [
            {
                "path": "achievement_files/vuzxBkwhxxLGOKDbFvX4ofofFpU73o6OEMQL409J.png",
                "original_name": "Screenshot from 2025-05-28 23-37-01.png",
                "mime_type": "image/png",
                "size": 15791
            },
            {
                "path": "achievement_files/ImzoNUNWK2xiWwiJlqhXZcXz9wzmrV5fcUdRO6X3.png",
                "original_name": "Screenshot from 2025-05-14 21-26-46.png",
                "mime_type": "image/png",
                "size": 190876
            },
            {
                "path": "achievement_files/BlmE5KnbYAoJXOjGq3CBBnv5GsWFFhxJ2b52g4mv.png",
                "original_name": "Screenshot from 2025-05-15 09-36-57.png",
                "mime_type": "image/png",
                "size": 25732
            }
        ],
        "professional_profile_id": 5,
        "updated_at": "2025-05-29T08:44:27.000000Z",
        "created_at": "2025-05-29T08:44:27.000000Z",
        "id": 10
    },
    "message": "Réalisation/Certification ajoutée avec succès."
}
```

### 3. Créer un Achievement avec Un Seul Fichier (Rétrocompatibilité)

```http
POST {{base_url}}/api/achievements
Authorization: Bearer {{token}}
Content-Type: multipart/form-data

Form Data:
- title: "Formation React Advanced"
- organization: "Tech Academy"
- date_obtained: "2024-01-10"
- description: "Formation avancée en React.js"
- file: [Sélectionner un fichier - diploma.pdf]
```

**Réponse attendue :**
```json
{
  "achievement": {
    "id": 16,
    "professional_profile_id": 5,
    "title": "Formation React Advanced",
    "organization": "Tech Academy",
    "date_obtained": "2024-01-10",
    "description": "Formation avancée en React.js",
    "files": [
      {
        "path": "achievement_files/jkl012_diploma.pdf",
        "original_name": "diploma.pdf",
        "mime_type": "application/pdf",
        "size": 198765
      }
    ],
    "achievement_url": null,
    "created_at": "2024-01-20T10:35:00.000000Z",
    "updated_at": "2024-01-20T10:35:00.000000Z"
  },
  "message": "Réalisation/Certification ajoutée avec succès."
}
```

### 4. Lister les Achievements

```http
GET {{base_url}}/api/achievements
Authorization: Bearer {{token}}
```

**Réponse attendue :**
```json
{
    "achievements": [
        {
            "id": 5,
            "title": "aaaaaaaaaaaaaaaaaaaaaa",
            "organization": "Amazon Web Services",
            "date_obtained": null,
            "description": "vvvvvvvvvvvvvvvvv",
            "file_path": null,
            "files": [
                {
                    "path": "achievement_files/EtftXocNIAWzuOrGf4LlAOIfcXz14GeQ3RpLSvcj.png",
                    "size": 23428,
                    "mime_type": "image/png",
                    "original_name": "Screenshot from 2025-05-15 15-40-31.png"
                }
            ],
            "achievement_url": "https://aws.amazon.com/certification/",
            "created_at": "2025-05-29T06:40:33.000000Z",
            "updated_at": "2025-05-29T08:31:18.000000Z",
            "professional_profile_id": 5
        },
        {
            "id": 6,
            "title": "Certification AWS Solutions Architect",
            "organization": "Amazon Web Services",
            "date_obtained": null,
            "description": "Certification professionnelle en architecture cloud",
            "file_path": null,
            "files": [
                {
                    "path": "achievement_files/kz7nyMEwCh0C9Ltpc2VrZNFzhLTKhPuR10Nq1TN0.png",
                    "size": 15791,
                    "mime_type": "image/png",
                    "original_name": "Screenshot from 2025-05-28 23-37-01.png"
                },
                {
                    "path": "achievement_files/uvIrWiIOW7IqF39wcAg26yRrE3mdxknEoK6is16C.png",
                    "size": 190876,
                    "mime_type": "image/png",
                    "original_name": "Screenshot from 2025-05-14 21-26-46.png"
                },
                {
                    "path": "achievement_files/bMEKTzKl1ZYkDsGTjtpB5C8iTX9R72pZh83DQLlX.png",
                    "size": 25732,
                    "mime_type": "image/png",
                    "original_name": "Screenshot from 2025-05-15 09-36-57.png"
                }
            ],
            "achievement_url": "https://aws.amazon.com/certification/",
            "created_at": "2025-05-29T06:41:21.000000Z",
            "updated_at": "2025-05-29T06:41:21.000000Z",
            "professional_profile_id": 5
        },
        {
            "id": 7,
            "title": "Certification AWS Solutions Architect",
            "organization": "Amazon Web Services",
            "date_obtained": null,
            "description": "Certification professionnelle en architecture cloud",
            "file_path": null,
            "files": [
                {
                    "path": "achievement_files/44uVY2L1ikhDyXj57vcYOUa6pWscFMHnNO39jDRV.png",
                    "size": 15791,
                    "mime_type": "image/png",
                    "original_name": "Screenshot from 2025-05-28 23-37-01.png"
                },
                {
                    "path": "achievement_files/sXNxh6qsADEM3tbLtxr4icy4Tk0HQgIN0bnBE9Dj.png",
                    "size": 190876,
                    "mime_type": "image/png",
                    "original_name": "Screenshot from 2025-05-14 21-26-46.png"
                },
                {
                    "path": "achievement_files/2pXzWAUhdqxMdowqe5UylYBCYuC3qAOUHLpTcDWh.png",
                    "size": 25732,
                    "mime_type": "image/png",
                    "original_name": "Screenshot from 2025-05-15 09-36-57.png"
                }
            ],
            "achievement_url": "https://aws.amazon.com/certification/",
            "created_at": "2025-05-29T08:26:46.000000Z",
            "updated_at": "2025-05-29T08:26:46.000000Z",
            "professional_profile_id": 5
        },
        {
            "id": 8,
            "title": "aaaaaaaaaaaaaaaaaaaaaa",
            "organization": "Amazon Web Services",
            "date_obtained": null,
            "description": "vvvvvvvvvvvvvvvvv",
            "file_path": null,
            "files": [
                {
                    "path": "achievement_files/o97OG49fDg6JuKsIaeLxbQH0Oh7WeMT0ZTpK1qtZ.png",
                    "size": 23428,
                    "mime_type": "image/png",
                    "original_name": "Screenshot from 2025-05-15 15-40-31.png"
                }
            ],
            "achievement_url": "https://aws.amazon.com/certification/",
            "created_at": "2025-05-29T08:29:03.000000Z",
            "updated_at": "2025-05-29T08:32:00.000000Z",
            "professional_profile_id": 5
        },
        {
            "id": 10,
            "title": "Certification AWS Solutions Architect",
            "organization": "Amazon Web Services",
            "date_obtained": null,
            "description": "Certification professionnelle en architecture cloud",
            "file_path": null,
            "files": [
                {
                    "path": "achievement_files/vuzxBkwhxxLGOKDbFvX4ofofFpU73o6OEMQL409J.png",
                    "size": 15791,
                    "mime_type": "image/png",
                    "original_name": "Screenshot from 2025-05-28 23-37-01.png"
                },
                {
                    "path": "achievement_files/ImzoNUNWK2xiWwiJlqhXZcXz9wzmrV5fcUdRO6X3.png",
                    "size": 190876,
                    "mime_type": "image/png",
                    "original_name": "Screenshot from 2025-05-14 21-26-46.png"
                },
                {
                    "path": "achievement_files/BlmE5KnbYAoJXOjGq3CBBnv5GsWFFhxJ2b52g4mv.png",
                    "size": 25732,
                    "mime_type": "image/png",
                    "original_name": "Screenshot from 2025-05-15 09-36-57.png"
                }
            ],
            "achievement_url": "https://aws.amazon.com/certification/",
            "created_at": "2025-05-29T08:44:27.000000Z",
            "updated_at": "2025-05-29T08:44:27.000000Z",
            "professional_profile_id": 5
        }
    ]
}
```

### 5. Télécharger un Fichier d'Achievement

```http
GET {{base_url}}/api/achievements/15/download?file_index=0
Authorization: Bearer {{token}}
```

**Réponse attendue :**
- **Headers :**
  - `Content-Type: application/pdf`
  - `Content-Disposition: attachment; filename="certificate.pdf"`
- **Body :** Contenu binaire du fichier

### 6. Mettre à Jour un Achievement

```http
POST {{base_url}}/api/achievements/15
Authorization: Bearer {{token}}
Content-Type: multipart/form-data

Form Data:
- title: "Certification AWS Solutions Architect - Updated"
- description: "Certification mise à jour avec nouvelles compétences"
- files[]: [Nouveau fichier 1]
- files[]: [Nouveau fichier 2]
```

**Réponse attendue :**
```json
{
    "achievement": {
        "id": 8,
        "title": "aaaaaaaaaaaaaaaaaaaaaa",
        "organization": "Amazon Web Services",
        "date_obtained": null,
        "description": "vvvvvvvvvvvvvvvvv",
        "file_path": null,
        "files": [
            {
                "path": "achievement_files/2gAv9OogdjUZo4bRZRpNxjXrGn3zgMuxkv5QCxRJ.png",
                "original_name": "Screenshot from 2025-05-15 15-40-31.png",
                "mime_type": "image/png",
                "size": 23428
            }
        ],
        "achievement_url": "https://aws.amazon.com/certification/",
        "created_at": "2025-05-29T08:29:03.000000Z",
        "updated_at": "2025-05-29T08:46:41.000000Z",
        "professional_profile_id": 5
    },
    "message": "Réalisation/Certification mise à jour avec succès."
}
```

### 7. Supprimer un Achievement

```http
DELETE {{base_url}}/api/achievements/15
Authorization: Bearer {{token}}
```

**Réponse attendue :**
```json
{
  "message": "Réalisation/Certification supprimée avec succès."
}
```

---

## 🧪 Tests Postman pour Services {#tests-postman-services}

### 1. Créer un Service avec Plusieurs Fichiers

```http
POST {{base_url}}/api/service-offers
Authorization: Bearer {{token}}
Content-Type: multipart/form-data

Form Data:
- title: "Développement Application Mobile"
- description: "Création d'applications mobiles iOS et Android"
- price: 2500.00
- execution_time: "4-6 semaines"
- concepts: "3 concepts inclus"
- revisions: "2 révisions gratuites"
- categories[]: "Développement Mobile"
- categories[]: "UI/UX Design"
- is_private: false
- status: "published"
- files[]: [Portfolio image 1]
- files[]: [Portfolio image 2]
- files[]: [Exemple de travail - PDF]
```

**Réponse attendue :**
```json
{
    "id": 3,
    "user_id": 7,
    "title": "Développement Application Mobile",
    "description": "Création d'applications mobiles iOS et Android",
    "price": "2500",
    "execution_time": "4-6 semaines",
    "concepts": "3 concepts inclus",
    "revisions": "2 révisions gratuites",
    "is_private": false,
    "categories": [
        "Développement Mobile",
        "UI/UX Design"
    ],
    "files": [
        {
            "path": "service_offer_files/nHkDnfeEIdyUirR6UdH5g86Mmp4gg2UWhaB66c4o.png",
            "original_name": "Screenshot from 2025-05-14 22-38-46.png",
            "mime_type": "image/png",
            "size": 213599
        },
        {
            "path": "service_offer_files/pSEkNYo0gYgUp9HxrmTSayeUco9KR4dSH438feVI.png",
            "original_name": "Screenshot from 2025-05-14 22-52-03.png",
            "mime_type": "image/png",
            "size": 190305
        },
        {
            "path": "service_offer_files/OKZxN9wAX6pNTl7yGycbIQGndUEjWVWFPr044kxr.png",
            "original_name": "Screenshot from 2025-05-15 10-06-37.png",
            "mime_type": "image/png",
            "size": 19030
        }
    ],
    "status": "published",
    "likes": null,
    "views": null,
    "created_at": "2025-05-29T10:04:49.000000Z",
    "updated_at": "2025-05-29T10:04:49.000000Z"
}
```

### 2. Lister les Services

```http
GET {{base_url}}/api/service-offers
Authorization: Bearer {{token}}
```

**Réponse attendue :**
```json
[
    {
        "id": 3,
        "user_id": 7,
        "user": {
            "id": 7,
            "first_name": "koko",
            "last_name": "koko",
            "email": "springboot455@gmail.com",
            "avatar": null,
            "is_professional": true
        },
        "title": "Développement Application Mobile",
        "description": "Création d'applications mobiles iOS et Android",
        "price": "2500.00",
        "execution_time": "4-6 semaines",
        "concepts": "3 concepts inclus",
        "revisions": "2 révisions gratuites",
        "is_private": false,
        "categories": [
            "Développement Mobile",
            "UI/UX Design"
        ],
        "files": [
            {
                "path": "service_offer_files/nHkDnfeEIdyUirR6UdH5g86Mmp4gg2UWhaB66c4o.png",
                "original_name": "Screenshot from 2025-05-14 22-38-46.png",
                "mime_type": "image/png",
                "size": 213599
            },
            {
                "path": "service_offer_files/pSEkNYo0gYgUp9HxrmTSayeUco9KR4dSH438feVI.png",
                "original_name": "Screenshot from 2025-05-14 22-52-03.png",
                "mime_type": "image/png",
                "size": 190305
            },
            {
                "path": "service_offer_files/OKZxN9wAX6pNTl7yGycbIQGndUEjWVWFPr044kxr.png",
                "original_name": "Screenshot from 2025-05-15 10-06-37.png",
                "mime_type": "image/png",
                "size": 19030
            }
        ],
        "status": "published",
        "likes": 0,
        "views": 0,
        "created_at": "2025-05-29T10:04:49.000000Z",
        "updated_at": "2025-05-29T10:04:49.000000Z"
    },
    {
        "id": 2,
        "user_id": 1,
        "user": {
            "id": 1,
            "first_name": "Marie Derica",
            "last_name": "SOAZANAHSINA",
            "email": "dericasoazanahasinamarie@gmail.com",
            "avatar": null,
            "is_professional": true
        },
        "title": "dqfdgggdfgd",
        "description": "fdgdgfdgfdgfg",
        "price": "233.00",
        "execution_time": "D'ici 1 à 2 semaines",
        "concepts": "2 concepts",
        "revisions": "2 révisions",
        "is_private": false,
        "categories": [
            "modeling",
            "animation"
        ],
        "files": [
            {
                "path": "service_offer_files/XAd5Mm9VpZ3ngrj9IV1Zl12qkKrGqexjPjtANEIy.jpg",
                "original_name": "WhatsApp Image 2025-05-09 at 12.42.13_52c0aa90.jpg",
                "mime_type": "image/jpeg",
                "size": 78492
            }
        ],
        "status": "published",
        "likes": 0,
        "views": 0,
        "created_at": "2025-05-12T12:22:49.000000Z",
        "updated_at": "2025-05-12T12:22:49.000000Z"
    },
    {
        "id": 1,
        "user_id": 1,
        "user": {
            "id": 1,
            "first_name": "Marie Derica",
            "last_name": "SOAZANAHSINA",
            "email": "dericasoazanahasinamarie@gmail.com",
            "avatar": null,
            "is_professional": true
        },
        "title": "ksdjhfkjdsh f",
        "description": "sdklfj ojdlksjf lksd",
        "price": "500.00",
        "execution_time": "D'ici 1 à 2 semaines",
        "concepts": "2 concepts",
        "revisions": "3 révisions",
        "is_private": false,
        "categories": [
            "modeling",
            "animation",
            "architectural",
            "product",
            "character",
            "environment"
        ],
        "files": [
            {
                "path": "service_offer_files/baxLRKXUCm3zTAuU2CFabRRlqRNh2pLUwzTNe7PI.png",
                "original_name": "Logo de l'AJBA Antsirabe.png",
                "mime_type": "image/png",
                "size": 2415015
            }
        ],
        "status": "published",
        "likes": 0,
        "views": 0,
        "created_at": "2025-05-09T11:34:32.000000Z",
        "updated_at": "2025-05-09T11:34:32.000000Z"
    }
]
```

### 3. Télécharger un Fichier de Service

```http
GET {{base_url}}/api/service-offers/25/download?file_index=0
Authorization: Bearer {{token}}
```
**Réponse attendue :**
- **Body :** Contenu binaire du fichier
---


### 4. Supprimer un Fichier de Service
```http
DELETE {{base_url}}/api/service-offers/2
Authorization: Bearer {{token}}
```

**Réponse attendue :**
```json
{
    "message": "Offre de service supprimée avec succès."
}
```


### 5. Modofier un Fichier de Service
```http
POST {{base_url}}/api/service-offers/3
Authorization: Bearer {{token}}
Content-Type: multipart/form-data
Accept: application/json
```

**Réponse attendue :**
```json
{
    "id": 3,
    "user_id": 7,
    "title": "Développement Application Mobile",
    "description": "bbbbbbbbbbbb",
    "price": "2500.00",
    "execution_time": "4-6 semaines",
    "concepts": "3 concepts inclus",
    "revisions": "2 révisions gratuites",
    "is_private": false,
    "categories": [
        "Développement Mobile",
        "UI/UX Design"
    ],
    "files": [
        {
            "path": "service_offer_files/nHkDnfeEIdyUirR6UdH5g86Mmp4gg2UWhaB66c4o.png",
            "original_name": "Screenshot from 2025-05-14 22-38-46.png",
            "mime_type": "image/png",
            "size": 213599
        },
        {
            "path": "service_offer_files/pSEkNYo0gYgUp9HxrmTSayeUco9KR4dSH438feVI.png",
            "original_name": "Screenshot from 2025-05-14 22-52-03.png",
            "mime_type": "image/png",
            "size": 190305
        },
        {
            "path": "service_offer_files/OKZxN9wAX6pNTl7yGycbIQGndUEjWVWFPr044kxr.png",
            "original_name": "Screenshot from 2025-05-15 10-06-37.png",
            "mime_type": "image/png",
            "size": 19030
        }
    ],
    "status": "published",
    "likes": 0,
    "views": 0,
    "created_at": "2025-05-29T10:04:49.000000Z",
    "updated_at": "2025-05-30T09:18:40.000000Z"
}
```

## ⚛️ Implémentation React pour Achievements {#react-achievements}

### 1. Hook pour Gérer les Achievements

```jsx
// hooks/useAchievements.js
import { useState, useEffect } from 'react';
import { achievementService } from '../services/achievementService';

export const useAchievements = () => {
  const [achievements, setAchievements] = useState([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  const fetchAchievements = async () => {
    setLoading(true);
    try {
      const response = await achievementService.getAll();
      setAchievements(response.data.achievements);
      setError(null);
    } catch (err) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  };

  const createAchievement = async (formData) => {
    setLoading(true);
    try {
      const response = await achievementService.create(formData);
      setAchievements(prev => [...prev, response.data.achievement]);
      setError(null);
      return response.data;
    } catch (err) {
      setError(err.message);
      throw err;
    } finally {
      setLoading(false);
    }
  };

  const updateAchievement = async (id, formData) => {
    setLoading(true);
    try {
      const response = await achievementService.update(id, formData);
      setAchievements(prev =>
        prev.map(achievement =>
          achievement.id === id ? response.data.achievement : achievement
        )
      );
      setError(null);
      return response.data;
    } catch (err) {
      setError(err.message);
      throw err;
    } finally {
      setLoading(false);
    }
  };

  const deleteAchievement = async (id) => {
    setLoading(true);
    try {
      await achievementService.delete(id);
      setAchievements(prev => prev.filter(achievement => achievement.id !== id));
      setError(null);
    } catch (err) {
      setError(err.message);
      throw err;
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchAchievements();
  }, []);

  return {
    achievements,
    loading,
    error,
    createAchievement,
    updateAchievement,
    deleteAchievement,
    refetch: fetchAchievements
  };
};
```

### 2. Service API pour Achievements

```jsx
// services/achievementService.js
import axios from 'axios';

const API_BASE_URL = process.env.REACT_APP_API_URL || 'http://localhost:8000/api';

const api = axios.create({
  baseURL: API_BASE_URL,
});

// Intercepteur pour ajouter le token d'authentification
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('auth_token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

export const achievementService = {
  getAll: () => api.get('/achievements'),

  getById: (id) => api.get(`/achievements/${id}`),

  create: (formData) => api.post('/achievements', formData, {
    headers: {
      'Content-Type': 'multipart/form-data',
    },
  }),

  update: (id, formData) => api.put(`/achievements/${id}`, formData, {
    headers: {
      'Content-Type': 'multipart/form-data',
    },
  }),

  delete: (id) => api.delete(`/achievements/${id}`),

  downloadFile: (id, fileIndex = 0) => {
    return api.get(`/achievements/${id}/download?file_index=${fileIndex}`, {
      responseType: 'blob',
    });
  },
};

// Fonction utilitaire pour télécharger un fichier
export const downloadAchievementFile = async (achievementId, fileIndex, fileName) => {
  try {
    const response = await achievementService.downloadFile(achievementId, fileIndex);
    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', fileName);
    document.body.appendChild(link);
    link.click();
    link.remove();
    window.URL.revokeObjectURL(url);
  } catch (error) {
    console.error('Erreur lors du téléchargement:', error);
    throw error;
  }
};
```

### 3. Composant de Formulaire pour Achievement

```jsx
// components/AchievementForm.jsx
import React, { useState } from 'react';
import { useAchievements } from '../hooks/useAchievements';

const AchievementForm = ({ achievement = null, onSuccess, onCancel }) => {
  const { createAchievement, updateAchievement, loading } = useAchievements();
  const [formData, setFormData] = useState({
    title: achievement?.title || '',
    organization: achievement?.organization || '',
    date_obtained: achievement?.date_obtained || '',
    description: achievement?.description || '',
    achievement_url: achievement?.achievement_url || '',
  });
  const [files, setFiles] = useState([]);
  const [errors, setErrors] = useState({});

  const handleInputChange = (e) => {
    const { name, value } = e.target;
    setFormData(prev => ({
      ...prev,
      [name]: value
    }));
  };

  const handleFileChange = (e) => {
    const selectedFiles = Array.from(e.target.files);
    setFiles(selectedFiles);
  };

  const validateForm = () => {
    const newErrors = {};

    if (!formData.title.trim()) {
      newErrors.title = 'Le titre est requis';
    }

    if (files.length === 0 && !achievement) {
      newErrors.files = 'Au moins un fichier est requis';
    }

    // Validation des fichiers
    files.forEach((file, index) => {
      const maxSize = 2 * 1024 * 1024; // 2MB
      const allowedTypes = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'image/jpeg',
        'image/png',
        'image/jpg',
        'image/gif',
        'image/svg+xml',
        'image/webp'
      ];

      if (file.size > maxSize) {
        newErrors[`file_${index}`] = `Le fichier ${file.name} dépasse 2MB`;
      }

      if (!allowedTypes.includes(file.type)) {
        newErrors[`file_${index}`] = `Type de fichier non supporté: ${file.name}`;
      }
    });

    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const handleSubmit = async (e) => {
    e.preventDefault();

    if (!validateForm()) {
      return;
    }

    const submitData = new FormData();

    // Ajouter les données du formulaire
    Object.keys(formData).forEach(key => {
      if (formData[key]) {
        submitData.append(key, formData[key]);
      }
    });

    // Ajouter les fichiers
    files.forEach((file) => {
      submitData.append('files[]', file);
    });

    try {
      if (achievement) {
        await updateAchievement(achievement.id, submitData);
      } else {
        await createAchievement(submitData);
      }
      onSuccess?.();
    } catch (error) {
      console.error('Erreur lors de la soumission:', error);
    }
  };

  return (
    <form onSubmit={handleSubmit} className="achievement-form">
      <div className="form-group">
        <label htmlFor="title">Titre *</label>
        <input
          type="text"
          id="title"
          name="title"
          value={formData.title}
          onChange={handleInputChange}
          className={errors.title ? 'error' : ''}
          required
        />
        {errors.title && <span className="error-message">{errors.title}</span>}
      </div>

      <div className="form-group">
        <label htmlFor="organization">Organisation</label>
        <input
          type="text"
          id="organization"
          name="organization"
          value={formData.organization}
          onChange={handleInputChange}
        />
      </div>

      <div className="form-group">
        <label htmlFor="date_obtained">Date d'obtention</label>
        <input
          type="date"
          id="date_obtained"
          name="date_obtained"
          value={formData.date_obtained}
          onChange={handleInputChange}
        />
      </div>

      <div className="form-group">
        <label htmlFor="description">Description</label>
        <textarea
          id="description"
          name="description"
          value={formData.description}
          onChange={handleInputChange}
          rows="4"
        />
      </div>

      <div className="form-group">
        <label htmlFor="achievement_url">URL de la réalisation</label>
        <input
          type="url"
          id="achievement_url"
          name="achievement_url"
          value={formData.achievement_url}
          onChange={handleInputChange}
          placeholder="https://..."
        />
      </div>

      <div className="form-group">
        <label htmlFor="files">
          Fichiers de preuve *
          <small>(PDF, DOC, DOCX, Images - Max 2MB par fichier)</small>
        </label>
        <input
          type="file"
          id="files"
          multiple
          onChange={handleFileChange}
          accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.gif,.svg,.webp"
          className={errors.files ? 'error' : ''}
        />
        {errors.files && <span className="error-message">{errors.files}</span>}

        {/* Affichage des fichiers sélectionnés */}
        {files.length > 0 && (
          <div className="selected-files">
            <h4>Fichiers sélectionnés:</h4>
            <ul>
              {files.map((file, index) => (
                <li key={index} className={errors[`file_${index}`] ? 'error' : ''}>
                  {file.name} ({(file.size / 1024 / 1024).toFixed(2)} MB)
                  {errors[`file_${index}`] && (
                    <span className="error-message">{errors[`file_${index}`]}</span>
                  )}
                </li>
              ))}
            </ul>
          </div>
        )}

        {/* Affichage des fichiers existants lors de la modification */}
        {achievement?.files && achievement.files.length > 0 && (
          <div className="existing-files">
            <h4>Fichiers actuels:</h4>
            <ul>
              {achievement.files.map((file, index) => (
                <li key={index}>
                  {file.original_name} ({(file.size / 1024 / 1024).toFixed(2)} MB)
                </li>
              ))}
            </ul>
            <small>Note: Sélectionner de nouveaux fichiers remplacera les fichiers existants</small>
          </div>
        )}
      </div>

      <div className="form-actions">
        <button type="button" onClick={onCancel} disabled={loading}>
          Annuler
        </button>
        <button type="submit" disabled={loading}>
          {loading ? 'Enregistrement...' : (achievement ? 'Mettre à jour' : 'Créer')}
        </button>
      </div>
    </form>
  );
};

export default AchievementForm;
```

### 4. Composant d'Affichage des Achievements

```jsx
// components/AchievementList.jsx
import React, { useState } from 'react';
import { useAchievements } from '../hooks/useAchievements';
import { downloadAchievementFile } from '../services/achievementService';
import AchievementForm from './AchievementForm';

const AchievementList = () => {
  const { achievements, loading, error, deleteAchievement } = useAchievements();
  const [editingAchievement, setEditingAchievement] = useState(null);
  const [showForm, setShowForm] = useState(false);

  const handleDownload = async (achievement, fileIndex) => {
    try {
      const fileName = achievement.files[fileIndex]?.original_name || `file_${fileIndex}`;
      await downloadAchievementFile(achievement.id, fileIndex, fileName);
    } catch (error) {
      alert('Erreur lors du téléchargement du fichier');
    }
  };

  const handleDelete = async (achievement) => {
    if (window.confirm(`Êtes-vous sûr de vouloir supprimer "${achievement.title}" ?`)) {
      try {
        await deleteAchievement(achievement.id);
      } catch (error) {
        alert('Erreur lors de la suppression');
      }
    }
  };

  const handleEdit = (achievement) => {
    setEditingAchievement(achievement);
    setShowForm(true);
  };

  const handleFormSuccess = () => {
    setShowForm(false);
    setEditingAchievement(null);
  };

  const handleFormCancel = () => {
    setShowForm(false);
    setEditingAchievement(null);
  };

  if (loading) return <div className="loading">Chargement...</div>;
  if (error) return <div className="error">Erreur: {error}</div>;

  return (
    <div className="achievement-list">
      <div className="header">
        <h2>Mes Réalisations</h2>
        <button
          onClick={() => setShowForm(true)}
          className="btn-primary"
        >
          Ajouter une réalisation
        </button>
      </div>

      {showForm && (
        <div className="modal">
          <div className="modal-content">
            <h3>{editingAchievement ? 'Modifier' : 'Ajouter'} une réalisation</h3>
            <AchievementForm
              achievement={editingAchievement}
              onSuccess={handleFormSuccess}
              onCancel={handleFormCancel}
            />
          </div>
        </div>
      )}

      <div className="achievements-grid">
        {achievements.map((achievement) => (
          <div key={achievement.id} className="achievement-card">
            <div className="achievement-header">
              <h3>{achievement.title}</h3>
              <div className="achievement-actions">
                <button onClick={() => handleEdit(achievement)}>Modifier</button>
                <button onClick={() => handleDelete(achievement)}>Supprimer</button>
              </div>
            </div>

            {achievement.organization && (
              <p className="organization">{achievement.organization}</p>
            )}

            {achievement.date_obtained && (
              <p className="date">Obtenu le: {new Date(achievement.date_obtained).toLocaleDateString()}</p>
            )}

            {achievement.description && (
              <p className="description">{achievement.description}</p>
            )}

            {achievement.achievement_url && (
              <a
                href={achievement.achievement_url}
                target="_blank"
                rel="noopener noreferrer"
                className="external-link"
              >
                Voir en ligne
              </a>
            )}

            {/* Affichage des fichiers */}
            {achievement.files && achievement.files.length > 0 && (
              <div className="files-section">
                <h4>Fichiers de preuve:</h4>
                <div className="files-list">
                  {achievement.files.map((file, index) => (
                    <div key={index} className="file-item">
                      <span className="file-name">{file.original_name}</span>
                      <span className="file-size">
                        ({(file.size / 1024 / 1024).toFixed(2)} MB)
                      </span>
                      <button
                        onClick={() => handleDownload(achievement, index)}
                        className="download-btn"
                      >
                        Télécharger
                      </button>
                    </div>
                  ))}
                </div>
              </div>
            )}

            {/* Support pour l'ancien format file_path */}
            {achievement.file_path && (!achievement.files || achievement.files.length === 0) && (
              <div className="files-section">
                <h4>Fichier de preuve:</h4>
                <button
                  onClick={() => handleDownload(achievement, 0)}
                  className="download-btn"
                >
                  Télécharger le fichier
                </button>
              </div>
            )}
          </div>
        ))}
      </div>

      {achievements.length === 0 && (
        <div className="empty-state">
          <p>Aucune réalisation trouvée.</p>
          <button onClick={() => setShowForm(true)}>
            Ajouter votre première réalisation
          </button>
        </div>
      )}
    </div>
  );
};

export default AchievementList;
```

---

## ⚛️ Implémentation React pour Services {#react-services}

### 1. Service API pour Services

```jsx
// services/serviceOfferService.js
import axios from 'axios';

const API_BASE_URL = process.env.REACT_APP_API_URL || 'http://localhost:8000/api';

const api = axios.create({
  baseURL: API_BASE_URL,
});

api.interceptors.request.use((config) => {
  const token = localStorage.getItem('auth_token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

export const serviceOfferService = {
  getAll: () => api.get('/service-offers'),

  getById: (id) => api.get(`/service-offers/${id}`),

  getPublic: (id) => api.get(`/service-offers/${id}/public`),

  create: (formData) => api.post('/service-offers', formData, {
    headers: {
      'Content-Type': 'multipart/form-data',
    },
  }),

  update: (id, formData) => api.put(`/service-offers/${id}`, formData, {
    headers: {
      'Content-Type': 'multipart/form-data',
    },
  }),

  delete: (id) => api.delete(`/service-offers/${id}`),

  downloadFile: (id, fileIndex = 0) => {
    return api.get(`/service-offers/${id}/download?file_index=${fileIndex}`, {
      responseType: 'blob',
    });
  },

  filter: (params) => api.get('/service-offers/filter', { params }),
};

export const downloadServiceFile = async (serviceId, fileIndex, fileName) => {
  try {
    const response = await serviceOfferService.downloadFile(serviceId, fileIndex);
    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', fileName);
    document.body.appendChild(link);
    link.click();
    link.remove();
    window.URL.revokeObjectURL(url);
  } catch (error) {
    console.error('Erreur lors du téléchargement:', error);
    throw error;
  }
};
```

### 2. Hook pour Gérer les Services

```jsx
// hooks/useServiceOffers.js
import { useState, useEffect } from 'react';
import { serviceOfferService } from '../services/serviceOfferService';

export const useServiceOffers = () => {
  const [services, setServices] = useState([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  const fetchServices = async () => {
    setLoading(true);
    try {
      const response = await serviceOfferService.getAll();
      setServices(response.data.data || response.data);
      setError(null);
    } catch (err) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  };

  const createService = async (formData) => {
    setLoading(true);
    try {
      const response = await serviceOfferService.create(formData);
      setServices(prev => [...prev, response.data.data || response.data]);
      setError(null);
      return response.data;
    } catch (err) {
      setError(err.message);
      throw err;
    } finally {
      setLoading(false);
    }
  };

  const updateService = async (id, formData) => {
    setLoading(true);
    try {
      const response = await serviceOfferService.update(id, formData);
      setServices(prev =>
        prev.map(service =>
          service.id === id ? (response.data.data || response.data) : service
        )
      );
      setError(null);
      return response.data;
    } catch (err) {
      setError(err.message);
      throw err;
    } finally {
      setLoading(false);
    }
  };

  const deleteService = async (id) => {
    setLoading(true);
    try {
      await serviceOfferService.delete(id);
      setServices(prev => prev.filter(service => service.id !== id));
      setError(null);
    } catch (err) {
      setError(err.message);
      throw err;
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchServices();
  }, []);

  return {
    services,
    loading,
    error,
    createService,
    updateService,
    deleteService,
    refetch: fetchServices
  };
};
```

### 3. Composant de Formulaire pour Service

```jsx
// components/ServiceOfferForm.jsx
import React, { useState } from 'react';
import { useServiceOffers } from '../hooks/useServiceOffers';

const ServiceOfferForm = ({ service = null, onSuccess, onCancel }) => {
  const { createService, updateService, loading } = useServiceOffers();
  const [formData, setFormData] = useState({
    title: service?.title || '',
    description: service?.description || '',
    price: service?.price || '',
    execution_time: service?.execution_time || '',
    concepts: service?.concepts || '',
    revisions: service?.revisions || '',
    is_private: service?.is_private || false,
    status: service?.status || 'draft',
    categories: service?.categories || [],
  });
  const [files, setFiles] = useState([]);
  const [errors, setErrors] = useState({});

  const handleInputChange = (e) => {
    const { name, value, type, checked } = e.target;
    setFormData(prev => ({
      ...prev,
      [name]: type === 'checkbox' ? checked : value
    }));
  };

  const handleCategoriesChange = (e) => {
    const value = e.target.value;
    const categories = value.split(',').map(cat => cat.trim()).filter(cat => cat);
    setFormData(prev => ({
      ...prev,
      categories
    }));
  };

  const handleFileChange = (e) => {
    const selectedFiles = Array.from(e.target.files);
    setFiles(selectedFiles);
  };

  const validateForm = () => {
    const newErrors = {};

    if (!formData.title.trim()) {
      newErrors.title = 'Le titre est requis';
    }

    if (!formData.price || formData.price <= 0) {
      newErrors.price = 'Le prix doit être supérieur à 0';
    }

    if (!formData.execution_time.trim()) {
      newErrors.execution_time = 'Le temps d\'exécution est requis';
    }

    if (formData.categories.length === 0) {
      newErrors.categories = 'Au moins une catégorie est requise';
    }

    // Validation des fichiers
    files.forEach((file, index) => {
      const maxSize = 10 * 1024 * 1024; // 10MB
      const allowedTypes = [
        'image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/svg+xml', 'image/webp',
        'application/pdf', 'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'application/zip', 'application/x-rar-compressed'
      ];

      if (file.size > maxSize) {
        newErrors[`file_${index}`] = `Le fichier ${file.name} dépasse 10MB`;
      }

      if (!allowedTypes.includes(file.type)) {
        newErrors[`file_${index}`] = `Type de fichier non supporté: ${file.name}`;
      }
    });

    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const handleSubmit = async (e) => {
    e.preventDefault();

    if (!validateForm()) {
      return;
    }

    const submitData = new FormData();

    // Ajouter les données du formulaire
    Object.keys(formData).forEach(key => {
      if (key === 'categories') {
        formData.categories.forEach(category => {
          submitData.append('categories[]', category);
        });
      } else if (formData[key] !== null && formData[key] !== undefined) {
        submitData.append(key, formData[key]);
      }
    });

    // Ajouter les fichiers
    files.forEach((file) => {
      submitData.append('files[]', file);
    });

    try {
      if (service) {
        await updateService(service.id, submitData);
      } else {
        await createService(submitData);
      }
      onSuccess?.();
    } catch (error) {
      console.error('Erreur lors de la soumission:', error);
    }
  };

  return (
    <form onSubmit={handleSubmit} className="service-form">
      <div className="form-group">
        <label htmlFor="title">Titre du service *</label>
        <input
          type="text"
          id="title"
          name="title"
          value={formData.title}
          onChange={handleInputChange}
          className={errors.title ? 'error' : ''}
          required
        />
        {errors.title && <span className="error-message">{errors.title}</span>}
      </div>

      <div className="form-group">
        <label htmlFor="description">Description</label>
        <textarea
          id="description"
          name="description"
          value={formData.description}
          onChange={handleInputChange}
          rows="4"
        />
      </div>

      <div className="form-row">
        <div className="form-group">
          <label htmlFor="price">Prix (€) *</label>
          <input
            type="number"
            id="price"
            name="price"
            value={formData.price}
            onChange={handleInputChange}
            min="0"
            step="0.01"
            className={errors.price ? 'error' : ''}
            required
          />
          {errors.price && <span className="error-message">{errors.price}</span>}
        </div>

        <div className="form-group">
          <label htmlFor="execution_time">Temps d'exécution *</label>
          <input
            type="text"
            id="execution_time"
            name="execution_time"
            value={formData.execution_time}
            onChange={handleInputChange}
            placeholder="ex: 2-3 semaines"
            className={errors.execution_time ? 'error' : ''}
            required
          />
          {errors.execution_time && <span className="error-message">{errors.execution_time}</span>}
        </div>
      </div>

      <div className="form-row">
        <div className="form-group">
          <label htmlFor="concepts">Concepts inclus</label>
          <input
            type="text"
            id="concepts"
            name="concepts"
            value={formData.concepts}
            onChange={handleInputChange}
            placeholder="ex: 3 concepts"
          />
        </div>

        <div className="form-group">
          <label htmlFor="revisions">Révisions incluses</label>
          <input
            type="text"
            id="revisions"
            name="revisions"
            value={formData.revisions}
            onChange={handleInputChange}
            placeholder="ex: 2 révisions gratuites"
          />
        </div>
      </div>

      <div className="form-group">
        <label htmlFor="categories">Catégories * (séparées par des virgules)</label>
        <input
          type="text"
          id="categories"
          name="categories"
          value={formData.categories.join(', ')}
          onChange={handleCategoriesChange}
          placeholder="ex: Développement Web, UI/UX Design"
          className={errors.categories ? 'error' : ''}
          required
        />
        {errors.categories && <span className="error-message">{errors.categories}</span>}
      </div>

      <div className="form-row">
        <div className="form-group">
          <label htmlFor="status">Statut</label>
          <select
            id="status"
            name="status"
            value={formData.status}
            onChange={handleInputChange}
          >
            <option value="draft">Brouillon</option>
            <option value="published">Publié</option>
            <option value="pending">En attente</option>
          </select>
        </div>

        <div className="form-group">
          <label className="checkbox-label">
            <input
              type="checkbox"
              name="is_private"
              checked={formData.is_private}
              onChange={handleInputChange}
            />
            Service privé
          </label>
        </div>
      </div>

      <div className="form-group">
        <label htmlFor="files">
          Fichiers du portfolio
          <small>(Images, Documents - Max 10MB par fichier)</small>
        </label>
        <input
          type="file"
          id="files"
          multiple
          onChange={handleFileChange}
          accept=".jpg,.jpeg,.png,.gif,.svg,.webp,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.zip,.rar"
        />

        {/* Affichage des fichiers sélectionnés */}
        {files.length > 0 && (
          <div className="selected-files">
            <h4>Fichiers sélectionnés:</h4>
            <ul>
              {files.map((file, index) => (
                <li key={index} className={errors[`file_${index}`] ? 'error' : ''}>
                  {file.name} ({(file.size / 1024 / 1024).toFixed(2)} MB)
                  {errors[`file_${index}`] && (
                    <span className="error-message">{errors[`file_${index}`]}</span>
                  )}
                </li>
              ))}
            </ul>
          </div>
        )}

        {/* Affichage des fichiers existants lors de la modification */}
        {service?.files && service.files.length > 0 && (
          <div className="existing-files">
            <h4>Fichiers actuels:</h4>
            <ul>
              {service.files.map((file, index) => (
                <li key={index}>
                  {file.original_name} ({(file.size / 1024 / 1024).toFixed(2)} MB)
                </li>
              ))}
            </ul>
            <small>Note: Sélectionner de nouveaux fichiers remplacera les fichiers existants</small>
          </div>
        )}
      </div>

      <div className="form-actions">
        <button type="button" onClick={onCancel} disabled={loading}>
          Annuler
        </button>
        <button type="submit" disabled={loading}>
          {loading ? 'Enregistrement...' : (service ? 'Mettre à jour' : 'Créer')}
        </button>
      </div>
    </form>
  );
};

export default ServiceOfferForm;
```

---

## 🔧 Composants Réutilisables {#composants-reutilisables}

### 1. Composant FileUpload Réutilisable

```jsx
// components/common/FileUpload.jsx
import React, { useState } from 'react';

const FileUpload = ({
  onFilesChange,
  accept = "*/*",
  maxSize = 2 * 1024 * 1024, // 2MB par défaut
  maxFiles = 10,
  allowedTypes = [],
  existingFiles = [],
  multiple = true
}) => {
  const [files, setFiles] = useState([]);
  const [errors, setErrors] = useState([]);

  const validateFile = (file) => {
    const errors = [];

    if (file.size > maxSize) {
      errors.push(`Le fichier ${file.name} dépasse ${(maxSize / 1024 / 1024).toFixed(0)}MB`);
    }

    if (allowedTypes.length > 0 && !allowedTypes.includes(file.type)) {
      errors.push(`Type de fichier non supporté: ${file.name}`);
    }

    return errors;
  };

  const handleFileChange = (e) => {
    const selectedFiles = Array.from(e.target.files);
    const newErrors = [];

    if (selectedFiles.length > maxFiles) {
      newErrors.push(`Maximum ${maxFiles} fichiers autorisés`);
      return;
    }

    selectedFiles.forEach(file => {
      const fileErrors = validateFile(file);
      newErrors.push(...fileErrors);
    });

    setErrors(newErrors);

    if (newErrors.length === 0) {
      setFiles(selectedFiles);
      onFilesChange(selectedFiles);
    }
  };

  const removeFile = (index) => {
    const newFiles = files.filter((_, i) => i !== index);
    setFiles(newFiles);
    onFilesChange(newFiles);
  };

  return (
    <div className="file-upload">
      <input
        type="file"
        multiple={multiple}
        accept={accept}
        onChange={handleFileChange}
        className={errors.length > 0 ? 'error' : ''}
      />

      {errors.length > 0 && (
        <div className="error-messages">
          {errors.map((error, index) => (
            <span key={index} className="error-message">{error}</span>
          ))}
        </div>
      )}

      {/* Fichiers sélectionnés */}
      {files.length > 0 && (
        <div className="selected-files">
          <h4>Fichiers sélectionnés:</h4>
          <ul>
            {files.map((file, index) => (
              <li key={index}>
                <span>{file.name}</span>
                <span>({(file.size / 1024 / 1024).toFixed(2)} MB)</span>
                <button type="button" onClick={() => removeFile(index)}>
                  Supprimer
                </button>
              </li>
            ))}
          </ul>
        </div>
      )}

      {/* Fichiers existants */}
      {existingFiles.length > 0 && (
        <div className="existing-files">
          <h4>Fichiers actuels:</h4>
          <ul>
            {existingFiles.map((file, index) => (
              <li key={index}>
                <span>{file.original_name}</span>
                <span>({(file.size / 1024 / 1024).toFixed(2)} MB)</span>
              </li>
            ))}
          </ul>
          {files.length > 0 && (
            <small>Note: Les nouveaux fichiers remplaceront les fichiers existants</small>
          )}
        </div>
      )}
    </div>
  );
};

export default FileUpload;
```

### 2. Composant FileList pour Affichage

```jsx
// components/common/FileList.jsx
import React from 'react';

const FileList = ({ files = [], onDownload, onDelete, showActions = true }) => {
  if (!files || files.length === 0) {
    return <p>Aucun fichier disponible</p>;
  }

  const getFileIcon = (mimeType) => {
    if (mimeType?.startsWith('image/')) return '🖼️';
    if (mimeType?.includes('pdf')) return '📄';
    if (mimeType?.includes('word')) return '📝';
    if (mimeType?.includes('excel')) return '📊';
    if (mimeType?.includes('powerpoint')) return '📽️';
    if (mimeType?.includes('zip') || mimeType?.includes('rar')) return '🗜️';
    return '📎';
  };

  return (
    <div className="file-list">
      {files.map((file, index) => (
        <div key={index} className="file-item">
          <div className="file-info">
            <span className="file-icon">{getFileIcon(file.mime_type)}</span>
            <div className="file-details">
              <span className="file-name">{file.original_name}</span>
              <span className="file-size">
                {(file.size / 1024 / 1024).toFixed(2)} MB
              </span>
            </div>
          </div>

          {showActions && (
            <div className="file-actions">
              {onDownload && (
                <button
                  onClick={() => onDownload(index)}
                  className="btn-download"
                  title="Télécharger"
                >
                  ⬇️
                </button>
              )}
              {onDelete && (
                <button
                  onClick={() => onDelete(index)}
                  className="btn-delete"
                  title="Supprimer"
                >
                  🗑️
                </button>
              )}
            </div>
          )}
        </div>
      ))}
    </div>
  );
};

export default FileList;
```

### 3. Hook Générique pour Upload de Fichiers

```jsx
// hooks/useFileUpload.js
import { useState } from 'react';

export const useFileUpload = (uploadFunction) => {
  const [uploading, setUploading] = useState(false);
  const [progress, setProgress] = useState(0);
  const [error, setError] = useState(null);

  const uploadFiles = async (files, additionalData = {}) => {
    setUploading(true);
    setError(null);
    setProgress(0);

    try {
      const formData = new FormData();

      // Ajouter les données supplémentaires
      Object.keys(additionalData).forEach(key => {
        if (Array.isArray(additionalData[key])) {
          additionalData[key].forEach(item => {
            formData.append(`${key}[]`, item);
          });
        } else {
          formData.append(key, additionalData[key]);
        }
      });

      // Ajouter les fichiers
      files.forEach(file => {
        formData.append('files[]', file);
      });

      const result = await uploadFunction(formData, {
        onUploadProgress: (progressEvent) => {
          const percentCompleted = Math.round(
            (progressEvent.loaded * 100) / progressEvent.total
          );
          setProgress(percentCompleted);
        }
      });

      setProgress(100);
      return result;
    } catch (err) {
      setError(err.message || 'Erreur lors de l\'upload');
      throw err;
    } finally {
      setUploading(false);
    }
  };

  return {
    uploadFiles,
    uploading,
    progress,
    error,
    resetError: () => setError(null)
  };
};
```

### 4. Styles CSS pour les Composants

```css
/* styles/components.css */

/* Styles pour les formulaires */
.achievement-form,
.service-form {
  max-width: 800px;
  margin: 0 auto;
  padding: 20px;
  background: #fff;
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.form-group {
  margin-bottom: 20px;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
}

.form-group label {
  display: block;
  margin-bottom: 5px;
  font-weight: 600;
  color: #333;
}

.form-group small {
  display: block;
  color: #666;
  font-size: 0.85em;
  margin-top: 2px;
}

.form-group input,
.form-group textarea,
.form-group select {
  width: 100%;
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 14px;
  transition: border-color 0.3s;
}

.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus {
  outline: none;
  border-color: #007bff;
  box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
}

.form-group input.error,
.form-group textarea.error,
.form-group select.error {
  border-color: #dc3545;
}

.error-message {
  color: #dc3545;
  font-size: 0.85em;
  margin-top: 5px;
  display: block;
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
}

.checkbox-label input[type="checkbox"] {
  width: auto;
}

/* Styles pour les fichiers */
.file-upload {
  border: 2px dashed #ddd;
  border-radius: 8px;
  padding: 20px;
  text-align: center;
  transition: border-color 0.3s;
}

.file-upload:hover {
  border-color: #007bff;
}

.file-upload.error {
  border-color: #dc3545;
}

.selected-files,
.existing-files {
  margin-top: 15px;
  padding: 15px;
  background: #f8f9fa;
  border-radius: 4px;
}

.selected-files h4,
.existing-files h4 {
  margin: 0 0 10px 0;
  color: #333;
  font-size: 14px;
}

.selected-files ul,
.existing-files ul {
  list-style: none;
  padding: 0;
  margin: 0;
}

.selected-files li,
.existing-files li {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px 0;
  border-bottom: 1px solid #eee;
}

.selected-files li:last-child,
.existing-files li:last-child {
  border-bottom: none;
}

.selected-files li.error {
  color: #dc3545;
}

/* Styles pour la liste de fichiers */
.file-list {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.file-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px;
  background: #f8f9fa;
  border-radius: 6px;
  border: 1px solid #e9ecef;
}

.file-info {
  display: flex;
  align-items: center;
  gap: 10px;
}

.file-icon {
  font-size: 20px;
}

.file-details {
  display: flex;
  flex-direction: column;
}

.file-name {
  font-weight: 500;
  color: #333;
}

.file-size {
  font-size: 0.85em;
  color: #666;
}

.file-actions {
  display: flex;
  gap: 8px;
}

.btn-download,
.btn-delete {
  background: none;
  border: none;
  cursor: pointer;
  padding: 4px;
  border-radius: 4px;
  transition: background-color 0.2s;
}

.btn-download:hover {
  background-color: #e3f2fd;
}

.btn-delete:hover {
  background-color: #ffebee;
}

/* Styles pour les actions de formulaire */
.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  margin-top: 30px;
  padding-top: 20px;
  border-top: 1px solid #eee;
}

.form-actions button {
  padding: 10px 20px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 14px;
  transition: background-color 0.3s;
}

.form-actions button[type="submit"] {
  background-color: #007bff;
  color: white;
}

.form-actions button[type="submit"]:hover:not(:disabled) {
  background-color: #0056b3;
}

.form-actions button[type="button"] {
  background-color: #6c757d;
  color: white;
}

.form-actions button[type="button"]:hover:not(:disabled) {
  background-color: #545b62;
}

.form-actions button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* Styles pour les listes d'achievements/services */
.achievement-list,
.service-list {
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px;
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 30px;
}

.header h2 {
  margin: 0;
  color: #333;
}

.btn-primary {
  background-color: #007bff;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 4px;
  cursor: pointer;
  font-size: 14px;
  transition: background-color 0.3s;
}

.btn-primary:hover {
  background-color: #0056b3;
}

.achievements-grid,
.services-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
  gap: 20px;
}

.achievement-card,
.service-card {
  background: white;
  border-radius: 8px;
  padding: 20px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  transition: transform 0.2s, box-shadow 0.2s;
}

.achievement-card:hover,
.service-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
}

.achievement-header,
.service-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 15px;
}

.achievement-header h3,
.service-header h3 {
  margin: 0;
  color: #333;
  font-size: 18px;
}

.achievement-actions,
.service-actions {
  display: flex;
  gap: 8px;
}

.achievement-actions button,
.service-actions button {
  padding: 4px 8px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 12px;
  transition: background-color 0.2s;
}

.achievement-actions button:first-child,
.service-actions button:first-child {
  background-color: #28a745;
  color: white;
}

.achievement-actions button:last-child,
.service-actions button:last-child {
  background-color: #dc3545;
  color: white;
}

.organization,
.date,
.description,
.price,
.categories {
  margin: 8px 0;
  color: #666;
  font-size: 14px;
}

.external-link {
  color: #007bff;
  text-decoration: none;
  font-size: 14px;
}

.external-link:hover {
  text-decoration: underline;
}

.files-section {
  margin-top: 15px;
  padding-top: 15px;
  border-top: 1px solid #eee;
}

.files-section h4 {
  margin: 0 0 10px 0;
  font-size: 14px;
  color: #333;
}

.download-btn {
  background-color: #17a2b8;
  color: white;
  border: none;
  padding: 4px 8px;
  border-radius: 4px;
  cursor: pointer;
  font-size: 12px;
  transition: background-color 0.2s;
}

.download-btn:hover {
  background-color: #138496;
}

/* Modal */
.modal {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.modal-content {
  background: white;
  border-radius: 8px;
  padding: 20px;
  max-width: 90vw;
  max-height: 90vh;
  overflow-y: auto;
}

.modal-content h3 {
  margin: 0 0 20px 0;
  color: #333;
}

/* États vides */
.empty-state {
  text-align: center;
  padding: 40px;
  color: #666;
}

.empty-state button {
  margin-top: 15px;
  background-color: #007bff;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 4px;
  cursor: pointer;
}

/* Loading et erreurs */
.loading {
  text-align: center;
  padding: 40px;
  color: #666;
}

.error {
  text-align: center;
  padding: 40px;
  color: #dc3545;
  background-color: #f8d7da;
  border: 1px solid #f5c6cb;
  border-radius: 4px;
  margin: 20px 0;
}

/* Responsive */
@media (max-width: 768px) {
  .form-row {
    grid-template-columns: 1fr;
  }

  .achievements-grid,
  .services-grid {
    grid-template-columns: 1fr;
  }

  .header {
    flex-direction: column;
    gap: 15px;
    align-items: stretch;
  }

  .achievement-header,
  .service-header {
    flex-direction: column;
    gap: 10px;
  }

  .file-item {
    flex-direction: column;
    align-items: flex-start;
    gap: 10px;
  }
}
```

---

## 📝 Résumé et Bonnes Pratiques

### Points Clés à Retenir

1. **API Cohérente** : Même structure pour achievements et services
2. **Validation Robuste** : Côté client et serveur
3. **Gestion d'Erreurs** : Messages clairs et informatifs
4. **Rétrocompatibilité** : Support de l'ancien format
5. **UX Optimisée** : Feedback visuel et états de chargement

### Checklist de Déploiement

- [ ] Tester tous les endpoints Postman
- [ ] Vérifier la validation des fichiers
- [ ] Tester l'upload multiple
- [ ] Vérifier le téléchargement
- [ ] Tester la rétrocompatibilité
- [ ] Valider l'interface utilisateur
- [ ] Tester sur mobile

### Prochaines Améliorations

1. **Drag & Drop** pour l'upload
2. **Prévisualisation** des images
3. **Compression** automatique des images
4. **Upload progressif** avec barre de progression
5. **Gestion des permissions** par fichier
