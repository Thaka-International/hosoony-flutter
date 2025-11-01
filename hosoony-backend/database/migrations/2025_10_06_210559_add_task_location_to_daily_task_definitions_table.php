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
        Schema::table('daily_task_definitions', function (Blueprint $table) {
            $table->enum('task_location', ['in_class', 'homework'])->default('in_class')->after('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_task_definitions', function (Blueprint $table) {
            $table->dropColumn('task_location');
        });
    }
};