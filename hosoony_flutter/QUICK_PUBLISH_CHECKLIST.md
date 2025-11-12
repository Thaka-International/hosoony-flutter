# ⚡ Quick Publish Checklist

## 🚀 Pre-Flight Checklist (5 minutes)

### **1. Generate Android Keystore**
```bash
cd hosoony_flutter
./android/generate_keystore.sh
# Follow prompts, save passwords securely
```

### **2. Configure key.properties**
```bash
cp android/key.properties.template android/key.properties
# Edit with your passwords
```

### **3. Verify Version**
```yaml
# pubspec.yaml
version: 1.0.0+1  # ✅ Current
```

---

## 🤖 **Android - Google Play (30 minutes)**

```bash
# Build
flutter clean && flutter pub get
flutter build appbundle --release

# File: build/app/outputs/bundle/release/app-release.aab
```

**Then:**
1. Go to https://play.google.com/console
2. Create app → Upload AAB
3. Complete store listing → Submit

---

## 🍎 **iOS - App Store (45 minutes)**

```bash
# Build
flutter clean && flutter pub get
flutter build ipa --release

# Or use Xcode:
# open ios/Runner.xcworkspace
# Product → Archive → Distribute
```

**Then:**
1. Go to https://appstoreconnect.apple.com
2. Create app (Bundle ID: `com.hosoony.hosoonyFlutter`)
3. Upload build → Complete listing → Submit

---

## 📋 **Required Information**

- [ ] App Name: **حصوني القرآني**
- [ ] Description (Arabic + English)
- [ ] Privacy Policy URL
- [ ] Support Email
- [ ] App Icon (1024x1024)
- [ ] Screenshots (see STORE_PUBLISHING_GUIDE.md)

---

## 🔑 **Critical Files**

**Keep Safe:**
- `android/keystore/hosoony-release-key.jks`
- `android/key.properties` (with passwords)

**Never commit these to Git!**

---

## ✅ **Done When:**
- [ ] Android: AAB uploaded, all sections green, submitted
- [ ] iOS: IPA uploaded, all sections complete, submitted
- [ ] Both: Waiting for review

---

**Full details:** See `STORE_PUBLISHING_GUIDE.md`








