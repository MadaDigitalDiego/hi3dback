# Configuration Meilisearch dans le Back-Office

## Vue d'ensemble

Le back-office Hi3D permet aux super-administrateurs et administrateurs de gérer la configuration Meilisearch directement depuis l'interface web, sans avoir besoin d'accéder aux fichiers de configuration.

## Accès à la fonctionnalité

### Permissions requises
- **Super Admin** : Accès complet (lecture et modification)
- **Admin** : Accès complet (lecture et modification)
- **Autres rôles** : Aucun accès

### Navigation
1. Connectez-vous au back-office : `http://votre-domaine.com/admin`
2. Dans le menu de navigation, allez dans **"Outils d'administration"**
3. Cliquez sur **"Gestion Meilisearch"**

## Fonctionnalités disponibles

### 1. Configuration des paramètres

#### Paramètres modifiables :
- **MEILISEARCH_HOST** : URL complète du serveur Meilisearch
  - Format : `https://ms-xxxxx.meilisearch.io`
  - Validation : Doit être une URL valide
  
- **MEILISEARCH_KEY** : Clé API pour l'authentification
  - Champ masqué par défaut (type password)
  - Bouton "révéler" pour afficher la clé
  - Validation : Champ requis

#### Actions disponibles :
- **Sauvegarder la configuration** : Met à jour le fichier `.env`
- **Tester la connexion** : Vérifie que les paramètres fonctionnent
- **Réindexer tout** : Réimporte tous les modèles dans Meilisearch
- **Vider les index** : Supprime tous les documents des index

### 2. Statut et monitoring

#### Informations affichées :
- **Service actif** : État de la connexion Meilisearch
- **Index configurés** : Nombre d'index actifs
- **Dernière synchronisation** : Horodatage de la dernière sync
- **Configuration actuelle** : Host Meilisearch configuré

#### Index surveillés :
- `professional_profiles_index` : Profils professionnels
- `service_offers_index` : Offres de service
- `achievements_index` : Réalisations

## Utilisation

### Modifier la configuration

1. **Accédez à la page** "Gestion Meilisearch"
2. **Modifiez les champs** dans la section "Configuration Meilisearch"
3. **Cliquez sur "Sauvegarder la configuration"**
4. **Testez la connexion** avec le bouton "Tester la connexion"

### Tester la configuration

Le bouton **"Tester la connexion"** effectue :
- Une requête HTTP vers l'endpoint `/health` de Meilisearch
- Vérification de l'authentification avec la clé API
- Affichage du statut de santé du service

### Réindexer les données

Après avoir modifié la configuration :
1. **Testez d'abord la connexion**
2. **Cliquez sur "Réindexer tout"** pour synchroniser les données
3. **Confirmez l'action** dans la modal de confirmation

## Sécurité

### Contrôle d'accès
- Seuls les utilisateurs avec le rôle `admin` ou `super_admin` peuvent accéder à cette page
- La méthode `canAccess()` vérifie automatiquement les permissions

### Protection des données sensibles
- La clé API est masquée par défaut
- Les modifications sont loggées dans les logs Laravel
- Le cache de configuration est automatiquement vidé après modification

### Validation
- URL du host validée comme URL valide
- Clé API requise et non vide
- Test de connexion avant utilisation

## Dépannage

### Erreurs courantes

#### "Impossible de se connecter à Meilisearch"
- Vérifiez que l'URL du host est correcte
- Vérifiez que la clé API est valide
- Vérifiez que le serveur Meilisearch est accessible

#### "Could not resolve host" (Erreur DNS)
- **Cause** : L'URL Meilisearch cloud n'est plus accessible
- **Solution** :
  1. Vérifiez que votre serveur Meilisearch cloud est actif
  2. Contactez votre fournisseur cloud Meilisearch
  3. Ou configurez un serveur Meilisearch local (voir section ci-dessous)

#### "Erreur lors de la sauvegarde"
- Vérifiez les permissions d'écriture sur le fichier `.env`
- Vérifiez que le fichier `.env` existe

#### "Erreur lors de la réindexation"
- Utilisez le **Diagnostic complet** dans l'interface admin
- Vérifiez la configuration Meilisearch
- Vérifiez les logs Laravel pour plus de détails

### Configuration Meilisearch local (alternative au cloud)

Si votre serveur cloud Meilisearch n'est plus accessible, vous pouvez configurer un serveur local :

#### Option 1 : Docker (Recommandé)
```bash
# Démarrer Meilisearch avec Docker
docker run -it --rm \
  -p 7700:7700 \
  -v $(pwd)/meili_data:/meili_data \
  getmeili/meilisearch:latest

# Puis dans l'interface admin, mettez à jour :
# MEILISEARCH_HOST=http://127.0.0.1:7700
# MEILISEARCH_KEY= (laisser vide pour le développement)
```

#### Option 2 : Installation directe
```bash
# Télécharger et installer Meilisearch
curl -L https://install.meilisearch.com | sh
./meilisearch --http-addr 127.0.0.1:7700
```

### Diagnostic automatique

L'interface admin dispose maintenant d'un **Diagnostic complet** qui vérifie :
- ✅ Configuration actuelle
- 🌐 Résolution DNS
- 🔗 Connexion HTTP
- 📊 Modèles indexables
- 🔧 Recommandations personnalisées

### Logs
Les erreurs sont automatiquement loggées dans :
- `storage/logs/laravel.log`
- Notifications Filament en temps réel

## Commandes Artisan alternatives

Si l'interface web n'est pas disponible, vous pouvez utiliser ces commandes :

```bash
# Réindexer un modèle spécifique
php artisan scout:import "App\Models\ProfessionalProfile"
php artisan scout:import "App\Models\ServiceOffer"
php artisan scout:import "App\Models\Achievement"

# Vider un index
php artisan scout:flush "App\Models\ProfessionalProfile"

# Vider le cache de configuration
php artisan config:clear
```

## Bonnes pratiques

1. **Toujours tester** la connexion après modification
2. **Sauvegarder** l'ancienne configuration avant modification
3. **Réindexer** après changement de serveur Meilisearch
4. **Surveiller** les logs après modification
5. **Documenter** les changements de configuration
