ActiveCampaign Integration
=========================

Résumé
------
Cette intégration utilise le service `App\Services\ActiveCampaignService` pour communiquer avec l'API officielle ActiveCampaign via le Http Client de Laravel. Les credentials sont stockés et gérés via Filament (ressource `ActiveCampaignSettingResource`).

Points clés
----------
- Credentials dynamiques stockés en base dans la table `active_campaign_settings`.
- Ressource Filament: `ActiveCampaignSettingResource` pour créer/modifier/activer la configuration.
- Service principal: `App\Services\ActiveCampaignService` (déjà présent).
- Job en queue: `App\Jobs\ActiveCampaignSyncContactJob` pour opérations en arrière-plan.
- Exemples de controller: `App\Http\Controllers\ActiveCampaignController` (méthodes `syncNow` et `syncQueued`).

Bonne pratique
--------------
- Toujours utiliser la version queued (`syncQueued`) pour workflows non-critique afin d'éviter latence utilisateur.
- Logger les réponses d'API et erreurs (déjà présent dans le service).
- Prévoir des retries et backoff pour les appels réseau (implémenté dans le Job via `$tries` et `backoff`).
- Valider le mapping JSON dans l'admin avant utilisation si vous activez l'option mapping.

Exemples d'utilisation
----------------------
1) Appel synchrone (contrôleur):

POST /api/activecampaign/sync-now
Payload: {"email":"user@example.com","firstName":"Jean","lastName":"Dupont"}

2) Enqueue job:

POST /api/activecampaign/sync-queued
Payload: {"email":"user@example.com","firstName":"Jean"}

Notes pour le déploiement
-------------------------
- Assurez-vous que la queue est configurée en production et qu'un worker tourne (supervisor/systemd).
- Ne stockez pas la clé API en clair dans les logs. Le modèle `ActiveCampaignSetting` masque `api_key` par défaut.
