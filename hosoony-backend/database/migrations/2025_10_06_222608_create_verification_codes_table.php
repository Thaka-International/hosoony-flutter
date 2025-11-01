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
        Schema::create('verification_codes', function (Blueprint $table) {
            $table->id();
            $table->string('phone_number', 20)->index();
            $table->string('code', 6);
            $table->enum('type', ['login', 'registration', 'password_reset'])->default('login');
            $table->boolean('is_used')->default(false);
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();
            $table->string('whatsapp_message_id')->nullable(); // Twilio WhatsApp message ID
            $table->timestamps();
            
            $table->index(['phone_number', 'code', 'is_used']);
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('verification_codes');
    }
};
