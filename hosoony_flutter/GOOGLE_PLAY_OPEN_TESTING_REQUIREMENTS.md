# 📋 Google Play Console - Open Testing Release Requirements

Complete requirements checklist for creating an open testing release on Google Play Console.

---

## 🎯 **Release Requirements Checklist**

### ✅ **1. App Bundle (AAB) - REQUIRED**

**What you need:**
- [ ] **App Bundle file** (`.aab` format, NOT APK)
- [ ] File location: `build/app/outputs/bundle/release/app-release.aab`

**How to build:**
```bash
cd /Users/ibraheem.designer/Documents/hosoony2/hosoony_flutter

# Clean and prepare
flutter clean
flutter pub get

# Build App Bundle for release
flutter build appbundle --release

# Output: build/app/outputs/bundle/release/app-release.aab
```

**Requirements:**
- ✅ Must be signed with release keystore
- ✅ Version code must be higher than previous releases
- ✅ Current version: `1.0.0+1` (versionName+buildNumber)
- ✅ File size: Check before upload (should be reasonable)

---

### ✅ **2. Release Name - REQUIRED**

**Field:** Release name *

**Requirements:**
- [ ] **Maximum 50 characters**
- [ ] **Not shown to users** (internal identifier only)
- [ ] **Suggested format:** `v1.0.0 - Open Testing` or `1.0.0-beta`

**Examples:**
- ✅ `v1.0.0 - Open Testing`
- ✅ `1.0.0-beta-release`
- ✅ `حصوني v1.0.0 - Open Testing`
- ❌ `This is a very long release name that exceeds fifty characters limit` (too long)

**Current suggestion:** `v1.0.0 - Open Testing`

---

### ✅ **3. Release Notes - REQUIRED**

**Field:** Release notes

**Requirements:**
- [ ] **Must provide release notes for each language** your app supports
- [ ] **Arabic (ar) is required** for your app
- [ ] **English (en) is recommended** for broader audience

**Format:**
- Use `<ar>` tags for Arabic content
- Use `<en>` tags for English content (if provided)

**Example Release Notes (Arabic):**
```xml
<ar>
الإصدار الأول من تطبيق حصوني القرآني

المميزات:
• تسجيل الدخول للمعلمين والطلاب
• إدارة الفصول الدراسية
• متابعة الحفظ اليومي
• نظام المهام الأسبوعية
• الإشعارات والتنبيهات

تحسينات:
• تحسين الأداء والاستقرار
• إصلاح الأخطاء المعروفة
</ar>
```

**Example Release Notes (English - Optional):**
```xml
<en>
First release of Hosoony Quranic App

Features:
• Login for teachers and students
• Class management
• Daily memorization tracking
• Weekly tasks system
• Notifications and alerts

Improvements:
• Performance and stability improvements
• Bug fixes
</en>
```

**Template for your release notes:**
```xml
<ar>
الإصدار الأول - حصوني القرآني v1.0.0

المميزات الجديدة:
• [Feature 1 in Arabic]
• [Feature 2 in Arabic]
• [Feature 3 in Arabic]

التحسينات:
• [Improvement 1]
• [Improvement 2]

إصلاحات الأخطاء:
• [Bug fix 1]
• [Bug fix 2]
</ar>
```

---

## 📦 **Pre-Upload Checklist**

Before uploading to Google Play Console, ensure:

### **Build Requirements:**
- [ ] **Keystore configured** (`android/key.properties` exists)
- [ ] **Version updated** in `pubspec.yaml` (if needed)
- [ ] **App bundle built** successfully
- [ ] **AAB file verified** (exists and is not corrupted)

### **App Information:**
- [ ] **App name:** حصوني القرآني
- [ ] **Package name:** `com.hosoony.hosoony_flutter`
- [ ] **Version:** `1.0.0+1`
- [ ] **Minimum SDK:** Android 5.0 (API 21)
- [ ] **Target SDK:** Latest (check `android/app/build.gradle.kts`)

### **Store Listing (Must be completed first):**
- [ ] **Store listing** section completed
- [ ] **App description** provided (Arabic + English)
- [ ] **Screenshots** uploaded (required sizes)
- [ ] **App icon** uploaded
- [ ] **Privacy Policy** URL provided
- [ ] **Content rating** completed
- [ ] **Data safety** form completed

---

## 🚀 **Step-by-Step Upload Process**

### **Step 1: Build App Bundle**
```bash
cd /Users/ibraheem.designer/Documents/hosoony2/hosoony_flutter
flutter clean
flutter pub get
flutter build appbundle --release
```

### **Step 2: Verify AAB File**
```bash
# Check file exists
ls -lh build/app/outputs/bundle/release/app-release.aab

# Check file size (should be reasonable, typically 20-100 MB)
du -h build/app/outputs/bundle/release/app-release.aab
```

### **Step 3: Upload to Google Play Console**

1. **Go to:** https://play.google.com/console
2. **Select your app:** حصوني القرآني
3. **Navigate to:** Testing → Open testing → Releases
4. **Click:** "Create new release" or "Create release"

### **Step 4: Fill Release Form**

**App bundles section:**
- [ ] Click **"Upload"** or drag and drop `app-release.aab`
- [ ] Wait for upload to complete
- [ ] Verify version code is accepted

**Release details section:**
- [ ] **Release name:** Enter `v1.0.0 - Open Testing` (or your preferred name, max 50 chars)
- [ ] **Release notes:** 
  - Click in the text area
  - Paste your Arabic release notes with `<ar>` tags
  - Optionally add English with `<en>` tags

### **Step 5: Review and Save**

- [ ] Review all information
- [ ] Click **"Save"** (draft) or **"Review release"**
- [ ] Complete review process
- [ ] Submit for review

---

## 📝 **Release Notes Template**

Copy and customize this template:

```xml
<ar>
الإصدار الأول - حصوني القرآني

نظام إدارة التعلم القرآني الشامل

المميزات الرئيسية:
• تسجيل الدخول الآمن للمعلمين والطلاب
• إدارة الفصول الدراسية والطلاب
• متابعة الحفظ اليومي والمراجعة
• نظام المهام الأسبوعية
• الإشعارات والتنبيهات المهمة
• واجهة مستخدم عربية سلسة وسهلة

الإصدار الأول يتضمن:
• جميع الوظائف الأساسية
• دعم اللغة العربية بالكامل
• تصميم متجاوب لجميع أحجام الشاشات
• أمان عالي لحماية البيانات

نرحب بملاحظاتكم واقتراحاتكم!
</ar>

<en>
First Release - Hosoony Quranic App

Comprehensive Quranic Learning Management System

Main Features:
• Secure login for teachers and students
• Class and student management
• Daily memorization and review tracking
• Weekly tasks system
• Important notifications and alerts
• Smooth and easy Arabic user interface

First release includes:
• All core functionalities
• Full Arabic language support
• Responsive design for all screen sizes
• High security for data protection

We welcome your feedback and suggestions!
</en>
```

---

## ⚠️ **Important Notes**

### **Version Code:**
- Each release must have a **higher version code** than the previous one
- Current: `1.0.0+1` (versionName `1.0.0`, versionCode `1`)
- Next release: `1.0.1+2` or `1.0.0+2`

### **Release Name:**
- **Internal only** - users don't see this
- Use it to identify releases (e.g., "v1.0.0-beta", "v1.0.0-stable")
- Keep it short and descriptive

### **Release Notes:**
- **Visible to testers** in the Play Store
- **Required for each language** your app supports
- **Arabic is mandatory** for your app
- Can be updated later if needed

### **Testing Track:**
- **Open testing:** Anyone can join via link
- **Closed testing:** Invite-only
- **Internal testing:** Up to 100 testers

---

## 🔍 **Troubleshooting**

### **Upload Fails:**
- Check AAB file is not corrupted
- Verify keystore is correct
- Ensure version code is higher than previous

### **Release Notes Not Saving:**
- Make sure `<ar>` tags are correct
- Check character encoding (UTF-8)
- Try copying from a plain text editor

### **Version Code Error:**
- Increment version code in `pubspec.yaml`
- Rebuild AAB with new version
- Check previous releases' version codes

---

## ✅ **Final Checklist Before Submitting**

- [ ] AAB file uploaded successfully
- [ ] Release name entered (max 50 chars)
- [ ] Release notes provided in Arabic (with `<ar>` tags)
- [ ] Release notes provided in English (optional, with `<en>` tags)
- [ ] All information reviewed
- [ ] Store listing section completed
- [ ] Content rating completed
- [ ] Data safety form completed
- [ ] Privacy Policy URL provided
- [ ] Ready to submit for review

---

## 📚 **Related Documentation**

- **Full Publishing Guide:** `STORE_PUBLISHING_GUIDE.md`
- **Quick Checklist:** `QUICK_PUBLISH_CHECKLIST.md`
- **Build Guide:** `BUILD_APK_GUIDE.md`

---

**Need help?** Check the Google Play Console help center or review the existing documentation files in this project.




