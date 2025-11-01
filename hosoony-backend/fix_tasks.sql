-- سكريبت SQL لإصلاح بيانات المهام
-- استخدم هذا السكريبت في phpMyAdmin أو MySQL

-- 1. فحص البيانات الموجودة
SELECT '=== فحص الفصول ===' as status;
SELECT id, name, status FROM classes LIMIT 5;

SELECT '=== فحص تعريفات المهام ===' as status;
SELECT id, name, type, is_active FROM daily_task_definitions LIMIT 5;

SELECT '=== فحص المهام الموكلة ===' as status;
SELECT cta.id, c.name as class_name, d.name as task_name, cta.is_active 
FROM class_task_assignments cta
JOIN classes c ON cta.class_id = c.id
JOIN daily_task_definitions d ON cta.daily_task_definition_id = d.id
LIMIT 5;

-- 2. إنشاء تعريفات مهام إذا لم تكن موجودة
INSERT IGNORE INTO daily_task_definitions (name, description, type, task_location, points_weight, duration_minutes, is_active, created_at, updated_at) VALUES
('حفظ سورة البقرة', 'حفظ آيات من سورة البقرة', 'hifz', 'homework', 5, 30, 1, NOW(), NOW()),
('مراجعة المحفوظ', 'مراجعة السور المحفوظة سابقاً', 'murajaah', 'in_class', 3, 20, 1, NOW(), NOW()),
('تلاوة القرآن', 'تلاوة القرآن الكريم', 'tilawah', 'in_class', 2, 15, 1, NOW(), NOW()),
('تعلم التجويد', 'تعلم أحكام التجويد', 'tajweed', 'in_class', 4, 25, 1, NOW(), NOW()),
('تفسير القرآن', 'تفسير آيات القرآن', 'tafseer', 'in_class', 6, 40, 1, NOW(), NOW());

-- 3. ربط المهام بالفصول النشطة
INSERT IGNORE INTO class_task_assignments (class_id, daily_task_definition_id, is_active, `order`, created_at, updated_at)
SELECT 
    c.id as class_id,
    d.id as daily_task_definition_id,
    1 as is_active,
    ROW_NUMBER() OVER (PARTITION BY c.id ORDER BY d.id) as `order`,
    NOW() as created_at,
    NOW() as updated_at
FROM classes c
CROSS JOIN daily_task_definitions d
WHERE c.status = 'active' 
AND d.is_active = 1
AND NOT EXISTS (
    SELECT 1 FROM class_task_assignments cta 
    WHERE cta.class_id = c.id 
    AND cta.daily_task_definition_id = d.id
);

-- 4. فحص النتائج
SELECT '=== النتائج بعد الإصلاح ===' as status;

SELECT 'الفصول:' as type;
SELECT id, name, status FROM classes WHERE status = 'active';

SELECT 'تعريفات المهام:' as type;
SELECT id, name, type, is_active FROM daily_task_definitions WHERE is_active = 1;

SELECT 'المهام الموكلة:' as type;
SELECT 
    cta.id,
    c.name as class_name, 
    d.name as task_name, 
    d.type as task_type,
    cta.is_active,
    cta.order
FROM class_task_assignments cta
JOIN classes c ON cta.class_id = c.id
JOIN daily_task_definitions d ON cta.daily_task_definition_id = d.id
ORDER BY c.name, cta.order;

-- 5. إحصائيات
SELECT '=== الإحصائيات ===' as status;
SELECT 
    'إجمالي الفصول' as metric,
    COUNT(*) as count
FROM classes
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
















