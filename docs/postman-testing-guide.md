# Guide de Test Postman - Workflow des Offres Ouvertes

## Configuration Postman

### Variables d'environnement
Créez un environnement Postman avec les variables suivantes :

```
base_url = http://localhost:8000/api
client_token = Bearer {votre_token_client}
professional_token = Bearer {votre_token_professionnel}
offer_id = {id_de_l_offre}
application_id = {id_de_la_candidature}
```

## Scénario de Test Complet

### 1. Authentification

#### Connexion Client
```http
POST {{base_url}}/login
Content-Type: application/json

{
    "email": "client@example.com",
    "password": "password"
}
```

**Réponse attendue :**
```json
{
    "access_token": "1|abc123...",
    "token_type": "Bearer",
    "user": {
        "id": 1,
        "email": "client@example.com",
        "is_professional": false
    }
}
```

#### Connexion Professionnel
```http
POST {{base_url}}/login
Content-Type: application/json

{
    "email": "professional@example.com",
    "password": "password"
}
```

### 2. Création d'une Offre (Client)

```http
POST {{base_url}}/open-offers
Authorization: {{client_token}}
Content-Type: application/json

{
    "title": "Développement d'une application mobile",
    "description": "Nous recherchons un développeur pour créer une app mobile",
    "budget": "5000-10000€",
    "deadline": "2024-06-01",
    "company": "Ma Société",
    "recruitment_type": "company",
    "open_to_applications": true,
    "filters": {
        "skills": ["React Native", "Flutter", "Mobile Development"]
    }
}
```

**Réponse attendue :**
```json
{
    "open_offer": {
        "id": 123,
        "title": "Développement d'une application mobile",
        "status": "open",
        "user_id": 1,
        "created_at": "2024-01-15T10:00:00.000000Z"
    },
    "message": "Offre ouverte créée avec succès."
}
```

### 3. Candidature des Professionnels

#### Candidature Professionnel 1
```http
POST {{base_url}}/open-offers/{{offer_id}}/apply
Authorization: {{professional_token}}
Content-Type: application/json

{
    "proposal": "Je suis expert en React Native avec 5 ans d'expérience. Je peux livrer votre projet en 3 mois."
}
```

**Réponse attendue :**
```json
{
    "application": {
        "id": 456,
        "open_offer_id": 123,
        "professional_profile_id": 10,
        "proposal": "Je suis expert en React Native...",
        "status": "pending",
        "created_at": "2024-01-15T11:00:00.000000Z"
    },
    "message": "Candidature soumise avec succès."
}
```

### 4. Consultation des Candidatures (Client)

#### Voir toutes les candidatures
```http
GET {{base_url}}/open-offers/{{offer_id}}/applications
Authorization: {{client_token}}
```

**Réponse attendue :**
```json
{
    "applications": [
        {
            "id": 456,
            "status": "pending",
            "proposal": "Je suis expert en React Native...",
            "created_at": "2024-01-15T11:00:00.000000Z",
            "freelanceProfile": {
                "id": 10,
                "user": {
                    "id": 2,
                    "first_name": "Jean",
                    "last_name": "Dupont",
                    "email": "professional@example.com"
                }
            }
        }
    ]
}
```

### 5. Acceptation des Candidatures (Client)

#### Accepter une candidature
```http
PATCH {{base_url}}/offer-applications/{{application_id}}/status
Authorization: {{client_token}}
Content-Type: application/json

{
    "status": "accepted"
}
```

**Réponse attendue :**
```json
{
    "application": {
        "id": 16,
        "open_offer_id": 8,
        "proposal": null,
        "status": "accepted",
        "created_at": "2025-05-29T12:29:42.000000Z",
        "updated_at": "2025-05-29T13:34:23.000000Z",
        "professional_profile_id": 3,
        "open_offer": {
            "id": 8,
            "user_id": 4,
            "title": "3D Modeling",
            "categories": [
                "modeling",
                "animation",
                "architectural",
                "product"
            ],
            "budget": "500 - 1000",
            "deadline": "2026-12-03T00:00:00.000000Z",
            "company": "Nova3D Studio",
            "website": "https://elie-alsatech.com",
            "description": "aaaaaaaaaaaaaaaaa",
            "files": null,
            "recruitment_type": "company",
            "open_to_applications": true,
            "auto_invite": false,
            "status": "open",
            "views_count": 34,
            "created_at": "2025-05-28T08:31:16.000000Z",
            "updated_at": "2025-05-29T13:19:37.000000Z"
        }
    },
    "message": "Statut de la candidature mis à jour avec succès."
}
```

#### Rejeter une candidature
```http
PATCH {{base_url}}/offer-applications/{{application_id}}/status
Authorization: {{client_token}}
Content-Type: application/json

{
    "status": "rejected"
}
```

### 6. Consultation des Candidatures Acceptées (Client)

```http
GET {{base_url}}/open-offers/{{offer_id}}/accepted-applications
Authorization: {{client_token}}
```

**Réponse attendue :**
```json
{
    "accepted_applications": [
        {
            "id": 456,
            "status": "accepted",
            "proposal": "Je suis expert en React Native...",
            "freelanceProfile": {
                "id": 10,
                "user": {
                    "id": 2,
                    "first_name": "Jean",
                    "last_name": "Dupont"
                }
            }
        }
    ]
}
```

### 7. Attribution de l'Offre (Client)

```http
POST {{base_url}}/open-offers/{{offer_id}}/assign
Authorization: {{client_token}}
Content-Type: application/json

{
    "application_id": 456
}
```

**Réponse attendue :**
```json
{
    "open_offer": {
        "id": 8,
        "user_id": 4,
        "title": "3D Modeling",
        "categories": [
            "modeling",
            "animation",
            "architectural",
            "product"
        ],
        "budget": "500 - 1000",
        "deadline": "2026-12-03T00:00:00.000000Z",
        "company": "Nova3D Studio",
        "website": "https://elie-alsatech.com",
        "description": "aaaaaaaaaaaaaaaaa",
        "files": null,
        "recruitment_type": "company",
        "open_to_applications": true,
        "auto_invite": false,
        "status": "in_progress",
        "views_count": 38,
        "created_at": "2025-05-28T08:31:16.000000Z",
        "updated_at": "2025-05-29T13:58:23.000000Z",
        "applications": [
            {
                "id": 13,
                "open_offer_id": 8,
                "proposal": null,
                "status": "rejected",
                "created_at": "2025-05-28T08:35:03.000000Z",
                "updated_at": "2025-05-29T13:41:14.000000Z",
                "professional_profile_id": 4,
                "freelance_profile": {
                    "id": 4,
                    "user_id": 6,
                    "first_name": "Rako",
                    "last_name": "ELa",
                    "email": "springboot455@gmail.com",
                    "avatar": null,
                    "portfolio_items": null,
                    "phone": "+26132546544029477",
                    "address": "aaaaaaaa",
                    "city": "aaaaaaaaaaa",
                    "country": "aaaaaaaa",
                    "bio": "aaaaaaaaaaa",
                    "title": null,
                    "expertise": null,
                    "completion_percentage": 71,
                    "profession": "Non spécifié",
                    "years_of_experience": 0,
                    "hourly_rate": "0.00",
                    "description": null,
                    "availability_status": "unavailable",
                    "estimated_response_time": null,
                    "rating": "0.0",
                    "skills": [
                        "Animation d'objets",
                        "ARCore",
                        "3ds Max",
                        "A-Frame",
                        "ARKit",
                        "Animation de personnages",
                        "Blender",
                        "ArchiCAD",
                        "Character Design",
                        "Character Modeling",
                        "Design industriel",
                        "Character Rigging",
                        "Facial Animation",
                        "Environment Design",
                        "Facial Rigging",
                        "Fusion 360",
                        "Game Asset Creation",
                        "Hard Surface Modeling",
                        "Landscape Modeling",
                        "KeyShot",
                        "Level Design",
                        "Motion Capture",
                        "Maya",
                        "Low Poly Modeling",
                        "Modélisation BIM",
                        "Lumion",
                        "Organic Modeling",
                        "Oculus SDK",
                        "Prototypage 3D",
                        "PBR Texturing"
                    ],
                    "languages": [],
                    "services_offered": [],
                    "portfolio": null,
                    "social_links": [],
                    "created_at": "2025-05-28T08:32:23.000000Z",
                    "updated_at": "2025-05-28T08:34:44.000000Z",
                    "user": {
                        "id": 6,
                        "first_name": "Rako",
                        "last_name": "ELa",
                        "email": "springboot4555@gmail.com",
                        "email_verified_at": "2025-05-28T08:33:38.000000Z",
                        "is_professional": true,
                        "created_at": "2025-05-28T08:32:23.000000Z",
                        "updated_at": "2025-05-28T08:34:44.000000Z",
                        "profile_completed": true
                    }
                }
            },
            {
                "id": 16,
                "open_offer_id": 8,
                "proposal": null,
                "status": "accepted",
                "created_at": "2025-05-29T12:29:42.000000Z",
                "updated_at": "2025-05-29T13:34:23.000000Z",
                "professional_profile_id": 3,
                "freelance_profile": {
                    "id": 3,
                    "user_id": 5,
                    "first_name": "Jao",
                    "last_name": "Mboty",
                    "email": "jaomboty123@gmail.com",
                    "avatar": null,
                    "portfolio_items": null,
                    "phone": "+26132546544029477",
                    "address": "Lazaret",
                    "city": "Diego",
                    "country": "Madagascar",
                    "bio": "3d",
                    "title": null,
                    "expertise": null,
                    "completion_percentage": 71,
                    "profession": "Non spécifié",
                    "years_of_experience": 0,
                    "hourly_rate": "0.00",
                    "description": null,
                    "availability_status": "unavailable",
                    "estimated_response_time": null,
                    "rating": "0.0",
                    "skills": [
                        "Animation d'objets",
                        "ARCore",
                        "3ds Max",
                        "A-Frame",
                        "ARKit",
                        "Animation de personnages",
                        "Blender",
                        "ArchiCAD",
                        "Character Design",
                        "Character Modeling",
                        "Design industriel",
                        "Facial Animation",
                        "Environment Design",
                        "Character Rigging",
                        "Facial Rigging",
                        "Fusion 360",
                        "Hard Surface Modeling",
                        "Game Asset Creation",
                        "KeyShot",
                        "Landscape Modeling",
                        "Low Poly Modeling",
                        "Level Design",
                        "Lumion",
                        "Maya",
                        "Motion Capture",
                        "Modélisation BIM",
                        "Oculus SDK",
                        "Organic Modeling"
                    ],
                    "languages": [],
                    "services_offered": [],
                    "portfolio": null,
                    "social_links": [],
                    "created_at": "2025-05-28T06:39:30.000000Z",
                    "updated_at": "2025-05-28T06:41:23.000000Z",
                    "user": {
                        "id": 5,
                        "first_name": "Jao",
                        "last_name": "Mboty",
                        "email": "jaomboty123@gmail.com",
                        "email_verified_at": "2025-05-28T06:40:09.000000Z",
                        "is_professional": true,
                        "created_at": "2025-05-28T06:39:30.000000Z",
                        "updated_at": "2025-05-28T06:41:24.000000Z",
                        "profile_completed": true
                    }
                }
            }
        ]
    },
    "assigned_application": {
        "id": 16,
        "open_offer_id": 8,
        "proposal": null,
        "status": "accepted",
        "created_at": "2025-05-29T12:29:42.000000Z",
        "updated_at": "2025-05-29T13:34:23.000000Z",
        "professional_profile_id": 3,
        "freelance_profile": {
            "id": 3,
            "user_id": 5,
            "first_name": "Jao",
            "last_name": "Mboty",
            "email": "jaomboty123@gmail.com",
            "avatar": null,
            "portfolio_items": null,
            "phone": "+26132546544029477",
            "address": "Lazaret",
            "city": "Diego",
            "country": "Madagascar",
            "bio": "3d",
            "title": null,
            "expertise": null,
            "completion_percentage": 71,
            "profession": "Non spécifié",
            "years_of_experience": 0,
            "hourly_rate": "0.00",
            "description": null,
            "availability_status": "unavailable",
            "estimated_response_time": null,
            "rating": "0.0",
            "skills": [
                "Animation d'objets",
                "ARCore",
                "3ds Max",
                "A-Frame",
                "ARKit",
                "Animation de personnages",
                "Blender",
                "ArchiCAD",
                "Character Design",
                "Character Modeling",
                "Design industriel",
                "Facial Animation",
                "Environment Design",
                "Character Rigging",
                "Facial Rigging",
                "Fusion 360",
                "Hard Surface Modeling",
                "Game Asset Creation",
                "KeyShot",
                "Landscape Modeling",
                "Low Poly Modeling",
                "Level Design",
                "Lumion",
                "Maya",
                "Motion Capture",
                "Modélisation BIM",
                "Oculus SDK",
                "Organic Modeling"
            ],
            "languages": [],
            "services_offered": [],
            "portfolio": null,
            "social_links": [],
            "created_at": "2025-05-28T06:39:30.000000Z",
            "updated_at": "2025-05-28T06:41:23.000000Z"
        }
    },
    "message": "Offre attribuée avec succès au professionnel choisi."
}
```

## Tests d'Erreur

### 1. Tentative d'attribution sans acceptation préalable

```http
POST {{base_url}}/open-offers/{{offer_id}}/assign
Authorization: {{client_token}}
Content-Type: application/json

{
    "application_id": 789
}
```

**Réponse attendue (400) :**
```json
{
    "message": "Seules les candidatures acceptées peuvent être attribuées."
}
```

### 2. Accès non autorisé

```http
PATCH {{base_url}}/offer-applications/{{application_id}}/status
Authorization: Bearer wrong_token
Content-Type: application/json

{
    "status": "accepted"
}
```

**Réponse attendue (403) :**
```json
{
    "message": "Non autorisé à modifier le statut de la candidature."
}
```

### 3. Candidature inexistante

```http
POST {{base_url}}/open-offers/{{offer_id}}/assign
Authorization: {{client_token}}
Content-Type: application/json

{
    "application_id": 99999
}
```

**Réponse attendue (400) :**
```json
{
    "message": "La candidature spécifiée n'appartient pas à cette offre."
}
```

## Collection Postman

### Structure recommandée :
```
📁 Open Offers Workflow
├── 📁 Authentication
│   ├── Login Client
│   └── Login Professional
├── 📁 Offer Management
│   ├── Create Offer
│   ├── View Applications
│   └── View Accepted Applications
├── 📁 Application Management
│   ├── Apply to Offer
│   ├── Accept Application
│   ├── Reject Application
│   └── Assign Offer
└── 📁 Error Cases
    ├── Unauthorized Access
    ├── Invalid Application ID
    └── Non-accepted Application Assignment
```

### Scripts de test automatiques

#### Pre-request Script (pour extraire les IDs) :
```javascript
// Extraire l'ID de l'offre de la réponse précédente
if (pm.response && pm.response.json()) {
    const response = pm.response.json();
    if (response.open_offer && response.open_offer.id) {
        pm.environment.set("offer_id", response.open_offer.id);
    }
    if (response.application && response.application.id) {
        pm.environment.set("application_id", response.application.id);
    }
}
```

#### Test Script (pour valider les réponses) :
```javascript
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

pm.test("Response has required fields", function () {
    const jsonData = pm.response.json();
    pm.expect(jsonData).to.have.property('message');
});

pm.test("Application status is accepted", function () {
    const jsonData = pm.response.json();
    pm.expect(jsonData.application.status).to.eql("accepted");
});
```

## Ordre de Test Recommandé

1. **Authentification** → Récupérer les tokens
2. **Création d'offre** → Récupérer l'offer_id
3. **Candidatures** → Créer plusieurs candidatures
4. **Acceptation** → Accepter 2-3 candidatures
5. **Consultation** → Vérifier les candidatures acceptées
6. **Attribution** → Attribuer à un professionnel choisi
7. **Vérification** → Confirmer le statut final

## Import de la Collection Postman

1. **Télécharger** le fichier `postman-collection.json`
2. **Ouvrir Postman** → Import → Upload Files
3. **Sélectionner** le fichier JSON
4. **Créer un environnement** avec les variables :
   - `base_url`: `http://localhost:8000/api`
   - `client_token`: (sera rempli automatiquement)
   - `professional_token`: (sera rempli automatiquement)
   - `offer_id`: (sera rempli automatiquement)
   - `application_id`: (sera rempli automatiquement)

## Checklist de Validation

### ✅ Étape 1 : Authentification
- [ ] Login client réussi (200)
- [ ] Token client sauvegardé
- [ ] Login professionnel réussi (200)
- [ ] Token professionnel sauvegardé

### ✅ Étape 2 : Création d'offre
- [ ] Offre créée avec succès (201)
- [ ] Statut initial = "open"
- [ ] offer_id sauvegardé

### ✅ Étape 3 : Candidatures
- [ ] Candidature 1 soumise (201)
- [ ] Candidature 2 soumise (201)
- [ ] Statut initial = "pending"

### ✅ Étape 4 : Gestion des candidatures
- [ ] Acceptation candidature 1 (200)
- [ ] Acceptation candidature 2 (200)
- [ ] Offre reste en statut "open"
- [ ] Rejet candidature 3 (200)

### ✅ Étape 5 : Consultation
- [ ] Liste toutes candidatures (200)
- [ ] Liste candidatures acceptées (200)
- [ ] Filtrage correct par statut

### ✅ Étape 6 : Attribution
- [ ] Attribution réussie (200)
- [ ] Offre passe en "in_progress"
- [ ] Autres candidatures rejetées automatiquement

### ✅ Étape 7 : Tests d'erreur
- [ ] Attribution candidature non-acceptée (400)
- [ ] Accès non autorisé (401/403)
- [ ] Candidature inexistante (400)

## Résultats Attendus par Endpoint

| Endpoint | Méthode | Statut | Action |
|----------|---------|--------|--------|
| `/login` | POST | 200 | Authentification |
| `/open-offers` | POST | 201 | Création offre |
| `/open-offers/{id}/apply` | POST | 201 | Candidature |
| `/offer-applications/{id}/status` | PATCH | 200 | Accept/Reject |
| `/open-offers/{id}/accepted-applications` | GET | 200 | Consultation |
| `/open-offers/{id}/assign` | POST | 200 | Attribution |

## Troubleshooting

### Erreur 401 - Unauthorized
- Vérifier que le token est bien préfixé par "Bearer "
- Vérifier que l'utilisateur est connecté
- Régénérer le token si nécessaire

### Erreur 403 - Forbidden
- Vérifier que l'utilisateur a les bonnes permissions
- Client pour les actions d'offre, Professionnel pour les candidatures

### Erreur 400 - Bad Request
- Vérifier le format JSON de la requête
- Vérifier que les IDs existent
- Vérifier les statuts requis

### Erreur 422 - Validation Error
- Vérifier les champs obligatoires
- Vérifier les formats de données (dates, emails, etc.)

## Scripts Postman Utiles

### Extraction automatique des IDs :
```javascript
// Dans l'onglet "Tests" de chaque requête
if (pm.response.code === 200 || pm.response.code === 201) {
    const response = pm.response.json();

    // Sauvegarder l'ID de l'offre
    if (response.open_offer && response.open_offer.id) {
        pm.environment.set("offer_id", response.open_offer.id);
    }

    // Sauvegarder l'ID de la candidature
    if (response.application && response.application.id) {
        pm.environment.set("application_id", response.application.id);
    }

    // Sauvegarder le token
    if (response.access_token) {
        pm.environment.set("auth_token", "Bearer " + response.access_token);
    }
}
```

### Validation des réponses :
```javascript
pm.test("Status code is success", function () {
    pm.expect(pm.response.code).to.be.oneOf([200, 201]);
});

pm.test("Response has message", function () {
    const jsonData = pm.response.json();
    pm.expect(jsonData).to.have.property('message');
});

pm.test("Offer status is correct", function () {
    const jsonData = pm.response.json();
    if (jsonData.open_offer) {
        pm.expect(jsonData.open_offer.status).to.be.oneOf(['open', 'in_progress']);
    }
});
```
