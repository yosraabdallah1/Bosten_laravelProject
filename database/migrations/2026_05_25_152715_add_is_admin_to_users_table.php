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
        // is_admin is already included in the initial users table migration.
        // This migration is kept for history but is a no-op to avoid duplicate column errors.
        if (! Schema::hasColumn('users', 'is_admin')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('is_admin')->default(false)->after('email');
            });
        }
    }

    public function down(): void
    {
        // Only drop if this migration actually added the column.
        // Since the column now originates from the create migration, we leave it alone.
    }
};
