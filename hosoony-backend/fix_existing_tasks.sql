-- فحص المشكلة الحقيقية: المهام موجودة لكن لا تظهر في الفصول
-- استخدم هذا السكريبت في phpMyAdmin

-- 1. فحص المهام الموجودة (تأكيد)
SELECT '=== المهام الموجودة ===' as status;
SELECT id, name, type, is_active FROM daily_task_definitions WHERE is_active = 1;

-- 2. فحص الفصول الموجودة
SELECT '=== الفصول الموجودة ===' as status;
SELECT id, name, status FROM classes WHERE status = 'active';

-- 3. فحص المهام الموكلة للفصول (هذا هو المهم!)
SELECT '=== المهام الموكلة للفصول ===' as status;
SELECT 
    cta.id,
    c.name as class_name, 
    d.name as task_name, 
    cta.is_active,
    cta.order
FROM class_task_assignments cta
JOIN classes c ON cta.class_id = c.id
JOIN daily_task_definitions d ON cta.daily_task_definition_id = d.id
ORDER BY c.name, cta.order;

-- 4. إذا كانت النتيجة فارغة، فهذا هو السبب!
-- المهام موجودة لكنها غير مربوطة بالفصول

-- 5. حل المشكلة: ربط المهام الموجودة بالفصول
SELECT '=== ربط المهام بالفصول ===' as status;

-- ربط جميع المهام النشطة بجميع الفصول النشطة
INSERT INTO class_task_assignments (class_id, daily_task_definition_id, is_active, `order`, created_at, updated_at)
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

-- 6. فحص النتائج بعد الربط
SELECT '=== النتائج بعد الربط ===' as status;
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
















