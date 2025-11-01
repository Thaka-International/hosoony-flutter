# 🔧 حل مشكلة PhaseScriptExecution failed في iOS

## ⚠️ المشكلة الرئيسية

بعد فحص `flutter doctor`، تم اكتشاف:
1. ❌ **Xcode installation is incomplete**
2. ❌ **CocoaPods not installed**

هذه هي الأسباب الجذرية لخطأ `PhaseScriptExecution failed`.

## 🎯 الحل الفوري

### الخطوة 1: إصلاح Xcode

```bash
# 1. تأكد من تثبيت Xcode من App Store
# 2. بعد التثبيت، نفذ:
sudo xcode-select --switch /Applications/Xcode.app/Contents/Developer
sudo xcodebuild -runFirstLaunch

# 3. قبول الرخصة
sudo xcodebuild -license accept
```

### الخطوة 2: تثبيت CocoaPods

```bash
# تثبيت CocoaPods
sudo gem install cocoapods

# أو باستخدام Homebrew (موصى به)
brew install cocoapods

# تحديث CocoaPods repo
pod repo update
```

### الخطوة 3: تنظيف وإعادة البناء

```bash
# استخدم السكريبت الجاهز
cd /Users/ibraheem.designer/Documents/hosoony2/hosoony_flutter
./fix_ios_build.sh

# أو يدوياً:
flutter clean
cd ios/
rm -rf Pods Podfile.lock .symlinks
pod install
cd ..
flutter pub get
```

---

## المشكلة
```
Command PhaseScriptExecution failed with a nonzero exit code
```

## الحلول السريعة (جرب بالترتيب)

### ✅ الحل 1: تنظيف وإعادة البناء (الأكثر شيوعاً)

```bash
# 1. تنظيف Flutter
cd /Users/ibraheem.designer/Documents/hosoony2/hosoony_flutter
flutter clean

# 2. تنظيف iOS
cd ios/
rm -rf Pods Podfile.lock .symlinks
rm -rf ~/Library/Developer/Xcode/DerivedData/*

# 3. إعادة تثبيت CocoaPods
pod deintegrate
pod install

# 4. العودة للمجلد الرئيسي وبناء التطبيق
cd ..
flutter pub get
flutter build ios --no-codesign
```

### ✅ الحل 2: تحديث CocoaPods و Flutter

```bash
# تحديث CocoaPods
sudo gem install cocoapods
pod repo update

# تحديث Flutter
flutter upgrade

# ثم اتبع الحل 1
```

### ✅ الحل 3: إصلاح Flutter Path

```bash
# 1. معرفة Flutter path
which flutter

# 2. في Xcode، اذهب إلى:
# Build Settings > Search for "FLUTTER_ROOT"
# تأكد من أن القيمة صحيحة (مثل: /Users/ibraheem.designer/Developer/flutter)

# 3. أو اضبط في Terminal:
export FLUTTER_ROOT=$(which flutter | sed 's|/bin/flutter||')
echo $FLUTTER_ROOT
```

### ✅ الحل 4: إصلاح Script Permissions

```bash
cd /Users/ibraheem.designer/Documents/hosoony2/hosoony_flutter/ios

# إعطاء صلاحيات تنفيذ للـ scripts
chmod +x Flutter/flutter_export_environment.sh
chmod +x Flutter/Generated.xcconfig
chmod -R 755 Flutter/
```

### ✅ الحل 5: إصلاح Xcode Build Settings

في Xcode:
1. افتح `ios/Runner.xcworkspace`
2. اذهب إلى **Runner** target > **Build Settings**
3. ابحث عن **"Run Script Phase"**
4. في **"Build Phases"**، ابحث عن:
   - **"Run Script"** phases
   - تأكد من أن `Shell` = `/bin/sh`
   - تأكد من أن `Show environment variables` مفعل
5. في كل "Run Script" phase، أضف في البداية:
   ```bash
   set -e
   export PATH="$PATH:/usr/local/bin"
   export FLUTTER_ROOT="$HOME/Developer/flutter"  # أو المسار الصحيح لـ Flutter
   ```

### ✅ الحل 6: إصلاح Firebase Configuration

```bash
cd /Users/ibraheem.designer/Documents/hosoony2/hosoony_flutter/ios

# إذا كان GoogleService-Info.plist غير موجود:
# 1. حمّل من Firebase Console
# 2. ضعه في Runner/
# 3. أضفه إلى Xcode project
```

### ✅ الحل 7: إصلاح Podfile (إذا كان موجوداً)

أنشئ أو تحديث `ios/Podfile`:

```ruby
# Uncomment this line to define a global platform for your project
platform :ios, '12.0'

# CocoaPods analytics will send metrics to their servers
# to help improve CocoaPods and other tools
use_frameworks!

target 'Runner' do
  use_frameworks!
  use_modular_headers!

  flutter_install_all_ios_pods File.dirname(File.realpath(__FILE__))
end

post_install do |installer|
  installer.pods_project.targets.each do |target|
    flutter_additional_ios_build_settings(target)
    target.build_configurations.each do |config|
      config.build_settings['IPHONEOS_DEPLOYMENT_TARGET'] = '12.0'
    end
  end
end
```

ثم:
```bash
cd ios/
pod install
```

## 🔍 التشخيص المتقدم

### معرفة الخطأ المحدد:

في Xcode:
1. افتح **View** > **Navigators** > **Show Report Navigator** (⌘9)
2. اختر آخر build فاشل
3. ابحث عن **"PhaseScriptExecution"**
4. اضغط على السهم بجانب الخطأ لرؤية التفاصيل

أو في Terminal:
```bash
cd /Users/ibraheem.designer/Documents/hosoony2/hosoony_flutter
flutter build ios --verbose 2>&1 | tee build.log
# ثم ابحث عن "error" أو "failed" في build.log
```

## 🎯 الحل الشامل (نصيحة)

جرّب هذا الترتيب:

```bash
# 1. تنظيف شامل
cd /Users/ibraheem.designer/Documents/hosoony2/hosoony_flutter
flutter clean
cd ios/
rm -rf Pods Podfile.lock .symlinks DerivedData
rm -rf ~/Library/Developer/Xcode/DerivedData/*

# 2. إعادة التثبيت
pod deintegrate
pod install --repo-update

# 3. تحديث Flutter packages
cd ..
flutter pub get

# 4. إعادة البناء
flutter build ios --no-codesign

# 5. افتح Xcode
open ios/Runner.xcworkspace
```

## 📝 ملاحظات مهمة

1. **استخدم `.xcworkspace` دائماً**: لا تفتح `.xcodeproj` مباشرة
2. **تأكد من Flutter path**: في Xcode Build Settings
3. **Podfile.lock**: إذا واجهت مشاكل، احذفه واعمل `pod install` من جديد
4. **Xcode Version**: تأكد من استخدام Xcode محدث (12.0+)

## 🆘 إذا لم يعمل شيء

1. **أعد تشغيل Xcode**
2. **أعد تشغيل Mac** (في بعض الأحيان يحل المشاكل)
3. **تأكد من Xcode Command Line Tools**:
   ```bash
   xcode-select --install
   ```
4. **تحقق من Flutter Doctor**:
   ```bash
   flutter doctor -v
   ```

## ✅ التحقق من الحل

بعد تطبيق أي حل:
1. افتح Xcode
2. اضغط **⌘ + Shift + K** (Clean Build Folder)
3. اضغط **⌘ + B** (Build)
4. إذا نجح Build، اضغط **⌘ + R** (Run)

