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
        $count = static::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->count() + 1;

        return sprintf('INV-%d%02d%05d', $year, $month, $count);
    }
}

