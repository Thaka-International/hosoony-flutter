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
        Schema::table('class_schedules', function (Blueprint $table) {
            // Replace room with zoom_link
            $table->dropColumn('room');
            $table->string('zoom_link')->nullable()->after('end_time');
            $table->string('zoom_meeting_id')->nullable()->after('zoom_link');
            $table->string('zoom_password')->nullable()->after('zoom_meeting_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('class_schedules', function (Blueprint $table) {
            $table->string('room')->nullable();
            $table->dropColumn(['zoom_link', 'zoom_meeting_id', 'zoom_password']);
        });
    }
};