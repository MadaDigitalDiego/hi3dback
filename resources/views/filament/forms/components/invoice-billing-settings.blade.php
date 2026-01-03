@php
    $metadata = $getRecord()->metadata ?? [];
    $billingSettings = $metadata['billing_settings_at_time_of_payment'] ?? null;
@endphp

@if($billingSettings)
    <div class="grid grid-cols-2 gap-4 p-4 border rounded-lg bg-gray-50 dark:bg-gray-800 dark:border-gray-700">
        <div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Entreprise</p>
            <p class="text-sm text-gray-900 dark:text-white">{{ $billingSettings['company_name'] ?? '-' }}</p>
        </div>
        <div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Email</p>
            <p class="text-sm text-gray-900 dark:text-white">{{ $billingSettings['email'] ?? '-' }}</p>
        </div>
        <div class="col-span-2">
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Adresse</p>
            <p class="text-sm text-gray-900 dark:text-white">{{ $billingSettings['address'] ?? '-' }}</p>
        </div>
        <div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">TVA</p>
            <p class="text-sm text-gray-900 dark:text-white">{{ $billingSettings['vat_number'] ?? '-' }}</p>
        </div>
        <div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Téléphone</p>
            <p class="text-sm text-gray-900 dark:text-white">{{ $billingSettings['phone'] ?? '-' }}</p>
        </div>
    </div>
@else
    <p class="text-sm text-gray-500 italic">Aucune information de facturation spécifique enregistrée pour cette facture.</p>
@endif
