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
        Schema::table('daily_logs', function (Blueprint $table) {
            $table->integer('finish_order')->nullable()->after('status');
            $table->index(['log_date', 'finish_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_logs', function (Blueprint $table) {
            $table->dropIndex(['log_date', 'finish_order']);
            $table->dropColumn('finish_order');
        });
    }
};
