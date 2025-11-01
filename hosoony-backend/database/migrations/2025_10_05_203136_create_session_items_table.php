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
        Schema::create('session_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('type', ['hifz', 'murajaah', 'tilawah', 'tajweed', 'tafseer', 'other']);
            $table->integer('duration_minutes')->nullable();
            $table->integer('order')->default(1);
            $table->text('content')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'completed', 'skipped'])->default('pending');
            $table->timestamps();

            $table->index(['session_id', 'order']);
            $table->index(['session_id', 'type']);
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('session_items');
    }
};
