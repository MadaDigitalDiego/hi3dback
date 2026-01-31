<x-filament-panels::page>
    <div class="space-y-6">
        <!-- General Statistics -->
        <x-filament::section>
            <x-slot name="heading">
                Overview
            </x-slot>
            
            @php
                $stats = $this->getStats();
            @endphp
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Users -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-blue-600 text-sm font-medium">Total Users</p>
                            <p class="text-2xl font-bold text-blue-900">{{ $stats['users']['total'] }}</p>
                        </div>
                        <x-heroicon-o-users class="w-8 h-8 text-blue-600" />
                    </div>
                    <div class="mt-2 text-xs text-blue-600">
                        <span class="font-medium">+{{ $stats['users']['today'] }}</span> today
                    </div>
                </div>
                
                <!-- Professionals -->
                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-green-600 text-sm font-medium">Professionals</p>
                            <p class="text-2xl font-bold text-green-900">{{ $stats['users']['professionals'] }}</p>
                        </div>
                        <x-heroicon-o-briefcase class="w-8 h-8 text-green-600" />
                    </div>
                    <div class="mt-2 text-xs text-green-600">
                        {{ round(($stats['users']['professionals'] / max($stats['users']['total'], 1)) * 100, 1) }}% of total
                    </div>
                </div>
                
                <!-- Active Offers -->
                <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-purple-600 text-sm font-medium">Active Offers</p>
                            <p class="text-2xl font-bold text-purple-900">{{ $stats['offers']['open_active'] }}</p>
                        </div>
                        <x-heroicon-o-document-text class="w-8 h-8 text-purple-600" />
                    </div>
                    <div class="mt-2 text-xs text-purple-600">
                        +{{ $stats['offers']['offers_today'] }} today
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
                        {{ $stats['communications']['messages_unread'] }} unread
                    </div>
                </div>
            </div>
        </x-filament::section>

        <!-- Details by Category -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Detailed Users -->
            <x-filament::section>
                <x-slot name="heading">
                    User Details
                </x-slot>
                
                <div class="space-y-3">
                    <div class="flex justify-between items-center p-3 bg-gray-50 rounded">
                        <span class="text-sm font-medium">Verified Emails</span>
                        <span class="text-sm text-gray-600">{{ $stats['users']['verified'] }} / {{ $stats['users']['total'] }}</span>
                    </div>
                    <div class="flex justify-between items-center p-3 bg-gray-50 rounded">
                        <span class="text-sm font-medium">Completed Profiles</span>
                        <span class="text-sm text-gray-600">{{ $stats['users']['completed_profiles'] }} / {{ $stats['users']['total'] }}</span>
                    </div>
                    <div class="flex justify-between items-center p-3 bg-gray-50 rounded">
                        <span class="text-sm font-medium">Signups This Week</span>
                        <span class="text-sm text-gray-600">{{ $stats['users']['this_week'] }}</span>
                    </div>
                    <div class="flex justify-between items-center p-3 bg-gray-50 rounded">
                        <span class="text-sm font-medium">Signups This Month</span>
                        <span class="text-sm text-gray-600">{{ $stats['users']['this_month'] }}</span>
                    </div>
                </div>
            </x-filament::section>

            <!-- Offers and Services -->
            <x-filament::section>
                <x-slot name="heading">
                    Offers and Services
                </x-slot>
                
                <div class="space-y-3">
                    <div class="flex justify-between items-center p-3 bg-gray-50 rounded">
                        <span class="text-sm font-medium">Total Open Offers</span>
                        <span class="text-sm text-gray-600">{{ $stats['offers']['open_total'] }}</span>
                    </div>
                    <div class="flex justify-between items-center p-3 bg-gray-50 rounded">
                        <span class="text-sm font-medium">Total Services</span>
                        <span class="text-sm text-gray-600">{{ $stats['offers']['service_total'] }}</span>
                    </div>
                    <div class="flex justify-between items-center p-3 bg-gray-50 rounded">
                        <span class="text-sm font-medium">Active Services</span>
                        <span class="text-sm text-gray-600">{{ $stats['offers']['service_active'] }}</span>
                    </div>
                    <div class="flex justify-between items-center p-3 bg-gray-50 rounded">
                        <span class="text-sm font-medium">Services Created Today</span>
                        <span class="text-sm text-gray-600">{{ $stats['offers']['services_today'] }}</span>
                    </div>
                </div>
            </x-filament::section>
        </div>

        <!-- Growth Chart -->
        <x-filament::section>
            <x-slot name="heading">
                7-Day Evolution
            </x-slot>
            
            @php
                $growthData = $this->getGrowthData();
            @endphp
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Users</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Offers</th>
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

        <!-- System Information -->
        <x-filament::section>
            <x-slot name="heading">
                System Information
            </x-slot>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-gray-50 rounded-lg p-4">
                    <h4 class="font-medium text-gray-900 mb-2">Laravel Version</h4>
                    <p class="text-sm text-gray-600">{{ app()->version() }}</p>
                </div>
                
                <div class="bg-gray-50 rounded-lg p-4">
                    <h4 class="font-medium text-gray-900 mb-2">PHP Version</h4>
                    <p class="text-sm text-gray-600">{{ PHP_VERSION }}</p>
                </div>
                
                <div class="bg-gray-50 rounded-lg p-4">
                    <h4 class="font-medium text-gray-900 mb-2">Environment</h4>
                    <p class="text-sm text-gray-600">{{ app()->environment() }}</p>
                </div>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
