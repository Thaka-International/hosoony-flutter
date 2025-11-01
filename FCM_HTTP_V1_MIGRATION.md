# دليل الانتقال من Legacy FCM API إلى HTTP v1 API

## ⚠️ أهمية الانتقال:

- Legacy FCM APIs deprecated في 20 يونيو 2023
- سيتم إيقافها نهائياً في 22 يوليو 2024
- **يجب الانتقال الآن لتجنب انقطاع الخدمة!**

## ✅ المزايا الجديدة في HTTP v1:

1. **أمان محسّن**: OAuth2 access tokens قصيرة الأمد بدلاً من Server Key ثابتة
2. **تخصيص أفضل**: رسائل مختلفة لكل منصة (Android/iOS/Web) في نفس الطلب
3. **دعم أفضل**: ميزات جديدة ومنصات مستقبلية

## 📋 ما تم تحديثه:

### 1. ✅ Service جديد: `FcmService`
- يستخدم HTTP v1 API
- يدعم OAuth2 authentication
- يدعم Service Account credentials بطرق متعددة

### 2. ✅ تحديث `NotificationService`
- يستخدم `FcmService` الجديد بدلاً من Legacy API

### 3. ✅ تحديث `.env` template
- إعدادات جديدة لـ HTTP v1 API

## 🔧 خطوات الإعداد:

### الخطوة 1: تثبيت المكتبة المطلوبة

```bash
cd hosoony-backend
composer require google/apiclient
```

### الخطوة 2: إنشاء Service Account في Firebase

1. افتح [Firebase Console](https://console.firebase.google.com/)
2. اختر المشروع: **hosoony-abbba**
3. انتقل إلى: **Project Settings** (⚙️)
4. افتح تبويب: **Service Accounts**
5. انقر على: **Generate new private key**
6. سيتم تحميل ملف JSON - احفظه في مكان آمن على السيرفر

### الخطوة 3: إضافة Service Account إلى `.env`

**الخيار 1: استخدام مسار الملف (موصى به)**

```env
FCM_PROJECT_ID=hosoony-abbba
FCM_SERVICE_ACCOUNT_PATH=/path/to/service-account-key.json
```

**الخيار 2: استخدام محتوى JSON مباشرة**

```env
FCM_PROJECT_ID=hosoony-abbba
FCM_SERVICE_ACCOUNT_JSON={"type":"service_account","project_id":"hosoony-abbba",...}
```

**الخيار 3: استخدام بيانات مفردة (للانتقال التدريجي)**

```env
FCM_PROJECT_ID=hosoony-abbba
FCM_CLIENT_EMAIL=your-service-account@hosoony-abbba.iam.gserviceaccount.com
FCM_PRIVATE_KEY="-----BEGIN PRIVATE KEY-----\n...\n-----END PRIVATE KEY-----\n"
FCM_PRIVATE_KEY_ID=your-private-key-id
FCM_CLIENT_ID=your-client-id
FCM_CLIENT_X509_CERT_URL=https://www.googleapis.com/robot/v1/metadata/x509/...
```

### الخطوة 4: إزالة Legacy Keys (اختياري)

بعد التأكد من عمل HTTP v1، يمكن إزالة:
```env
# هذه لم تعد ضرورية بعد الانتقال
# FCM_SERVER_KEY=
# FCM_SENDER_ID=
```

## 🔄 التغييرات التقنية:

### Legacy API (القديم):
```php
// Endpoint
https://fcm.googleapis.com/fcm/send

// Authentication
Authorization: key=YOUR_SERVER_KEY

// Payload
{
  "to": "token",
  "notification": {
    "title": "...",
    "body": "..."
  }
}
```

### HTTP v1 API (الجديد):
```php
// Endpoint
https://fcm.googleapis.com/v1/projects/PROJECT_ID/messages:send

// Authentication
Authorization: Bearer ACCESS_TOKEN (OAuth2)

// Payload
{
  "message": {
    "token": "token",
    "notification": {
      "title": "...",
      "body": "..."
    },
    "android": {...},  // Optional
    "apns": {...},     // Optional
    "webpush": {...}   // Optional
  }
}
```

## ✅ الاختبار:

### 1. التحقق من التكوين:

```bash
php artisan tinker
```

```php
$fcmService = app(\App\Services\FcmService::class);
// Should not throw exception
```

### 2. اختبار إرسال إشعار:

```bash
POST /api/v1/notifications/test
{
  "user_id": 1,
  "type": "info",
  "title": "اختبار HTTP v1",
  "message": "هذا إشعار باستخدام HTTP v1 API",
  "channel": "push"
}
```

### 3. التحقق من Logs:

في `storage/logs/laravel.log` يجب أن ترى:
```
FCM notification sent successfully
```

بدلاً من أي أخطاء.

## 📝 ملاحظات مهمة:

1. **Security**: Service Account JSON يجب أن يكون محمي ومنفذ فقط للقراءة
2. **Caching**: Access tokens يتم تخزينها في Cache لمدة 50 دقيقة (صالحة لساعة)
3. **Backward Compatibility**: الكود يدعم Legacy credentials مؤقتاً للانتقال التدريجي

## 🚀 النشر:

بعد التأكد من العمل المحلي:

```bash
# في hosoony-backend
composer install --no-dev
composer require google/apiclient

# انسخ ملفات محدثة
cp app/Services/FcmService.php ../hosoony2-git/app/Services/FcmService.php
cp app/Services/NotificationService.php ../hosoony2-git/app/Services/NotificationService.php

# في hosoony2-git
cd ../hosoony2-git
git add .
git commit -m "Migrate FCM to HTTP v1 API"
git push origin production
```

## ❓ استكشاف الأخطاء:

### خطأ: "FCM service account not configured"
- تأكد من وجود `FCM_SERVICE_ACCOUNT_PATH` أو `FCM_SERVICE_ACCOUNT_JSON`
- تأكد من أن الملف موجود وصحيح

### خطأ: "Failed to get access token"
- تحقق من Service Account credentials
- تأكد من أن Service Account لديه صلاحيات Firebase Cloud Messaging

### خطأ: "FCM HTTP v1 request failed"
- تحقق من أن `FCM_PROJECT_ID` صحيح
- تحقق من logs للحصول على تفاصيل الخطأ

## ✅ الخلاصة:

| المكون | الحالة |
|--------|--------|
| FcmService (HTTP v1) | ✅ جاهز |
| NotificationService Integration | ✅ محدث |
| Composer Dependencies | ⚠️ يحتاج `composer require` |
| Service Account Setup | ⚠️ يحتاج إعداد في Firebase |
| .env Configuration | ⚠️ يحتاج تحديث |

**بعد إكمال الخطوات أعلاه، النظام جاهز للعمل مع HTTP v1 API!**



