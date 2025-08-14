# 🔧 Guide de Dépannage Meilisearch - Hi3D

## 🚨 Problème : La réindexation ne fonctionne pas

### Symptômes observés
- ❌ Erreur "Could not resolve host" dans les logs
- ❌ La réindexation échoue dans l'interface admin
- ❌ Les recherches ne retournent pas de résultats

### 🔍 Diagnostic rapide

1. **Accédez à l'interface admin** : `/admin/meilisearch-management`
2. **Cliquez sur "Diagnostic complet"** pour voir l'état détaillé
3. **Analysez les résultats** :

#### ✅ Si tout est vert
- La connexion fonctionne
- Procédez à la réindexation

#### ❌ Si erreur DNS/Connexion
- Votre serveur Meilisearch cloud n'est plus accessible
- Suivez les solutions ci-dessous

## 🛠️ Solutions

### Solution 1 : Vérifier le serveur cloud Meilisearch

1. **Vérifiez l'état de votre serveur cloud**
   - Connectez-vous à votre tableau de bord Meilisearch cloud
   - Vérifiez que le serveur est actif
   - Vérifiez que l'URL et la clé API sont correctes

2. **Mettre à jour la configuration si nécessaire**
   - Dans l'admin : `/admin/meilisearch-management`
   - Mettez à jour `MEILISEARCH_HOST` et `MEILISEARCH_KEY`
   - Testez la connexion
   - Réindexez

### Solution 2 : Configurer un serveur Meilisearch local

Si votre serveur cloud n'est plus disponible, configurez un serveur local :

#### Avec Docker (Recommandé)

```bash
# 1. Démarrer Meilisearch
docker run -d \
  --name meilisearch \
  -p 7700:7700 \
  -v $(pwd)/meili_data:/meili_data \
  getmeili/meilisearch:latest

# 2. Vérifier que ça fonctionne
curl http://127.0.0.1:7700/health
```

#### Configuration dans Hi3D

1. **Accédez à** `/admin/meilisearch-management`
2. **Mettez à jour la configuration** :
   - `MEILISEARCH_HOST` : `http://127.0.0.1:7700`
   - `MEILISEARCH_KEY` : (laisser vide pour le développement local)
3. **Testez la connexion**
4. **Réindexez tous les modèles**

### Solution 3 : Installation directe de Meilisearch

```bash
# Télécharger Meilisearch
curl -L https://install.meilisearch.com | sh

# Démarrer le serveur
./meilisearch --http-addr 127.0.0.1:7700
```

## 📋 Checklist de vérification

- [ ] Le serveur Meilisearch répond à `/health`
- [ ] La configuration dans `.env` est correcte
- [ ] Le test de connexion dans l'admin fonctionne
- [ ] Les modèles ont le trait `Searchable`
- [ ] La réindexation se termine sans erreur

## 🔄 Processus de réindexation

1. **Vider les index existants** (optionnel)
   ```bash
   php artisan scout:flush "App\Models\ProfessionalProfile"
   php artisan scout:flush "App\Models\ServiceOffer"
   php artisan scout:flush "App\Models\Achievement"
   ```

2. **Réindexer via l'interface admin**
   - Allez dans `/admin/meilisearch-management`
   - Cliquez sur "Réindexer tout"

3. **Ou réindexer via Artisan**
   ```bash
   php artisan scout:import "App\Models\ProfessionalProfile"
   php artisan scout:import "App\Models\ServiceOffer"
   php artisan scout:import "App\Models\Achievement"
   ```

## 📊 Vérification des résultats

### Via l'interface Meilisearch
```bash
# Vérifier les index
curl http://127.0.0.1:7700/indexes

# Vérifier le contenu d'un index
curl http://127.0.0.1:7700/indexes/professional_profiles_index/documents
```

### Via l'API Hi3D
```bash
# Test de recherche
curl -X POST http://localhost:8000/api/search \
  -H "Content-Type: application/json" \
  -d '{"query": "test", "models": ["professional_profiles"]}'
```

## 🆘 Support

Si les problèmes persistent :

1. **Vérifiez les logs** : `storage/logs/laravel.log`
2. **Utilisez le diagnostic complet** dans l'interface admin
3. **Vérifiez la connectivité réseau**
4. **Contactez l'équipe de développement** avec :
   - Les résultats du diagnostic
   - Les logs d'erreur
   - La configuration actuelle

## 📝 Notes importantes

- **Sauvegardez** toujours votre configuration avant modification
- **Testez** la connexion après chaque changement
- **Réindexez** après changement de serveur
- **Surveillez** les logs pendant la réindexation
