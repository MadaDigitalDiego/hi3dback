<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Services\InvoicePdfService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class InvoiceController extends Controller
{
	    protected InvoicePdfService $invoicePdfService;

	    public function __construct(InvoicePdfService $invoicePdfService)
	    {
	        $this->middleware('auth:sanctum');
	        $this->invoicePdfService = $invoicePdfService;
	    }

    /**
     * Get user's invoices with pagination and filtering.
     */
    public function getInvoices(Request $request): JsonResponse
    {
        $query = auth()->user()->invoices();

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->query('status'));
        }

        // Filter by date range
        if ($request->has('from_date')) {
            $query->whereDate('created_at', '>=', $request->query('from_date'));
        }

        if ($request->has('to_date')) {
            $query->whereDate('created_at', '<=', $request->query('to_date'));
        }

        $perPage = $request->query('per_page', 15);
        $invoices = $query->latest()->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $invoices->items(),
            'pagination' => [
                'total' => $invoices->total(),
                'per_page' => $invoices->perPage(),
                'current_page' => $invoices->currentPage(),
                'last_page' => $invoices->lastPage(),
            ],
        ]);
    }

    /**
     * Get invoice details.
     */
    public function getInvoice(int $id): JsonResponse
    {
        $invoice = Invoice::findOrFail($id);

        // Check authorization
        if ($invoice->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $invoice->load('subscription', 'lineItems'),
        ]);
    }

    /**
     * Download invoice as PDF.
     */
    public function downloadInvoice(int $id): JsonResponse
	    {
	        $invoice = Invoice::findOrFail($id);

	        // Check authorization
	        if ($invoice->user_id !== auth()->id()) {
	            return response()->json([
	                'success' => false,
	                'message' => 'Unauthorized',
	            ], 403);
	        }

	        try {
	            $pdfUrl = $invoice->pdf_url;

	            // Generate internal PDF if not already available
	            if (!$pdfUrl) {
	                $pdfUrl = $this->invoicePdfService->generateAndStore($invoice);
	            }

	            if (!$pdfUrl) {
	                // As a last resort, fall back to Stripe-hosted PDF stored in metadata
	                $stripePdf = $invoice->metadata['stripe_invoice_pdf_url'] ?? null;
	                if (!$stripePdf) {
	                    return response()->json([
	                        'success' => false,
	                        'message' => 'PDF not available',
	                    ], 404);
	                }

	                $pdfUrl = $stripePdf;
	            }

	            return response()->json([
	                'success' => true,
	                'data' => [
	                    'url' => $pdfUrl,
	                    'pdf_url' => $pdfUrl,
	                    'invoice_number' => $invoice->invoice_number,
	                ],
	            ]);
	        } catch (\Exception $e) {
	            Log::error('Error downloading invoice: ' . $e->getMessage());
	            return response()->json([
	                'success' => false,
	                'message' => 'Failed to download invoice',
	            ], 500);
	        }
	    }

    /**
     * Get invoice statistics.
     */
    public function getStatistics(): JsonResponse
    {
        $invoices = auth()->user()->invoices();

        $stats = [
            'total_invoices' => $invoices->count(),
            'total_paid' => $invoices->where('status', 'paid')->sum('amount'),
            'total_pending' => $invoices->whereIn('status', ['draft', 'open'])->sum('amount'),
            'total_failed' => $invoices->where('status', 'uncollectible')->sum('amount'),
            'average_amount' => $invoices->avg('amount'),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}

