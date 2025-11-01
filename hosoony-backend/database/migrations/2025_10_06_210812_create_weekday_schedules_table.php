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
        Schema::create('weekday_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // اسم الجدول مثل "جدول الأسبوع الأول"
            $table->text('description')->nullable();
            $table->json('schedule'); // {"sunday": {"start_time": "07:00", "end_time": "08:00"}, ...}
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false); // جدول افتراضي
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weekday_schedules');
    }
};