# Hosoony Flutter APK Build Guide

## Configuration Updated
✅ **API URL**: Updated to `https://thakaa.me/api/v1`
✅ **Debug Mode**: Set to `false` for production
✅ **Document Root**: Fixed for backend deployment

## Build APK Commands

### Prerequisites
Make sure you have Flutter SDK installed and configured.

### Step 1: Clean and Get Dependencies
```bash
cd /Users/ibraheem.designer/Documents/hosoony2/hosoony_flutter
flutter clean
flutter pub get
```

### Step 2: Build Release APK
```bash
# Build APK for release
flutter build apk --release

# Build APK with specific target architecture (optional)
flutter build apk --release --target-platform android-arm64
```

### Step 3: Locate Built APK
The APK will be created at:
```
build/app/outputs/flutter-apk/app-release.apk
```

### Step 4: Build App Bundle (for Google Play Store)
```bash
# Build AAB for Google Play Store
flutter build appbundle --release
```

The AAB will be created at:
```
build/app/outputs/bundle/release/app-release.aab
```

## Build Options

### Debug APK (for testing)
```bash
flutter build apk --debug
```

### Release APK (for production)
```bash
flutter build apk --release
```

### Split APKs by Architecture
```bash
flutter build apk --release --split-per-abi
```

This creates separate APKs for:
- `app-arm64-v8a-release.apk` (64-bit ARM)
- `app-armeabi-v7a-release.apk` (32-bit ARM)
- `app-x86_64-release.apk` (64-bit x86)

## APK Information
- **App Name**: حصوني القرآني
- **Package**: com.hosoony.hosoony_flutter
- **Version**: 1.0.0
- **API Endpoint**: https://thakaa.me/api/v1
- **Backend**: https://thakaa.me/api

## Installation
1. Enable "Unknown Sources" on Android device
2. Transfer APK to device
3. Install APK
4. Launch app and test with backend

## Testing
After installation, test:
- Login functionality
- API connectivity
- All app features
- Backend integration
