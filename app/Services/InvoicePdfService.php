<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\BillingSetting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class InvoicePdfService
{
    /**
     * Generate a PDF for the given invoice, store it on the public disk
     * and update the invoice's pdf_url.
     *
     * @return string|null Public URL of the stored PDF, or null on failure.
     */
    public function generateAndStore(Invoice $invoice): ?string
    {
        try {
            $user = $invoice->user;
            $subscription = $invoice->subscription;
            $settings = BillingSetting::first();

            $pdf = Pdf::loadView('pdf.invoice', [
                'invoice' => $invoice,
                'user' => $user,
                'subscription' => $subscription,
                'settings' => $settings,
            ])->setPaper('a4');

            $baseName = $invoice->invoice_number ?: ('INV-' . $invoice->id);
            $fileName = $baseName . '.pdf';
            $path = 'invoices/' . $invoice->user_id . '/' . $fileName;

            Storage::disk('public')->put($path, $pdf->output());

            $url = Storage::disk('public')->url($path);

            $invoice->pdf_url = $url;
            $invoice->save();

            return $url;
        } catch (\Throwable $e) {
            Log::error('Failed to generate invoice PDF', [
                'invoice_id' => $invoice->id ?? null,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}

