<?php

namespace App\Console\Commands;

use App\Models\Session;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Console\Command;
use Carbon\Carbon;

class SessionRemindersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'session:reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send session reminders T-15 minutes before session starts';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $reminderMinutes = config('quran_lms.reminders.times.session_t_minus_minutes', 15);
        $reminderTime = now()->addMinutes($reminderMinutes);

        // Get sessions starting in the next 15 minutes
        $sessions = Session::where('status', 'scheduled')
            ->whereBetween('starts_at', [now(), $reminderTime])
            ->with(['class', 'teacher'])
            ->get();

        $notificationsSent = 0;

        foreach ($sessions as $session) {
            // Send notification to teacher
            $this->sendNotification(
                $session->teacher,
                'تذكير الجلسة',
                "جلسة {$session->title} ستبدأ خلال {$reminderMinutes} دقيقة",
                'session_reminder',
                ['session_id' => $session->id]
            );

            // Send notification to students in the class
            $students = User::where('class_id', $session->class_id)
                ->where('role', 'student')
                ->where('status', 'active')
                ->get();

            foreach ($students as $student) {
                $this->sendNotification(
                    $student,
                    'تذكير الجلسة',
                    "جلسة {$session->title} ستبدأ خلال {$reminderMinutes} دقيقة",
                    'session_reminder',
                    ['session_id' => $session->id]
                );
            }

            $notificationsSent += $students->count() + 1; // +1 for teacher
        }

        $this->info("Session reminders sent: {$notificationsSent} notifications for {$sessions->count()} sessions");
        
        // Update last run time
        \App\Http\Controllers\Api\V1\OperationsController::updateLastRun('session:reminders');
        
        return Command::SUCCESS;
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