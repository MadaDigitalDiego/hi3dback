#!/bin/bash

# Script de vérification post-déploiement pour Laravel Forge
# Ce script vérifie que l'automatisation Meilisearch fonctionne correctement

set -e

echo "🔍 Vérification post-déploiement - Automatisation Meilisearch"
echo "============================================================"

# Variables
SITE_PATH=${FORGE_SITE_PATH:-$(pwd)}
PHP_CMD=${FORGE_PHP:-php}
DOMAIN=${FORGE_SITE_DOMAIN:-localhost}

cd $SITE_PATH

# 1. Vérifier la configuration Laravel
echo "1. 🔧 Vérification de la configuration Laravel..."
CONFIG_CHECK=$($PHP_CMD artisan tinker --execute="echo config('scout.driver');" 2>/dev/null || echo "error")
if [ "$CONFIG_CHECK" = "meilisearch" ]; then
    echo "   ✅ Scout configuré sur Meilisearch"
else
    echo "   ❌ Scout n'est pas configuré sur Meilisearch (actuel: $CONFIG_CHECK)"
    exit 1
fi

# 2. Vérifier la connexion Meilisearch
echo "2. 🌐 Test de connexion Meilisearch..."
MEILISEARCH_HOST=$($PHP_CMD artisan tinker --execute="echo config('scout.meilisearch.host');" 2>/dev/null)
if curl -f -s "$MEILISEARCH_HOST/health" > /dev/null 2>&1; then
    echo "   ✅ Meilisearch accessible à $MEILISEARCH_HOST"
else
    echo "   ❌ Meilisearch non accessible à $MEILISEARCH_HOST"
    exit 1
fi

# 3. Vérifier les commandes Artisan
echo "3. 🔨 Vérification des commandes Artisan..."
COMMANDS=("forge:index" "search:index" "search:flush" "meilisearch:reindex")
for cmd in "${COMMANDS[@]}"; do
    if $PHP_CMD artisan list | grep -q "$cmd"; then
        echo "   ✅ Commande $cmd disponible"
    else
        echo "   ❌ Commande $cmd manquante"
    fi
done

# 4. Test d'indexation
echo "4. 📊 Test d'indexation..."
echo "   Comptage des modèles..."
PROFILE_COUNT=$($PHP_CMD artisan tinker --execute="echo App\Models\ProfessionalProfile::count();" 2>/dev/null || echo "0")
OFFER_COUNT=$($PHP_CMD artisan tinker --execute="echo App\Models\ServiceOffer::count();" 2>/dev/null || echo "0")
ACHIEVEMENT_COUNT=$($PHP_CMD artisan tinker --execute="echo App\Models\Achievement::count();" 2>/dev/null || echo "0")

echo "   - Profils professionnels: $PROFILE_COUNT"
echo "   - Offres de service: $OFFER_COUNT"
echo "   - Réalisations: $ACHIEVEMENT_COUNT"

if [ "$PROFILE_COUNT" -gt 0 ] || [ "$OFFER_COUNT" -gt 0 ] || [ "$ACHIEVEMENT_COUNT" -gt 0 ]; then
    echo "   ✅ Données disponibles pour l'indexation"
    
    # Test d'indexation rapide
    echo "   🚀 Test d'indexation rapide..."
    if timeout 60 $PHP_CMD artisan forge:index --check-health > /dev/null 2>&1; then
        echo "   ✅ Indexation test réussie"
    else
        echo "   ⚠️  Indexation test échouée ou timeout"
    fi
else
    echo "   ⚠️  Aucune donnée à indexer"
fi

# 5. Vérifier les tâches cron
echo "5. ⏰ Vérification des tâches cron..."
if crontab -l 2>/dev/null | grep -q "forge:index"; then
    echo "   ✅ Tâches cron d'indexation configurées"
else
    echo "   ⚠️  Tâches cron d'indexation non trouvées"
    echo "   💡 Ajoutez ces tâches dans Laravel Forge:"
    echo "      0 2 * * * cd $SITE_PATH && $PHP_CMD artisan forge:index --check-health"
    echo "      0 */6 * * * cd $SITE_PATH && $PHP_CMD artisan forge:index --check-health"
fi

# 6. Test de l'API de recherche
echo "6. 🔍 Test de l'API de recherche..."
if command -v curl >/dev/null 2>&1; then
    API_URL="https://$DOMAIN/api/search/stats"
    if curl -f -s "$API_URL" > /dev/null 2>&1; then
        echo "   ✅ API de recherche accessible"
    else
        echo "   ⚠️  API de recherche non accessible à $API_URL"
    fi
else
    echo "   ⚠️  curl non disponible pour tester l'API"
fi

# 7. Vérifier les logs
echo "7. 📋 Vérification des logs récents..."
if [ -f "storage/logs/laravel.log" ]; then
    ERROR_COUNT=$(tail -100 storage/logs/laravel.log | grep -c "ERROR" || echo "0")
    MEILISEARCH_LOGS=$(tail -100 storage/logs/laravel.log | grep -c "Meilisearch\|indexation" || echo "0")
    
    echo "   - Erreurs récentes: $ERROR_COUNT"
    echo "   - Logs Meilisearch récents: $MEILISEARCH_LOGS"
    
    if [ "$ERROR_COUNT" -gt 10 ]; then
        echo "   ⚠️  Beaucoup d'erreurs récentes détectées"
    else
        echo "   ✅ Niveau d'erreur acceptable"
    fi
else
    echo "   ⚠️  Fichier de log non trouvé"
fi

# 8. Vérifier les queues (si configurées)
echo "8. 🚀 Vérification des queues..."
QUEUE_CONNECTION=$($PHP_CMD artisan tinker --execute="echo config('queue.default');" 2>/dev/null || echo "sync")
echo "   Connection queue: $QUEUE_CONNECTION"

if [ "$QUEUE_CONNECTION" != "sync" ]; then
    FAILED_JOBS=$($PHP_CMD artisan queue:failed --format=json 2>/dev/null | wc -l || echo "0")
    echo "   - Jobs échoués: $FAILED_JOBS"
    
    if [ "$FAILED_JOBS" -gt 0 ]; then
        echo "   ⚠️  Des jobs ont échoué"
        echo "   💡 Exécutez: php artisan queue:failed pour voir les détails"
    else
        echo "   ✅ Aucun job échoué"
    fi
fi

# 9. Résumé et recommandations
echo ""
echo "📈 Résumé de la vérification:"
echo "================================"

# Vérifier si tout est OK
ALL_OK=true

# Vérifications critiques
if [ "$CONFIG_CHECK" != "meilisearch" ]; then ALL_OK=false; fi
if ! curl -f -s "$MEILISEARCH_HOST/health" > /dev/null 2>&1; then ALL_OK=false; fi

if [ "$ALL_OK" = true ]; then
    echo "✅ Automatisation Meilisearch opérationnelle!"
    echo ""
    echo "🎉 Prochaines étapes:"
    echo "   1. Surveillez les logs d'indexation"
    echo "   2. Testez la recherche sur votre application"
    echo "   3. Configurez les notifications si souhaité"
    echo ""
    echo "📚 Commandes utiles:"
    echo "   - Indexation manuelle: php artisan forge:index --check-health"
    echo "   - Vérifier les stats: curl https://$DOMAIN/api/search/stats"
    echo "   - Voir les logs: tail -f storage/logs/laravel.log | grep Meilisearch"
else
    echo "❌ Des problèmes ont été détectés!"
    echo ""
    echo "🔧 Actions recommandées:"
    echo "   1. Vérifiez la configuration Meilisearch dans .env"
    echo "   2. Assurez-vous que Meilisearch est accessible"
    echo "   3. Consultez les logs pour plus de détails"
    echo "   4. Relancez ce script après corrections"
    exit 1
fi

echo ""
echo "✅ Vérification post-déploiement terminée!"
