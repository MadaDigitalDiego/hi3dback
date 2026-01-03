<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Facture #{{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        .invoice-container { max-width: 800px; margin: 0 auto; padding: 20px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 20px; }
        .company-info { text-align: center; margin-bottom: 30px; }
        .client-info { margin-bottom: 30px; }
        .invoice-details { margin-bottom: 30px; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .table th { background-color: #f2f2f2; text-align: left; padding: 8px; border: 1px solid #ddd; }
        .table td { padding: 8px; border: 1px solid #ddd; }
        .totals { text-align: right; margin-top: 30px; }
        .footer { margin-top: 50px; text-align: center; font-size: 10px; color: #666; }
        .page-break { page-break-before: always; }
    </style>
</head>
<body>
    <div class="invoice-container">
        <!-- En-tête -->
        <div class="header">
            <h1>FACTURE</h1>
            <h2>#{{ $invoice->invoice_number }}</h2>
        </div>
        
        <!-- Informations de la société -->
        <div class="company-info">
            @if($settings && $settings->logo_path)
                <img src="{{ public_path('storage/' . $settings->logo_path) }}" style="max-height: 100px; margin-bottom: 10px;">
            @endif
            <h3>{{ $settings->company_name ?? 'VOTRE SOCIÉTÉ' }}</h3>
            <p>{{ $settings->address ?? '123 Rue de l\'Exemple' }}</p>
            @if($settings && $settings->phone)
                <p>Tél: {{ $settings->phone }}</p>
            @endif
            <p>Email: {{ $settings->email ?? 'facturation@votredomaine.com' }}</p>
            @if($settings && $settings->vat_number)
                <p>TVA: {{ $settings->vat_number }}</p>
            @endif
        </div>
        
        <!-- Informations du client -->
        <div class="client-info">
            <h4>FACTURÉ À</h4>
            <p><strong>{{ $user->name }}</strong></p>
            <p>{{ $user->email }}</p>
        </div>
        
        <!-- Détails de la facture -->
        <div class="invoice-details">
            <table style="width: 100%; margin-bottom: 20px;">
                <tr>
                    <td><strong>Date d'émission:</strong></td>
                    <td>{{ $invoice->created_at->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <td><strong>Date d'échéance:</strong></td>
                    <td>{{ $invoice->due_date ? $invoice->due_date->format('d/m/Y') : 'Sur réception' }}</td>
                </tr>
                <tr>
                    <td><strong>Statut:</strong></td>
                    <td>{{ strtoupper($invoice->status) }}</td>
                </tr>
                @if($subscription)
                <tr>
                    <td><strong>Abonnement:</strong></td>
                    <td>{{ $subscription->plan->title ?? $subscription->plan->name }}</td>
                </tr>
                <tr>
                    <td><strong>ID Abonnement:</strong></td>
                    <td>{{ $subscription->stripe_subscription_id }}</td>
                </tr>
                @endif
            </table>
        </div>
        
        <!-- Détails des articles -->
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 50%;">DESCRIPTION</th>
                    <th style="width: 15%; text-align: right;">PRIX UNITAIRE</th>
                    <th style="width: 10%; text-align: center;">QUANTITÉ</th>
                    <th style="width: 15%; text-align: right;">TAXE</th>
                    <th style="width: 10%; text-align: right;">TOTAL</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $invoice->description ?? 'Abonnement' }}</td>
                    <td style="text-align: right;">{{ number_format($invoice->amount, 2, ',', ' ') }} {{ $invoice->currency }}</td>
                    <td style="text-align: center;">1</td>
                    <td style="text-align: right;">{{ number_format($invoice->tax, 2, ',', ' ') }} {{ $invoice->currency }}</td>
                    <td style="text-align: right;">{{ number_format($invoice->total, 2, ',', ' ') }} {{ $invoice->currency }}</td>
                </tr>
            </tbody>
        </table>
        
        <!-- Totaux -->
        <div class="totals">
            <table style="width: 300px; margin-left: auto;">
                <tr>
                    <td><strong>Sous-total:</strong></td>
                    <td style="text-align: right;">{{ number_format($invoice->amount, 2, ',', ' ') }} {{ $invoice->currency }}</td>
                </tr>
                <tr>
                    <td><strong>Taxe:</strong></td>
                    <td style="text-align: right;">{{ number_format($invoice->tax, 2, ',', ' ') }} {{ $invoice->currency }}</td>
                </tr>
                <tr style="border-top: 2px solid #333;">
                    <td><strong>TOTAL:</strong></td>
                    <td style="text-align: right; font-size: 14px;"><strong>{{ number_format($invoice->total, 2, ',', ' ') }} {{ $invoice->currency }}</strong></td>
                </tr>
            </table>
        </div>
        
        <!-- Conditions de paiement -->
        <div class="footer">
            @if($settings && $settings->legal_mentions)
                <div style="margin-bottom: 20px; text-align: left;">
                    <h4>MENTIONS LÉGALES</h4>
                    <p>{{ $settings->legal_mentions }}</p>
                </div>
            @endif
            
            <div style="border-top: 1px solid #ddd; padding-top: 20px;">
                <p>{{ $settings->footer_text ?? 'Merci pour votre confiance !' }}</p>
                @if(!$settings || !$settings->footer_text)
                    <p>Pour toute question concernant cette facture, contactez-nous à {{ $settings->email ?? 'facturation@votredomaine.com' }}</p>
                @endif
                <p>Cette facture a été générée automatiquement par notre système.</p>
            </div>
        </div>
    </div>
</body>
</html>