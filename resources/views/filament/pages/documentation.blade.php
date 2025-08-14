<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Introduction -->
        <x-filament::section>
            <x-slot name="heading">
                Bienvenue dans le Back-Office Hi3D
            </x-slot>
            
            <div class="prose max-w-none">
                <p class="text-lg text-gray-600">
                    Ce back-office administratif vous permet de gérer tous les aspects de la plateforme Hi3D. 
                    Vous pouvez administrer les utilisateurs, modérer les contenus, surveiller les performances 
                    et maintenir le système.
                </p>
            </div>
        </x-filament::section>

        <!-- Fonctionnalités principales -->
        <x-filament::section>
            <x-slot name="heading">
                Fonctionnalités principales
            </x-slot>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <h3 class="font-semibold text-blue-900 mb-2">👥 Gestion des utilisateurs</h3>
                    <ul class="text-blue-800 text-sm space-y-1">
                        <li>• Visualiser et modifier les profils utilisateurs</li>
                        <li>• Gérer les rôles et permissions</li>
                        <li>• Modérer les comptes professionnels et clients</li>
                        <li>• Suivre les statistiques d'inscription</li>
                    </ul>
                </div>
                
                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <h3 class="font-semibold text-green-900 mb-2">📋 Gestion des offres</h3>
                    <ul class="text-green-800 text-sm space-y-1">
                        <li>• Modérer les offres ouvertes</li>
                        <li>• Gérer les services proposés</li>
                        <li>• Suivre les applications aux offres</li>
                        <li>• Analyser les tendances du marché</li>
                    </ul>
                </div>
                
                <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                    <h3 class="font-semibold text-purple-900 mb-2">💬 Gestion des communications</h3>
                    <ul class="text-purple-800 text-sm space-y-1">
                        <li>• Modérer les messages entre utilisateurs</li>
                        <li>• Gérer les contacts et demandes</li>
                        <li>• Surveiller les échanges</li>
                        <li>• Résoudre les conflits</li>
                    </ul>
                </div>
                
                <div class="bg-orange-50 border border-orange-200 rounded-lg p-4">
                    <h3 class="font-semibold text-orange-900 mb-2">🔧 Outils d'administration</h3>
                    <ul class="text-orange-800 text-sm space-y-1">
                        <li>• Gérer les index Meilisearch</li>
                        <li>• Consulter les logs système</li>
                        <li>• Exécuter des commandes Artisan</li>
                        <li>• Maintenir les performances</li>
                    </ul>
                </div>
            </div>
        </x-filament::section>

        <!-- Rôles et permissions -->
        <x-filament::section>
            <x-slot name="heading">
                Rôles et permissions
            </x-slot>
            
            <div class="space-y-4">
                <div class="border border-gray-200 rounded-lg p-4">
                    <div class="flex items-center mb-2">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 mr-3">
                            Super Admin
                        </span>
                        <h4 class="font-medium text-gray-900">Accès complet</h4>
                    </div>
                    <p class="text-sm text-gray-600">
                        Accès à toutes les fonctionnalités, gestion des autres administrateurs, 
                        maintenance système critique.
                    </p>
                </div>
                
                <div class="border border-gray-200 rounded-lg p-4">
                    <div class="flex items-center mb-2">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 mr-3">
                            Admin
                        </span>
                        <h4 class="font-medium text-gray-900">Administration générale</h4>
                    </div>
                    <p class="text-sm text-gray-600">
                        Gestion des utilisateurs, modération des contenus, accès aux statistiques 
                        et outils de maintenance.
                    </p>
                </div>
                
                <div class="border border-gray-200 rounded-lg p-4">
                    <div class="flex items-center mb-2">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 mr-3">
                            Modérateur
                        </span>
                        <h4 class="font-medium text-gray-900">Modération de contenu</h4>
                    </div>
                    <p class="text-sm text-gray-600">
                        Modération des offres, messages et profils. Accès limité aux fonctionnalités 
                        de gestion.
                    </p>
                </div>
            </div>
        </x-filament::section>

        <!-- Navigation -->
        <x-filament::section>
            <x-slot name="heading">
                Navigation rapide
            </x-slot>
            
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <a href="{{ route('filament.admin.resources.users.index') }}" 
                   class="block p-4 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                    <div class="text-center">
                        <x-heroicon-o-users class="w-8 h-8 mx-auto text-blue-600 mb-2" />
                        <span class="text-sm font-medium text-gray-900">Utilisateurs</span>
                    </div>
                </a>
                
                <a href="{{ route('filament.admin.resources.open-offers.index') }}" 
                   class="block p-4 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                    <div class="text-center">
                        <x-heroicon-o-document-text class="w-8 h-8 mx-auto text-green-600 mb-2" />
                        <span class="text-sm font-medium text-gray-900">Offres</span>
                    </div>
                </a>
                
                <a href="{{ route('filament.admin.pages.meilisearch-management') }}" 
                   class="block p-4 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                    <div class="text-center">
                        <x-heroicon-o-magnifying-glass class="w-8 h-8 mx-auto text-purple-600 mb-2" />
                        <span class="text-sm font-medium text-gray-900">Meilisearch</span>
                    </div>
                </a>
                
                <a href="{{ route('filament.admin.pages.system-logs') }}" 
                   class="block p-4 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                    <div class="text-center">
                        <x-heroicon-o-document-text class="w-8 h-8 mx-auto text-orange-600 mb-2" />
                        <span class="text-sm font-medium text-gray-900">Logs</span>
                    </div>
                </a>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
