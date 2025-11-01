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
            // Drop the foreign key first
            $table->dropForeign(['class_id']);
        });
        
        Schema::table('activities', function (Blueprint $table) {
            // Then drop the index
            $table->dropIndex('activities_class_id_status_index');
        });
        
        Schema::table('activities', function (Blueprint $table) {
            // Finally drop the column
            $table->dropColumn('class_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->foreignId('class_id')->nullable()->constrained('classes')->onDelete('cascade');
        });
    }
};