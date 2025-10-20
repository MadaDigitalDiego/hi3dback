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
        Schema::table('files', function (Blueprint $table) {
            // Add receiver_id to track who can access the file
            $table->foreignId('receiver_id')
                ->nullable()
                ->after('user_id')
                ->constrained('users')
                ->onDelete('cascade');

            // Add access control fields
            $table->boolean('is_shared')->default(false)->after('receiver_id');
            $table->timestamp('shared_at')->nullable()->after('is_shared');
            $table->timestamp('accessed_at')->nullable()->after('shared_at');

            // Add indexes for performance
            $table->index(['user_id', 'receiver_id']);
            $table->index(['message_id', 'receiver_id']);
            $table->index(['is_shared', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('files', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'receiver_id']);
            $table->dropIndex(['message_id', 'receiver_id']);
            $table->dropIndex(['is_shared', 'status']);
            
            $table->dropForeign(['receiver_id']);
            $table->dropColumn(['receiver_id', 'is_shared', 'shared_at', 'accessed_at']);
        });
    }
};

