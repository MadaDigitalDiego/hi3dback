<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Introduction -->
        <x-filament::section>
            <x-slot name="heading">
                Welcome to the Hi3D Back-Office
            </x-slot>
            
            <div class="prose max-w-none">
                <p class="text-lg text-gray-600">
                    This administrative back-office allows you to manage all aspects of the Hi3D platform. 
                    You can administer users, moderate content, monitor performance, 
                    and maintain the system.
                </p>
            </div>
        </x-filament::section>

        <!-- Main Features -->
        <x-filament::section>
            <x-slot name="heading">
                Main Features
            </x-slot>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <h3 class="font-semibold text-blue-900 mb-2">👥 User Management</h3>
                    <ul class="text-blue-800 text-sm space-y-1">
                        <li>• View and edit user profiles</li>
                        <li>• Manage roles and permissions</li>
                        <li>• Moderate professional and client accounts</li>
                        <li>• Track registration statistics</li>
                    </ul>
                </div>
                
                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <h3 class="font-semibold text-green-900 mb-2">📋 Offer Management</h3>
                    <ul class="text-green-800 text-sm space-y-1">
                        <li>• Moderate open offers</li>
                        <li>• Manage offered services</li>
                        <li>• Track offer applications</li>
                        <li>• Analyze market trends</li>
                    </ul>
                </div>
                
                <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                    <h3 class="font-semibold text-purple-900 mb-2">💬 Communication Management</h3>
                    <ul class="text-purple-800 text-sm space-y-1">
                        <li>• Moderate messages between users</li>
                        <li>• Manage contacts and requests</li>
                        <li>• Monitor exchanges</li>
                        <li>• Resolve conflicts</li>
                    </ul>
                </div>
                
                <div class="bg-orange-50 border border-orange-200 rounded-lg p-4">
                    <h3 class="font-semibold text-orange-900 mb-2">🔧 Administration Tools</h3>
                    <ul class="text-orange-800 text-sm space-y-1">
                        <li>• Manage Meilisearch indexes</li>
                        <li>• View system logs</li>
                        <li>• Execute Artisan commands</li>
                        <li>• Maintain performance</li>
                    </ul>
                </div>
            </div>
        </x-filament::section>

        <!-- Roles and Permissions -->
        <x-filament::section>
            <x-slot name="heading">
                Roles and Permissions
            </x-slot>
            
            <div class="space-y-4">
                <div class="border border-gray-200 rounded-lg p-4">
                    <div class="flex items-center mb-2">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 mr-3">
                            Super Admin
                        </span>
                        <h4 class="font-medium text-gray-900">Full Access</h4>
                    </div>
                    <p class="text-sm text-gray-600">
                        Access to all features, management of other administrators, 
                        critical system maintenance.
                    </p>
                </div>
                
                <div class="border border-gray-200 rounded-lg p-4">
                    <div class="flex items-center mb-2">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 mr-3">
                            Admin
                        </span>
                        <h4 class="font-medium text-gray-900">General Administration</h4>
                    </div>
                    <p class="text-sm text-gray-600">
                        User management, content moderation, access to statistics 
                        and maintenance tools.
                    </p>
                </div>
                
                <div class="border border-gray-200 rounded-lg p-4">
                    <div class="flex items-center mb-2">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 mr-3">
                            Moderator
                        </span>
                        <h4 class="font-medium text-gray-900">Content Moderation</h4>
                    </div>
                    <p class="text-sm text-gray-600">
                        Moderation of offers, messages, and profiles. Limited access to 
                        management features.
                    </p>
                </div>
            </div>
        </x-filament::section>

        <!-- Navigation -->
        <x-filament::section>
            <x-slot name="heading">
                Quick Navigation
            </x-slot>
            
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <a href="{{ route('filament.admin.resources.users.index') }}" 
                   class="block p-4 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                    <div class="text-center">
                        <x-heroicon-o-users class="w-8 h-8 mx-auto text-blue-600 mb-2" />
                        <span class="text-sm font-medium text-gray-900">Users</span>
                    </div>
                </a>
                
                <a href="{{ route('filament.admin.resources.open-offers.index') }}" 
                   class="block p-4 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                    <div class="text-center">
                        <x-heroicon-o-document-text class="w-8 h-8 mx-auto text-green-600 mb-2" />
                        <span class="text-sm font-medium text-gray-900">Offers</span>
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
