<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // Add missing columns if they don't exist
            if (!Schema::hasColumn('invoices', 'invoice_number')) {
                $table->string('invoice_number')->unique()->after('id');
            }
            if (!Schema::hasColumn('invoices', 'pdf_url')) {
                $table->string('pdf_url')->nullable()->after('invoice_number');
            }
            if (!Schema::hasColumn('invoices', 'amount')) {
                $table->decimal('amount', 10, 2)->after('pdf_url');
            }
            if (!Schema::hasColumn('invoices', 'currency')) {
                $table->string('currency')->default('EUR')->after('amount');
            }
            if (!Schema::hasColumn('invoices', 'description')) {
                $table->text('description')->nullable()->after('currency');
            }
            if (!Schema::hasColumn('invoices', 'due_date')) {
                $table->date('due_date')->nullable()->after('description');
            }
            if (!Schema::hasColumn('invoices', 'paid_at')) {
                $table->timestamp('paid_at')->nullable()->after('due_date');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn([
                'invoice_number',
                'pdf_url',
                'status',
                'amount',
                'currency',
                'description',
                'due_date',
                'paid_at',
            ]);
        });
    }
};

