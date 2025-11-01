# 📁 قائمة الملفات المطلوب تحديثها في cPanel

## 🎯 الملفات المحدثة

### 1️⃣ TaskAssignmentsRelationManager.php
**📂 المسار المحلي:**
```
/Users/ibraheem.designer/Documents/hosoony2/hosoony-backend/app/Filament/Resources/ClassResource/RelationManagers/TaskAssignmentsRelationManager.php
```

**🌐 المسار في cPanel:**
```
/public_html/app/Filament/Resources/ClassResource/RelationManagers/TaskAssignmentsRelationManager.php
```

**📝 التغييرات:**
- إضافة أزرار واضحة لإضافة المهام
- تحسين رسائل الحالة الفارغة
- إضافة أيقونات وألوان للأزرار

---

### 2️⃣ DailyTasksService.php
**📂 المسار المحلي:**
```
/Users/ibraheem.designer/Documents/hosoony2/hosoony-backend/app/Services/DailyTasksService.php
```

**🌐 المسار في cPanel:**
```
/public_html/app/Services/DailyTasksService.php
```

**📝 التغييرات:**
- ربط المهام بالفصول بدلاً من جلب جميع المهام
- إضافة معلومات إضافية للمهام
- معالجة حالة الطلاب غير المرتبطين بفصول

---

### 3️⃣ api_service.dart
**📂 المسار المحلي:**
```
/Users/ibraheem.designer/Documents/hosoony2/hosoony_flutter/lib/services/api_service.dart
```

**🌐 المسار في cPanel:**
```
/public_html/hosoony_flutter/lib/services/api_service.dart
```

**📝 التغييرات:**
- إضافة التاريخ المطلوب في طلبات API
- تحديث معالجة استجابة API

---

## 🚀 خطوات النشر السريع

### الطريقة الأولى: File Manager
1. **افتح File Manager في cPanel**
2. **انتقل إلى المجلدات التالية:**
   - `/public_html/app/Filament/Resources/ClassResource/RelationManagers/`
   - `/public_html/app/Services/`
   - `/public_html/hosoony_flutter/lib/services/`
3. **ارفع الملفات الثلاثة المحدثة**

### الطريقة الثانية: Terminal
```bash
# انتقل إلى مجلد المشروع
cd /public_html

# مسح الكاش
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

---

## ✅ قائمة التحقق

- [ ] رفع `TaskAssignmentsRelationManager.php`
- [ ] رفع `DailyTasksService.php`  
- [ ] رفع `api_service.dart`
- [ ] مسح الكاش
- [ ] اختبار الوظائف

---

**📅 تاريخ التحديث:** $(date)
**🔧 الحالة:** جاهز للنشر
















