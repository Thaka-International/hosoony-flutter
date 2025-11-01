# خطة العمل الكاملة لإصلاح نظام الرفيقات

## 📋 الملخص التنفيذي

### **المشاكل المكتشفة:**
1. ❌ عرض أرقام بدلاً من أسماء في Filament
2. ❌ عدم وجود Service منفصل (CompanionsService)
3. ❌ بنية API غير موحدة مع DailyTasks
4. ❌ Flutter غير متوافق مع API الحالي
5. ⚠️ لا يوجد نشر تلقائي للرفيقات

---

## 🎯 المرحلة 1: إصلاح عرض الأسماء في Filament

### **الأولوية:** 🔴 عالية جداً

### **الملفات:** `hosoony2-git/app/Filament/Resources/CompanionsPublicationResource.php`

#### **1.1 إصلاح عرض الرفيقات المولدة (السطر 138-159)**

```php
// ❌ الكود الحالي
$html .= implode(', ', $pair);  // يطبع: 123, 456

// ✅ الكود المطلوب
// جلب أسماء الطالبات
$studentIds = collect($record->pairings)->flatten()->unique()->toArray();
$students = User::whereIn('id', $studentIds)->pluck('name', 'id')->toArray();

foreach ($record->pairings as $index => $pair) {
    $names = array_map(function ($id) use ($students) {
        return $students[$id] ?? "ID: $id";
    }, $pair);
    
    $html .= implode(', ', $names);  // يطبع: فاطمة أحمد، خديجة محمد
}
```

#### **1.2 إصلاح معاينة الغرف (السطر 322-340)**

```php
// ❌ الكود الحالي
$preview .= implode(', ', $students);  // أرقام

// ✅ الكود المطلوب
$studentIds = collect($roomAssignments)->flatten()->unique()->toArray();
$studentNames = User::whereIn('id', $studentIds)
    ->pluck('name', 'id')
    ->toArray();

foreach ($roomAssignments as $room => $group) {
    $names = array_map(function ($id) use ($studentNames) {
        return $studentNames[$id] ?? "ID: $id";
    }, $group);
    
    $preview .= '<strong>الغرفة ' . $room . ':</strong> ' . implode(', ', $names);
}
```

#### **1.3 إصلاح الرفيقات المثبتة (السطر 117-136)**

```php
// استبدال TextInput بـ Select مع أسماء الطالبات
Repeater::make('locked_pairs')
    ->schema([
        Repeater::make('students')
            ->schema([
                Select::make('student_id')  // بدلاً من TextInput
                    ->label('اسم الطالبة')
                    ->options(function ($record) {
                        if (!$record || !$record->class_id) {
                            return [];
                        }
                        return User::where('class_id', $record->class_id)
                            ->where('role', 'student')
                            ->where('status', 'active')
                            ->pluck('name', 'id')
                            ->toArray();
                    })
                    ->searchable()
                    ->required(),
            ])
```

#### **النتيجة المتوقعة:**
- ✅ عرض أسماء الطالبات بدلاً من الأرقام
- ✅ البحث عن الطالبات بالاسم في locked_pairs
- ✅ معاينة الغرف بالأسماء

#### **وقت التنفيذ:** 15-20 دقيقة

---

## 🎯 المرحلة 2: إنشاء CompanionsService

### **الأولوية:** 🔴 عالية

### **الملف الجديد:** `hosoony2-git/app/Services/CompanionsService.php`

```php
<?php

namespace App\Services;

use App\Models\CompanionsPublication;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CompanionsService
{
    /**
     * Get companions for a student
     * Returns unified structure like DailyTasks
     */
    public function getStudentCompanions(int $studentId, string $date): array
    {
        try {
            $student = User::findOrFail($studentId);
            
            // Check if student has a class
            if (!$student->class_id) {
                return [
                    'success' => false,
                    'message' => 'الطالب غير مسجل في أي فصل',
                    'date' => $date,
                    'companions' => [],
                ];
            }

            // Find published companions publication
            $publication = CompanionsPublication::where('class_id', $student->class_id)
                ->where('target_date', $date)
                ->whereNotNull('published_at')
                ->first();

            if (!$publication) {
                return [
                    'success' => false,
                    'message' => 'لا توجد رفيقات منشورة لهذا التاريخ',
                    'date' => $date,
                    'companions' => [],
                ];
            }

            // Find student's group
            $studentGroup = null;
            $roomNumber = null;

            foreach ($publication->room_assignments ?? [] as $room => $group) {
                if (in_array($studentId, $group)) {
                    $studentGroup = $group;
                    $roomNumber = $room;
                    break;
                }
            }

            if (!$studentGroup) {
                return [
                    'success' => false,
                    'message' => 'لم يتم العثور على مجموعة الطالب',
                    'date' => $date,
                    'companions' => [],
                ];
            }

            // Get companion names
            $companions = User::whereIn('id', $studentGroup)
                ->where('id', '!=', $studentId)
                ->get(['id', 'name']);

            return [
                'success' => true,
                'message' => 'تم جلب الرفيقات بنجاح',
                'date' => $date,
                'room_number' => $roomNumber,
                'zoom_url' => $publication->zoom_url_snapshot,
                'zoom_password' => $publication->zoom_password_snapshot,
                'companions' => $companions->map(function ($companion) {
                    return [
                        'id' => $companion->id,
                        'name' => $companion->name,
                    ];
                })->toArray(),
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'حدث خطأ في جلب الرفيقات: ' . $e->getMessage(),
                'date' => $date,
                'companions' => [],
            ];
        }
    }

    /**
     * Get companions for a teacher
     */
    public function getTeacherCompanions(int $teacherId, string $date): array
    {
        try {
            $teacher = User::findOrFail($teacherId);
            
            if (!$teacher->class_id) {
                return [
                    'success' => false,
                    'message' => 'المعلم غير مسجل في أي فصل',
                    'date' => $date,
                    'groups' => [],
                ];
            }

            $publication = CompanionsPublication::where('class_id', $teacher->class_id)
                ->where('target_date', $date)
                ->whereNotNull('published_at')
                ->first();

            if (!$publication) {
                return [
                    'success' => false,
                    'message' => 'لا توجد رفيقات منشورة لهذا التاريخ',
                    'date' => $date,
                    'groups' => [],
                ];
            }

            // Get all groups with student names
            $allGroups = [];
            foreach ($publication->room_assignments ?? [] as $room => $group) {
                $students = User::whereIn('id', $group)->get(['id', 'name']);
                $allGroups[] = [
                    'room_number' => $room,
                    'students' => $students->map(function ($student) {
                        return [
                            'id' => $student->id,
                            'name' => $student->name,
                        ];
                    })->toArray(),
                ];
            }

            return [
                'success' => true,
                'message' => 'تم جلب الرفيقات بنجاح',
                'date' => $date,
                'zoom_url' => $publication->zoom_url_snapshot,
                'zoom_password' => $publication->zoom_password_snapshot,
                'groups' => $allGroups,
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'حدث خطأ في جلب الرفيقات: ' . $e->getMessage(),
                'date' => $date,
                'groups' => [],
            ];
        }
    }
}
```

#### **الوقت المتوقع:** 20-30 دقيقة

---

## 🎯 المرحلة 3: تحديث CompanionsController

### **الأولوية:** 🔴 عالية

### **الملف:** `hosoony2-git/app/Http/Controllers/Api/V1/CompanionsController.php`

#### **3.1 إضافة Service إلى Constructor**

```php
private CompanionsBuilder $builder;
private NotificationService $notificationService;
private CompanionsService $companionsService;  // ✅ إضافة

public function __construct(
    CompanionsBuilder $builder,
    NotificationService $notificationService,
    CompanionsService $companionsService  // ✅ إضافة
) {
    $this->builder = $builder;
    $this->notificationService = $notificationService;
    $this->companionsService = $companionsService;  // ✅ إضافة
}
```

#### **3.2 تحديث getStudentCompanions (السطر 203-251)**

```php
// ❌ الكود الحالي (يقوم بكل العمل)
private function getStudentCompanions(User $student, string $date): JsonResponse
{
    if (!$student->class_id) {
        return response()->json(['message' => '...'], 404);
    }
    
    // ... الكثير من الكود ...
    
    return response()->json([...]);
}

// ✅ الكود الجديد (بسيط)
private function getStudentCompanions(User $student, string $date): JsonResponse
{
    $result = $this->companionsService->getStudentCompanions($student->id, $date);
    return response()->json($result);
}
```

#### **3.3 تحديث getTeacherCompanions (السطر 253-289)**

```php
// ❌ الكود الحالي
private function getTeacherCompanions(User $teacher, string $date): JsonResponse
{
    // ... الكثير من الكود ...
}

// ✅ الكود الجديد
private function getTeacherCompanions(User $teacher, string $date): JsonResponse
{
    $result = $this->companionsService->getTeacherCompanions($teacher->id, $date);
    return response()->json($result);
}
```

#### **الوقت المتوقع:** 15-20 دقيقة

---

## 🎯 المرحلة 4: تحديث Flutter API Service

### **الأولوية:** 🟡 متوسطة

### **الملف:** `hosoony_flutter/lib/services/api_service.dart`

#### **4.1 تحديث getMyCompanions (السطر 163-184)**

```dart
// ❌ الكود الحالي
static Future<List<Map<String, dynamic>>> getMyCompanions() async {
  final response = await _dio.get('/me/companions');
  if (response.data['data'] != null && response.data['data']['companions'] != null) {
    return List<Map<String, dynamic>>.from(response.data['data']['companions'] ?? []);
  }
  return List<Map<String, dynamic>>.from(response.data['data'] ?? []);
}

// ✅ الكود الجديد (موحد مثل DailyTasks)
static Future<Map<String, dynamic>> getMyCompanions() async {
  if (_token == null) {
    throw Exception('No authentication token available');
  }
  
  final response = await _dio.get('/me/companions');
  final data = response.data;
  
  return {
    'success': data['success'] ?? true,
    'message': data['message'],
    'date': data['date'],
    'room_number': data['room_number'],
    'zoom_url': data['zoom_url'],
    'zoom_password': data['zoom_password'],
    'companions': List<Map<String, dynamic>>.from(data['companions'] ?? []),
  };
}
```

#### **الوقت المتوقع:** 10-15 دقيقة

---

## 🎯 المرحلة 5: تحديث Flutter Companions Page

### **الأولوية:** 🟡 متوسطة

### **الملف:** `hosoony_flutter/lib/features/student/pages/companions_page.dart`

#### **5.1 تحديث _loadCompanions (السطر 60-92)**

```dart
// ❌ الكود الحالي
Future<void> _loadCompanions() async {
  final companions = await ApiService.getMyCompanions();
  setState(() {
    _companions = companions;
  });
}

// ✅ الكود الجديد (يشمل معالجة success/error)
Future<void> _loadCompanions() async {
  try {
    setState(() {
      _isLoading = true;
      _error = null;
    });

    final authState = ref.read(authStateProvider);
    if (authState.token != null) {
      ApiService.setToken(authState.token!);
    }

    final response = await ApiService.getMyCompanions();
    
    if (response['success'] == true) {
      setState(() {
        _companions = List<Map<String, dynamic>>.from(
          response['companions'] ?? []
        );
        _date = response['date'];
        _roomNumber = response['room_number'];
        _zoomUrl = response['zoom_url'];
        _zoomPassword = response['zoom_password'];
        _isLoading = false;
      });
      _animationController.forward();
    } else {
      setState(() {
        _error = response['message'] ?? 'لا توجد رفيقات متاحة';
        _isLoading = false;
      });
    }
  } catch (e) {
    setState(() {
      if (e.toString().contains('404')) {
        _error = 'لا توجد رفيقات متاحة حالياً';
      } else if (e.toString().contains('401')) {
        _error = 'انتهت صلاحية الجلسة، يرجى تسجيل الدخول مرة أخرى';
      } else {
        _error = 'خطأ في تحميل الرفيقات: ${e.toString()}';
      }
      _isLoading = false;
    });
  }
}
```

#### **5.2 إضافة State Variables**

```dart
String? _date;
String? _roomNumber;
String? _zoomUrl;
String? _zoomPassword;
```

#### **الوقت المتوقع:** 20-30 دقيقة

---

## 🎯 المرحلة 6: إضافة نشر تلقائي (اختياري)

### **الأولوية:** 🟢 منخفضة

### **ملف جديد:** `hosoony2-git/app/Console/Commands/AutoPublishCompanions.php`

```php
<?php

namespace App\Console\Commands;

use App\Models\CompanionsPublication;
use App\Domain\Companions\CompanionsBuilder;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class AutoPublishCompanions extends Command
{
    protected $signature = 'companions:auto-publish';
    protected $description = 'Auto-publish companions for today';
    
    public function handle()
    {
        $today = now()->format('Y-m-d');
        
        $publications = CompanionsPublication::where('target_date', $today)
            ->whereNull('published_at')
            ->with('class')
            ->get();
        
        $this->info("Found {$publications->count()} publications to publish");
        
        foreach ($publications as $publication) {
            try {
                // تخصيص الغرف
                $builder = app(CompanionsBuilder::class);
                $roomAssignments = $builder->assignRooms(
                    $publication->pairings,
                    $publication->class->zoom_room_start
                );
                
                // تحديث السجل
                $publication->update([
                    'room_assignments' => $roomAssignments,
                    'zoom_url_snapshot' => $publication->class->zoom_url,
                    'zoom_password_snapshot' => $publication->class->zoom_password,
                    'published_at' => now(),
                    'published_by' => 1, // System
                    'auto_published' => true,
                ]);
                
                // إرسال الإشعارات (نفس الكود من Filament)
                // ...
                
                $this->info("Published companions for class: {$publication->class->name}");
            } catch (\Exception $e) {
                $this->error("Failed to publish: {$e->getMessage()}");
            }
        }
    }
}
```

#### **إضافة إلى Kernel.php**

```php
protected function schedule(Schedule $schedule)
{
    // نشر الرفيقات الساعة 6 مساءً كل يوم
    $schedule->command('companions:auto-publish')
        ->dailyAt('18:00')
        ->timezone('Africa/Cairo');
}
```

#### **الوقت المتوقع:** 30-40 دقيقة

---

## 📊 جدول زمني

| المرحلة | الوقت | الأولوية |
|---------|-------|----------|
| المرحلة 1: Filament Names | 20 دقيقة | 🔴 عالية جداً |
| المرحلة 2: CompanionsService | 30 دقيقة | 🔴 عالية |
| المرحلة 3: Controller Update | 20 دقيقة | 🔴 عالية |
| المرحلة 4: Flutter API | 15 دقيقة | 🟡 متوسطة |
| المرحلة 5: Flutter UI | 30 دقيقة | 🟡 متوسطة |
| المرحلة 6: Auto Publish | 40 دقيقة | 🟢 منخفضة |
| **المجموع** | **~3 ساعات** | |

---

## 📝 ترتيب التنفيذ المقترح

### **الخيار 1: التنفيذ الكامل**
1. المرحلة 1 (Filament) ← فوراً
2. المرحلة 2 (Service) ← مهم جداً
3. المرحلة 3 (Controller) ← يكمل Service
4. المرحلة 4 (Flutter API) ← لاستخدام التطبيق
5. المرحلة 5 (Flutter UI) ← يكمل التطبيق
6. المرحلة 6 (Auto Publish) ← اختياري

### **الخيار 2: التنفيذ السريع**
1. المرحلة 1 (Filament) فقط ← لحل مشكلة المعلمات ✅

---

## ✅ اختبارات مطلوبة

### **After كل مرحلة:**

#### **مرحلة 1:** 
- ✅ فتح Filament → نشرات الرفيقات
- ✅ توليد رفيقات
- ✅ التحقق من ظهور الأسماء

#### **مرحلتين 2+3:**
- ✅ `POST /api/v1/me/companions?date=2025-10-26`
- ✅ التحقق من Response structure
- ✅ التحقق من `success` field

#### **مرحلتين 4+5:**
- ✅ فتح التطبيق → الرفيقات
- ✅ التحقق من تحميل البيانات
- ✅ التحقق من عرض الأسماء

---

## 📦 الملفات المطلوب تعديلها

| الملف | التعديل | المرحلة |
|-------|---------|----------|
| `CompanionsPublicationResource.php` | عرض الأسماء | 1 |
| `CompanionsService.php` | ⭐ إنشاء ملف جديد | 2 |
| `CompanionsController.php` | استخدام Service | 3 |
| `api_service.dart` | توحيد Response | 4 |
| `companions_page.dart` | معالجة Response | 5 |
| `AutoPublishCompanions.php` | ⭐ إنشاء ملف جديد | 6 |
| `Kernel.php` | إضافة Schedule | 6 |

---

## 🎯 النتيجة النهائية المتوقعة

### **في Filament:**
- ✅ عرض أسماء الطالبات بدلاً من الأرقام
- ✅ بحث عن الطالبات بالاسم
- ✅ معاينة الغرف بأسماء واضحة

### **في API:**
- ✅ Response موحد مثل DailyTasks
- ✅ `success` + `message` + `data`
- ✅ Service منفصل للاختبار

### **في التطبيق:**
- ✅ عرض الرفيقات بالأسماء
- ✅ معلومات Zoom ورقم الغرفة
- ✅ معالجة الأخطاء بشكل صحيح

### **اختياري:**
- ✅ نشر تلقائي حسب الجدول الزمني
- ✅ إشعارات تلقائية للطالبات

---

## 🚀 هل تريد البدء الآن؟

**اختيار سريع:**
- **أ) إصلاح Filament فقط** (20 دقيقة) → حل مشكلة المعلمات
- **ب) إصلاح Filament + API** (مراحل 1+2+3 = 70 دقيقة) → حل كامل للخلفية
- **ج) إصلاح كامل** (مراحل 1-5 = 135 دقيقة) → كل شيء يعمل ✅
- **د) مع نشر تلقائي** (مراحل 1-6 = 175 دقيقة) → نظام كامل مع Cron ✅✅

**ما رأيك؟** 🤔

