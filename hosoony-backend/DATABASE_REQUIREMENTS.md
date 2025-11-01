# 📊 البيانات المطلوبة في قاعدة البيانات لإظهار المهام للطالبة

## 🔍 الجداول والعلاقات المطلوبة:

### 1. جدول `users` (المستخدمين)
```sql
-- يجب أن يحتوي على طلاب مرتبطين بفصول
SELECT id, name, role, class_id 
FROM users 
WHERE role = 'student' AND class_id IS NOT NULL;
```

**البيانات المطلوبة:**
- `role = 'student'` - أن يكون المستخدم طالب
- `class_id IS NOT NULL` - أن يكون مرتبط بفصل

### 2. جدول `classes` (الفصول)
```sql
-- يجب أن يحتوي على فصول نشطة
SELECT id, name, status 
FROM classes 
WHERE status = 'active';
```

**البيانات المطلوبة:**
- `status = 'active'` - أن يكون الفصل نشط

### 3. جدول `daily_task_definitions` (تعريفات المهام اليومية)
```sql
-- يجب أن يحتوي على تعريفات مهام نشطة
SELECT id, name, type, points_weight, is_active 
FROM daily_task_definitions 
WHERE is_active = 1;
```

**البيانات المطلوبة:**
- `is_active = 1` - أن تكون المهمة نشطة
- `name` - اسم المهمة
- `type` - نوع المهمة (hifz, murajaah, tilawah, etc.)

### 4. جدول `class_task_assignments` (ربط المهام بالفصول) ⭐ **الأهم**
```sql
-- يجب أن يحتوي على ربط بين المهام والفصول
SELECT cta.id, c.name as class_name, d.name as task_name, cta.is_active
FROM class_task_assignments cta
JOIN classes c ON cta.class_id = c.id
JOIN daily_task_definitions d ON cta.daily_task_definition_id = d.id
WHERE cta.is_active = 1;
```

**البيانات المطلوبة:**
- `class_id` - معرف الفصل
- `daily_task_definition_id` - معرف تعريف المهمة
- `is_active = 1` - أن يكون الربط نشط

## 🔗 العلاقات المطلوبة:

### العلاقة الأساسية:
```
الطالب (users.class_id) → الفصل (classes.id) → المهام الموكلة (class_task_assignments.class_id) → تعريفات المهام (daily_task_definitions.id)
```

### الاستعلام الذي يستخدمه النظام:
```php
// في PwaController::studentDashboard()
$taskAssignments = ClassModel::find($user->class_id)
    ->activeTaskAssignments()  // WHERE is_active = 1
    ->with('taskDefinition')
    ->get();
```

## 📋 البيانات المطلوبة بالتفصيل:

### 1. بيانات الطلاب:
```sql
-- مثال على البيانات المطلوبة
INSERT INTO users (name, email, role, class_id) VALUES
('فاطمة الطالبة', 'fatima@example.com', 'student', 1),
('عبدالله الطالب', 'abdullah@example.com', 'student', 2);
```

### 2. بيانات الفصول:
```sql
-- مثال على البيانات المطلوبة
INSERT INTO classes (name, status) VALUES
('حلقة الإناث - الثلاثاء', 'active'),
('حلقة الذكور - الأحد', 'active');
```

### 3. بيانات تعريفات المهام:
```sql
-- مثال على البيانات المطلوبة
INSERT INTO daily_task_definitions (name, type, points_weight, is_active) VALUES
('حفظ آيات جديدة', 'hifz', 10, 1),
('مراجعة المحفوظ', 'murajaah', 8, 1),
('تلاوة القرآن', 'tilawah', 5, 1),
('تعلم التجويد', 'tajweed', 6, 1),
('تفسير القرآن', 'tafseer', 7, 1);
```

### 4. بيانات ربط المهام بالفصول: ⭐ **الأهم**
```sql
-- مثال على البيانات المطلوبة
INSERT INTO class_task_assignments (class_id, daily_task_definition_id, is_active, `order`) VALUES
-- للفصل الأول
(1, 1, 1, 1),  -- حفظ آيات جديدة
(1, 2, 1, 2),  -- مراجعة المحفوظ
(1, 3, 1, 3),  -- تلاوة القرآن
(1, 4, 1, 4),  -- تعلم التجويد
(1, 5, 1, 5),  -- تفسير القرآن
-- للفصل الثاني
(2, 1, 1, 1),  -- حفظ آيات جديدة
(2, 2, 1, 2),  -- مراجعة المحفوظ
(2, 3, 1, 3),  -- تلاوة القرآن
(2, 4, 1, 4),  -- تعلم التجويد
(2, 5, 1, 5);  -- تفسير القرآن
```

## 🔍 استعلامات الفحص:

### فحص الطلاب:
```sql
SELECT COUNT(*) as total_students FROM users WHERE role = 'student';
SELECT COUNT(*) as students_with_class FROM users WHERE role = 'student' AND class_id IS NOT NULL;
```

### فحص الفصول:
```sql
SELECT COUNT(*) as total_classes FROM classes;
SELECT COUNT(*) as active_classes FROM classes WHERE status = 'active';
```

### فحص تعريفات المهام:
```sql
SELECT COUNT(*) as total_task_definitions FROM daily_task_definitions;
SELECT COUNT(*) as active_task_definitions FROM daily_task_definitions WHERE is_active = 1;
```

### فحص المهام الموكلة: ⭐ **الأهم**
```sql
SELECT COUNT(*) as total_assignments FROM class_task_assignments;
SELECT COUNT(*) as active_assignments FROM class_task_assignments WHERE is_active = 1;
```

### فحص شامل للطالب:
```sql
SELECT 
    u.name as student_name,
    c.name as class_name,
    COUNT(cta.id) as available_tasks
FROM users u
LEFT JOIN classes c ON u.class_id = c.id
LEFT JOIN class_task_assignments cta ON c.id = cta.class_id AND cta.is_active = 1
WHERE u.role = 'student' AND u.class_id IS NOT NULL
GROUP BY u.id, u.name, c.name;
```

## ❌ المشاكل الشائعة:

### 1. جدول `class_task_assignments` فارغ:
```sql
-- المشكلة: لا توجد مهام مربوطة بالفصول
SELECT COUNT(*) FROM class_task_assignments;  -- النتيجة: 0
```

**الحل:**
```sql
-- ربط المهام بالفصول النشطة
INSERT INTO class_task_assignments (class_id, daily_task_definition_id, is_active, `order`)
SELECT 
    c.id as class_id,
    d.id as daily_task_definition_id,
    1 as is_active,
    ROW_NUMBER() OVER (PARTITION BY c.id ORDER BY d.id) as `order`
FROM classes c
CROSS JOIN daily_task_definitions d
WHERE c.status = 'active' AND d.is_active = 1;
```

### 2. لا توجد تعريفات مهام:
```sql
-- المشكلة: لا توجد تعريفات مهام
SELECT COUNT(*) FROM daily_task_definitions WHERE is_active = 1;  -- النتيجة: 0
```

**الحل:**
```sql
-- إنشاء تعريفات المهام الأساسية
INSERT INTO daily_task_definitions (name, type, points_weight, is_active) VALUES
('حفظ آيات جديدة', 'hifz', 10, 1),
('مراجعة المحفوظ', 'murajaah', 8, 1),
('تلاوة القرآن', 'tilawah', 5, 1),
('تعلم التجويد', 'tajweed', 6, 1),
('تفسير القرآن', 'tafseer', 7, 1);
```

### 3. الطلاب غير مرتبطين بفصول:
```sql
-- المشكلة: الطلاب لا يملكون class_id
SELECT COUNT(*) FROM users WHERE role = 'student' AND class_id IS NULL;  -- النتيجة: > 0
```

**الحل:**
```sql
-- ربط الطلاب بالفصول
UPDATE users SET class_id = 1 WHERE role = 'student' AND class_id IS NULL;
```

## ✅ سكريبت الإصلاح الشامل:

```sql
-- 1. إنشاء تعريفات المهام
INSERT IGNORE INTO daily_task_definitions (name, type, points_weight, is_active) VALUES
('حفظ آيات جديدة', 'hifz', 10, 1),
('مراجعة المحفوظ', 'murajaah', 8, 1),
('تلاوة القرآن', 'tilawah', 5, 1),
('تعلم التجويد', 'tajweed', 6, 1),
('تفسير القرآن', 'tafseer', 7, 1);

-- 2. ربط المهام بالفصول النشطة
INSERT IGNORE INTO class_task_assignments (class_id, daily_task_definition_id, is_active, `order`)
SELECT 
    c.id as class_id,
    d.id as daily_task_definition_id,
    1 as is_active,
    ROW_NUMBER() OVER (PARTITION BY c.id ORDER BY d.id) as `order`
FROM classes c
CROSS JOIN daily_task_definitions d
WHERE c.status = 'active' AND d.is_active = 1;

-- 3. فحص النتائج
SELECT 
    'الطلاب المرتبطين بفصول' as metric,
    COUNT(*) as count
FROM users WHERE role = 'student' AND class_id IS NOT NULL
UNION ALL
SELECT 
    'الفصول النشطة' as metric,
    COUNT(*) as count
FROM classes WHERE status = 'active'
UNION ALL
SELECT 
    'تعريفات المهام النشطة' as metric,
    COUNT(*) as count
FROM daily_task_definitions WHERE is_active = 1
UNION ALL
SELECT 
    'المهام الموكلة' as metric,
    COUNT(*) as count
FROM class_task_assignments WHERE is_active = 1;
```

---

## 🎯 الخلاصة:

**المشكلة الأساسية:** جدول `class_task_assignments` فارغ!

**الحل:** ربط المهام الموجودة بالفصول النشطة.

**النتيجة:** الطلاب سيرون المهام الموكلة لفصولهم.












