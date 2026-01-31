<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Log File Selector -->
        <x-filament::section>
            <x-slot name="heading">
                Available Log Files
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
                    Selected file: <strong>{{ $selectedLog }}</strong>
                </div>
            </div>
        </x-filament::section>

        <!-- Log Content -->
        <x-filament::section>
            <x-slot name="heading">
                Log Content (Last 50 lines)
            </x-slot>
            
            <div class="bg-gray-900 text-green-400 p-4 rounded-lg overflow-auto max-h-96">
                <pre class="text-xs whitespace-pre-wrap">{{ $logContent ?: 'No content available' }}</pre>
            </div>
        </x-filament::section>

        <!-- Log Information -->
        <x-filament::section>
            <x-slot name="heading">
                Information
            </x-slot>
            
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex">
                    <x-heroicon-o-information-circle class="w-5 h-5 text-blue-600 mr-2 mt-0.5" />
                    <div class="text-blue-800">
                        <h4 class="font-medium">About logs</h4>
                        <ul class="mt-2 text-sm space-y-1">
                            <li>• Logs are stored in <code>storage/logs/</code></li>
                            <li>• Only the last 50 lines are displayed for performance reasons</li>
                            <li>• Use the "Download" button to get the complete file</li>
                            <li>• Logs are automatically rotated by Laravel</li>
                        </ul>
                    </div>
                </div>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
