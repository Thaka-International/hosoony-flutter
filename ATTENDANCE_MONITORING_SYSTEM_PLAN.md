# خطة نظام مراقبة الحضور والغياب للطالبات

## 📋 نظرة عامة

### **الهدف:**
نظام شامل لمراقبة حضور الطالبات وتتبع التغيب والتأخير مع آلية للتنبيهات والتعقيدات.

---

## 🎯 المتطلبات التفصيلية

### **1. تسجيل الحضور**

#### **للطالبة:**
- **الموقع:** زر في صفحة المهام اليومية
- **العمل:** تسجيل الحضور مع الوقت
- **المقارنة:** مقارنة وقت الحضور بوقت بدء الحلقة من جدول الفصل

#### **المنطق:**
```
وقت الحضور - وقت بدء الحلقة = التأخير
if (التأخير > 10 دقائق) {
    → إضافة تنبيه تأخير
    → رقمنة التنبيهات في الشهر
    if (التنبيهات في الشهر >= 6) {
        → تعطيل الحساب
        → إشعار للمعلمة
        → خصم 50 نقطة
    }
}
```

---

### **2. التنبيهات**

#### **النوع:**
- **تأخير أكثر من 10 دقائق**
- **مرقم خلال الشهر الواحد**
- **آلية التعقيد:**
  - 6 تنبيهات = تعطيل الحساب تلقائياً
  - إشعار للمعلمة
  - خصم 50 نقطة

#### **السجل:**
```json
{
  "date": "2025-10-26",
  "type": "late_attendance",
  "delay_minutes": 15,
  "session_start_time": "14:00",
  "attendance_time": "14:15",
  "month_count": 3, // التنبيه رقم 3 في الشهر
  "status": "active" // أو "account_suspended"
}
```

---

### **3. مراقبة الغياب**

#### **التعريف:**
- عدم تسجيل الحضور خلال **ساعة من بدء الحلقة**
- **بدون عذر مسبق**

#### **المنطق:**
```
وقت بدء الحلقة + ساعة = الوقت النهائي للحضور
if (لم يتم تسجيل الحضور قبل الوقت النهائي) {
    if (لا يوجد عذر مسبق) {
        → تسجيل غياب
        → إضافة للتقارير
    }
}
```

---

### **4. نظام الأعذار**

#### **للطالبة:**
- يمكن إضافة عذر مسبق قبل تاريخ الحلقة
- العذر يظهر للمعلمة للقبول/الرفض

#### **للطلمة:**
- تشاهد قائمة الأعذار الواردة
- تختار قبول/رفض العذر
- **إذا قبلت:** يتم حذف سجل الغياب
- **إذا رفضت:** يبقى سجل الغياب

#### **السجل:**
```json
{
  "id": 123,
  "student_id": 456,
  "date": "2025-10-26",
  "session_id": 789,
  "excuse_text": "مرض",
  "excuse_type": "medical",
  "submitted_at": "2025-10-25",
  "status": "pending", // pending, approved, rejected
  "reviewed_by": null,
  "reviewed_at": null
}
```

---

### **5. تقارير الحضور**

#### **للمعلمة:**
```json
{
  "date_range": "2025-10-01 to 2025-10-31",
  "students": [
    {
      "id": 1,
      "name": "فاطمة",
      "attendance_rate": 85,
      "total_sessions": 20,
      "attended": 17,
      "absent": 3,
      "late": 2,
      "excused_absence": 0
    }
  ],
  "summary": {
    "total_students": 25,
    "average_attendance": 88.5
  }
}
```

#### **للطالبة:**
- عرض سجل الحضور الشخصي
- عدد أيام الحضور
- عدد أيام الغياب
- عدد التنبيهات
- معالجة الأعذار

---

### **6. آلية التعطيل**

#### **التعطيل بسبب التأخير:**
```
6 تنبيهات تأخير في الشهر → تعطيل الحساب
```

#### **التعطيل بسبب الغياب:**
```
3 أيام غياب بدون عذر في الشهر → تعطيل الحساب
```

#### **العواقب:**
- تعطيل الحساب (status = inactive)
- إشعار للمعلمة
- خصم 50 نقطة من نقاط الطالبة
- تسجيل في سجل الطالبة

#### **إعادة التفعيل:**
- تتم يدوياً من قبل المعلمة
- زر في Filament: "إعادة تفعيل الحساب"

---

### **7. الدرجات والنقاط**

#### **نظام النقاط:**
- **خصم عند تعطيل:** 50 نقطة
- **سبب الخصم:** التأخير المتكرر أو الغياب المتكرر

#### **درجات الغياب الشهرية:**
- **من 20 درجة**
- **صيغة الحساب:**
```
درجات الغياب = 20 - (أيام الغياب بدون عذر × 5)
if (درجات الغياب < 0) { درجات الغياب = 0 }
```
- **التسجيل:** في سجل الطالبة الشهري

---

## 🗄️ قاعدة البيانات

### **جداول جديدة مطلوبة:**

#### **1. attendance_logs (سجل الحضور)**
```sql
CREATE TABLE attendance_logs (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    student_id BIGINT NOT NULL,
    class_id BIGINT NOT NULL,
    session_id BIGINT, // ربط بالحلقة إذا كان موجود
    attendance_date DATE NOT NULL,
    attendance_time TIME NOT NULL,
    session_start_time TIME NOT NULL,
    delay_minutes INT DEFAULT 0,
    is_on_time BOOLEAN DEFAULT true,
    status ENUM('present', 'late', 'absent') DEFAULT 'present',
    month_reference VARCHAR(7), // YYYY-MM
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (student_id) REFERENCES users(id),
    FOREIGN KEY (class_id) REFERENCES classes(id),
    
    INDEX idx_student_date (student_id, attendance_date),
    INDEX idx_class_date (class_id, attendance_date),
    INDEX idx_month (month_reference)
);
```

#### **2. attendance_warnings (التنبيهات)**
```sql
CREATE TABLE attendance_warnings (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    student_id BIGINT NOT NULL,
    attendance_log_id BIGINT NOT NULL,
    warning_date DATE NOT NULL,
    warning_type ENUM('late', 'absent') NOT NULL,
    delay_minutes INT DEFAULT 0,
    month_count INT NOT NULL, // رقم التنبيه في الشهر
    month_reference VARCHAR(7), // YYYY-MM
    status ENUM('active', 'account_suspended') DEFAULT 'active',
    created_at TIMESTAMP,
    
    FOREIGN KEY (student_id) REFERENCES users(id),
    FOREIGN KEY (attendance_log_id) REFERENCES attendance_logs(id),
    
    INDEX idx_student_month (student_id, month_reference)
);
```

#### **3. attendance_excuses (الأعذار)**
```sql
CREATE TABLE attendance_excuses (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    student_id BIGINT NOT NULL,
    attendance_log_id BIGINT, // NULL للعذر المسبق
    excuse_date DATE NOT NULL,
    excuse_text TEXT NOT NULL,
    excuse_type ENUM('medical', 'personal', 'family', 'other') NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    submitted_at TIMESTAMP NOT NULL,
    reviewed_by BIGINT,
    reviewed_at TIMESTAMP,
    review_notes TEXT,
    
    FOREIGN KEY (student_id) REFERENCES users(id),
    FOREIGN KEY (reviewed_by) REFERENCES users(id),
    
    INDEX idx_student_date (student_id, excuse_date),
    INDEX idx_status (status)
);
```

#### **4. attendance_scores (درجات الغياب)**
```sql
CREATE TABLE attendance_scores (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    student_id BIGINT NOT NULL,
    month_reference VARCHAR(7) NOT NULL, // YYYY-MM
    total_days INT NOT NULL,
    attended_days INT NOT NULL,
    absent_days INT NOT NULL,
    excused_absent_days INT NOT NULL,
    warning_count INT DEFAULT 0,
    score INT NOT NULL, // من 20
    is_account_suspended BOOLEAN DEFAULT false,
    suspension_reason TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (student_id) REFERENCES users(id),
    
    UNIQUE KEY unique_student_month (student_id, month_reference)
);
```

---

## 🔧 الـ APIs المطلوبة

### **للتطبيق (Flutter - للطالبات):**

#### **1. تسجيل الحضور**
```php
POST /api/v1/attendance/checkin
Body:
{
  "date": "2025-10-26",
  "time": "14:15:00"
}

Response:
{
  "success": true,
  "message": "تم تسجيل الحضور بنجاح",
  "attendance": {
    "id": 123,
    "time": "14:15:00",
    "is_late": true,
    "delay_minutes": 15,
    "warning_count": 3,
    "has_account_suspension": false
  }
}
```

#### **2. جلب سجل الحضور**
```php
GET /api/v1/attendance/my-record?start_date=2025-10-01&end_date=2025-10-31

Response:
{
  "success": true,
  "summary": {
    "total_days": 20,
    "attended": 18,
    "absent": 2,
    "late_count": 3,
    "current_month_score": 18,
    "warnings_count": 2
  },
  "logs": [...]
}
```

#### **3. إضافة عذر**
```php
POST /api/v1/attendance/excuse
Body:
{
  "date": "2025-10-27",
  "excuse_text": "مرض",
  "excuse_type": "medical"
}

Response:
{
  "success": true,
  "excuse_id": 456,
  "status": "pending"
}
```

#### **4. جلب الأعذار**
```php
GET /api/v1/attendance/excuses?month=2025-10

Response:
{
  "success": true,
  "excuses": [
    {
      "id": 456,
      "date": "2025-10-27",
      "text": "مرض",
      "type": "medical",
      "status": "pending",
      "submitted_at": "2025-10-26"
    }
  ]
}
```

---

### **للمعلمات (Filament + API):**

#### **1. عرض تقارير الحضور**
```php
GET /api/v1/teacher/attendance/report?class_id=1&month=2025-10

Response:
{
  "success": true,
  "report": {
    "month": "2025-10",
    "class_name": "فصل أ",
    "students": [...],
    "summary": {...}
  }
}
```

#### **2. قبول/رفض عذر**
```php
PUT /api/v1/teacher/attendance/excuses/{excuseId}/review
Body:
{
  "action": "approve", // or "reject"
  "notes": "تم القبول"
}

Response:
{
  "success": true,
  "excuse": {...}
}
```

#### **3. إعادة تفعيل الحساب**
```php
POST /api/v1/teacher/attendance/activate-account
Body:
{
  "student_id": 123
}

Response:
{
  "success": true,
  "message": "تم تفعيل الحساب وإعادة 50 نقطة"
}
```

---

## 🎨 واجهة المستخدم (Flutter)

### **صفحة المهام (daily_tasks_page):**

```dart
// إضافة زر الحضور
FloatingActionButton(
  onPressed: _markAttendance,
  child: Icon(Icons.person_add),
  backgroundColor: AppTokens.primaryGreen,
)

// Dialog لتسجيل الحضور
void _markAttendance() {
  showDialog(
    context: context,
    builder: (context) => AlertDialog(
      title: Text('تسجيل الحضور'),
      content: Column(
        children: [
          Text('وقت الحضور: ${_currentTime}'),
          Text('وقت بدء الحلقة: ${_sessionStartTime}'),
          if (_isLate)
            Text(
              '⚠️ تأخير ${_delayMinutes} دقيقة',
              style: TextStyle(color: Colors.orange),
            ),
        ],
      ),
      actions: [
        ElevatedButton(
          onPressed: _confirmAttendance,
          child: Text('تأكيد'),
        ),
      ],
    ),
  );
}
```

### **صفحة الأذونات (attendance_excuses_page.dart):**

```dart
// صفحة جديدة للأعذار
class AttendanceExcusesPage extends StatefulWidget {
  // عرض الأعذار
  // إضافة عذر جديد
  // مشاهدة حالة العذر (pending/approved/rejected)
}
```

---

## 📁 الهيكل التنظيمي

### **Backend (PHP):**

```
hosoony2-git/
├── app/
│   ├── Models/
│   │   ├── AttendanceLog.php
│   │   ├── AttendanceWarning.php
│   │   ├── AttendanceExcuse.php
│   │   └── AttendanceScore.php
│   ├── Http/
│   │   └── Controllers/
│   │       └── Api/
│   │           └── V1/
│   │               └── AttendanceController.php
│   └── Services/
│       └── AttendanceService.php
└── database/
    └── migrations/
        ├── create_attendance_logs_table.php
        ├── create_attendance_warnings_table.php
        ├── create_attendance_excuses_table.php
        └── create_attendance_scores_table.php
```

### **Flutter (Dart):**

```
hosoony_flutter/
├── lib/
│   ├── features/
│   │   └── student/
│   │       └── pages/
│   │           ├── daily_tasks_page_new.dart (إضافة زر الحضور)
│   │           └── attendance_excuses_page.dart (جديد)
│   └── services/
│       └── attendance_service.dart (جديد)
```

---

## ⚙️ الخوارزميات المطلوبة

### **1. حساب التأخير:**
```php
function calculateDelay($attendanceTime, $sessionStartTime) {
    $attendance = Carbon::parse($attendanceTime);
    $sessionStart = Carbon::parse($sessionStartTime);
    $delay = $attendance->diffInMinutes($sessionStart);
    
    return [
        'delay_minutes' => $delay,
        'is_late' => $delay > 10,
        'warning_required' => $delay > 10
    ];
}
```

### **2. التحقق من 6 تنبيهات:**
```php
function checkWarningsCount($studentId, $month) {
    $count = AttendanceWarning::where('student_id', $studentId)
        ->where('month_reference', $month)
        ->count();
    
    if ($count >= 6) {
        // تعطيل الحساب
        // خصم 50 نقطة
        // إشعار للمعلمة
    }
}
```

### **3. حساب درجات الغياب:**
```php
function calculateAttendanceScore($studentId, $month) {
    $attendance = AttendanceScore::where('student_id', $studentId)
        ->where('month_reference', $month)
        ->first();
    
    $absentDays = $attendance->absent_days - $attendance->excused_absent_days;
    $score = max(0, 20 - ($absentDays * 5));
    
    return $score;
}
```

---

## 📊 الجداول والعلاقات

### **ER Diagram:**
```
users (students)
    ↓
attendance_logs (سجل الحضور)
    ↓
attendance_warnings (التنبيهات)
    ↓
attendance_scores (الدرجات الشهرية)
    
users (students)
    ↓
attendance_excuses (الأعذار)
    ↓
attendance_logs (إذا تم الموافقة)

classes
    ↓
sessions (الحلقات)
    ↓
attendance_logs (ربط الحلقة)
```

---

## 🎯 خطة التنفيذ

### **المرحلة 1: قاعدة البيانات (2-3 ساعات)**
- [ ] إنشاء migrations
- [ ] إنشاء Models
- [ ] إضافة العلاقات

### **المرحلة 2: AttendanceService (3-4 ساعات)**
- [ ] تسجيل الحضور
- [ ] حساب التأخير
- [ ] إدارة التنبيهات
- [ ] حساب الدرجات
- [ ] التعطيل التلقائي

### **المرحلة 3: AttendanceController (2-3 ساعات)**
- [ ] API تسجيل الحضور
- [ ] API الأعذار
- [ ] API التقارير
- [ ] API إعادة التفعيل

### **المرحلة 4: واجهة Filament (2 ساعات)**
- [ ] AttendanceReports Resource
- [ ] عرض التقارير
- [ ] قبول/رفض الأعذار
- [ ] زر إعادة التفعيل

### **المرحلة 5: Flutter (4-5 ساعات)**
- [ ] زر الحضور في daily_tasks_page
- [ ] صفحة الأعذار (attendance_excuses_page)
- [ ] AttendanceService في Flutter
- [ ] التنبيهات والإشعارات

### **المرحلة 6: الاختبار (2 ساعات)**
- [ ] Test تسجيل الحضور
- [ ] Test الأعذار
- [ ] Test التعطيل
- [ ] Test التقارير

**المجموع: ~18 ساعة عمل**

---

## ✅ المتطلبات الإضافية

### **قواعد العمل:**
1. **التأخير > 10 دقائق** → تنبيه
2. **6 تنبيهات شهرياً** → تعطيل + خصم 50 نقطة
3. **ساعة من بدء الحلقة** → انتهاء وقت الحضور
4. **3 أيام غياب بدون عذر** → تعطيل + خصم 50 نقطة
5. **درجات الغياب الشهرية** → 20 - (أيام الغياب × 5)

### **الإشعارات:**
- تنبيه تأخير → للطالبة
- تعطيل الحساب → للطالبة والمعلمة
- عذر جديد → للمعلمة
- قبول عذر → للطالبة

---

## 📋 ملفات يجب إنشاؤها

### **Backend:**
1. `app/Models/AttendanceLog.php`
2. `app/Models/AttendanceWarning.php`
3. `app/Models/AttendanceExcuse.php`
4. `app/Models/AttendanceScore.php`
5. `app/Services/AttendanceService.php`
6. `app/Http/Controllers/Api/V1/AttendanceController.php`
7. `app/Filament/Resources/AttendanceReportResource.php`
8. `database/migrations/*_create_attendance_*.php`

### **Flutter:**
1. `lib/services/attendance_service.dart`
2. `lib/features/student/pages/attendance_excuses_page.dart`
3. تعديل `daily_tasks_page_new.dart` (إضافة زر الحضور)

---

## 🚀 هل تريد البدء في التنفيذ؟

**الخطوات الأولى المقترحة:**
1. إنشاء قاعدة البيانات (Migrations + Models)
2. إنشاء AttendanceService
3. إنشاء AttendanceController
4. إضافة واجهة Flutter

**وقت التنفيذ الكامل: ~18 ساعة**

⚠️ **ملاحظة:** هذا نظام معقد ويحتاج تفصيل أكثر. هل تريد البدء بإحدى المراحل؟

