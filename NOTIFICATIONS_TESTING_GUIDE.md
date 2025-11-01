# دليل اختبار نظام الإشعارات

## ✅ الحالة الحالية:

### جاهز للاختبار:
1. **In-App Notifications** ✅ **جاهز تماماً**
2. **Push Notifications (FCM)** ✅ **جاهز الآن بعد الإصلاحات**

## 📋 الإصلاحات المطبقة:

### 1. ✅ إصلاح NotificationController.sendTestNotification()
- تم تغيير `sendNotification()` إلى `sendNotificationLegacy()`

### 2. ✅ إصلاح Device Status Check
- تم استبدال `where('status', 'active')` بـ `isActive()` method

### 3. ✅ إضافة Device Registration Endpoint
- `POST /api/v1/devices/register` - لتسجيل FCM token
- `POST /api/v1/devices/unregister` - لإلغاء تسجيل device

### 4. ✅ تحديث Flutter NotificationService
- إرسال FCM token تلقائياً عند التهيئة
- تحديث token تلقائياً عند التغيير

### 5. ✅ تحسين Push Notification Payload
- استخدام `title` و `message` من Notification مباشرة

## 🧪 خطوات الاختبار:

### اختبار 1: In-App Notifications (جاهز الآن)

#### الطريقة 1: من Filament Admin Panel
1. افتح Filament: `https://thakaa.me/admin`
2. استخدم أي أداة API testing (أو Postman)
3. أرسل:
```bash
POST https://thakaa.me/api/v1/notifications/test
Authorization: Bearer {admin_token}
Content-Type: application/json

{
  "user_id": 1,
  "type": "info",
  "title": "اختبار إشعار",
  "message": "هذا إشعار تجريبي للاختبار",
  "channel": "in_app"
}
```

4. افتح التطبيق → صفحة الإشعارات
5. يجب أن يظهر الإشعار الجديد

#### الطريقة 2: من Terminal
```bash
curl -X POST https://thakaa.me/api/v1/notifications/test \
  -H "Authorization: Bearer YOUR_ADMIN_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "user_id": 1,
    "type": "info",
    "title": "اختبار إشعار",
    "message": "هذا إشعار تجريبي",
    "channel": "in_app"
  }'
```

### اختبار 2: Push Notifications (بعد التأكد من FCM)

#### المتطلبات:
1. ✅ FCM Server Key في `.env`:
   ```
   FCM_SERVER_KEY=your_fcm_server_key_here
   ```

2. ✅ Firebase config files موجودة:
   - `android/app/google-services.json`
   - `ios/Runner/GoogleService-Info.plist`

#### خطوات الاختبار:
1. **تشغيل التطبيق:**
   - سجل الدخول كطالب
   - سيسجل التطبيق FCM token تلقائياً

2. **التحقق من تسجيل Device:**
   - تحقق في قاعدة البيانات: `devices` table
   - يجب أن يحتوي على:
     - `user_id` = ID الطالب
     - `fcm_token` = token من Firebase
     - `platform` = 'android' أو 'ios'
     - `last_seen_at` = timestamp حديث

3. **إرسال Push Notification:**
```bash
POST https://thakaa.me/api/v1/notifications/test
{
  "user_id": 1,
  "type": "info",
  "title": "اختبار Push",
  "message": "هذا إشعار push تجريبي",
  "channel": "push"
}
```

4. **النتيجة المتوقعة:**
   - يجب أن يظهر إشعار push على الجهاز
   - حتى لو كان التطبيق في الخلفية

## 📝 ملاحظات مهمة:

### 1. FCM Server Key:
- احصل على Server Key من Firebase Console
- أضفه إلى `.env` في Backend
- بدون هذا، Push notifications لن تعمل

### 2. Firebase Configuration:
- تأكد من وجود `google-services.json` في Android
- تأكد من وجود `GoogleService-Info.plist` في iOS
- بدون هذه الملفات، FCM لن يعمل

### 3. Device Registration:
- يتم التسجيل تلقائياً عند تسجيل الدخول
- عند تحديث FCM token، يتم التحديث تلقائياً

## ✅ الخلاصة:

| النوع | الحالة | جاهز للاختبار |
|------|--------|---------------|
| **In-App** | ✅ كامل | ✅ **نعم - جاهز الآن** |
| **Push (FCM)** | ✅ كامل | ✅ **نعم - بعد إضافة FCM_SERVER_KEY** |

## 🎯 الخطوات التالية:

1. ✅ **In-App Notifications:** جاهزة للاختبار الآن!
2. ⚠️ **Push Notifications:** أضف `FCM_SERVER_KEY` إلى `.env` ثم جاهزة!

## 📞 للدعم:

إذا واجهت أي مشاكل:
1. تحقق من logs في Backend: `storage/logs/laravel.log`
2. تحقق من logs في Flutter: console output
3. تحقق من FCM token في قاعدة البيانات
4. تحقق من Firebase Console للتأكد من إعدادات المشروع



