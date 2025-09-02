#!/bin/bash

# Laravel Forge Deployment Script with Meilisearch Indexation
# This script should be added to your Laravel Forge deployment script

set -e

echo "🚀 Starting Laravel Forge deployment..."

# Navigate to the application directory
cd $FORGE_SITE_PATH

# Pull the latest changes
git pull origin $FORGE_SITE_BRANCH

# Install/update composer dependencies
$FORGE_COMPOSER install --no-interaction --prefer-dist --optimize-autoloader --no-dev

# Clear and cache configuration
$FORGE_PHP artisan config:clear
$FORGE_PHP artisan config:cache

# Clear and cache routes
$FORGE_PHP artisan route:clear
$FORGE_PHP artisan route:cache

# Clear and cache views
$FORGE_PHP artisan view:clear
$FORGE_PHP artisan view:cache

# Run database migrations
$FORGE_PHP artisan migrate --force

# Clear application cache
$FORGE_PHP artisan cache:clear

# Restart queue workers (if using queues)
$FORGE_PHP artisan queue:restart

# === MEILISEARCH INDEXATION ===
echo "🔍 Starting Meilisearch indexation..."

# Check if Meilisearch is available
if curl -f -s "$MEILISEARCH_HOST/health" > /dev/null 2>&1; then
    echo "✅ Meilisearch is available"
    
    # Reindex all searchable models
    echo "📊 Indexing searchable models..."
    $FORGE_PHP artisan search:index --fresh --show-progress
    
    echo "✅ Meilisearch indexation completed successfully"
else
    echo "❌ Warning: Meilisearch is not available at $MEILISEARCH_HOST"
    echo "   Skipping indexation. Please check your Meilisearch configuration."
fi

# Optimize application
$FORGE_PHP artisan optimize

echo "✅ Deployment completed successfully!"

# Optional: Send notification (uncomment if needed)
# curl -X POST "https://hooks.slack.com/your-webhook-url" \
#      -H 'Content-type: application/json' \
#      --data '{"text":"🚀 Hi3D Backend deployed successfully with Meilisearch indexation!"}'
