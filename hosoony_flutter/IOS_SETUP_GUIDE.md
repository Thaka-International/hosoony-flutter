# دليل إعداد iOS للتطبيق

## المتطلبات الأساسية

### 1. إضافة GoogleService-Info.plist
⚠️ **مهم جداً**: يجب إضافة ملف `GoogleService-Info.plist` من Firebase Console

**خطوات الإضافة:**
1. اذهب إلى [Firebase Console](https://console.firebase.google.com/)
2. اختر المشروع المناسب
3. اذهب إلى Project Settings > General
4. في قسم "Your apps"، ابحث عن iOS app أو أنشئ واحدة جديدة
5. حمّل ملف `GoogleService-Info.plist`
6. ضع الملف في: `ios/Runner/GoogleService-Info.plist`
7. تأكد من إضافة الملف إلى Xcode project (افتح Xcode > Runner > اضغط بالزر الأيمن > Add Files to "Runner" > اختر GoogleService-Info.plist)

### 2. إعداد Xcode Project

#### 2.1 Bundle Identifier
- افتح `ios/Runner.xcworkspace` في Xcode
- اذهب إلى Runner > Signing & Capabilities
- تأكد من أن Bundle Identifier متطابق مع Firebase iOS app

#### 2.2 Capabilities
تأكد من تفعيل:
- ✅ Push Notifications
- ✅ Background Modes > Remote notifications

#### 2.3 Deployment Target
- الحد الأدنى: iOS 12.0 (أو أحدث حسب متطلبات المشروع)

### 3. إعداد CocoaPods

في terminal، من داخل مجلد `ios/`:
```bash
cd ios/
pod install
```

### 4. الصلاحيات المضافة في Info.plist

تم إضافة الصلاحيات التالية:
- ✅ **NSCameraUsageDescription**: للوصول إلى الكاميرا
- ✅ **NSPhotoLibraryUsageDescription**: للوصول إلى الصور
- ✅ **NSPhotoLibraryAddUsageDescription**: لحفظ الصور
- ✅ **NSMicrophoneUsageDescription**: للوصول إلى الميكروفون
- ✅ **UIBackgroundModes**: للإشعارات والعمليات الخلفية

### 5. Firebase Configuration

تم إضافة Firebase initialization في `AppDelegate.swift`:
- ✅ Firebase initialization
- ✅ Push notifications setup
- ✅ Remote notification handling

## الاختبار على Xcode

### الخطوات:
1. افتح `ios/Runner.xcworkspace` (⚠️ مهم: `.xcworkspace` وليس `.xcodeproj`)
2. اختر device أو simulator
3. اضغط Run (⌘R)

### التحقق من:
- ✅ التطبيق يعمل بدون أخطاء
- ✅ Firebase يعمل بشكل صحيح
- ✅ الإشعارات تعمل (اختبار في simulator قد يكون محدود)
- ✅ الكاميرا تعمل (إذا تم استخدامها)
- ✅ اختيار الصور يعمل

## المشاكل الشائعة وحلولها

### 1. "GoogleService-Info.plist not found"
**الحل**: تأكد من إضافة الملف في Xcode project (انظر الخطوة 1 أعلاه)

### 2. "Signing for Runner requires a development team"
**الحل**: 
- اذهب إلى Signing & Capabilities في Xcode
- اختر Team (Apple Developer Account)

### 3. "Firebase initialization failed"
**الحل**:
- تأكد من وجود `GoogleService-Info.plist`
- تأكد من تطابق Bundle Identifier مع Firebase
- راجع logs في Xcode console

### 4. Push notifications لا تعمل
**الحل**:
- تأكد من تفعيل Push Notifications capability
- تأكد من الحصول على APNs certificate في Firebase Console
- على device حقيقي: تأكد من السماح للإشعارات

### 5. "Module 'FirebaseCore' not found"
**الحل**:
```bash
cd ios/
rm -rf Pods Podfile.lock
pod install
```

## ملاحظات مهمة

1. **استخدم `.xcworkspace` دائماً**: لا تفتح `.xcodeproj` مباشرة بعد إضافة CocoaPods
2. **Firebase iOS SDK**: يتم تثبيته تلقائياً عبر CocoaPods من `pubspec.yaml`
3. **Testing على Simulator**: بعض الميزات (مثل Push Notifications) قد لا تعمل بشكل كامل على Simulator - اختبر على device حقيقي
4. **Build Settings**: تأكد من أن Build Configuration (Debug/Release) صحيح

## الخطوات التالية بعد الإعداد

بعد إكمال الإعداد:
1. ✅ اختبر جميع الوظائف في Android أولاً
2. ✅ اختبر نفس الوظائف في iOS
3. ✅ تحقق من أن جميع APIs تعمل
4. ✅ تحقق من أن Firebase يعمل بشكل صحيح
5. ✅ اختبر Push Notifications

## الدعم

إذا واجهت أي مشاكل:
1. راجع logs في Xcode console
2. راجع Firebase Console logs
3. تحقق من أن جميع dependencies محدثة في `pubspec.yaml`

