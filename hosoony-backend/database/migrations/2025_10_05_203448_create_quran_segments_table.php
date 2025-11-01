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
        Schema::create('quran_segments', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "سورة البقرة", "جزء عم"
            $table->text('description')->nullable();
            $table->enum('type', ['surah', 'juz', 'hizb', 'page', 'ayah'])->default('surah');
            $table->integer('start_ayah')->nullable();
            $table->integer('end_ayah')->nullable();
            $table->integer('start_page')->nullable();
            $table->integer('end_page')->nullable();
            $table->integer('order')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['type', 'order']);
            $table->index(['start_page', 'end_page']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quran_segments');
    }
};
