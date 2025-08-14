<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Statistiques générales -->
        <x-filament::section>
            <x-slot name="heading">
                Vue d'ensemble
            </x-slot>
            
            @php
                $stats = $this->getStats();
            @endphp
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Utilisateurs -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-blue-600 text-sm font-medium">Total Utilisateurs</p>
                            <p class="text-2xl font-bold text-blue-900">{{ $stats['users']['total'] }}</p>
                        </div>
                        <x-heroicon-o-users class="w-8 h-8 text-blue-600" />
                    </div>
                    <div class="mt-2 text-xs text-blue-600">
                        <span class="font-medium">+{{ $stats['users']['today'] }}</span> aujourd'hui
                    </div>
                </div>
                
                <!-- Professionnels -->
                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-green-600 text-sm font-medium">Professionnels</p>
                            <p class="text-2xl font-bold text-green-900">{{ $stats['users']['professionals'] }}</p>
                        </div>
                        <x-heroicon-o-briefcase class="w-8 h-8 text-green-600" />
                    </div>
                    <div class="mt-2 text-xs text-green-600">
                        {{ round(($stats['users']['professionals'] / max($stats['users']['total'], 1)) * 100, 1) }}% du total
                    </div>
                </div>
                
                <!-- Offres actives -->
                <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-purple-600 text-sm font-medium">Offres Actives</p>
                            <p class="text-2xl font-bold text-purple-900">{{ $stats['offers']['open_active'] }}</p>
                        </div>
                        <x-heroicon-o-document-text class="w-8 h-8 text-purple-600" />
                    </div>
                    <div class="mt-2 text-xs text-purple-600">
                        +{{ $stats['offers']['offers_today'] }} aujourd'hui
                    </div>
                </div>
                
                <!-- Messages -->
                <div class="bg-orange-50 border border-orange-200 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-orange-600 text-sm font-medium">Messages</p>
                            <p class="text-2xl font-bold text-orange-900">{{ $stats['communications']['messages_total'] }}</p>
                        </div>
                        <x-heroicon-o-chat-bubble-left-right class="w-8 h-8 text-orange-600" />
                    </div>
                    <div class="mt-2 text-xs text-orange-600">
                        {{ $stats['communications']['messages_unread'] }} non lus
                    </div>
                </div>
            </div>
        </x-filament::section>

        <!-- Détails par catégorie -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Utilisateurs détaillés -->
            <x-filament::section>
                <x-slot name="heading">
                    Détails Utilisateurs
                </x-slot>
                
                <div class="space-y-3">
                    <div class="flex justify-between items-center p-3 bg-gray-50 rounded">
                        <span class="text-sm font-medium">Emails vérifiés</span>
                        <span class="text-sm text-gray-600">{{ $stats['users']['verified'] }} / {{ $stats['users']['total'] }}</span>
                    </div>
                    <div class="flex justify-between items-center p-3 bg-gray-50 rounded">
                        <span class="text-sm font-medium">Profils complétés</span>
                        <span class="text-sm text-gray-600">{{ $stats['users']['completed_profiles'] }} / {{ $stats['users']['total'] }}</span>
                    </div>
                    <div class="flex justify-between items-center p-3 bg-gray-50 rounded">
                        <span class="text-sm font-medium">Inscriptions cette semaine</span>
                        <span class="text-sm text-gray-600">{{ $stats['users']['this_week'] }}</span>
                    </div>
                    <div class="flex justify-between items-center p-3 bg-gray-50 rounded">
                        <span class="text-sm font-medium">Inscriptions ce mois</span>
                        <span class="text-sm text-gray-600">{{ $stats['users']['this_month'] }}</span>
                    </div>
                </div>
            </x-filament::section>

            <!-- Offres et services -->
            <x-filament::section>
                <x-slot name="heading">
                    Offres et Services
                </x-slot>
                
                <div class="space-y-3">
                    <div class="flex justify-between items-center p-3 bg-gray-50 rounded">
                        <span class="text-sm font-medium">Total offres ouvertes</span>
                        <span class="text-sm text-gray-600">{{ $stats['offers']['open_total'] }}</span>
                    </div>
                    <div class="flex justify-between items-center p-3 bg-gray-50 rounded">
                        <span class="text-sm font-medium">Total services</span>
                        <span class="text-sm text-gray-600">{{ $stats['offers']['service_total'] }}</span>
                    </div>
                    <div class="flex justify-between items-center p-3 bg-gray-50 rounded">
                        <span class="text-sm font-medium">Services actifs</span>
                        <span class="text-sm text-gray-600">{{ $stats['offers']['service_active'] }}</span>
                    </div>
                    <div class="flex justify-between items-center p-3 bg-gray-50 rounded">
                        <span class="text-sm font-medium">Services créés aujourd'hui</span>
                        <span class="text-sm text-gray-600">{{ $stats['offers']['services_today'] }}</span>
                    </div>
                </div>
            </x-filament::section>
        </div>

        <!-- Graphique de croissance -->
        <x-filament::section>
            <x-slot name="heading">
                Évolution sur 7 jours
            </x-slot>
            
            @php
                $growthData = $this->getGrowthData();
            @endphp
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Utilisateurs</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Offres</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Messages</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($growthData as $day)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $day['date'] }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ $day['users'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        {{ $day['offers'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                        {{ $day['messages'] }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-filament::section>

        <!-- Informations système -->
        <x-filament::section>
            <x-slot name="heading">
                Informations Système
            </x-slot>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-gray-50 rounded-lg p-4">
                    <h4 class="font-medium text-gray-900 mb-2">Version Laravel</h4>
                    <p class="text-sm text-gray-600">{{ app()->version() }}</p>
                </div>
                
                <div class="bg-gray-50 rounded-lg p-4">
                    <h4 class="font-medium text-gray-900 mb-2">Version PHP</h4>
                    <p class="text-sm text-gray-600">{{ PHP_VERSION }}</p>
                </div>
                
                <div class="bg-gray-50 rounded-lg p-4">
                    <h4 class="font-medium text-gray-900 mb-2">Environnement</h4>
                    <p class="text-sm text-gray-600">{{ app()->environment() }}</p>
                </div>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
