<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Sélecteur de fichier de log -->
        <x-filament::section>
            <x-slot name="heading">
                Fichiers de logs disponibles
            </x-slot>
            
            <div class="space-y-4">
                <div class="flex flex-wrap gap-2">
                    @foreach($this->getAvailableLogs() as $log)
                        <button 
                            wire:click="$set('selectedLog', '{{ $log }}')"
                            wire:click="loadLogContent"
                            class="px-3 py-2 text-sm rounded-md border {{ $selectedLog === $log ? 'bg-blue-100 border-blue-300 text-blue-800' : 'bg-white border-gray-300 text-gray-700 hover:bg-gray-50' }}"
                        >
                            {{ $log }}
                        </button>
                    @endforeach
                </div>
                
                <div class="text-sm text-gray-600">
                    Fichier sélectionné : <strong>{{ $selectedLog }}</strong>
                </div>
            </div>
        </x-filament::section>

        <!-- Contenu du log -->
        <x-filament::section>
            <x-slot name="heading">
                Contenu du log (50 dernières lignes)
            </x-slot>
            
            <div class="bg-gray-900 text-green-400 p-4 rounded-lg overflow-auto max-h-96">
                <pre class="text-xs whitespace-pre-wrap">{{ $logContent ?: 'Aucun contenu disponible' }}</pre>
            </div>
        </x-filament::section>

        <!-- Informations sur les logs -->
        <x-filament::section>
            <x-slot name="heading">
                Informations
            </x-slot>
            
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex">
                    <x-heroicon-o-information-circle class="w-5 h-5 text-blue-600 mr-2 mt-0.5" />
                    <div class="text-blue-800">
                        <h4 class="font-medium">À propos des logs</h4>
                        <ul class="mt-2 text-sm space-y-1">
                            <li>• Les logs sont stockés dans <code>storage/logs/</code></li>
                            <li>• Seules les 50 dernières lignes sont affichées pour des raisons de performance</li>
                            <li>• Utilisez le bouton "Télécharger" pour obtenir le fichier complet</li>
                            <li>• Les logs sont automatiquement rotatés par Laravel</li>
                        </ul>
                    </div>
                </div>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
