# دليل إكمال إعداد Firebase

## ✅ الحالة الحالية:

### في Flutter:
- ✅ `google-services.json` موجود - Android جاهز
- ✅ Firebase initialized في `main.dart`
- ✅ Firebase Messaging configured

### في Backend:
- ⚠️ يحتاج `FCM_SERVER_KEY` في `.env`

## 🔑 كيفية الحصول على FCM Server Key:

### الطريقة 1: من Firebase Console (الطريقة الموصى بها)

1. افتح [Firebase Console](https://console.firebase.google.com/)
2. اختر المشروع: **hosoony-abbba**
3. انتقل إلى: **Project Settings** (⚙️ في أعلى اليسار)
4. افتح تبويب: **Cloud Messaging**
5. ابحث عن: **Server key** (أو **Cloud Messaging API (Legacy) Server Key**)
6. انسخ المفتاح

### الطريقة 2: من ملف Firebase Config على السيرفر

إذا كان لديك ملف Firebase config على السيرفر (كما ذكرت في `.env`):

1. افتح الملف الموجود على السيرفر
2. ابحث عن:
   - `server_key` أو
   - `FCM_SERVER_KEY` أو
   - `messagingSenderId`

**ملاحظة:** `FCM_SERVER_KEY` مختلف عن `api_key` الموجود في `google-services.json`

## 📝 إضافة FCM Server Key إلى `.env`:

### في Backend:

افتح ملف `.env` وأضف:

```env
# FCM Configuration
FCM_SERVER_KEY=your_fcm_server_key_here
FCM_SENDER_ID=302783003103
FCM_PROJECT_ID=hosoony-abbba
```

**ملاحظة:**
- `FCM_SENDER_ID` = Project Number من `google-services.json` (302783003103)
- `FCM_PROJECT_ID` = Project ID من `google-services.json` (hosoony-abbba)

## ✅ التحقق من الإعداد:

### 1. تحقق من Flutter:
```bash
cd hosoony_flutter
flutter run
```

في logs يجب أن ترى:
```
FCM Token: <token>
Device registered successfully with FCM token
```

### 2. تحقق من Backend:

في قاعدة البيانات `devices` table:
- يجب أن يحتوي على سجلات مع `fcm_token`
- `user_id` مربوط بالطالب
- `platform` = 'android' أو 'ios'

### 3. اختبار Push Notification:

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

## 📋 ملخص المعلومات من google-services.json:

```
Project ID: hosoony-abbba
Project Number: 302783003103
API Key: AIzaSyCmOIADuKtVIbxrfbh3ip0sNf3YBkFi5pE
```

**⚠️ مهم:** `FCM_SERVER_KEY` مختلف عن `API Key` أعلاه!

## 🔍 إذا كان الملف على السيرفر:

إذا كان `.env` يحتوي على رابط لملف Firebase config:

1. افتح الملف من السيرفر
2. ابحث عن `server_key` أو `FCM_SERVER_KEY`
3. انسخه وأضفه إلى `.env`

أو يمكنك استخدام:
```bash
# مثال إذا كان الملف في /path/to/firebase-config.json
FCM_SERVER_KEY=$(cat /path/to/firebase-config.json | jq -r '.server_key')
```

## ✅ الخلاصة:

| المكون | الحالة | ملاحظات |
|--------|--------|---------|
| Flutter Firebase Config | ✅ جاهز | google-services.json موجود |
| Firebase Initialization | ✅ جاهز | يعمل في main.dart |
| Device Registration | ✅ جاهز | يتم تلقائياً |
| FCM Server Key | ⚠️ يحتاج | أضفه إلى .env |

## 🎯 الخطوة التالية:

**أضف `FCM_SERVER_KEY` إلى `.env` في Backend ثم Push Notifications ستكون جاهزة تماماً!**



