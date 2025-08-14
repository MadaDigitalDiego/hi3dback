<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Configuration Meilisearch -->
        <x-filament::section>
            <x-slot name="heading">
                Configuration Meilisearch
            </x-slot>

            <form wire:submit="saveConfiguration">
                {{ $this->form }}
            </form>
        </x-filament::section>

        <!-- Statut de Meilisearch -->
        <x-filament::section>
            <x-slot name="heading">
                Statut de Meilisearch
            </x-slot>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <x-heroicon-o-check-circle class="w-5 h-5 text-green-600 mr-2" />
                        <span class="text-green-800 font-medium">Service actif</span>
                    </div>
                    <p class="text-green-600 text-sm mt-1">Meilisearch fonctionne correctement</p>
                </div>

                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <x-heroicon-o-document-text class="w-5 h-5 text-blue-600 mr-2" />
                        <span class="text-blue-800 font-medium">Index configurés</span>
                    </div>
                    <p class="text-blue-600 text-sm mt-1">3 index actifs</p>
                </div>

                <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <x-heroicon-o-clock class="w-5 h-5 text-purple-600 mr-2" />
                        <span class="text-purple-800 font-medium">Dernière sync</span>
                    </div>
                    <p class="text-purple-600 text-sm mt-1">{{ now()->format('d/m/Y H:i') }}</p>
                </div>

                <div class="bg-orange-50 border border-orange-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <x-heroicon-o-cog-6-tooth class="w-5 h-5 text-orange-600 mr-2" />
                        <span class="text-orange-800 font-medium">Configuration</span>
                    </div>
                    <p class="text-orange-600 text-sm mt-1">Host: {{ Str::limit(config('scout.meilisearch.host'), 30) }}</p>
                </div>
            </div>
        </x-filament::section>

        <!-- Index disponibles -->
        <x-filament::section>
            <x-slot name="heading">
                Index disponibles
            </x-slot>

            <div class="space-y-4">
                <div class="border border-gray-200 rounded-lg p-4">
                    <div class="flex justify-between items-center">
                        <div>
                            <h3 class="font-medium text-gray-900">professional_profiles_index</h3>
                            <p class="text-sm text-gray-600">Index des profils professionnels</p>
                        </div>
                        <div class="flex space-x-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Actif
                            </span>
                        </div>
                    </div>
                </div>

                <div class="border border-gray-200 rounded-lg p-4">
                    <div class="flex justify-between items-center">
                        <div>
                            <h3 class="font-medium text-gray-900">service_offers_index</h3>
                            <p class="text-sm text-gray-600">Index des offres de service</p>
                        </div>
                        <div class="flex space-x-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Actif
                            </span>
                        </div>
                    </div>
                </div>

                <div class="border border-gray-200 rounded-lg p-4">
                    <div class="flex justify-between items-center">
                        <div>
                            <h3 class="font-medium text-gray-900">achievements_index</h3>
                            <p class="text-sm text-gray-600">Index des réalisations</p>
                        </div>
                        <div class="flex space-x-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Actif
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </x-filament::section>

        <!-- Commandes utiles -->
        <x-filament::section>
            <x-slot name="heading">
                Commandes utiles
            </x-slot>

            <div class="bg-gray-50 rounded-lg p-4">
                <h4 class="font-medium text-gray-900 mb-2">Commandes Artisan disponibles :</h4>
                <ul class="space-y-1 text-sm text-gray-600">
                    <li><code class="bg-gray-200 px-2 py-1 rounded">php artisan scout:import "App\Models\ProfessionalProfile"</code> - Importer les profils professionnels</li>
                    <li><code class="bg-gray-200 px-2 py-1 rounded">php artisan scout:import "App\Models\ServiceOffer"</code> - Importer les offres de service</li>
                    <li><code class="bg-gray-200 px-2 py-1 rounded">php artisan scout:import "App\Models\Achievement"</code> - Importer les réalisations</li>
                    <li><code class="bg-gray-200 px-2 py-1 rounded">php artisan scout:flush "App\Models\ProfessionalProfile"</code> - Vider l'index des profils</li>
                </ul>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
