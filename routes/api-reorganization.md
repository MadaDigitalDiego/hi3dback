# Plan de réorganisation des routes API

## Routes de profil

### Routes actuelles à consolider
- `/profile/professional` (POST) - Créer un profil professionnel
- `/profile/client` (POST) - Créer un profil client
- `/profile/client` (GET) - Récupérer le profil client authentifié
- `/profile/client` (PUT) - Mettre à jour le profil client
- `/profile/complete` (POST) - Compléter le profil client
- `/profile/completion-status` (GET) - Récupérer le statut de complétion du profil
- `/profile/completion` (GET) - Récupérer les données de complétion du profil
- `/profile/completion/{step}` (PUT) - Mettre à jour une étape du profil
- `/profile/completion/availability` (PUT) - Mettre à jour la disponibilité
- `/profile/portfolio` (POST) - Ajouter un élément au portfolio
- `/profile/portfolio/{path}` (DELETE) - Supprimer un élément du portfolio
- `/profile/upload-avatar` (POST) - Uploader un avatar
- `/profile/availability` (PUT) - Mettre à jour la disponibilité

### Nouvelles routes standardisées
- `/profile` (GET) - Récupérer le profil de l'utilisateur authentifié (client ou professionnel)
- `/profile` (PUT) - Mettre à jour le profil de l'utilisateur authentifié
- `/profile/complete` (POST) - Compléter le profil (première connexion)
- `/profile/completion` (GET) - Récupérer le statut de complétion du profil
- `/profile/avatar` (POST) - Uploader un avatar
- `/profile/portfolio` (POST) - Ajouter un élément au portfolio
- `/profile/portfolio/{id}` (DELETE) - Supprimer un élément du portfolio
- `/profile/availability` (PUT) - Mettre à jour la disponibilité

## Routes de dashboard

### Routes actuelles
- `/dashboard` (GET) - Récupérer les données du tableau de bord
- `/projects` (GET, POST, PUT, DELETE) - Gérer les projets du dashboard

### Nouvelles routes standardisées
- `/dashboard` (GET) - Récupérer les données du tableau de bord
- `/dashboard/projects` (GET, POST) - Lister et créer des projets
- `/dashboard/projects/{id}` (GET, PUT, DELETE) - Gérer un projet spécifique
- `/dashboard/projects/{id}/attachments/{index}` (DELETE) - Supprimer une pièce jointe

## Routes de professionnels

### Routes actuelles
- `/professionals/public` (GET) - Lister les professionnels publiquement
- `/professionals/protected` (GET) - Lister les professionnels (authentifié)
- `/professionals/availability` (GET) - Lister les disponibilités des professionnels
- `/professionals/{professionalId}/attributed-offers` (GET) - Récupérer les offres attribuées

### Nouvelles routes standardisées
- `/professionals` (GET) - Lister les professionnels (avec paramètre ?public=true pour l'accès public)
- `/professionals/{id}` (GET) - Récupérer les détails d'un professionnel
- `/professionals/{id}/offers` (GET) - Récupérer les offres d'un professionnel
