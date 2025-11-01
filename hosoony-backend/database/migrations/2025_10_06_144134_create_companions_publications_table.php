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
        Schema::create('companions_publications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained()->onDelete('cascade');
            $table->date('target_date');
            $table->enum('grouping', ['pairs', 'triplets']);
            $table->enum('algorithm', ['random', 'rotation', 'manual']);
            $table->enum('attendance_source', ['all', 'committed_only']);
            $table->json('locked_pairs')->nullable();
            $table->json('pairings')->nullable();
            $table->json('room_assignments')->nullable();
            $table->string('zoom_url_snapshot')->nullable();
            $table->string('zoom_password_snapshot')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->foreignId('published_by')->nullable()->constrained('users')->onDelete('set null');
            $table->boolean('auto_published')->default(false);
            $table->timestamps();

            $table->unique(['class_id', 'target_date']);
            $table->index('target_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companions_publications');
    }
};