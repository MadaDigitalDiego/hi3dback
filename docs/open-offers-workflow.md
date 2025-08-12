# Workflow des Offres Ouvertes - Gestion des Candidatures et Attribution

## Vue d'ensemble

Le système de gestion des offres ouvertes a été amélioré pour séparer clairement deux actions distinctes :
1. **Accepter/Rejeter les candidatures** 
2. **Attribuer l'offre au professionnel choisi**

## Flux de travail

### 1. Création d'une offre
- **Endpoint** : `POST /api/open-offers`
- **Statut initial** : `open`
- L'offre est visible aux professionnels éligibles

### 2. Candidatures des professionnels
- **Endpoint** : `POST /api/open-offers/{open_offer}/apply`
- Les professionnels peuvent postuler avec une proposition
- **Statut candidature** : `pending`

### 3. Consultation des candidatures (Client)
- **Toutes les candidatures** : `GET /api/open-offers/{open_offer}/applications`
- **Candidatures acceptées** : `GET /api/open-offers/{open_offer}/accepted-applications`

### 4. Acceptation/Rejet des candidatures (Client)
- **Endpoint** : `PATCH /api/offer-applications/{application}/status`
- **Paramètres** : `{"status": "accepted"}` ou `{"status": "rejected"}`
- **Important** : L'offre reste en statut `open`
- Le client peut accepter plusieurs candidatures avant de choisir

### 5. Attribution de l'offre (Client)
- **Endpoint** : `POST /api/open-offers/{openOffer}/assign`
- **Paramètres** : `{"application_id": 123}`
- **Conditions** :
  - La candidature doit être `accepted`
  - L'offre doit être en statut `open`
- **Actions automatiques** :
  - L'offre passe en statut `in_progress`
  - Toutes les autres candidatures sont rejetées automatiquement

## Endpoints disponibles

### Gestion des candidatures

#### Accepter/Rejeter une candidature
```http
PATCH /api/offer-applications/{application}/status
Content-Type: application/json
Authorization: Bearer {token}

{
    "status": "accepted" // ou "rejected"
}
```

**Réponse** :
```json
{
    "application": {
        "id": 123,
        "status": "accepted",
        "proposal": "...",
        "freelanceProfile": {...}
    },
    "message": "Statut de la candidature mis à jour avec succès."
}
```

#### Voir les candidatures acceptées
```http
GET /api/open-offers/{open_offer}/accepted-applications
Authorization: Bearer {token}
```

### Attribution de l'offre

#### Attribuer l'offre à un professionnel
```http
POST /api/open-offers/{openOffer}/assign
Content-Type: application/json
Authorization: Bearer {token}

{
    "application_id": 123
}
```

**Réponse** :
```json
{
    "open_offer": {
        "id": 456,
        "status": "in_progress",
        "title": "...",
        "applications": [...]
    },
    "assigned_application": {
        "id": 123,
        "status": "accepted",
        "freelanceProfile": {...}
    },
    "message": "Offre attribuée avec succès au professionnel choisi."
}
```

## Statuts des candidatures

- **`pending`** : Candidature en attente de réponse
- **`accepted`** : Candidature acceptée (mais offre pas encore attribuée)
- **`rejected`** : Candidature rejetée
- **`invited`** : Professionnel invité directement

## Statuts des offres

- **`open`** : Offre ouverte aux candidatures
- **`in_progress`** : Offre attribuée et en cours
- **`completed`** : Offre terminée
- **`closed`** : Offre fermée sans attribution

## Avantages de cette approche

1. **Flexibilité** : Le client peut accepter plusieurs candidatures avant de choisir
2. **Clarté** : Séparation claire entre acceptation et attribution
3. **Contrôle** : Le client garde le contrôle total du processus
4. **Transparence** : Chaque étape est explicite et traçable

## Cas d'usage typique

1. Client crée une offre
2. Plusieurs professionnels postulent
3. Client examine les candidatures
4. Client accepte 2-3 candidatures intéressantes
5. Client compare les profils acceptés
6. Client attribue l'offre au professionnel choisi
7. Les autres candidatures sont automatiquement rejetées
