<?php

namespace App\Console\Commands;

use App\Models\Announcement;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Console\Command;
use Carbon\Carbon;

class AnnouncementsDispatchDailyCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'announcements:dispatch-daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatch daily announcements at 08:00';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dailyTime = config('quran_lms.reminders.times.daily_tasks_at', '19:30');
        $currentTime = now()->format('H:i');

        // Only run at the configured time
        if ($currentTime !== $dailyTime) {
            $this->info("Skipping daily announcements dispatch. Current time: {$currentTime}, configured time: {$dailyTime}");
            return Command::SUCCESS;
        }

        // Get pending daily announcements
        $announcements = Announcement::where('status', 'pending')
            ->where('target_audience', 'daily')
            ->whereNull('sent_at')
            ->get();

        $notificationsSent = 0;

        foreach ($announcements as $announcement) {
            $users = $this->getTargetUsers($announcement);

            foreach ($users as $user) {
                $this->sendNotification(
                    $user,
                    $announcement->title,
                    $announcement->content,
                    'announcement',
                    ['announcement_id' => $announcement->id]
                );
                $notificationsSent++;
            }

            // Mark announcement as sent
            $announcement->update([
                'sent_at' => now(),
                'status' => 'sent',
            ]);
        }

        $this->info("Daily announcements dispatched: {$notificationsSent} notifications for {$announcements->count()} announcements");
        
        // Update last run time
        \App\Http\Controllers\Api\V1\OperationsController::updateLastRun('announcements:dispatch-daily');
        
        return Command::SUCCESS;
    }

    /**
     * Get target users for announcement
     */
    private function getTargetUsers(Announcement $announcement)
    {
        if ($announcement->target_class_id) {
            return User::where('class_id', $announcement->target_class_id)
                ->where('status', 'active')
                ->get();
        }

        return User::where('status', 'active')->get();
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