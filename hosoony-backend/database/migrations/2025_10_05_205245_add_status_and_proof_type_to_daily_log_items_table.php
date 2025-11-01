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
        Schema::table('daily_log_items', function (Blueprint $table) {
            $table->enum('status', ['pending', 'in_progress', 'completed', 'skipped'])->default('pending')->after('task_definition_id');
            $table->enum('proof_type', ['none', 'note', 'audio', 'video'])->default('none')->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_log_items', function (Blueprint $table) {
            $table->dropColumn(['status', 'proof_type']);
        });
    }
};
