<?php

namespace App\Console\Commands;

use App\Domain\Companions\CompanionsBuilder;
use App\Models\CompanionsPublication;
use App\Models\ClassModel;
use App\Services\NotificationService;
use Illuminate\Console\Command;
use Carbon\Carbon;

class CompanionsAutoPublish extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'companions:autopublish';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'نشر الرفيقات تلقائياً لليوم التالي';

    private CompanionsBuilder $builder;
    private NotificationService $notificationService;

    public function __construct(CompanionsBuilder $builder, NotificationService $notificationService)
    {
        parent::__construct();
        $this->builder = $builder;
        $this->notificationService = $notificationService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('بدء النشر التلقائي للرفيقات...');

        $tomorrow = Carbon::tomorrow();
        $this->info("البحث عن النشرات المطلوبة لليوم: {$tomorrow->format('Y-m-d')}");

        // البحث عن companions_publications لليوم التالي التي لم تُنشر
        $unpublishedPublications = CompanionsPublication::where('target_date', $tomorrow)
            ->whereNull('published_at')
            ->with('class')
            ->get();

        if ($unpublishedPublications->isEmpty()) {
            $this->info('لا توجد نشرات مطلوبة لليوم التالي.');
            return 0;
        }

        $this->info("تم العثور على {$unpublishedPublications->count()} نشر مطلوب.");

        $publishedCount = 0;
        $errorCount = 0;

        foreach ($unpublishedPublications as $publication) {
            try {
                $this->processPublication($publication);
                $publishedCount++;
                $this->info("تم نشر الرفيقات للفصل: {$publication->class->name}");
            } catch (\Exception $e) {
                $errorCount++;
                $this->error("خطأ في نشر الفصل {$publication->class->name}: {$e->getMessage()}");
            }
        }

        $this->info("تم الانتهاء من النشر التلقائي.");
        $this->info("نشرات ناجحة: {$publishedCount}");
        $this->info("أخطاء: {$errorCount}");

        return 0;
    }

    private function processPublication(CompanionsPublication $publication): void
    {
        $class = $publication->class;

        // إذا لم تكن pairings موجودة، استدعي CompanionsBuilder بالإعدادات الافتراضية
        if (empty($publication->pairings)) {
            $this->info("توليد الرفيقات للفصل: {$class->name}");

            $result = $this->builder->build(
                $class->id,
                $publication->target_date->format('Y-m-d'),
                'pairs', // الإعداد الافتراضي
                'rotation', // الإعداد الافتراضي
                $publication->locked_pairs,
                config('quran_lms.companions.default_attendance_source', 'committed_only') // الإعداد الافتراضي
            );

            $publication->update([
                'grouping' => 'pairs',
                'algorithm' => 'rotation',
                'attendance_source' => config('quran_lms.companions.default_attendance_source', 'committed_only'),
                'pairings' => $result['pairings'],
            ]);
        }

        // نشر مع وضع auto_published=true
        $this->publishPublication($publication);
    }

    private function publishPublication(CompanionsPublication $publication): void
    {
        $class = $publication->class;

        // تجميد pairings وتوليد room_assignments
        $roomAssignments = $this->builder->assignRooms($publication->pairings, $class->zoom_room_start);

        // تحديث النشر مع البيانات النهائية
        $publication->update([
            'room_assignments' => $roomAssignments,
            'zoom_url_snapshot' => $class->zoom_url,
            'zoom_password_snapshot' => $class->zoom_password,
            'published_at' => now(),
            'published_by' => null, // نشر تلقائي
            'auto_published' => true,
        ]);

        // إرسال الإشعارات للطالبات
        $this->sendCompanionsNotifications($class, $publication);

        $this->info("تم نشر الرفيقات وإرسال الإشعارات للفصل: {$class->name}");
    }

    private function sendCompanionsNotifications(ClassModel $class, CompanionsPublication $publication): void
    {
        $students = $class->students()->where('status', 'active')->get();

        foreach ($publication->room_assignments as $roomNumber => $group) {
            $groupStudents = $students->whereIn('id', $group);
            
            foreach ($groupStudents as $student) {
                $companions = $groupStudents->where('id', '!=', $student->id);
                $companionNames = $companions->pluck('name')->join(' و ');
                
                // بناء رسالة الإشعار حسب التنسيق المطلوب
                $message = "رفيقتك/رفيقاتك: {$companionNames} — غرفة {$roomNumber}";
                
                if ($publication->zoom_url_snapshot) {
                    $message .= " — رابط Zoom {$publication->zoom_url_snapshot}";
                }
                
                if ($publication->zoom_password_snapshot) {
                    $message .= " — رمز الدخول: {$publication->zoom_password_snapshot}";
                }

                // إنشاء إشعارات متعددة القنوات
                $this->createNotification($student->id, 'رفيقات اليوم', $message, 'push');
                $this->createNotification($student->id, 'رفيقات اليوم', $message, 'email');
            }
        }
    }

    private function createNotification(int $userId, string $title, string $message, string $channel): void
    {
        $notification = \App\Models\Notification::create([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'channel' => $channel,
            'sent_at' => now(),
        ]);

        $this->notificationService->sendNotification($notification);
    }
}