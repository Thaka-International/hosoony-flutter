<?php

namespace App\Console\Commands;

use App\Models\Payment;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Console\Command;
use Carbon\Carbon;

class PaymentRemindersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payment:reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send payment reminders every hour';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $reminderDays = config('quran_lms.reminders.times.payments_days_offsets', [7, 3, 1]);
        $notificationsSent = 0;

        foreach ($reminderDays as $daysOffset) {
            $dueDate = now()->addDays($daysOffset)->startOfDay();
            
            // Get payments due on this date
            $payments = Payment::where('status', 'pending')
                ->whereDate('due_date', $dueDate)
                ->with('student')
                ->get();

            foreach ($payments as $payment) {
                $this->sendPaymentReminder($payment, $daysOffset);
                $notificationsSent++;
            }
        }

        $this->info("Payment reminders sent: {$notificationsSent} notifications");
        
        // Update last run time
        \App\Http\Controllers\Api\V1\OperationsController::updateLastRun('payment:reminders');
        
        return Command::SUCCESS;
    }

    /**
     * Send payment reminder to student
     */
    private function sendPaymentReminder(Payment $payment, int $daysOffset): void
    {
        $student = $payment->student;
        
        $title = 'تذكير الدفع';
        $message = "يُرجى تسديد المبلغ {$payment->amount} {$payment->currency} خلال {$daysOffset} " . ($daysOffset === 1 ? 'يوم' : 'أيام');
        
        $this->sendNotification(
            $student,
            $title,
            $message,
            'payment_reminder',
            [
                'payment_id' => $payment->id,
                'amount' => $payment->amount,
                'currency' => $payment->currency,
                'due_date' => $payment->due_date->format('Y-m-d'),
                'days_remaining' => $daysOffset,
            ]
        );
    }

    /**
     * Send notification to user
     */
    private function sendNotification(User $user, string $title, string $message, string $type, array $data = []): void
    {
        Notification::create([
            'user_id' => $user->id,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'channel' => 'push',
            'data' => $data,
            'sent_at' => now(),
            'status' => 'sent',
        ]);
    }
}