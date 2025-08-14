<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Commandes de cache -->
        <x-filament::section>
            <x-slot name="heading">
                Gestion du cache
            </x-slot>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <div class="flex items-center mb-2">
                        <x-heroicon-o-trash class="w-5 h-5 text-yellow-600 mr-2" />
                        <span class="text-yellow-800 font-medium">Vider le cache</span>
                    </div>
                    <p class="text-yellow-600 text-sm">Supprime tous les caches de l'application</p>
                </div>
                
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-center mb-2">
                        <x-heroicon-o-cog-6-tooth class="w-5 h-5 text-blue-600 mr-2" />
                        <span class="text-blue-800 font-medium">Cache config</span>
                    </div>
                    <p class="text-blue-600 text-sm">Met en cache la configuration</p>
                </div>
                
                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <div class="flex items-center mb-2">
                        <x-heroicon-o-map class="w-5 h-5 text-green-600 mr-2" />
                        <span class="text-green-800 font-medium">Cache routes</span>
                    </div>
                    <p class="text-green-600 text-sm">Met en cache les routes</p>
                </div>
                
                <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                    <div class="flex items-center mb-2">
                        <x-heroicon-o-rocket-launch class="w-5 h-5 text-purple-600 mr-2" />
                        <span class="text-purple-800 font-medium">Optimiser</span>
                    </div>
                    <p class="text-purple-600 text-sm">Optimise l'application complète</p>
                </div>
            </div>
        </x-filament::section>

        <!-- Commandes de base de données -->
        <x-filament::section>
            <x-slot name="heading">
                Base de données
            </x-slot>
            
            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="flex items-center mb-2">
                    <x-heroicon-o-circle-stack class="w-5 h-5 text-red-600 mr-2" />
                    <span class="text-red-800 font-medium">Migrations</span>
                </div>
                <p class="text-red-600 text-sm">Exécute les migrations en attente (action irréversible)</p>
            </div>
        </x-filament::section>

        <!-- Commandes disponibles -->
        <x-filament::section>
            <x-slot name="heading">
                Commandes disponibles
            </x-slot>
            
            <div class="bg-gray-50 rounded-lg p-4">
                <h4 class="font-medium text-gray-900 mb-3">Commandes Artisan courantes :</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <h5 class="font-medium text-gray-800 mb-2">Cache :</h5>
                        <ul class="space-y-1 text-gray-600">
                            <li><code class="bg-gray-200 px-2 py-1 rounded">cache:clear</code> - Vider le cache</li>
                            <li><code class="bg-gray-200 px-2 py-1 rounded">config:cache</code> - Cache config</li>
                            <li><code class="bg-gray-200 px-2 py-1 rounded">route:cache</code> - Cache routes</li>
                            <li><code class="bg-gray-200 px-2 py-1 rounded">view:cache</code> - Cache vues</li>
                        </ul>
                    </div>
                    
                    <div>
                        <h5 class="font-medium text-gray-800 mb-2">Base de données :</h5>
                        <ul class="space-y-1 text-gray-600">
                            <li><code class="bg-gray-200 px-2 py-1 rounded">migrate</code> - Exécuter migrations</li>
                            <li><code class="bg-gray-200 px-2 py-1 rounded">migrate:rollback</code> - Annuler migrations</li>
                            <li><code class="bg-gray-200 px-2 py-1 rounded">db:seed</code> - Peupler la DB</li>
                            <li><code class="bg-gray-200 px-2 py-1 rounded">migrate:fresh</code> - Recréer la DB</li>
                        </ul>
                    </div>
                </div>
            </div>
        </x-filament::section>

        <!-- Avertissement -->
        <x-filament::section>
            <x-slot name="heading">
                ⚠️ Avertissement
            </x-slot>
            
            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="flex">
                    <x-heroicon-o-exclamation-triangle class="w-5 h-5 text-red-600 mr-2 mt-0.5" />
                    <div class="text-red-800">
                        <h4 class="font-medium">Utilisation en production</h4>
                        <p class="mt-1 text-sm">
                            Soyez très prudent lors de l'exécution de commandes en production. 
                            Certaines commandes peuvent affecter les performances ou la disponibilité du site.
                            Toujours faire une sauvegarde avant les opérations critiques.
                        </p>
                    </div>
                </div>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
