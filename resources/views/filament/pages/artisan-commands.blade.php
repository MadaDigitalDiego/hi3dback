<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Cache Commands -->
        <x-filament::section>
            <x-slot name="heading">
                Cache Management
            </x-slot>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <div class="flex items-center mb-2">
                        <x-heroicon-o-trash class="w-5 h-5 text-yellow-600 mr-2" />
                        <span class="text-yellow-800 font-medium">Clear Cache</span>
                    </div>
                    <p class="text-yellow-600 text-sm">Removes all application caches</p>
                </div>
                
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-center mb-2">
                        <x-heroicon-o-cog-6-tooth class="w-5 h-5 text-blue-600 mr-2" />
                        <span class="text-blue-800 font-medium">Config Cache</span>
                    </div>
                    <p class="text-blue-600 text-sm">Caches the configuration</p>
                </div>
                
                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <div class="flex items-center mb-2">
                        <x-heroicon-o-map class="w-5 h-5 text-green-600 mr-2" />
                        <span class="text-green-800 font-medium">Routes Cache</span>
                    </div>
                    <p class="text-green-600 text-sm">Caches the routes</p>
                </div>
                
                <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                    <div class="flex items-center mb-2">
                        <x-heroicon-o-rocket-launch class="w-5 h-5 text-purple-600 mr-2" />
                        <span class="text-purple-800 font-medium">Optimize</span>
                    </div>
                    <p class="text-purple-600 text-sm">Optimizes the complete application</p>
                </div>
            </div>
        </x-filament::section>

        <!-- Database Commands -->
        <x-filament::section>
            <x-slot name="heading">
                Database
            </x-slot>
            
            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="flex items-center mb-2">
                    <x-heroicon-o-circle-stack class="w-5 h-5 text-red-600 mr-2" />
                    <span class="text-red-800 font-medium">Migrations</span>
                </div>
                <p class="text-red-600 text-sm">Runs pending migrations (irreversible action)</p>
            </div>
        </x-filament::section>

        <!-- Available Commands -->
        <x-filament::section>
            <x-slot name="heading">
                Available Commands
            </x-slot>
            
            <div class="bg-gray-50 rounded-lg p-4">
                <h4 class="font-medium text-gray-900 mb-3">Common Artisan Commands:</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <h5 class="font-medium text-gray-800 mb-2">Cache:</h5>
                        <ul class="space-y-1 text-gray-600">
                            <li><code class="bg-gray-200 px-2 py-1 rounded">cache:clear</code> - Clear cache</li>
                            <li><code class="bg-gray-200 px-2 py-1 rounded">config:cache</code> - Cache config</li>
                            <li><code class="bg-gray-200 px-2 py-1 rounded">route:cache</code> - Cache routes</li>
                            <li><code class="bg-gray-200 px-2 py-1 rounded">view:cache</code> - Cache views</li>
                        </ul>
                    </div>
                    
                    <div>
                        <h5 class="font-medium text-gray-800 mb-2">Database:</h5>
                        <ul class="space-y-1 text-gray-600">
                            <li><code class="bg-gray-200 px-2 py-1 rounded">migrate</code> - Run migrations</li>
                            <li><code class="bg-gray-200 px-2 py-1 rounded">migrate:rollback</code> - Rollback migrations</li>
                            <li><code class="bg-gray-200 px-2 py-1 rounded">db:seed</code> - Seed the database</li>
                            <li><code class="bg-gray-200 px-2 py-1 rounded">migrate:fresh</code> - Drop all tables and re-run</li>
                        </ul>
                    </div>
                </div>
            </div>
        </x-filament::section>

        <!-- Warning -->
        <x-filament::section>
            <x-slot name="heading">
                ⚠️ Warning
            </x-slot>
            
            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="flex">
                    <x-heroicon-o-exclamation-triangle class="w-5 h-5 text-red-600 mr-2 mt-0.5" />
                    <div class="text-red-800">
                        <h4 class="font-medium">Production Use</h4>
                        <p class="mt-1 text-sm">
                            Be very careful when executing commands in production. 
                            Some commands may affect performance or site availability.
                            Always make a backup before critical operations.
                        </p>
                    </div>
                </div>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
