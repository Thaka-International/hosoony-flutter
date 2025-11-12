# 🔥 إعداد Firebase لـ iOS

## ✅ تم إضافة GoogleService-Info.plist

تم إنشاء ملف `GoogleService-Info.plist` في `ios/Runner/` بناءً على معلومات من `google-services.json`.

## ⚠️ مهم جداً: تحتاج إلى تحديث الملف

الملف الحالي يحتوي على قيم placeholder. يجب استبداله بالملف الرسمي من Firebase Console.

### الخطوات:

1. **اذهب إلى Firebase Console:**
   - https://console.firebase.google.com/
   - اختر المشروع: `hosoony-abbba`

2. **أضف iOS App (إذا لم يكن موجوداً):**
   - Project Settings → General
   - اضغط "Add app" → اختر iOS
   - Bundle ID: `com.hosoony.hosoonyFlutter`
   - App nickname: (اختياري) Hosoony iOS

3. **حمّل GoogleService-Info.plist:**
   - بعد إضافة iOS app، سيظهر زر "Download GoogleService-Info.plist"
   - حمّل الملف

4. **استبدل الملف:**
   ```bash
   # احذف الملف القديم
   rm ios/Runner/GoogleService-Info.plist
   
   # ضع الملف الجديد من Firebase
   mv ~/Downloads/GoogleService-Info.plist ios/Runner/
   ```

5. **في Xcode:**
   - افتح `Runner.xcworkspace`
   - في Project Navigator، اسحب `GoogleService-Info.plist` إلى `Runner` group
   - ✅ تأكد من أن "Copy items if needed" محدد
   - ✅ تأكد من أن "Runner" target محدد
   - ✅ اضغط "Finish"

6. **تحقق من Xcode:**
   - اختر `Runner` target
   - اذهب إلى "Build Phases" → "Copy Bundle Resources"
   - تأكد من أن `GoogleService-Info.plist` موجود في القائمة

## ✅ بعد التحديث:

```bash
cd /Users/ibraheem.designer/Documents/hosoony2/hosoony_flutter
flutter clean
flutter pub get
```

ثم في Xcode:
- Product → Clean Build Folder (⌘ + Shift + K)
- Product → Build (⌘ + B)
- Product → Run (⌘ + R)

## 📝 معلومات المشروع الحالية:

من `google-services.json`:
- **Project ID:** `hosoony-abbba`
- **Project Number:** `302783003103`
- **Bundle ID (iOS):** `com.hosoony.hosoonyFlutter`
- **API Key:** `AIzaSyCmOIADuKtVIbxrfbh3ip0sNf3YBkFi5pE`

## ⚠️ ملاحظة:

الملف الحالي (`GoogleService-Info.plist`) سيعمل بشكل أساسي لكن قد لا يكون كاملاً. 
**يجب استبداله بالملف الرسمي من Firebase Console للحصول على جميع الميزات.**









