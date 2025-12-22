<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'subscription_id',
        'stripe_invoice_id',
        'invoice_number',
        'status',
        'amount',
        'tax',
        'discount',
        'total',
        'currency',
        'description',
        'due_date',
        'paid_at',
        'voided_at',
        'pdf_url',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'tax' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
        'due_date' => 'datetime',
        'paid_at' => 'datetime',
        'voided_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Get the user that owns the invoice.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the subscription associated with the invoice.
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    /**
     * Check if the invoice is paid.
     */
    public function isPaid(): bool
    {
        return $this->status === 'paid' && $this->paid_at !== null;
    }

    /**
     * Check if the invoice is pending.
     */
    public function isPending(): bool
    {
        return in_array($this->status, ['draft', 'open']);
    }

    /**
     * Mark the invoice as paid.
     */
    public function markAsPaid(): void
    {
        $this->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);
    }

    /**
     * Mark the invoice as void.
     */
    public function markAsVoid(): void
    {
        $this->update([
            'status' => 'void',
            'voided_at' => now(),
        ]);
    }

    /**
     * Generate invoice number if not exists.
     */
 
    public static function generateInvoiceNumber(): string
    {
        $year = now()->year;
        $month = now()->month;
        
        // Formater le mois sur 2 chiffres (01, 02, ..., 12)
        $formattedMonth = str_pad($month, 2, '0', STR_PAD_LEFT);
        
        // Compter les factures créées ce mois-ci
        // IMPORTANT: Utiliser whereRaw pour comparer correctement les mois/années
        $count = static::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->count() + 1;
        
        // Formater le compteur sur 5 chiffres (00001, 00002, etc.)
        $formattedCount = str_pad($count, 5, '0', STR_PAD_LEFT);
        
        // Générer le numéro de facture
        $invoiceNumber = sprintf('INV-%d%s-%s', $year, $formattedMonth, $formattedCount);
        
        // VÉRIFICATION CRITIQUE : S'assurer que le numéro n'existe pas déjà
        // Cela peut arriver si deux appels sont faits en même temps
        $attempts = 0;
        $maxAttempts = 5;
        
        while (static::where('invoice_number', $invoiceNumber)->exists()) {
            $attempts++;
            
            if ($attempts >= $maxAttempts) {
                // Si on a trop de collisions, ajouter un identifiant unique
                $uniqueId = substr(uniqid(), -4);
                $invoiceNumber = sprintf('INV-%d%s-%s-%s', $year, $formattedMonth, $formattedCount, $uniqueId);
                break;
            }
            
            // Incrémenter le compteur et régénérer
            $count++;
            $formattedCount = str_pad($count, 5, '0', STR_PAD_LEFT);
            $invoiceNumber = sprintf('INV-%d%s-%s', $year, $formattedMonth, $formattedCount);
        }
        
        return $invoiceNumber;
    }
}

