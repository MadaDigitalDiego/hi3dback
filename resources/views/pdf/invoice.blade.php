<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice #{{ $invoice->invoice_number }}</title>
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
        <!-- Header -->
        <div class="header">
            <h1>INVOICE</h1>
            <h2>#{{ $invoice->invoice_number }}</h2>
        </div>
        
        <!-- Company Information -->
        <div class="company-info">
            @if($settings && $settings->logo_path)
                <img src="{{ public_path('storage/' . $settings->logo_path) }}" style="max-height: 100px; margin-bottom: 10px;">
            @endif
            <h3>{{ $settings->company_name ?? 'YOUR COMPANY' }}</h3>
            <p>{{ $settings->address ?? '123 Example Street' }}</p>
            @if($settings && $settings->phone)
                <p>Phone: {{ $settings->phone }}</p>
            @endif
            <p>Email: {{ $settings->email ?? 'billing@yourdomain.com' }}</p>
            @if($settings && $settings->vat_number)
                <p>VAT: {{ $settings->vat_number }}</p>
            @endif
        </div>
        
        <!-- Client Information -->
        <div class="client-info">
            <h4>BILLED TO</h4>
            <p><strong>{{ $user->name }}</strong></p>
            <p>{{ $user->email }}</p>
        </div>
        
        <!-- Invoice Details -->
        <div class="invoice-details">
            <table style="width: 100%; margin-bottom: 20px;">
                <tr>
                    <td><strong>Issue Date:</strong></td>
                    <td>{{ $invoice->created_at->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <td><strong>Due Date:</strong></td>
                    <td>{{ $invoice->due_date ? $invoice->due_date->format('d/m/Y') : 'Upon receipt' }}</td>
                </tr>
                <tr>
                    <td><strong>Status:</strong></td>
                    <td>{{ strtoupper($invoice->status) }}</td>
                </tr>
                @if($subscription)
                <tr>
                    <td><strong>Subscription:</strong></td>
                    <td>{{ $subscription->plan->title ?? $subscription->plan->name }}</td>
                </tr>
                <tr>
                    <td><strong>Subscription ID:</strong></td>
                    <td>{{ $subscription->stripe_subscription_id }}</td>
                </tr>
                @endif
            </table>
        </div>
        
        <!-- Line Items -->
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 50%;">DESCRIPTION</th>
                    <th style="width: 15%; text-align: right;">UNIT PRICE</th>
                    <th style="width: 10%; text-align: center;">QUANTITY</th>
                    <th style="width: 15%; text-align: right;">TAX</th>
                    <th style="width: 10%; text-align: right;">TOTAL</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $invoice->description ?? 'Subscription' }}</td>
                    <td style="text-align: right;">{{ number_format($invoice->amount, 2, ',', ' ') }} {{ $invoice->currency }}</td>
                    <td style="text-align: center;">1</td>
                    <td style="text-align: right;">{{ number_format($invoice->tax, 2, ',', ' ') }} {{ $invoice->currency }}</td>
                    <td style="text-align: right;">{{ number_format($invoice->total, 2, ',', ' ') }} {{ $invoice->currency }}</td>
                </tr>
            </tbody>
        </table>
        
        <!-- Totals -->
        <div class="totals">
            <table style="width: 300px; margin-left: auto;">
                <tr>
                    <td><strong>Subtotal:</strong></td>
                    <td style="text-align: right;">{{ number_format($invoice->amount, 2, ',', ' ') }} {{ $invoice->currency }}</td>
                </tr>
                <tr>
                    <td><strong>Tax:</strong></td>
                    <td style="text-align: right;">{{ number_format($invoice->tax, 2, ',', ' ') }} {{ $invoice->currency }}</td>
                </tr>
                <tr style="border-top: 2px solid #333;">
                    <td><strong>TOTAL:</strong></td>
                    <td style="text-align: right; font-size: 14px;"><strong>{{ number_format($invoice->total, 2, ',', ' ') }} {{ $invoice->currency }}</strong></td>
                </tr>
            </table>
        </div>
        
        <!-- Payment Terms -->
        <div class="footer">
            @if($settings && $settings->legal_mentions)
                <div style="margin-bottom: 20px; text-align: left;">
                    <h4>LEGAL INFORMATION</h4>
                    <p>{{ $settings->legal_mentions }}</p>
                </div>
            @endif
            
            <div style="border-top: 1px solid #ddd; padding-top: 20px;">
                <p>{{ $settings->footer_text ?? 'Thank you for your trust!' }}</p>
                @if(!$settings || !$settings->footer_text)
                    <p>For any questions regarding this invoice, contact us at {{ $settings->email ?? 'billing@yourdomain.com' }}</p>
                @endif
                <p>This invoice was automatically generated by our system.</p>
            </div>
        </div>
    </div>
</body>
</html>
