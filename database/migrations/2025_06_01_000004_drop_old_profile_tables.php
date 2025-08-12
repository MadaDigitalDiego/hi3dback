<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Log::warning('Dropping old profile tables. Make sure all data has been migrated correctly.');

        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Drop foreign key constraints using raw SQL to avoid Laravel's naming issues
        $this->dropForeignKeys('experiences', 'freelance_profile_id');
        // Add other tables with foreign keys to profile tables here

        // Drop the tables
        Schema::dropIfExists('freelance_profiles');
        Schema::dropIfExists('professional_profiles');
        Schema::dropIfExists('company_profiles');
        Schema::dropIfExists('client_profiles');

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        Log::info('All old profile tables have been dropped successfully');
    }

    /**
     * Drop foreign keys using raw SQL to avoid Laravel's naming issues
     */
    protected function dropForeignKeys(string $table, string $column): void
    {
        $connection = DB::connection();
        $dbName = $connection->getDatabaseName();

        $constraints = $connection->select("
            SELECT CONSTRAINT_NAME
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = ?
            AND TABLE_NAME = ?
            AND COLUMN_NAME = ?
            AND REFERENCED_TABLE_NAME IS NOT NULL
        ", [$dbName, $table, $column]);

        foreach ($constraints as $constraint) {
            $constraintName = $constraint->CONSTRAINT_NAME;
            $connection->statement("ALTER TABLE `$table` DROP FOREIGN KEY `$constraintName`");
            Log::info("Dropped foreign key constraint: $constraintName");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Log::error('Attempted to reverse drop_old_profile_tables migration - this is not supported.');
        Log::error('Please restore from a backup if you need to revert this change.');
    }
};