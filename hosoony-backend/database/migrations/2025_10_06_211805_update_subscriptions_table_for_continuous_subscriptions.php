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
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->enum('billing_cycle', ['monthly', 'quarterly', 'semester', 'yearly'])->default('monthly')->after('student_id');
            $table->decimal('amount', 10, 2)->nullable()->after('billing_cycle');
            $table->date('next_billing_date')->nullable()->after('amount');
            $table->boolean('is_active')->default(true)->after('next_billing_date');
            $table->boolean('auto_renew')->default(true)->after('is_active');
            $table->json('billing_history')->nullable()->after('auto_renew'); // تاريخ الفواتير
            $table->timestamp('last_payment_at')->nullable()->after('billing_history');
            $table->timestamp('cancelled_at')->nullable()->after('last_payment_at');
            $table->text('cancellation_reason')->nullable()->after('cancelled_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn([
                'billing_cycle',
                'amount',
                'next_billing_date',
                'is_active',
                'auto_renew',
                'billing_history',
                'last_payment_at',
                'cancelled_at',
                'cancellation_reason',
            ]);
        });
    }
};