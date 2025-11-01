<?php

namespace App\Domain\Companions;

use App\Models\ClassModel;
use App\Models\User;
use App\Models\CompanionsPublication;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class CompanionsBuilder
{
    public function build(
        int $classId,
        string $targetDate,
        string $grouping,
        string $algorithm,
        ?array $lockedPairs = null,
        string $attendanceSource = 'all'
    ): array {
        $class = ClassModel::findOrFail($classId);
        
        // جلب الطالبات فقط مع احترام Gender/Policies
        $students = $this->getEligibleStudents($class, $attendanceSource);
        
        if ($students->count() < 2) {
            return [
                'pairings' => [],
                'room_assignments' => [],
                'message' => 'لا يوجد عدد كافي من الطالبات لتكوين الرفيقات'
            ];
        }
        
        // تطبيق الخوارزمية المحددة
        $pairings = $this->applyAlgorithm($students, $grouping, $algorithm, $lockedPairs, $classId, $targetDate);
        
        // التحقق من صحة locked_pairs
        if ($lockedPairs) {
            $this->validateLockedPairs($lockedPairs, $students, $grouping);
        }
        
        // تطبيق fail-safe للطالبات المتبقيات
        $pairings = $this->applyFailSafe($pairings, $students, $grouping);
        
        // التحقق النهائي من صحة جميع الطالبات
        $this->validateFinalPairings($pairings, $students, $classId);
        
        // توزيع الغرف
        $roomAssignments = $this->assignRooms($pairings, $class->zoom_room_start);
        
        return [
            'pairings' => $pairings,
            'room_assignments' => $roomAssignments,
            'students_count' => $students->count(),
            'groups_count' => count($pairings)
        ];
    }
    
    private function getEligibleStudents(ClassModel $class, string $attendanceSource): Collection
    {
        $students = $class->students()->where('status', 'active')->get();
        
        if ($attendanceSource === 'committed_only') {
            $students = $this->filterByAttendance($students);
        }
        
        return $students;
    }
    
    private function filterByAttendance(Collection $students): Collection
    {
        $attendanceWindowDays = config('quran_lms.companions.attendance_window_days', 14);
        $minRate = config('quran_lms.companions.min_rate', 0.6);
        
        $cutoffDate = Carbon::now()->subDays($attendanceWindowDays);
        
        return $students->filter(function ($student) use ($cutoffDate, $minRate) {
            // حساب معدل الحضور للطالبة في آخر N يوم
            $totalSessions = $student->dailyLogs()
                ->where('log_date', '>=', $cutoffDate)
                ->count();
                
            $attendedSessions = $student->dailyLogs()
                ->where('log_date', '>=', $cutoffDate)
                ->whereNotNull('verified_at')
                ->count();
            
            if ($totalSessions === 0) {
                return false; // لا توجد جلسات في الفترة المحددة
            }
            
            $attendanceRate = $attendedSessions / $totalSessions;
            return $attendanceRate >= $minRate;
        });
    }
    
    private function applyAlgorithm(
        Collection $students,
        string $grouping,
        string $algorithm,
        ?array $lockedPairs,
        int $classId,
        string $targetDate
    ): array {
        switch ($algorithm) {
            case 'manual':
                return $this->manualGrouping($students, $grouping, $lockedPairs);
                
            case 'rotation':
                return $this->rotationGrouping($students, $grouping, $lockedPairs, $classId, $targetDate);
                
            case 'random':
            default:
                return $this->randomGrouping($students, $grouping, $lockedPairs);
        }
    }
    
    private function manualGrouping(Collection $students, string $grouping, ?array $lockedPairs): array
    {
        $pairings = [];
        $usedStudentIds = collect();
        
        // إضافة الثنائيات/الثلاثيات المثبتة
        if ($lockedPairs) {
            foreach ($lockedPairs as $pair) {
                if ($this->isValidPair($pair, $students, $grouping)) {
                    $pairings[] = $pair;
                    $usedStudentIds = $usedStudentIds->merge($pair);
                }
            }
        }
        
        // توزيع الطالبات المتبقيات عشوائياً
        $remainingStudents = $students->whereNotIn('id', $usedStudentIds)->shuffle();
        $pairings = array_merge($pairings, $this->createGroupsFromStudents($remainingStudents, $grouping));
        
        return $pairings;
    }
    
    private function rotationGrouping(
        Collection $students,
        string $grouping,
        ?array $lockedPairs,
        int $classId,
        string $targetDate
    ): array
    {
        // البحث عن آخر نشر لهذا الفصل
        $lastPublication = CompanionsPublication::where('class_id', $classId)
            ->where('target_date', '<', $targetDate)
            ->whereNotNull('published_at')
            ->orderBy('target_date', 'desc')
            ->first();
        
        $pairings = [];
        $usedStudentIds = collect();
        
        // إضافة الثنائيات/الثلاثيات المثبتة
        if ($lockedPairs) {
            foreach ($lockedPairs as $pair) {
                if ($this->isValidPair($pair, $students, $grouping)) {
                    $pairings[] = $pair;
                    $usedStudentIds = $usedStudentIds->merge($pair);
                }
            }
        }
        
        // تطبيق التدوير على الطالبات المتبقيات
        $remainingStudents = $students->whereNotIn('id', $usedStudentIds);
        
        if ($lastPublication && $lastPublication->pairings) {
            $pairings = array_merge($pairings, $this->rotateFromLastPublication($remainingStudents, $lastPublication->pairings, $grouping));
        } else {
            // إذا لم توجد نشرات سابقة، استخدم التوزيع العشوائي
            $pairings = array_merge($pairings, $this->createGroupsFromStudents($remainingStudents->shuffle(), $grouping));
        }
        
        return $pairings;
    }
    
    private function randomGrouping(Collection $students, string $grouping, ?array $lockedPairs): array
    {
        $pairings = [];
        $usedStudentIds = collect();
        
        // إضافة الثنائيات/الثلاثيات المثبتة
        if ($lockedPairs) {
            foreach ($lockedPairs as $pair) {
                if ($this->isValidPair($pair, $students, $grouping)) {
                    $pairings[] = $pair;
                    $usedStudentIds = $usedStudentIds->merge($pair);
                }
            }
        }
        
        // توزيع الطالبات المتبقيات عشوائياً
        $remainingStudents = $students->whereNotIn('id', $usedStudentIds)->shuffle();
        $pairings = array_merge($pairings, $this->createGroupsFromStudents($remainingStudents, $grouping));
        
        return $pairings;
    }
    
    private function isValidPair(array $pair, Collection $students, string $grouping): bool
    {
        $expectedSize = $grouping === 'triplets' ? 3 : 2;
        
        if (count($pair) !== $expectedSize) {
            return false;
        }
        
        // التحقق من وجود جميع الطالبات في القائمة
        foreach ($pair as $studentId) {
            if (!$students->contains('id', $studentId)) {
                return false;
            }
        }
        
        return true;
    }
    
    private function createGroupsFromStudents(Collection $students, string $grouping): array
    {
        $groups = [];
        $studentsArray = $students->toArray();
        
        $groupSize = $grouping === 'triplets' ? 3 : 2;
        
        for ($i = 0; $i < count($studentsArray); $i += $groupSize) {
            $group = array_slice($studentsArray, $i, $groupSize);
            
            // إذا كانت المجموعة الأخيرة تحتوي على طالبة واحدة فقط، أضفها للمجموعة السابقة
            if (count($group) === 1 && count($groups) > 0) {
                $groups[count($groups) - 1][] = $group[0]['id'];
            } else {
                $groups[] = array_column($group, 'id');
            }
        }
        
        return $groups;
    }
    
    private function rotateFromLastPublication(Collection $students, array $lastPairings, string $grouping): array
    {
        // تطبيق تدوير بسيط: نقل كل طالبة إلى المجموعة التالية
        $allStudentIds = $students->pluck('id')->toArray();
        $rotatedPairings = [];
        
        // إنشاء خريطة للتدوير
        $rotationMap = [];
        for ($i = 0; $i < count($allStudentIds); $i++) {
            $nextIndex = ($i + 1) % count($allStudentIds);
            $rotationMap[$allStudentIds[$i]] = $allStudentIds[$nextIndex];
        }
        
        // تطبيق التدوير على المجموعات السابقة
        foreach ($lastPairings as $pair) {
            $rotatedPair = [];
            foreach ($pair as $studentId) {
                if (isset($rotationMap[$studentId])) {
                    $rotatedPair[] = $rotationMap[$studentId];
                }
            }
            
            if (count($rotatedPair) >= 2) {
                $rotatedPairings[] = $rotatedPair;
            }
        }
        
        return $rotatedPairings;
    }
    
    /**
     * التحقق من صحة locked_pairs
     */
    private function validateLockedPairs(array $lockedPairs, Collection $students, string $grouping): void
    {
        $expectedSize = $grouping === 'triplets' ? 3 : 2;
        
        foreach ($lockedPairs as $pair) {
            if (count($pair) !== $expectedSize) {
                throw new \InvalidArgumentException("حجم المجموعة المثبتة يجب أن يكون {$expectedSize}");
            }
            
            foreach ($pair as $studentId) {
                if (!$students->contains('id', $studentId)) {
                    throw new \InvalidArgumentException("الطالبة رقم {$studentId} غير موجودة في الفصل أو غير مؤهلة");
                }
            }
        }
    }

    /**
     * تطبيق fail-safe للطالبات المتبقيات
     */
    private function applyFailSafe(array $pairings, Collection $students, string $grouping): array
    {
        if ($grouping !== 'pairs') {
            return $pairings; // fail-safe يطبق فقط على الثنائيات
        }

        $usedStudentIds = collect();
        foreach ($pairings as $pair) {
            $usedStudentIds = $usedStudentIds->merge($pair);
        }

        $remainingStudents = $students->whereNotIn('id', $usedStudentIds);
        
        if ($remainingStudents->count() === 1) {
            // طالبة واحدة متبقية - ضمها لأقرب ثلاثية
            $studentId = $remainingStudents->first()->id;
            
            // البحث عن أول ثنائية لتحويلها لثلاثية
            foreach ($pairings as $index => $pair) {
                if (count($pair) === 2) {
                    $pairings[$index][] = $studentId;
                    break;
                }
            }
        }

        return $pairings;
    }

    /**
     * التحقق النهائي من صحة جميع الطالبات
     */
    private function validateFinalPairings(array $pairings, Collection $students, int $classId): void
    {
        $usedStudentIds = collect();
        
        foreach ($pairings as $pair) {
            foreach ($pair as $studentId) {
                if ($usedStudentIds->contains($studentId)) {
                    throw new \InvalidArgumentException("الطالبة رقم {$studentId} مكررة في الرفيقات");
                }
                
                $student = $students->firstWhere('id', $studentId);
                if (!$student) {
                    throw new \InvalidArgumentException("الطالبة رقم {$studentId} غير موجودة");
                }
                
                if ($student->class_id !== $classId) {
                    throw new \InvalidArgumentException("الطالبة رقم {$studentId} لا تنتمي للفصل المحدد");
                }
                
                if ($student->gender !== 'female') {
                    throw new \InvalidArgumentException("الطالبة رقم {$studentId} ليست أنثى");
                }
                
                $usedStudentIds->push($studentId);
            }
        }
    }

    public function assignRooms(array $pairings, int $roomStart): array
    {
        $roomAssignments = [];
        $currentRoom = $roomStart;

        foreach ($pairings as $pairing) {
            $roomAssignments[(string)$currentRoom] = $pairing;
            $currentRoom++;
        }

        return $roomAssignments;
    }
}
