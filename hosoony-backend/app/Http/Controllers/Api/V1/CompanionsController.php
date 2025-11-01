<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Domain\Companions\CompanionsBuilder;
use App\Models\ClassModel;
use App\Models\CompanionsPublication;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CompanionsController extends Controller
{
    private CompanionsBuilder $builder;
    private NotificationService $notificationService;

    public function __construct(CompanionsBuilder $builder, NotificationService $notificationService)
    {
        $this->builder = $builder;
        $this->notificationService = $notificationService;
    }

    /**
     * Generate companions for a class
     */
    public function generate(Request $request, $classId): JsonResponse
    {
        $user = Auth::user();
        
        // صلاحيات: admin و teacher_support يمكنهما الإنشاء/التعديل/النشر
        if (!in_array($user->role, ['admin', 'teacher_support'])) {
            return response()->json(['message' => 'غير مصرح لك بهذا الإجراء'], 403);
        }

        $request->validate([
            'target_date' => 'required|date|after_or_equal:today',
            'grouping' => 'required|in:pairs,triplets',
            'algorithm' => 'required|in:random,rotation,manual',
            'attendance_source' => 'required|in:all,committed_only',
            'locked_pairs' => 'nullable|array',
            'locked_pairs.*' => 'array',
        ]);

        $class = ClassModel::findOrFail($classId);

        try {
            $result = $this->builder->build(
                $class->id,
                $request->target_date,
                $request->grouping,
                $request->algorithm,
                $request->locked_pairs,
                $request->attendance_source
            );

            // إنشاء/تحديث سجل companions_publications
            $publication = CompanionsPublication::updateOrCreate(
                [
                    'class_id' => $class->id,
                    'target_date' => $request->target_date,
                ],
                [
                    'grouping' => $request->grouping,
                    'algorithm' => $request->algorithm,
                    'attendance_source' => $request->attendance_source,
                    'locked_pairs' => $request->locked_pairs,
                    'pairings' => $result['pairings'],
                ]
            );

            return response()->json([
                'message' => 'تم توليد الرفيقات بنجاح',
                'publication_id' => $publication->id,
                'pairings' => $result['pairings'],
                'students_count' => $result['students_count'],
                'groups_count' => $result['groups_count'],
            ]);

        } catch (\Exception $e) {
            return response()->json(['message' => 'حدث خطأ في توليد الرفيقات: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Lock companions pairs before publishing
     */
    public function lock(Request $request, $classId, string $targetDate): JsonResponse
    {
        $user = Auth::user();
        
        // صلاحيات: admin و teacher_support يمكنهما التعديل
        if (!in_array($user->role, ['admin', 'teacher_support'])) {
            return response()->json(['message' => 'غير مصرح لك بهذا الإجراء'], 403);
        }

        $request->validate([
            'locked_pairs' => 'required|array',
            'locked_pairs.*' => 'array',
        ]);

        $publication = CompanionsPublication::where('class_id', $classId)
            ->where('target_date', $targetDate)
            ->first();

        if (!$publication) {
            return response()->json(['message' => 'لم يتم العثور على نشر للتاريخ المحدد'], 404);
        }

        $publication->update([
            'locked_pairs' => $request->locked_pairs,
        ]);

        return response()->json([
            'message' => 'تم تثبيت الرفيقات بنجاح',
            'locked_pairs' => $publication->locked_pairs,
        ]);
    }

    /**
     * Publish companions and send notifications
     */
    public function publish(Request $request, $classId, string $targetDate): JsonResponse
    {
        $user = Auth::user();
        
        // صلاحيات: admin و teacher_support يمكنهما النشر
        if (!in_array($user->role, ['admin', 'teacher_support'])) {
            return response()->json(['message' => 'غير مصرح لك بهذا الإجراء'], 403);
        }

        $publication = CompanionsPublication::where('class_id', $classId)
            ->where('target_date', $targetDate)
            ->first();

        if (!$publication) {
            return response()->json(['message' => 'لم يتم العثور على نشر للتاريخ المحدد'], 404);
        }

        if ($publication->isPublished()) {
            return response()->json(['message' => 'تم نشر الرفيقات مسبقاً'], 409);
        }

        try {
            $class = ClassModel::findOrFail($classId);
            
            // تجميد pairings وتوليد room_assignments
            $roomAssignments = $this->builder->assignRooms($publication->pairings, $class->zoom_room_start);

            // تحديث النشر مع البيانات النهائية
            $publication->update([
                'room_assignments' => $roomAssignments,
                'zoom_url_snapshot' => $class->zoom_url,
                'zoom_password_snapshot' => $class->zoom_password,
                'published_at' => now(),
                'published_by' => $user->id,
                'auto_published' => false,
            ]);

            // إرسال الإشعارات للطالبات
            $this->sendCompanionsNotifications($class, $publication);

            return response()->json([
                'message' => 'تم نشر الرفيقات بنجاح وإرسال الإشعارات',
                'publication_id' => $publication->id,
                'room_assignments' => $roomAssignments,
                'published_at' => $publication->published_at,
            ]);

        } catch (\Exception $e) {
            return response()->json(['message' => 'حدث خطأ في نشر الرفيقات: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get companions for current user
     */
    public function getMyCompanions(Request $request): JsonResponse
    {
        $user = Auth::user();
        $date = $request->query('date', now()->format('Y-m-d'));

        if (!$date) {
            return response()->json(['message' => 'التاريخ مطلوب'], 400);
        }

        try {
            if ($user->role === 'student') {
                return $this->getStudentCompanions($user, $date);
            } elseif (in_array($user->role, ['teacher', 'teacher_support'])) {
                return $this->getTeacherCompanions($user, $date);
            } else {
                return response()->json(['message' => 'غير مصرح لك بهذا الإجراء'], 403);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'حدث خطأ في جلب الرفيقات: ' . $e->getMessage()], 500);
        }
    }

    private function getStudentCompanions(User $student, string $date): JsonResponse
    {
        if (!$student->class_id) {
            return response()->json(['message' => 'الطالب غير مسجل في أي فصل'], 404);
        }

        $publication = CompanionsPublication::where('class_id', $student->class_id)
            ->where('target_date', $date)
            ->whereNotNull('published_at')
            ->first();

        if (!$publication) {
            return response()->json(['message' => 'لا توجد رفيقات منشورة لهذا التاريخ'], 404);
        }

        // البحث عن مجموعة الطالب
        $studentGroup = null;
        $roomNumber = null;

        foreach ($publication->room_assignments as $room => $group) {
            if (in_array($student->id, $group)) {
                $studentGroup = $group;
                $roomNumber = $room;
                break;
            }
        }

        if (!$studentGroup) {
            return response()->json(['message' => 'لم يتم العثور على مجموعة الطالب'], 404);
        }

        // جلب أسماء الرفيقات
        $companions = User::whereIn('id', $studentGroup)
            ->where('id', '!=', $student->id)
            ->get(['id', 'name']);

        return response()->json([
            'date' => $date,
            'room_number' => $roomNumber,
            'zoom_url' => $publication->zoom_url_snapshot,
            'zoom_password' => $publication->zoom_password_snapshot,
            'companions' => $companions->map(function ($companion) {
                return [
                    'id' => $companion->id,
                    'name' => $companion->name,
                ];
            }),
        ]);
    }

    private function getTeacherCompanions(User $teacher, string $date): JsonResponse
    {
        if (!$teacher->class_id) {
            return response()->json(['message' => 'المعلم غير مسجل في أي فصل'], 404);
        }

        $publication = CompanionsPublication::where('class_id', $teacher->class_id)
            ->where('target_date', $date)
            ->whereNotNull('published_at')
            ->first();

        if (!$publication) {
            return response()->json(['message' => 'لا توجد رفيقات منشورة لهذا التاريخ'], 404);
        }

        // جلب جميع المجموعات مع أسماء الطالبات
        $allGroups = [];
        foreach ($publication->room_assignments as $room => $group) {
            $students = User::whereIn('id', $group)->get(['id', 'name']);
            $allGroups[] = [
                'room_number' => $room,
                'students' => $students->map(function ($student) {
                    return [
                        'id' => $student->id,
                        'name' => $student->name,
                    ];
                }),
            ];
        }

        return response()->json([
            'date' => $date,
            'zoom_url' => $publication->zoom_url_snapshot,
            'zoom_password' => $publication->zoom_password_snapshot,
            'groups' => $allGroups,
        ]);
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