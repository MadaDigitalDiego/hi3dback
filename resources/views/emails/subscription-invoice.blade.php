<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Facture #{{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { text-align: center; margin-bottom: 30px; }
        .invoice-details { background: #f5f5f5; padding: 20px; border-radius: 5px; margin-bottom: 20px; }
        .details-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .detail-item { margin-bottom: 10px; }
        .detail-label { font-weight: bold; color: #666; }
        .detail-value { color: #333; }
        .table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .table th, .table td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        .table th { background: #f8f9fa; }
        .total { text-align: right; font-size: 1.2em; font-weight: bold; margin-top: 20px; }
        .footer { margin-top: 30px; text-align: center; color: #666; font-size: 0.9em; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Facture #{{ $invoice->invoice_number }}</h1>
            <p>Date d'émission: {{ $invoice->created_at->format('d/m/Y') }}</p>
        </div>

        <div class="invoice-details">
            <div class="details-grid">
                <div>
                    <div class="detail-item">
                        <div class="detail-label">Facturé à:</div>
                        <div class="detail-value">{{ $user->name }}<br>{{ $user->email }}</div>
                    </div>
                </div>
                <div>
                    <div class="detail-item">
                        <div class="detail-label">Statut:</div>
                        <div class="detail-value">{{ ucfirst($invoice->status) }}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Date d'échéance:</div>
                        <div class="detail-value">{{ $invoice->due_date ? $invoice->due_date->format('d/m/Y') : 'N/A' }}</div>
                    </div>
                </div>
            </div>
        </div>

        @if($subscription)
        <div>
            <h3>Détails de l'abonnement</h3>
            <table class="table">
                <tr>
                    <th>Plan</th>
                    <td>{{ $subscription->plan->title ?? $subscription->plan->name }}</td>
                </tr>
                <tr>
                    <th>Période</th>
                    <td>{{ $subscription->current_period_start->format('d/m/Y') }} - {{ $subscription->current_period_end->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <th>ID Stripe</th>
                    <td>{{ $subscription->stripe_subscription_id }}</td>
                </tr>
            </table>
        </div>
        @endif

        <table class="table">
            <thead>
                <tr>
                    <th>Description</th>
                    <th>Montant HT</th>
                    <th>Taxe</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $invoice->description ?? 'Abonnement mensuel' }}</td>
                    <td>{{ number_format($invoice->amount, 2, ',', ' ') }} {{ $invoice->currency }}</td>
                    <td>{{ number_format($invoice->tax, 2, ',', ' ') }} {{ $invoice->currency }}</td>
                    <td>{{ number_format($invoice->total, 2, ',', ' ') }} {{ $invoice->currency }}</td>
                </tr>
            </tbody>
        </table>

        <div class="total">
            Total: {{ number_format($invoice->total, 2, ',', ' ') }} {{ $invoice->currency }}
        </div>

        <div class="footer">
            <p>Cette facture est disponible en pièce jointe au format PDF.</p>
            <p>Pour toute question concernant cette facture, veuillez nous contacter à support@votredomaine.com</p>
            <p>&copy; {{ date('Y') }} Votre Société. Tous droits réservés.</p>
        </div>
    </div>
</body>
</html>