# تحليل تدفق بيانات المهام اليومية

## نظرة عامة
يتم جلب المهام اليومية للطالب من خلال API endpoint وتمر بعدة طبقات من الكود والجداول.

## المسار الكامل:

### 1. في التطبيق (Flutter)
**الملف:** `lib/services/api_service.dart`
```dart
ApiService.getDailyTasks(String studentId, {String? date})
```
- **Endpoint:** `GET /api/v1/students/daily-tasks`
- **المعاملات:** 
  - `date` (اختياري): التاريخ المطلوب (افتراضي: اليوم)
  - `studentId` يُستخرج من token المصادقة

### 2. في Backend - Controller
**الملف:** `app/Http/Controllers/Api/V1/DailyTasksController.php`
```php
public function getDailyTasks(Request $request): JsonResponse
```
- يحصل على المستخدم المصادق من `Auth::user()`
- يتحقق من أن الدور `role === 'student'`
- يستدعي `DailyTasksService::getDailyTasks($studentId, $date)`

### 3. في Backend - Service
**الملف:** `app/Services/DailyTasksService.php`
```php
public function getDailyTasks(int $studentId, string $date, ?int $classId = null): array
```

#### الخطوات التفصيلية:

**الخطوة 1: الحصول على class_id للطالب**
```php
$student = User::findOrFail($studentId);
$classId = $student->class_id ?? null;
```
- **الجدول:** `users` (column: `class_id`)
- **العلاقة:** User → ClassModel

**الخطوة 2: جلب المهام الموكلة للفصل**
```php
$taskAssignments = ClassModel::findOrFail($classId)
    ->activeTaskAssignments()
    ->with('taskDefinition')
    ->get();
```
- **الجدول الأساسي:** `classes` (id = class_id)
- **العلاقة:** ClassModel → ClassTaskAssignment
- **الجدول:** `class_task_assignments`
  - شروط: `is_active = 1` (من activeTaskAssignments)
  - join مع: `daily_task_definitions` (عبر `daily_task_definition_id`)
- **الجدول المرتبط:** `daily_task_definitions`

**الخطوة 3: فحص السجلات الموجودة**
```php
$existingLog = DailyLog::where('student_id', $studentId)
    ->whereDate('log_date', $logDate)
    ->first();
```
- **الجدول:** `daily_logs`
- **الغرض:** التحقق من وجود سجل يومي سابق للطالب في هذا التاريخ

**الخطوة 4: بناء قائمة المهام**
```php
foreach ($taskAssignments as $assignment) {
    $definition = $assignment->taskDefinition;
    $existingItem = $existingLog?->items()
        ->where('task_definition_id', $definition->id)
        ->first();
    
    $tasks[] = [
        'task_id' => $definition->id,
        'task_key' => $definition->name,
        'task_name' => $definition->description,
        'task_type' => $definition->type,
        'task_location' => $definition->task_location,  // ⭐ هنا يتم جلب task_location
        'points_weight' => $definition->points_weight,
        'duration_minutes' => $definition->duration_minutes,
        'completed' => $existingItem ? $existingItem->status === 'completed' : false,
        'proof_type' => $existingItem?->proof_type ?? 'none',
        'notes' => $existingItem?->notes,
        'quantity' => $existingItem?->quantity,
        'assignment_order' => $assignment->order,
    ];
}
```

## الجداول المستخدمة:

### 1. `users`
- **العمود:** `class_id`
- **الغرض:** معرف الفصل الخاص بالطالب

### 2. `classes`
- **العمود:** `id`
- **الغرض:** بيانات الفصل

### 3. `class_task_assignments` ⭐ **الجدول الأساسي**
- **الأعمدة:**
  - `id`
  - `class_id` (FK → classes.id)
  - `daily_task_definition_id` (FK → daily_task_definitions.id)
  - `is_active` (boolean) - **يجب أن يكون = 1**
  - `order` (ترتيب المهمة في الفصل)
  - `created_at`, `updated_at`
- **الغرض:** ربط المهام بالفصول (Pivot Table)
- **الشرط:** `WHERE is_active = 1` (عبر `activeTaskAssignments()`)

### 4. `daily_task_definitions` ⭐ **جدول التعريفات**
- **الأعمدة:**
  - `id`
  - `name` → يُستخدم كـ `task_key` في الاستجابة
  - `description` → يُستخدم كـ `task_name` في الاستجابة
  - `type` → `task_type` (hifz, murajaah, tilawah, tajweed, tafseer, other)
  - `task_location` → **يُستخدم مباشرة** ⭐ (in_class, homework)
  - `points_weight`
  - `duration_minutes`
  - `is_active`
- **الغرض:** تعريفات المهام (القوالب)

### 5. `daily_logs` (اختياري)
- **الغرض:** السجلات اليومية المقدمة من الطالب
- **يُستخدم لـ:** التحقق من حالة الإكمال (`completed`)

### 6. `daily_log_items` (اختياري)
- **الغرض:** تفاصيل كل مهمة في السجل اليومي
- **يُستخدم لـ:** معلومات إضافية مثل `proof_type`, `notes`, `quantity`

## العلاقات (Relationships):

```
User (student)
  └──> class_id → ClassModel (class)
        └──> activeTaskAssignments() → ClassTaskAssignment[]
              └──> taskDefinition → DailyTaskDefinition
                    ├──> name → task_key
                    ├──> description → task_name
                    ├──> type → task_type
                    └──> task_location → task_location ⭐
```

## مثال على الاستجابة:

```json
{
  "date": "2025-10-31",
  "class_id": 2,
  "tasks": [
    {
      "task_id": 1,
      "task_key": "تثبيت النصاب (الثلاثيات)",
      "task_name": "سرد النصاب الجديد ثلاث مرات بالأوجه...",
      "task_type": "hifz",
      "task_location": "in_class",  // ⭐ من daily_task_definitions.task_location
      "points_weight": 2,
      "duration_minutes": 20,
      "completed": false,
      "proof_type": "none",
      "notes": null,
      "quantity": null,
      "assignment_order": 1
    }
  ]
}
```

## ملاحظات مهمة:

1. **task_location يأتي من `daily_task_definitions.task_location`** وليس من `class_task_assignments`
2. **يتم فلترة المهام:** فقط المهام التي `is_active = 1` في `class_task_assignments`
3. **الترتيب:** يتم الحفاظ على `order` من `class_task_assignments`
4. **الحالة (completed):** تُحدد من `daily_log_items.status` إذا كان هناك سجل موجود

## السؤال عن "غير محدد":

إذا كانت `task_location` تظهر كـ "غير محدد" في التطبيق، فهذا يعني:
- إما أن `daily_task_definitions.task_location` = `null` أو قيمة غير معروفة
- أو أن الكود في Flutter لا يتعامل مع القيم `'in_class'` و `'homework'` بشكل صحيح

**الحل:** تم إصلاحه في `daily_tasks_page.dart` بإضافة case `'homework'` في `_getLocationText()`.



