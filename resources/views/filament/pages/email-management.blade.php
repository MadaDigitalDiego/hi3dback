<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Configuration Email -->
        <x-filament::section>
            <x-slot name="heading">
                Configuration Email
            </x-slot>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-center mb-2">
                        <x-heroicon-o-cog-6-tooth class="w-5 h-5 text-blue-600 mr-2" />
                        <span class="text-blue-800 font-medium">Driver Email</span>
                    </div>
                    <p class="text-blue-600 text-sm">{{ config('mail.default') }}</p>
                </div>
                
                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <div class="flex items-center mb-2">
                        <x-heroicon-o-envelope class="w-5 h-5 text-green-600 mr-2" />
                        <span class="text-green-800 font-medium">Email par défaut</span>
                    </div>
                    <p class="text-green-600 text-sm">{{ config('mail.from.address') }}</p>
                </div>
            </div>
        </x-filament::section>

        <!-- Templates d'emails -->
        <x-filament::section>
            <x-slot name="heading">
                Templates d'emails disponibles
            </x-slot>
            
            <div class="space-y-4">
                <div class="border border-gray-200 rounded-lg p-4">
                    <div class="flex justify-between items-center">
                        <div>
                            <h3 class="font-medium text-gray-900">Email de vérification</h3>
                            <p class="text-sm text-gray-600">Template pour la vérification d'email des nouveaux utilisateurs</p>
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
                            <h3 class="font-medium text-gray-900">Réinitialisation de mot de passe</h3>
                            <p class="text-sm text-gray-600">Template pour la réinitialisation des mots de passe</p>
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
                            <h3 class="font-medium text-gray-900">Notification d'offre</h3>
                            <p class="text-sm text-gray-600">Template pour notifier les professionnels des nouvelles offres</p>
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
                            <h3 class="font-medium text-gray-900">Mise à jour de profil</h3>
                            <p class="text-sm text-gray-600">Template pour confirmer les mises à jour de profil</p>
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

        <!-- Queue des emails -->
        <x-filament::section>
            <x-slot name="heading">
                Queue des emails
            </x-slot>
            
            <div class="bg-gray-50 rounded-lg p-4">
                <h4 class="font-medium text-gray-900 mb-2">Gestion de la queue :</h4>
                <ul class="space-y-1 text-sm text-gray-600">
                    <li><strong>Traiter la queue</strong> : Traite les emails en attente d'envoi</li>
                    <li><strong>Vider les échecs</strong> : Supprime les emails qui ont échoué</li>
                    <li><strong>Test d'envoi</strong> : Envoie un email de test pour vérifier la configuration</li>
                </ul>
            </div>
        </x-filament::section>

        <!-- Statistiques -->
        <x-filament::section>
            <x-slot name="heading">
                Statistiques d'envoi
            </x-slot>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <x-heroicon-o-paper-airplane class="w-5 h-5 text-blue-600 mr-2" />
                        <span class="text-blue-800 font-medium">Emails envoyés aujourd'hui</span>
                    </div>
                    <p class="text-blue-600 text-sm mt-1">Fonctionnalité à implémenter</p>
                </div>
                
                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <x-heroicon-o-check-circle class="w-5 h-5 text-green-600 mr-2" />
                        <span class="text-green-800 font-medium">Taux de succès</span>
                    </div>
                    <p class="text-green-600 text-sm mt-1">Fonctionnalité à implémenter</p>
                </div>
                
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <x-heroicon-o-x-circle class="w-5 h-5 text-red-600 mr-2" />
                        <span class="text-red-800 font-medium">Échecs</span>
                    </div>
                    <p class="text-red-600 text-sm mt-1">Fonctionnalité à implémenter</p>
                </div>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
