<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Facture {{ $invoice->invoice_number ?? ('INV-' . $invoice->id) }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #333; }
        .header, .footer { width: 100%; margin-bottom: 20px; }
        .header h1 { margin: 0 0 5px 0; font-size: 20px; }
        .section-title { font-weight: bold; margin-bottom: 5px; }
        .box { border: 1px solid #ddd; padding: 8px; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        th { background-color: #f5f5f5; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ config('app.name') }} - Facture</h1>
        <p>
            N° de facture : <strong>{{ $invoice->invoice_number ?? ('INV-' . $invoice->id) }}</strong><br>
            Date : <strong>{{ optional($invoice->created_at)->format('d/m/Y') }}</strong><br>
            @if($invoice->paid_at)
                Payée le : <strong>{{ $invoice->paid_at->format('d/m/Y') }}</strong><br>
            @endif
        </p>
    </div>

    <div class="box">
        <div class="section-title">Facturé à</div>
        <p>
            @php
                $fullName = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));
            @endphp
            <strong>{{ $fullName !== '' ? $fullName : $user->email }}</strong><br>
            @if(!empty($user->company_name))
                {{ $user->company_name }}<br>
            @endif
            @if(!empty($user->address))
                {{ $user->address }}<br>
            @endif
        </p>
    </div>

    <div class="box">
        <div class="section-title">Détails de la facture</div>
        <table>
            <thead>
                <tr>
                    <th>Description</th>
                    <th class="text-right">Montant HT</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        {{ $invoice->description ?? 'Abonnement Hi3D' }}
                        @if($subscription && !empty($subscription->name))
                            <br><small>Plan : {{ $subscription->name }}</small>
                        @endif
                    </td>
                    <td class="text-right">
                        {{ number_format((float) $invoice->amount, 2, ',', ' ') }} {{ $invoice->currency }}
                    </td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <th class="text-right">Remise</th>
                    <td class="text-right">- {{ number_format((float) $invoice->discount, 2, ',', ' ') }} {{ $invoice->currency }}</td>
                </tr>
                <tr>
                    <th class="text-right">TVA</th>
                    <td class="text-right">{{ number_format((float) $invoice->tax, 2, ',', ' ') }} {{ $invoice->currency }}</td>
                </tr>
                <tr>
                    <th class="text-right">Total TTC</th>
                    <td class="text-right"><strong>{{ number_format((float) $invoice->total, 2, ',', ' ') }} {{ $invoice->currency }}</strong></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="footer">
        <p>Ce document a été généré automatiquement par {{ config('app.name') }}.</p>
    </div>
</body>
</html>

