# 📱 Store Publishing Guide - حصوني القرآني

Complete guide for publishing to Google Play Store and Apple App Store.

---

## 📋 **Prerequisites Checklist**

### ✅ General Requirements
- [ ] Flutter SDK 3.0+ installed
- [ ] Developer accounts:
  - [ ] Google Play Console account ($25 one-time fee)
  - [ ] Apple Developer account ($99/year)
- [ ] App information ready:
  - [ ] App name: **حصوني القرآني**
  - [ ] Description (Arabic + English)
  - [ ] Screenshots (required sizes for both platforms)
  - [ ] App icon (1024x1024 for iOS, 512x512 for Android)
  - [ ] Privacy Policy URL
  - [ ] Support URL/Email
- [ ] Version: `1.0.0+1` (versionName+versionCode/buildNumber)

---

## 🤖 **ANDROID - Google Play Store**

### **Step 1: Generate Release Keystore**

```bash
cd /Users/ibraheem.designer/Documents/hosoony2/hosoony_flutter
./android/generate_keystore.sh
```

**Important:**
- Store passwords in a secure password manager
- Backup the keystore file (`android/keystore/hosoony-release-key.jks`) in multiple secure locations
- **Never lose the keystore!** You cannot update the app without it.

### **Step 2: Configure key.properties**

```bash
cp android/key.properties.template android/key.properties
```

Edit `android/key.properties` and fill in:
```properties
storePassword=your-keystore-password
keyPassword=your-key-password
keyAlias=hosoony-release-key
storeFile=../keystore/hosoony-release-key.jks
```

### **Step 3: Update Version (Optional)**

Edit `pubspec.yaml`:
```yaml
version: 1.0.0+1  # Format: versionName+buildNumber
```

For subsequent releases:
- Increment versionName (e.g., 1.0.1)
- Increment buildNumber (e.g., +2, +3, etc.)

### **Step 4: Build App Bundle (AAB)**

```bash
flutter clean
flutter pub get
flutter build appbundle --release
```

Output: `build/app/outputs/bundle/release/app-release.aab`

**Note:** Google Play Store requires AAB format, not APK.

### **Step 5: Test the Build Locally (Optional)**

```bash
# Build APK for testing
flutter build apk --release

# Install on connected device
adb install build/app/outputs/flutter-apk/app-release.apk
```

### **Step 6: Google Play Console Setup**

1. **Create App:**
   - Go to https://play.google.com/console
   - Click "Create app"
   - Fill in:
     - App name: **حصوني القرآني**
     - Default language: **Arabic (العربية)**
     - App type: **App**
     - Free or Paid: **Free**
     - Declare: Yes (GDPR)

2. **Complete Store Listing:**
   - App details (description, screenshots, etc.)
   - Graphics: Icon, feature graphic, screenshots
   - Categorization: Education
   - Content rating questionnaire

3. **Set Up App Content:**
   - Privacy Policy URL (required)
   - Target audience
   - Ads (if applicable)
   - Data safety form

4. **Upload AAB:**
   - Go to "Production" → "Create new release"
   - Upload `app-release.aab`
   - Add release notes
   - Review and rollout

5. **Complete App Access:**
   - Content rating
   - Pre-launch report
   - Pricing & distribution

### **Step 7: Submit for Review**

- All sections must be completed (green checkmarks)
- Click "Submit for review"
- Review typically takes 1-3 days

---

## 🍎 **iOS - Apple App Store**

### **Step 1: Apple Developer Account Setup**

1. **Enroll in Apple Developer Program:**
   - Go to https://developer.apple.com/programs/
   - Enroll ($99/year)
   - Wait for approval (can take 24-48 hours)

2. **Create App ID in App Store Connect:**
   - Go to https://appstoreconnect.apple.com
   - My Apps → "+" → New App
   - Bundle ID: `com.hosoony.hosoonyFlutter`
   - App name: **حصوني القرآني**
   - Primary language: **Arabic**
   - SKU: `hosoony-flutter-001`

### **Step 2: Configure Xcode Project**

1. **Open in Xcode:**
   ```bash
   open ios/Runner.xcworkspace
   ```

2. **Set Bundle Identifier:**
   - Select Runner target
   - General tab → Bundle Identifier: `com.hosoony.hosoonyFlutter`
   - Team: Select your Apple Developer team

3. **Configure Signing:**
   - Signing & Capabilities tab
   - Automatically manage signing: ✅ Enabled
   - Team: Your Apple Developer team
   - Xcode will create certificates and provisioning profiles automatically

4. **Update Version:**
   - General tab → Version: `1.0.0`
   - Build: `1`
   - (Or use `pubspec.yaml` version)

### **Step 3: Update iOS Info.plist**

Verify `ios/Runner/Info.plist` has:
- ✅ Display name: **حصوني القرآني**
- ✅ All permission descriptions (Camera, Photos, etc.)
- ✅ Bundle identifier (already set)

### **Step 4: Build for Release**

```bash
flutter clean
flutter pub get
flutter build ipa --release
```

Output: `build/ios/ipa/hosoony_flutter.ipa`

**Alternative (using Xcode):**
1. Open `ios/Runner.xcworkspace`
2. Product → Archive
3. Organizer window opens
4. Distribute App → App Store Connect

### **Step 5: App Store Connect Setup**

1. **Complete App Information:**
   - App Store Information
   - Pricing and Availability
   - Version Information:
     - What's New (Release Notes)
     - Screenshots (all required sizes)
     - Description
     - Keywords
     - Support URL
     - Marketing URL (optional)

2. **Content Rights:**
   - Export compliance
   - Content rights

3. **App Privacy:**
   - Privacy Policy URL (required)
   - Data collection practices

4. **Upload Build:**
   - Use Xcode Organizer or `flutter build ipa`
   - Upload via Xcode or Transporter app
   - Wait for processing (10-30 minutes)

### **Step 6: Submit for Review**

1. **Create New Version:**
   - Select the uploaded build
   - Fill in all required information
   - Screenshots (all required sizes for each device type)

2. **App Review Information:**
   - Contact information
   - Demo account (if login required)
   - Notes (any special instructions)

3. **Submit:**
   - Click "Submit for Review"
   - Review typically takes 1-7 days

---

## 📸 **Required Assets**

### **Google Play Store:**
- **App Icon:** 512x512 PNG (no transparency)
- **Feature Graphic:** 1024x500 PNG
- **Screenshots:** 
  - Phone: At least 2, up to 8 (16:9 or 9:16)
  - Tablet: At least 1 (7", 10")
  - Recommended: 1080x1920 or 1920x1080

### **Apple App Store:**
- **App Icon:** 1024x1024 PNG (no transparency)
- **Screenshots:**
  - iPhone 6.7" (1290x2796): At least 1, up to 10
  - iPhone 6.5" (1242x2688): At least 1, up to 10
  - iPhone 5.5" (1242x2208): Optional
  - iPad Pro 12.9" (2048x2732): Optional
  - iPad Pro 11" (1668x2388): Optional

**Tools for Screenshots:**
- Use `flutter screenshot` command
- Or device emulators with proper dimensions

---

## 🔐 **Security Checklist**

### **Before Publishing:**
- [ ] Remove debug keys
- [ ] Disable debug mode
- [ ] Remove test/development API endpoints
- [ ] Review permissions (only request what's needed)
- [ ] Implement app signing (✅ Done for Android)
- [ ] Set up app attestation (optional but recommended)

### **Environment Variables:**
Verify production API endpoints in your app:
- Backend API: `https://thakaa.me/api/v1`
- Ensure no hardcoded secrets

---

## 📝 **Version Management**

### **Format:**
```yaml
version: 1.0.0+1
```
- `1.0.0` = Version Name (shown to users)
- `+1` = Build Number (internal, must increment)

### **For Updates:**
- **Patch update:** `1.0.1+2` (bug fixes)
- **Minor update:** `1.1.0+3` (new features)
- **Major update:** `2.0.0+4` (breaking changes)

### **Version History:**
- `1.0.0+1` - Initial release

---

## 🚨 **Common Issues & Solutions**

### **Android:**

**Issue: "Upload failed"**
- Solution: Ensure AAB is signed correctly
- Verify `key.properties` exists and is correct

**Issue: "Version code already exists"**
- Solution: Increment build number in `pubspec.yaml`

### **iOS:**

**Issue: "No signing certificate found"**
- Solution: 
  - Xcode → Preferences → Accounts
  - Add Apple ID
  - Download certificates

**Issue: "Invalid Bundle Identifier"**
- Solution: Register Bundle ID in App Store Connect first

**Issue: "ITMS-90704: Missing App Icon"**
- Solution: Ensure 1024x1024 icon is in `ios/Runner/Assets.xcassets/AppIcon.appiconset`

---

## 📞 **Support Information**

**For Store Issues:**
- Google Play Support: https://support.google.com/googleplay/android-developer
- Apple Developer Support: https://developer.apple.com/support/

**App Information:**
- **Name:** حصوني القرآني (Hosoony Al-Qurani)
- **Package:** `com.hosoony.hosoony_flutter` (Android)
- **Bundle ID:** `com.hosoony.hosoonyFlutter` (iOS)
- **Version:** 1.0.0+1

---

## ✅ **Final Checklist Before Submission**

### **Android:**
- [ ] Keystore generated and backed up
- [ ] AAB built successfully
- [ ] All store listing sections completed
- [ ] Privacy policy URL added
- [ ] Screenshots uploaded (all required sizes)
- [ ] App tested on multiple devices
- [ ] Version code incremented

### **iOS:**
- [ ] Apple Developer account active
- [ ] App ID registered in App Store Connect
- [ ] IPA built and uploaded
- [ ] All store listing sections completed
- [ ] Privacy policy URL added
- [ ] Screenshots uploaded (all required sizes)
- [ ] App tested on real iOS devices
- [ ] Version and build number set correctly

---

## 🎉 **After Approval**

### **Monitor:**
- User reviews and ratings
- Crash reports (Firebase Crashlytics)
- Analytics (if implemented)
- App performance metrics

### **Updates:**
- Regularly update app with bug fixes
- Add new features based on user feedback
- Keep dependencies updated

---

**Good luck with your app launch! 🚀**

For questions or issues, refer to:
- Flutter Documentation: https://docs.flutter.dev/deployment
- Google Play Console Help: https://support.google.com/googleplay/android-developer
- App Store Connect Help: https://help.apple.com/app-store-connect/


