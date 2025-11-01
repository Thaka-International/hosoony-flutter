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
        Schema::table('activities', function (Blueprint $table) {
            // Add columns that don't exist yet
            if (!Schema::hasColumn('activities', 'instructions')) {
                $table->text('instructions')->nullable()->after('status');
            }
            if (!Schema::hasColumn('activities', 'requirements')) {
                $table->text('requirements')->nullable()->after('instructions');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->dropColumn(['is_daily', 'is_recurring', 'created_by', 'status', 'instructions', 'requirements']);
        });
    }
};