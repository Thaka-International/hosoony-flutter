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
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->text('content');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->enum('target_audience', ['all', 'students', 'teachers', 'parents', 'specific_class']);
            $table->foreignId('target_class_id')->nullable()->constrained('classes')->onDelete('cascade');
            $table->timestamp('sent_at')->nullable();
            $table->enum('status', ['draft', 'sent', 'cancelled'])->default('draft');
            $table->timestamps();

            $table->index(['status', 'sent_at']);
            $table->index(['target_audience', 'target_class_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
