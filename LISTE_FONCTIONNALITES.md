# 📋 LISTE COMPLÈTE DES FONCTIONNALITÉS DU PROJET

## 🎯 Vue d'ensemble
Plateforme de mise en relation entre clients et professionnels 3D avec gestion d'offres, services, profils, messages et système de recherche avancé.

---

## 🔐 1. AUTHENTIFICATION & UTILISATEURS

### 1.1 Gestion des utilisateurs
- ✅ **Inscription** (`POST /api/register`)
  - Inscription client ou professionnel
  - Création automatique du profil correspondant
  - Envoi d'email de vérification
  
- ✅ **Connexion** (`POST /api/login`)
  - Authentification par email/mot de passe
  - Génération de token Sanctum
  - Vérification de l'email requis
  
- ✅ **Déconnexion** (`POST /api/logout`)
  - Suppression des tokens d'authentification
  
- ✅ **Informations utilisateur** (`GET /api/user`)
  - Récupération des données de l'utilisateur authentifié

### 1.2 Gestion des mots de passe
- ✅ **Mot de passe oublié** (`POST /api/password/forgot`)
  - Demande de réinitialisation
  - Envoi d'email avec lien de réinitialisation
  
- ✅ **Réinitialisation mot de passe** (`POST /api/password/reset`)
  - Nouveau mot de passe avec token de validation

### 1.3 Vérification d'email
- ✅ **Vérification email** (`GET /api/email/verify/{id}/{hash}`)
  - Validation du compte via lien signé
  
- ✅ **Renvoyer email de vérification** (`GET /api/email/verify/resend`)
  - Nouveau lien de vérification

### 1.4 Authentification Gmail
- ✅ **Redirection Gmail** (`GET /api/auth/gmail/redirect`)
  - Initiation de l'authentification OAuth Gmail
  
- ✅ **Callback frontend** (`GET /api/auth/gmail/frontend-callback`)
  - Gestion du callback OAuth côté frontend
  
- ✅ **Statut Gmail** (`GET /api/auth/gmail/status`)
  - Vérification de l'état de l'authentification Gmail

---

## 👤 2. GESTION DES PROFILS

### 2.1 Profil standardisé (ProfileController)
- ✅ **Récupérer le profil** (`GET /api/profile`)
- ✅ **Mettre à jour le profil** (`PUT /api/profile`)
- ✅ **Compléter le profil** (`POST /api/profile/complete`)
- ✅ **Compléter le profil (client)** (`POST /api/profile/complete-profile`)
- ✅ **Statut de complétion** (`GET /api/profile/completion`)
- ✅ **Upload avatar** (`POST /api/profile/avatar`)
- ✅ **Upload image de couverture** (`POST /api/profile/cover`)
- ✅ **Supprimer avatar** (`DELETE /api/delete-profile-avatar`)
- ✅ **Upload élément portfolio** (`POST /api/profile/portfolio`)
- ✅ **Supprimer élément portfolio** (`DELETE /api/profile/portfolio/{id}`)
- ✅ **Mettre à jour disponibilité** (`PUT /api/profile/availability`)

### 2.2 Profil client spécifique
- ✅ **Récupérer profil client** (`GET /api/profile/client`)
- ✅ **Mettre à jour profil client** (`PUT /api/profile/client`)
- ✅ **Créer profil client** (`POST /api/profile/client`)
- ✅ **Mise à jour JSON** (`POST /api/profile/client/json`)
- ✅ **Mise à jour avec avatar** (`POST /api/profile/client/with-avatar`)

### 2.3 Nouveau profil unifié (NewProfileController)
- ✅ **Récupérer le profil** (`GET /api/profile/new`)
- ✅ **Mettre à jour le profil** (`PUT /api/profile/new`)
- ✅ **Compléter le profil** (`POST /api/profile/new/complete`)
- ✅ **Statut de complétion** (`GET /api/profile/new/completion`)
- ✅ **Upload avatar** (`POST /api/profile/new/avatar`)
- ✅ **Upload élément portfolio** (`POST /api/profile/new/portfolio`)
- ✅ **Supprimer élément portfolio** (`DELETE /api/profile/new/portfolio/{id}`)
- ✅ **Mettre à jour disponibilité** (`PUT /api/profile/new/availability`)

---

## 💼 3. PROFESSIONNELS

### 3.1 Liste et recherche des professionnels
- ✅ **Liste des professionnels** (`GET /api/professionals`)
- ✅ **Filtrer les professionnels** (`GET /api/professionals/filter`)
- ✅ **Disponibilité des professionnels** (`GET /api/professionals/availability`)
- ✅ **Tous les profils freelance** (`GET /api/freelance-profiles`)
- ✅ **Détails d'un professionnel** (`GET /api/professionals/{id}`)

### 3.2 Offres attribuées aux professionnels
- ✅ **Offres attribuées** (`GET /api/professionals/{id}/offers`)
- ✅ **Réalisations par professionnel** (`GET /api/professionals/{id}/achievements`)
- ✅ **Services d'un professionnel** (`GET /api/professionals/{id}/service-offers`)

### 3.3 Interactions avec les profils professionnels
- ✅ **Enregistrer une vue** (`POST /api/professionals/{professionalProfile}/view`)
- ✅ **Statistiques de vues** (`GET /api/professionals/{professionalProfile}/view/stats`)
- ✅ **Vérifier si déjà vu** (`GET /api/professionals/{professionalProfile}/view/status`)
- ✅ **Ajouter un like** (`POST /api/professionals/{professionalProfile}/like`)
- ✅ **Retirer un like** (`DELETE /api/professionals/{professionalProfile}/like`)
- ✅ **Toggle like** (`POST /api/professionals/{professionalProfile}/like/toggle`)
- ✅ **Statut du like** (`GET /api/professionals/{professionalProfile}/like/status`)

---

## 🎨 4. OFFRES OUVERTES (OPEN OFFERS)

### 4.1 Gestion CRUD des offres
- ✅ **Créer une offre** (`POST /api/open-offers`)
- ✅ **Lister les offres** (`GET /api/open-offers`)
- ✅ **Détails d'une offre** (`GET /api/open-offers/{open_offer}`)
- ✅ **Mettre à jour une offre** (`PUT /api/open-offers/{open_offer}`)
- ✅ **Supprimer une offre** (`DELETE /api/open-offers/{open_offer}`)

### 4.2 Candidatures aux offres
- ✅ **Postuler à une offre** (`POST /api/open-offers/{open_offer}/apply`)
- ✅ **Liste des candidatures** (`GET /api/open-offers/{open_offer}/applications`)
- ✅ **Candidatures acceptées** (`GET /api/open-offers/{open_offer}/accepted-applications`)
- ✅ **Mettre à jour statut candidature** (`PATCH /api/offer-applications/{application}/status`)
- ✅ **Candidatures reçues** (`GET /api/offer-applications/received`)
- ✅ **Accepter une candidature** (`PUT /api/offer-applications/{id}/accept`)
- ✅ **Refuser une candidature** (`PUT /api/offer-applications/{id}/decline`)

### 4.3 Gestion du workflow des offres
- ✅ **Attribuer une offre** (`POST /api/open-offers/{openOffer}/assign`)
- ✅ **Fermer une offre** (`PUT /api/open-offers/{openOffer}/close`)
- ✅ **Marquer comme complétée** (`PUT /api/open-offers/{openOffer}/complete`)
- ✅ **Refuser une offre** (`POST /api/open-offers/{openOffer}/reject`)
- ✅ **Inviter un professionnel** (`POST /api/open-offers/{openOffer}/invite`)

### 4.4 Offres du client
- ✅ **Offres du client** (`GET /api/client/open-offers`)
- ✅ **Offres en cours** (`GET /api/client/open-offers/in-progress`)
- ✅ **Offres en attente** (`GET /api/client/open-offers/pending`)
- ✅ **Offres complétées** (`GET /api/client/open-offers/completed`)
- ✅ **Offres fermées/complétées** (`GET /api/client/closed-completed-offers`)

### 4.5 Outils de débogage (dev)
- ✅ **Debug matching** (`POST /api/open-offers/debug-matching`)
- ✅ **Test envoi email** (`POST /api/open-offers/test-email`)

---

## 💬 5. MESSAGES

### 5.1 Messages liés aux offres
- ✅ **Liste des messages d'une offre** (`GET /api/open-offers/{openOffer}/messages`)
- ✅ **Envoyer un message** (`POST /api/open-offers/{openOffer}/messages`)

### 5.2 Messages de service
- ✅ **Envoyer un message** (`POST /api/messages/send`)
- ✅ **Liste des messages** (`GET /api/messages`)
- ✅ **Messages d'un service** (`GET /api/messages/service/{serviceId}`)
- ✅ **Marquer comme lu** (`PUT /api/messages/{id}/read`)
- ✅ **Conversation avec un utilisateur** (`GET /api/messages/conversation/{userId}`)

### 5.3 Notifications de messages
- ✅ **Liste des notifications** (`GET /api/notif-messages`)
- ✅ **Marquer comme lu** (`PUT /api/notif-messages/{id}/read`)
- ✅ **Nombre de non-lus** (`GET /api/notif-messages/count`)
- ✅ **Supprimer une notification** (`DELETE /api/notif-messages/{id}`)

---

## 🛍️ 6. SERVICES (SERVICE OFFERS)

### 6.1 Gestion CRUD des services
- ✅ **Créer un service** (`POST /api/service-offers`)
- ✅ **Liste des services** (`GET /api/service-offers`)
- ✅ **Détails d'un service** (`GET /api/service-offers/{serviceoffers}`)
- ✅ **Mettre à jour un service** (`POST /api/service-offers/{serviceoffers}`)
- ✅ **Supprimer un service** (`DELETE /api/service-offers/{serviceoffers}`)

### 6.2 Recherche et filtrage
- ✅ **Filtrer les services** (`GET /api/service-offers/filter`)
- ✅ **Vue publique d'un service** (`GET /api/service-offers/{id}/public`)
- ✅ **Télécharger fichier service** (`GET /api/service-offers/{serviceOffer}/download`)

### 6.3 Interactions avec les services
- ✅ **Enregistrer une vue** (`POST /api/service-offers/{serviceOffer}/view`)
- ✅ **Statistiques de vues** (`GET /api/service-offers/{serviceOffer}/view/stats`)
- ✅ **Vérifier si déjà vu** (`GET /api/service-offers/{serviceOffer}/view/status`)
- ✅ **Ajouter un like** (`POST /api/service-offers/{serviceOffer}/like`)
- ✅ **Retirer un like** (`DELETE /api/service-offers/{serviceOffer}/like`)
- ✅ **Toggle like** (`POST /api/service-offers/{serviceOffer}/like/toggle`)
- ✅ **Statut du like** (`GET /api/service-offers/{serviceOffer}/like/status`)

---

## 🏆 7. EXPÉRIENCES & RÉALISATIONS

### 7.1 Expériences (ExperienceController)
- ✅ **Créer une expérience** (`POST /api/experiences`)
- ✅ **Liste des expériences** (`GET /api/experiences`)
- ✅ **Détails d'une expérience** (`GET /api/experiences/{experience}`)
- ✅ **Mettre à jour une expérience** (`PUT /api/experiences/{experience}`)
- ✅ **Supprimer une expérience** (`DELETE /api/experiences/{experience}`)

### 7.2 Réalisations (AchievementController)
- ✅ **Créer une réalisation** (`POST /api/achievements`)
- ✅ **Liste des réalisations** (`GET /api/achievements`)
- ✅ **Détails d'une réalisation** (`GET /api/achievements/{achievement}`)
- ✅ **Mettre à jour une réalisation** (`POST /api/achievements/{achievement}`)
- ✅ **Supprimer une réalisation** (`DELETE /api/achievements/{achievement}`)
- ✅ **Télécharger fichier** (`GET /api/achievements/{achievement}/download`)
- ✅ **Réalisations pour l'explorateur** (`GET /api/explorer/achievements`)

### 7.3 Projets liés aux expériences
- ✅ **Créer un projet** (`POST /api/experiences/{experience}/projects`)
- ✅ **Détails d'un projet** (`GET /api/projects/{project}`)
- ✅ **Mettre à jour un projet** (`PUT /api/projects/{project}`)
- ✅ **Supprimer un projet** (`DELETE /api/projects/{project}`)

---

## 📁 8. PROJETS DU TABLEAU DE BORD

### 8.1 Gestion des projets dashboard
- ✅ **Créer un projet** (`POST /api/dashboard/projects`)
- ✅ **Liste des projets** (`GET /api/dashboard/projects`)
- ✅ **Détails d'un projet** (`GET /api/dashboard/projects/{project}`)
- ✅ **Mettre à jour un projet** (`PUT /api/dashboard/projects/{project}`)
- ✅ **Supprimer un projet** (`DELETE /api/dashboard/projects/{project}`)
- ✅ **Filtrer les projets** (`GET /api/dashboard/projects/filter`)
- ✅ **Supprimer une pièce jointe** (`DELETE /api/dashboard/projects/{project}/attachments/{index}`)

---

## 🎯 9. TABLEAU DE BORD

### 9.1 Données du tableau de bord
- ✅ **Données du dashboard** (`GET /api/dashboard`)
  - Statistiques personnalisées (professionnel/client)
  - Projets actifs
  - Revenus/dépenses
  - Activités récentes
  - Professionnels recommandés (clients)

### 9.2 Activités
- ✅ **Toutes les activités** (`GET /api/activities`)
  - Historique complet des activités
  - Candidatures
  - Projets attribués
  - Projets complétés

---

## 🔍 10. RECHERCHE GLOBALE

### 10.1 Recherche multi-modèles
- ✅ **Recherche globale** (`GET /api/search`)
  - Recherche dans professionnels, services, réalisations
  - Filtres avancés
  - Pagination
  - Cache intelligent
  
- ✅ **Recherche professionnels** (`GET /api/search/professionals`)
- ✅ **Recherche services** (`GET /api/search/services`)
- ✅ **Recherche réalisations** (`GET /api/search/achievements`)

### 10.2 Suggestions et statistiques
- ✅ **Suggestions de recherche** (`GET /api/search/suggestions`)
- ✅ **Statistiques de recherche** (`GET /api/search/stats`)
- ✅ **Recherches populaires** (`GET /api/search/popular`)
- ✅ **Métriques de recherche** (`GET /api/search/metrics`)
- ✅ **Métriques temps réel** (`GET /api/search/metrics/realtime`)

### 10.3 Administration recherche
- ✅ **Vider le cache** (`DELETE /api/search/cache`)
- ✅ **Nettoyer les métriques** (`DELETE /api/search/metrics`)

---

## 🗂️ 11. CATÉGORIES

### 11.1 Gestion des catégories
- ✅ **Liste des catégories** (`GET /api/categories`)
- ✅ **Hiérarchie des catégories** (`GET /api/categories/hierarchy`)
- ✅ **Détails d'une catégorie** (`GET /api/categories/{id}`)
- ✅ **Sous-catégories** (`GET /api/categories/parent/{parentValue}`)

---

## 🌐 12. EXPLORATEUR (PUBLIC)

### 12.1 Découverte publique
- ✅ **Liste des professionnels** (`GET /api/explorer/professionals`)
- ✅ **Détails d'un professionnel** (`GET /api/explorer/professionals/{id}`)
- ✅ **Liste des services** (`GET /api/explorer/services`)
- ✅ **Statistiques de recherche** (`GET /api/explorer/search-stats`)
- ✅ **Données indexées** (`GET /api/explorer/indexed-data`)

---

## 📎 13. GESTION DES FICHIERS

### 13.1 Upload et gestion
- ✅ **Upload de fichier** (`POST /api/files/upload`)
- ✅ **Liste des fichiers** (`GET /api/files`)
- ✅ **Détails d'un fichier** (`GET /api/files/{file}`)
- ✅ **Télécharger un fichier** (`GET /api/files/{file}/download`)
- ✅ **Supprimer un fichier** (`DELETE /api/files/{file}`)

### 13.2 Fichiers liés aux messages
- ✅ **Fichiers d'un message** (`GET /api/files/message/{messageId}`)

### 13.3 Statistiques (admin)
- ✅ **Statistiques fichiers** (`GET /api/files/admin/stats`)

---

## 🎨 14. IMAGES HERO

### 14.1 Images publiques
- ✅ **Liste des images hero** (`GET /api/hero-images`)
- ✅ **Statistiques** (`GET /api/hero-images/stats`)
- ✅ **Détails d'une image** (`GET /api/hero-images/{heroImage}`)

### 14.2 Administration (admin)
- ✅ **Toutes les images** (`GET /api/admin/hero-images/all`)

---

## 💳 15. ABONNEMENTS

### 15.1 Gestion des abonnements
- ✅ **Créer un abonnement** (`POST /api/subscriptions`)
- ✅ **Confirmer le paiement** (`POST /api/subscriptions/confirm`)

---

## 📞 16. CONTACTS

### 16.1 Gestion des contacts
- ✅ **Créer un contact** (`POST /api/contacts`)
- ✅ **Liste des contacts** (`GET /api/contacts`)
- ✅ **Détails d'un contact** (`GET /api/contacts/{contact}`)
- ✅ **Mettre à jour un contact** (`PUT /api/contacts/{contact}`)
- ✅ **Supprimer un contact** (`DELETE /api/contacts/{contact}`)

---

## 🏥 17. SANTÉ & MONITORING

### 17.1 Endpoints de test
- ✅ **Ping** (`GET /api/ping`)
- ✅ **Health check** (`GET /api/health-check`)

---

## 📊 STATISTIQUES GLOBALES

### Résumé par catégorie
- **Authentification** : 8 fonctionnalités
- **Profils** : 28 fonctionnalités
- **Professionnels** : 12 fonctionnalités
- **Offres ouvertes** : 18 fonctionnalités
- **Messages** : 9 fonctionnalités
- **Services** : 11 fonctionnalités
- **Expériences & Réalisations** : 11 fonctionnalités
- **Projets Dashboard** : 7 fonctionnalités
- **Tableau de bord** : 2 fonctionnalités
- **Recherche** : 11 fonctionnalités
- **Catégories** : 4 fonctionnalités
- **Explorateur** : 5 fonctionnalités
- **Fichiers** : 8 fonctionnalités
- **Images Hero** : 4 fonctionnalités
- **Abonnements** : 2 fonctionnalités
- **Contacts** : 5 fonctionnalités
- **Monitoring** : 2 fonctionnalités

### **TOTAL : ~147 fonctionnalités API**

---

## 🔐 SÉCURITÉ & PERMISSIONS

### Middleware appliqués
- ✅ **auth:sanctum** : Authentification requise
- ✅ **verified** : Email vérifié requis
- ✅ **search.ratelimit** : Limitation de taux pour la recherche (100 req/min)
- ✅ **admin.access** : Accès administrateur pour certaines routes

### Types d'utilisateurs
- ✅ **Client** : Création d'offres, gestion de projets
- ✅ **Professionnel** : Candidatures, gestion de services
- ✅ **Admin** : Accès aux statistiques et administration

---

## 📝 NOTES IMPORTANTES

1. **Deux systèmes de profils** :
   - Ancien système (`ProfileController`)
   - Nouveau système unifié (`NewProfileController`)

2. **Deux types d'offres** :
   - Offres ouvertes (open-offers) : clients créent, professionnels postulent
   - Offres de service (service-offers) : professionnels proposent des services

3. **Système de recherche avancé** :
   - Utilise Meilisearch pour la recherche full-text
   - Cache intelligent pour améliorer les performances
   - Métriques et statistiques de recherche

4. **Workflow des offres** :
   - Séparation claire entre acceptation des candidatures et attribution
   - Gestion des statuts (open, in_progress, completed, closed)

5. **Interactions sociales** :
   - Système de vues et likes pour professionnels et services
   - Statistiques d'engagement

---

*Document généré à partir de l'analyse complète de `routes/api.php`*
