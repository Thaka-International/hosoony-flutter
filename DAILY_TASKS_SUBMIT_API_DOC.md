# API تسليم المهام اليومية - Documentation

## 📍 API Endpoint

**URL:** `POST /api/v1/students/daily-tasks/submit`

**Route:** في `routes/api.php`
```php
Route::post('/students/daily-tasks/submit', [DailyTasksController::class, 'submitDailyLog']);
```

---

## 📦 البيانات المُرسلة

### Request Body:
```json
{
  "log_date": "2025-10-26",
  "tasks": [
    {
      "id": 123,
      "status": "completed",
      "notes": "تم إكمال المهمة بنجاح",
      "quantity": 1,
      "duration_minutes": 15,
      "proof_type": "none"
    }
  ]
}
```

### الحقول المطلوبة:

| الحقل | النوع | حالة | الوصف |
|-------|-------|------|-------|
| `log_date` | string | required | تاريخ المهمة بصيغة `Y-m-d` |
| `tasks` | array | required | مصفوفة المهام المكتملة |

### الحقول داخل `tasks[].*`:

| الحقل | النوع | حالة | القيم المسموحة | الوصف |
|-------|-------|------|----------------|-------|
| `id` | integer | required | - | رقم `DailyLogItem` الموجود |
| `status` | string | required | `pending`, `in_progress`, `completed`, `skipped` | حالة المهمة |
| `notes` | string | optional | - | ملاحظات إضافية |
| `quantity` | integer | optional | >= 0 | الكمية المنفذة |
| `duration_minutes` | integer | optional | >= 0 | الوقت المستغرق بالدقائق |
| `proof_type` | string | optional | `none`, `note`, `audio`, `video` | نوع الإثبات |

---

## 🔍 آلية العمل

### 1. التحقق من الهوية:
```php
$user = Auth::user();
if (!$user || $user->role !== 'student') {
    return 401;
}
```

### 2. إنشاء/استرجاع DailyLog:
```php
$dailyLog = DailyLog::firstOrCreate(
    ['student_id' => $user->id, 'log_date' => $logDate],
    ['status' => 'pending']
);
```

### 3. تحديث DailyLogItems:
```php
foreach ($tasks as $taskData) {
    $logItem = DailyLogItem::find($taskData['id']);
    
    if ($logItem && $logItem->dailyLog->student_id === $user->id) {
        $logItem->update([
            'status' => $taskData['status'],
            'notes' => $taskData['notes'] ?? $logItem->notes,
            'quantity' => $taskData['quantity'] ?? $logItem->quantity,
            'duration_minutes' => $taskData['duration_minutes'] ?? $logItem->duration_minutes,
            'proof_type' => $taskData['proof_type'] ?? $logItem->proof_type
        ]);
    }
}
```

### 4. تحديث حالة DailyLog:
```php
$allCompleted = $dailyLog->dailyLogItems->every(function ($item) {
    return $item->status === 'completed';
});

if ($allCompleted) {
    $dailyLog->update(['status' => 'submitted']);
}
```

---

## 📤 Response

### نجاح (200):
```json
{
  "success": true,
  "message": "Daily log submitted successfully",
  "daily_log_id": 123,
  "updated_tasks_count": 5
}
```

### خطأ (422 - Validation):
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "log_date": ["The log date field is required."],
    "tasks.0.id": ["The tasks.0.id field is required."]
  }
}
```

### خطأ (401 - Unauthorized):
```json
{
  "success": false,
  "message": "Unauthorized access"
}
```

### خطأ (500 - Server Error):
```json
{
  "success": false,
  "message": "An error occurred while submitting daily log",
  "error": "Error message (in debug mode only)"
}
```

---

## ⚠️ المشاكل الحالية

### 1. **API يبحث عن DailyLogItem موجود**
```php
$logItem = DailyLogItem::find($taskData['id']);
if ($logItem && $logItem->dailyLog->student_id === $user->id) {
    // update existing item
}
```

**المشكلة:** لا يوجد `DailyLogItem` عند البداية!  
**الحل:** نحتاج لإنشاء الـ items أولاً

### 2. **نظامان مختلفان**

#### أ) API الحالي (Controller):
- يبحث عن `DailyLogItem` موجود
- يستخدم `id` من الداتا الموجودة
- يعطي خطأ 500 إذا لم يجد الـ item

#### ب) Service (Web):
```php
// في DailyTasksService::submitDailyLog()
foreach ($items as $item) {
    $taskDefinition = DailyTaskDefinition::where('name', $item['task_key'])->first();
    
    if ($taskDefinition) {
        DailyLogItem::updateOrCreate(
            [
                'daily_log_id' => $dailyLog->id,
                'task_definition_id' => $taskDefinition->id,
            ],
            [
                'status' => $item['completed'] ? 'completed' : 'pending',
                // ...
            ]
        );
    }
}
```

---

## 💡 الحل الصحيح

### الطريقة الصحيحة (مثل Web):
```php
$dailyLog = DailyLog::firstOrCreate(...);

foreach ($tasks as $taskData) {
    $taskDefinition = DailyTaskDefinition::find($taskData['task_definition_id']);
    
    if ($taskDefinition) {
        DailyLogItem::updateOrCreate(
            [
                'daily_log_id' => $dailyLog->id,
                'task_definition_id' => $taskDefinition->id,
            ],
            [
                'status' => $taskData['status'],
                'proof_type' => $taskData['proof_type'] ?? 'none',
                'notes' => $taskData['notes'],
                'quantity' => $taskData['quantity'] ?? 1,
                'duration_minutes' => $taskData['duration_minutes'],
            ]
        );
    }
}
```

### البيانات الصحيحة للإرسال:
```json
{
  "log_date": "2025-10-26",
  "tasks": [
    {
      "task_definition_id": 1,
      "task_key": "hifz_surah_al_fatiha",
      "status": "completed",
      "duration_minutes": 15,
      "quantity": 1,
      "proof_type": "none",
      "notes": null
    }
  ]
}
```

---

## 🎯 توصيات

1. **تحديث DailyTasksController::submitDailyLog** لاستخدام نفس منطق `DailyTasksService`
2. **إضافة منطق النقاط** من `GamificationPoint`
3. **إضافة calculateFinishOrder** لتحديد ترتيب الإنجاز
4. **معالجة errors بشكل أفضل**


