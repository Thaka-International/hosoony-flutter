<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use App\Models\Payment;
use App\Services\NotificationService;
use Illuminate\Console\Command;
use Carbon\Carbon;

class ProcessSubscriptionBilling extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:process-billing {--dry-run : Run without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process subscription billing for due subscriptions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        
        if ($isDryRun) {
            $this->info('🔍 Running in DRY RUN mode - no changes will be made');
        }

        $this->info('🔄 Processing subscription billing...');

        // Get subscriptions due for billing
        $dueSubscriptions = Subscription::dueForBilling()
            ->with(['student', 'feesPlan'])
            ->get();

        if ($dueSubscriptions->isEmpty()) {
            $this->info('✅ No subscriptions due for billing');
            return;
        }

        $this->info("📊 Found {$dueSubscriptions->count()} subscriptions due for billing");

        $processed = 0;
        $errors = 0;

        foreach ($dueSubscriptions as $subscription) {
            try {
                $this->processSubscription($subscription, $isDryRun);
                $processed++;
                
                $this->line("✅ Processed subscription for {$subscription->student->name}");
            } catch (\Exception $e) {
                $errors++;
                $this->error("❌ Error processing subscription for {$subscription->student->name}: {$e->getMessage()}");
            }
        }

        $this->info("📈 Summary:");
        $this->info("   - Processed: {$processed}");
        $this->info("   - Errors: {$errors}");
        
        if ($isDryRun) {
            $this->info('🔍 This was a DRY RUN - no actual changes were made');
        }
    }

    /**
     * Process a single subscription
     */
    private function processSubscription(Subscription $subscription, bool $isDryRun): void
    {
        if ($isDryRun) {
            $this->line("🔍 Would process billing for {$subscription->student->name}");
            return;
        }

        // Create payment record
        $payment = Payment::create([
            'student_id' => $subscription->student_id,
            'amount' => $subscription->amount,
            'payment_method' => 'online', // Default to online, can be updated later
            'status' => 'pending',
            'due_date' => $subscription->next_billing_date,
            'notes' => "Auto-generated payment for {$subscription->billing_cycle} subscription",
            'paid_date' => null,
        ]);

        // Add billing record to subscription
        $subscription->addBillingRecord([
            'payment_id' => $payment->id,
            'billing_cycle' => $subscription->billing_cycle,
            'status' => 'pending',
        ]);

        // Send notification to student
        $this->sendBillingNotification($subscription, $payment);
    }

    /**
     * Send billing notification to student
     */
    private function sendBillingNotification(Subscription $subscription, Payment $payment): void
    {
        $notificationService = app(NotificationService::class);
        
        $message = "تم إنشاء فاتورة جديدة لاشتراكك الشهري بقيمة {$subscription->amount} ريال. تاريخ الاستحقاق: {$payment->due_date->format('Y-m-d')}";
        
        $notificationService->sendNotification(
            $subscription->student,
            'فاتورة جديدة',
            $message,
            ['email', 'push']
        );
    }
}