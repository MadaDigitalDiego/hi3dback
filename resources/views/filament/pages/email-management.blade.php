<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Email Configuration -->
        <x-filament::section>
            <x-slot name="heading">
                Email Configuration
            </x-slot>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-center mb-2">
                        <x-heroicon-o-cog-6-tooth class="w-5 h-5 text-blue-600 mr-2" />
                        <span class="text-blue-800 font-medium">Email Driver</span>
                    </div>
                    <p class="text-blue-600 text-sm">{{ config('mail.default') }}</p>
                </div>
                
                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <div class="flex items-center mb-2">
                        <x-heroicon-o-envelope class="w-5 h-5 text-green-600 mr-2" />
                        <span class="text-green-800 font-medium">Default Email</span>
                    </div>
                    <p class="text-green-600 text-sm">{{ config('mail.from.address') }}</p>
                </div>
            </div>
        </x-filament::section>

        <!-- Email Templates -->
        <x-filament::section>
            <x-slot name="heading">
                Available Email Templates
            </x-slot>
            
            <div class="space-y-4">
                <div class="border border-gray-200 rounded-lg p-4">
                    <div class="flex justify-between items-center">
                        <div>
                            <h3 class="font-medium text-gray-900">Verification Email</h3>
                            <p class="text-sm text-gray-600">Template for email verification of new users</p>
                        </div>
                        <div class="flex space-x-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Active
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="border border-gray-200 rounded-lg p-4">
                    <div class="flex justify-between items-center">
                        <div>
                            <h3 class="font-medium text-gray-900">Password Reset</h3>
                            <p class="text-sm text-gray-600">Template for password reset</p>
                        </div>
                        <div class="flex space-x-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Active
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="border border-gray-200 rounded-lg p-4">
                    <div class="flex justify-between items-center">
                        <div>
                            <h3 class="font-medium text-gray-900">Offer Notification</h3>
                            <p class="text-sm text-gray-600">Template to notify professionals of new offers</p>
                        </div>
                        <div class="flex space-x-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Active
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="border border-gray-200 rounded-lg p-4">
                    <div class="flex justify-between items-center">
                        <div>
                            <h3 class="font-medium text-gray-900">Profile Update</h3>
                            <p class="text-sm text-gray-600">Template to confirm profile updates</p>
                        </div>
                        <div class="flex space-x-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Active
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </x-filament::section>

        <!-- Email Queue -->
        <x-filament::section>
            <x-slot name="heading">
                Email Queue
            </x-slot>
            
            <div class="bg-gray-50 rounded-lg p-4">
                <h4 class="font-medium text-gray-900 mb-2">Queue Management:</h4>
                <ul class="space-y-1 text-sm text-gray-600">
                    <li><strong>Process Queue</strong>: Processes pending emails for sending</li>
                    <li><strong>Clear Failures</strong>: Removes failed emails</li>
                    <li><strong>Send Test</strong>: Sends a test email to verify configuration</li>
                </ul>
            </div>
        </x-filament::section>

        <!-- Statistics -->
        <x-filament::section>
            <x-slot name="heading">
                Sending Statistics
            </x-slot>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <x-heroicon-o-paper-airplane class="w-5 h-5 text-blue-600 mr-2" />
                        <span class="text-blue-800 font-medium">Emails Sent Today</span>
                    </div>
                    <p class="text-blue-600 text-sm mt-1">Feature to implement</p>
                </div>
                
                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <x-heroicon-o-check-circle class="w-5 h-5 text-green-600 mr-2" />
                        <span class="text-green-800 font-medium">Success Rate</span>
                    </div>
                    <p class="text-green-600 text-sm mt-1">Feature to implement</p>
                </div>
                
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <x-heroicon-o-x-circle class="w-5 h-5 text-red-600 mr-2" />
                        <span class="text-red-800 font-medium">Failures</span>
                    </div>
                    <p class="text-red-600 text-sm mt-1">Feature to implement</p>
                </div>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
