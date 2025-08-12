# Guide de Démarrage Rapide - Tests Postman

## 🚀 Configuration Rapide (5 minutes)

### 1. Prérequis
- ✅ Serveur Laravel démarré (`php artisan serve`)
- ✅ Base de données configurée et migrée
- ✅ Postman installé

### 2. Import des fichiers Postman

#### Importer la collection
1. Ouvrir Postman
2. Cliquer sur **Import** (bouton en haut à gauche)
3. Glisser-déposer le fichier `postman-collection.json`
4. Cliquer sur **Import**

#### Importer l'environnement
1. Cliquer sur l'icône **⚙️** (Manage Environments)
2. Cliquer sur **Import**
3. Sélectionner le fichier `postman-environment.json`
4. Cliquer sur **Import**
5. Sélectionner l'environnement **"Open Offers Workflow Environment"**

### 3. Préparation des données de test

#### Créer des utilisateurs de test
```bash
# Dans le terminal Laravel
php artisan tinker

# Créer un client
$client = \App\Models\User::factory()->create([
    'email' => 'client@example.com',
    'password' => bcrypt('password'),
    'is_professional' => false,
    'email_verified_at' => now()
]);

# Créer un professionnel
$pro = \App\Models\User::factory()->create([
    'email' => 'professional@example.com', 
    'password' => bcrypt('password'),
    'is_professional' => true,
    'email_verified_at' => now()
]);

# Créer le profil professionnel
\App\Models\ProfessionalProfile::factory()->create(['user_id' => $pro->id]);

exit
```

## 🧪 Test Rapide (10 minutes)

### Séquence de test automatisée

1. **Authentification** 
   - Exécuter `Login Client` → Token sauvegardé automatiquement
   - Exécuter `Login Professional` → Token sauvegardé automatiquement

2. **Création d'offre**
   - Exécuter `Create Open Offer` → offer_id sauvegardé automatiquement

3. **Candidature**
   - Exécuter `Apply to Offer` → application_id sauvegardé automatiquement

4. **Gestion candidature**
   - Exécuter `Accept Application` → Candidature acceptée
   - Exécuter `View Accepted Applications` → Vérifier la liste

5. **Attribution finale**
   - Exécuter `Assign Offer to Professional` → Offre attribuée

### Vérifications rapides

| Étape | Statut attendu | Vérification |
|-------|----------------|--------------|
| Après création offre | `status: "open"` | ✅ |
| Après candidature | `status: "pending"` | ✅ |
| Après acceptation | `status: "accepted"` + offre reste `"open"` | ✅ |
| Après attribution | Offre `"in_progress"` | ✅ |

## 🔍 Tests d'Erreur Rapides

### Test 1 : Attribution sans acceptation
```http
POST /api/open-offers/{{offer_id}}/assign
{
    "application_id": 99999
}
```
**Attendu :** `400 - La candidature spécifiée n'appartient pas à cette offre`

### Test 2 : Accès non autorisé
```http
PATCH /api/offer-applications/{{application_id}}/status
Authorization: Bearer invalid_token
```
**Attendu :** `401 - Unauthorized`

## 📊 Résultats Attendus

### Workflow Complet Réussi

```json
// 1. Création offre
{
    "open_offer": {
        "id": 1,
        "status": "open",
        "title": "Développement d'une application mobile"
    }
}

// 2. Candidature
{
    "application": {
        "id": 1,
        "status": "pending",
        "proposal": "Je suis expert en React Native..."
    }
}

// 3. Acceptation
{
    "application": {
        "id": 1,
        "status": "accepted"
    },
    "message": "Statut de la candidature mis à jour avec succès."
}

// 4. Attribution
{
    "open_offer": {
        "id": 1,
        "status": "in_progress"
    },
    "assigned_application": {
        "id": 1,
        "status": "accepted"
    },
    "message": "Offre attribuée avec succès au professionnel choisi."
}
```

## 🐛 Dépannage Express

### Problème : Token non reconnu
**Solution :** Vérifier que le token commence par "Bearer "

### Problème : 404 Not Found
**Solution :** Vérifier que le serveur Laravel est démarré sur le bon port

### Problème : 422 Validation Error
**Solution :** Vérifier le format JSON et les champs obligatoires

### Problème : Variables non définies
**Solution :** Exécuter les requêtes dans l'ordre (Auth → Create → Apply → Accept → Assign)

## 📝 Checklist Finale

- [ ] Collection importée
- [ ] Environnement sélectionné
- [ ] Utilisateurs de test créés
- [ ] Serveur Laravel démarré
- [ ] Séquence de test complète exécutée
- [ ] Tous les statuts vérifiés
- [ ] Tests d'erreur validés

## 🎯 Points Clés à Retenir

1. **Séparation claire** : Acceptation ≠ Attribution
2. **Statuts d'offre** : `open` → `in_progress` seulement lors de l'attribution
3. **Gestion automatique** : Les autres candidatures sont rejetées automatiquement
4. **Sécurité** : Seul le propriétaire de l'offre peut gérer les candidatures
5. **Flexibilité** : Le client peut accepter plusieurs candidatures avant de choisir

## 📞 Support

En cas de problème :
1. Vérifier les logs Laravel : `tail -f storage/logs/laravel.log`
2. Vérifier la console Postman pour les erreurs JavaScript
3. Vérifier que toutes les migrations sont appliquées : `php artisan migrate:status`
