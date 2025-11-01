# خطة APIs الرفيقات للمعلمات في التطبيق

## 📋 الوضع الحالي والملاحظات

### **المشكلة المكتشفة:**
- الكود الحالي يتعامل مع `teacher` و `teacher_support` كأنهم "طلاب" ويحاولون جلب رفيقات! ❌
- الواقع: المعلمات هن من **تقوم بإعداد ونشر** الرفيقات للطالبات
- المعلمات **ليست** رفيقات - هن **مدرسات**

### **ملاحظات:**
1. **فهم خاطئ في getTeacherCompanions:** 
   - الحالي: يجلب "رفيقات المعلم" ❌
   - الصحيح: يجب أن يجلب "إدارة الرفيقات للفصل" ✅

2. **APIs المطلوبة للمعلمات:**
   - ✅ إعداد خطة الرفيقات
   - ✅ مراجعة الرفيقات المولدة
   - ✅ تعديل الرفيقات يدوياً
   - ✅ نشر الرفيقات للطالبات

---

## 🎯 الخطة المقترحة

### **المرحلة أ: تحديث/إصلاح getTeacherCompanions**

#### **المشكلة الحالية:**
```php
// خطأ: يحاول جلب "رفيقات المعلم" ❌
private function getTeacherCompanions(User $teacher, string $date): JsonResponse
{
    // يجلب رفيقات للطالب المتخيل أنه معلم
}

// في getMyCompanions:
elseif (in_array($user->role, ['teacher', 'teacher_support'])) {
    return $this->getTeacherCompanions($user, $date); // ❌ خطأ في المنطق
}
```

#### **الحل المطلوب:**

**خيار 1:** حذف getTeacherCompanions تماماً
```php
public function getMyCompanions(Request $request): JsonResponse
{
    $user = Auth::user();
    $date = $request->query('date', now()->format('Y-m-d'));

    if ($user->role === 'student') {
        return $this->getStudentCompanions($user, $date);
    } 
    // لا يوجد elif للمعلمين - المعلمات يستخدمون APIs إدارة الرفيقات
    else {
        return response()->json(['message' => 'غير مصرح لك بهذا الإجراء'], 403);
    }
}
```

**خيار 2:** تغيير getTeacherCompanions إلى "عرض الرفيقات للفصل" (للمراقبة)
```php
// يجلب كل مجموعات الفصل لمراقبة المعلمة
private function getTeacherClassCompanions(User $teacher, string $date): JsonResponse
{
    // يظهر جميع المجموعات والغرف
}
```

---

### **المرحلة ب: إضافة APIs إدارة الرفيقات للمعلمات**

#### **الـ API المطلوب:**

```php
// 1. عرض الرفيقات المحفوظة للفصل (مراجعة)
GET /api/v1/teacher/companions/class/{classId}?date=2025-10-26
Response:
{
    "success": true,
    "publication": {
        "id": 123,
        "target_date": "2025-10-26",
        "grouping": "pairs",
        "algorithm": "rotation",
        "published_at": null,
        "pairings": [...],  // Groups by IDs
        "pairings_display": [...], // Groups by Names
    },
    "class": {
        "id": 1,
        "name": "فصل أ"
    }
}

// 2. توليد الرفيقات
POST /api/v1/teacher/companions/generate
Body:
{
    "class_id": 1,
    "target_date": "2025-10-26",
    "grouping": "pairs",
    "algorithm": "random",
    "attendance_source": "all"
}

// 3. تعديل الرفيقات يدوياً
PUT /api/v1/teacher/companions/{publicationId}/lock
Body:
{
    "locked_pairs": [
        [student_id1, student_id2],
        [student_id3, student_id4]
    ]
}

// 4. نشر الرفيقات
POST /api/v1/teacher/companions/{publicationId}/publish
Response:
{
    "success": true,
    "message": "تم النشر بنجاح",
    "notifications_sent": 25
}
```

---

## 🔧 التعديلات المطلوبة

### **1. تعديل CompanionsController**

#### **أ) حذف getTeacherCompanions من getMyCompanions**
```php
public function getMyCompanions(Request $request): JsonResponse
{
    $user = Auth::user();
    $date = $request->query('date', now()->format('Y-m-d'));

    // ✅ فقط للطلاب
    if ($user->role === 'student') {
        return $this->getStudentCompanions($user, $date);
    } 
    
    // ❌ حذف سطر المعلمين
    return response()->json(['message' => 'غير مصرح لك بهذا الإجراء'], 403);
}
```

#### **ب) حذف getTeacherCompanions method**
```php
// ❌ حذف هذا الـ method كاملاً
// private function getTeacherCompanions(...)
```

#### **ج) إضافة APIs جديدة للمعلمات**
```php
/**
 * Get companions publication for teacher's class
 */
public function getClassCompanions(Request $request, $classId): JsonResponse
{
    $user = Auth::user();
    
    // ✅ صلاحية المعلمات فقط
    if (!in_array($user->role, ['teacher', 'teacher_support', 'admin'])) {
        return response()->json(['message' => 'غير مصرح لك بهذا الإجراء'], 403);
    }

    $date = $request->query('date', now()->format('Y-m-d'));
    
    $publication = CompanionsPublication::where('class_id', $classId)
        ->where('target_date', $date)
        ->first();

    if (!$publication) {
        return response()->json([
            'success' => false,
            'message' => 'لا توجد خطة رفيقات للتاريخ المحدد',
            'publication' => null,
        ]);
    }

    // ✅ جلب أسماء الطالبات
    $studentIds = collect($publication->pairings)->flatten()->unique()->toArray();
    $students = User::whereIn('id', $studentIds)->pluck('name', 'id')->toArray();

    // ✅ إضافة pairings_display بالأسماء
    $pairingsDisplay = [];
    foreach ($publication->pairings as $group) {
        $names = array_map(function ($id) use ($students) {
            return [
                'id' => $id,
                'name' => $students[$id] ?? "ID: $id"
            ];
        }, $group);
        $pairingsDisplay[] = $names;
    }

    return response()->json([
        'success' => true,
        'message' => 'تم جلب خطة الرفيقات',
        'publication' => [
            'id' => $publication->id,
            'target_date' => $publication->target_date,
            'grouping' => $publication->grouping,
            'algorithm' => $publication->algorithm,
            'published_at' => $publication->published_at,
            'pairings' => $publication->pairings, // IDs
            'pairings_display' => $pairingsDisplay, // Names
        ],
        'class' => $publication->class,
    ]);
}
```

---

### **2. إضافة Routes الجديدة**

```php
// routes/api.php

// ✅ للطلاب فقط
Route::get('/me/companions', [CompanionsController::class, 'getMyCompanions']);

// ✅ للمعلمات - إدارة الرفيقات
Route::prefix('teacher')->group(function () {
    // عرض خطة الرفيقات
    Route::get('/companions/class/{classId}', [CompanionsController::class, 'getClassCompanions']);
    
    // توليد الرفيقات
    Route::post('/companions/generate', [CompanionsController::class, 'generate']);
    
    // تثبيت الرفيقات
    Route::put('/companions/{publicationId}/lock', [CompanionsController::class, 'lock']);
    
    // نشر الرفيقات
    Route::post('/companions/{publicationId}/publish', [CompanionsController::class, 'publish']);
});
```

---

### **3. تحديث Flutter - صفحة المعلمات**

#### **ملف جديد:** `teacher_companions_management_page.dart`

```dart
class TeacherCompanionsManagementPage extends StatefulWidget {
  final int classId;
  
  @override
  _loadCompanions() async {
    final response = await ApiService.getTeacherClassCompanions(classId);
    
    if (response['success']) {
      setState(() {
        _publication = response['publication'];
        _pairingsDisplay = response['pairings_display']; // بالأسماء
      });
    }
  }
  
  // ✅ عرض الرفيقات
  Widget _buildCompanionsList() {
    return ListView.builder(
      itemBuilder: (context, index) {
        final group = _pairingsDisplay[index];
        return Card(
          child: ExpansionTile(
            title: Text('المجموعة ${index + 1}'),
            children: group.map((student) {
              return ListTile(
                leading: Icon(Icons.person),
                title: Text(student['name']),
              );
            }).toList(),
          ),
        );
      },
    );
  }
  
  // ✅ زر توليد جديد
  Future<void> _generateCompanions() async {
    await ApiService.generateCompanions(
      classId: widget.classId,
      date: _selectedDate,
      grouping: 'pairs',
      algorithm: 'random',
    );
  }
  
  // ✅ زر تعديل يدوي
  Future<void> _lockPairs(List<List<int>> lockedPairs) async {
    await ApiService.lockCompanionsPairs(
      publicationId: _publication['id'],
      lockedPairs: lockedPairs,
    );
  }
  
  // ✅ زر نشر
  Future<void> _publishCompanions() async {
    final result = await ApiService.publishCompanions(_publication['id']);
    
    if (result['success']) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('تم النشر وإرسال ${result['notifications_sent']} إشعار')),
      );
    }
  }
}
```

---

### **4. تحديث Flutter API Service**

```dart
// ✅ جلب خطة الرفيقات للفصل
static Future<Map<String, dynamic>> getTeacherClassCompanions(int classId, {String? date}) async {
  final response = await _dio.get('/teacher/companions/class/$classId', 
    queryParameters: {'date': date ?? DateTime.now().toIso8601String().split('T')[0]}
  );
  return response.data;
}

// ✅ توليد الرفيقات
static Future<Map<String, dynamic>> generateCompanions({
  required int classId,
  required String date,
  required String grouping,
  required String algorithm,
}) async {
  final response = await _dio.post('/teacher/companions/generate', data: {
    'class_id': classId,
    'target_date': date,
    'grouping': grouping,
    'algorithm': algorithm,
    'attendance_source': 'all',
  });
  return response.data;
}

// ✅ نشر الرفيقات
static Future<Map<String, dynamic>> publishCompanions(int publicationId) async {
  final response = await _dio.post('/teacher/companions/$publicationId/publish');
  return response.data;
}
```

---

## ✅ الخلاصة

### **ما يجب تنفيذه:**

1. ❌ **حذف getTeacherCompanions** من getMyCompanions
2. ❌ **حذف getTeacherCompanions method**
3. ✅ **إضافة getClassCompanions** للمعلمات
4. ✅ **إضافة Routes** للمعلمات
5. ✅ **تحديث Flutter** - صفحة إدارة رفيقات للمعلمات
6. ✅ **تحديث Flutter API Service** - methods للمعلمات

### **النقاط المهمة:**
- المعلمات **ليست** رفيقات - هن مدرسات
- المعلمات **تقوم بإعداد** الرفيقات للطالبات
- الطالبات **يحصلون على** الرفيقات
- المعلمات **تدير وتنشر** الرفيقات

هل أبدأ في التعديلات الآن؟

