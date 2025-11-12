# 📱 إعلان Advertising ID في Google Play Console

## ✅ تم تحديث AndroidManifest.xml

تم إضافة إعدادات في `AndroidManifest.xml` لتعطيل جمع Advertising ID في Firebase Analytics:
- `google_analytics_adid_collection_enabled = false`
- `google_analytics_ssaid_collection_enabled = false`

## 🔧 خطوات إكمال الإعلان في Google Play Console

### الخطوة 1: افتح Google Play Console
1. اذهب إلى: https://play.google.com/console
2. سجل الدخول بحساب المطور
3. اختر التطبيق: **حصوني القرآني**

### الخطوة 2: اذهب إلى صفحة الإعلان
1. من القائمة الجانبية، اذهب إلى: **Policy** → **App content**
2. أو اذهب مباشرة إلى: **Policy** → **Advertising ID**

### الخطوة 3: أكمل الإعلان
ستجد سؤال: **"Does your app use advertising ID?"**

#### إذا كان التطبيق لا يستخدم Advertising ID (الخيار الموصى به):
1. اختر: **"No, my app does not use advertising ID"**
2. اضغط **Save**

#### إذا كان التطبيق يستخدم Advertising ID:
1. اختر: **"Yes, my app uses advertising ID"**
2. املأ التفاصيل المطلوبة:
   - **How is advertising ID used?** (كيف يتم استخدام Advertising ID)
   - **Is advertising ID used for ads?** (هل يُستخدم للإعلانات)
   - **Is advertising ID used for analytics?** (هل يُستخدم للتحليلات)
   - **Is advertising ID used for fraud prevention?** (هل يُستخدم لمنع الاحتيال)

### الخطوة 4: أعد بناء التطبيق
بعد تحديث `AndroidManifest.xml`، يجب إعادة بناء التطبيق:

```bash
cd /Users/ibraheem.designer/Documents/hosoony2/hosoony_flutter
flutter clean
flutter pub get
flutter build appbundle --release
```

### الخطوة 5: ارفع الإصدار الجديد
1. ارفع ملف `.aab` الجديد إلى Google Play Console
2. تأكد من أن الإعلان مكتمل قبل إرسال التطبيق للمراجعة

## 📝 ملاحظات مهمة

### ✅ التطبيق الحالي لا يستخدم Advertising ID
- تم تعطيل جمع Advertising ID في Firebase Analytics
- لا توجد مكتبات إعلانات في التطبيق
- **الخيار الموصى به:** اختر **"No, my app does not use advertising ID"**

### ⚠️ إذا كنت تريد استخدام Advertising ID في المستقبل:
1. احذف أو غيّر `google_analytics_adid_collection_enabled` إلى `true`
2. أضف `<uses-permission android:name="com.google.android.gms.permission.AD_ID"/>` في AndroidManifest.xml
3. حدّث الإعلان في Google Play Console إلى **"Yes"**

## 🔗 روابط مفيدة

- [Google Play Advertising ID Policy](https://support.google.com/googleplay/android-developer/answer/6048248)
- [Firebase Analytics Advertising ID](https://firebase.google.com/docs/analytics/android/events)
- [Android Advertising ID Documentation](https://developer.android.com/training/articles/ad-id)

## ✅ بعد الإكمال

بعد إكمال الإعلان في Google Play Console:
1. ✅ سيختفي التحذير "Incomplete advertising ID declaration"
2. ✅ يمكنك إرسال التطبيق للمراجعة
3. ✅ سيتم نشر التطبيق تلقائياً بعد الموافقة (إذا كان Managed publishing مفعّل)

---

**تاريخ التحديث:** $(date)
**الإصدار:** 1.0.0+1
**Target SDK:** 36 (Android 13+)


