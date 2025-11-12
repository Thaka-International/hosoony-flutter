# 📱 Android 7-Inch Tablet Emulator Setup Guide

This guide will help you create and configure an Android emulator for a 7-inch tablet device.

## Method 1: Using Android Studio (Recommended)

### Step 1: Open AVD Manager
1. Open **Android Studio**
2. Go to **Tools** → **Device Manager** (or **AVD Manager** in older versions)
3. Click **Create Device**

### Step 2: Select Hardware Profile
1. In the **Virtual Device Configuration** window, select **Tablet** category
2. Choose one of these options:
   - **7.0" WSVGA (Tablet)** - 1024 x 600, mdpi
   - **7.0" WSVGA (Tablet)** - 1024 x 600, hdpi
   - Or click **New Hardware Profile** to create custom

### Step 3: Create Custom 7-Inch Tablet (If needed)
If you want to create a custom 7-inch tablet:

1. Click **New Hardware Profile**
2. Configure:
   - **Name**: `7_inch_tablet`
   - **Device Type**: Tablet
   - **Screen Size**: 7.0"
   - **Resolution**: 
     - Width: 1024 px
     - Height: 600 px
   - **Screen Density**: mdpi (160 dpi) or hdpi (240 dpi)
   - **RAM**: 2048 MB (minimum)
3. Click **Finish**

### Step 4: Select System Image
1. Choose an **Android version** (recommended: Android 11 or 12)
2. Download the system image if not already downloaded
3. Click **Next**

### Step 5: Configure AVD
1. **AVD Name**: `7_inch_tablet_android_11` (or your preferred name)
2. **Startup orientation**: Portrait or Landscape
3. **Graphics**: 
   - **Automatic** (recommended)
   - Or **Hardware - GLES 2.0** for better performance
4. Click **Finish**

### Step 6: Launch Emulator
1. Click the **Play** button (▶) next to your AVD
2. Wait for the emulator to boot
3. Your 7-inch tablet emulator is ready!

---

## Method 2: Using Command Line (avdmanager)

### Prerequisites
Make sure you have Android SDK command-line tools installed and `ANDROID_HOME` is set.

### Step 1: List Available System Images
```bash
# Check available system images
$ANDROID_HOME/cmdline-tools/latest/bin/sdkmanager --list | grep "system-images"

# Or if using older SDK tools
android list sdk | grep "system-images"
```

### Step 2: Create Hardware Profile (Optional)
```bash
# Create a hardware profile for 7-inch tablet
$ANDROID_HOME/cmdline-tools/latest/bin/avdmanager create avd \
  -n "7_inch_tablet" \
  -k "system-images;android-31;google_apis;x86_64" \
  -d "7.0in WSVGA Tablet" \
  -c 2048M
```

### Step 3: Create AVD with Custom Configuration
```bash
# Create AVD with specific configuration
$ANDROID_HOME/cmdline-tools/latest/bin/avdmanager create avd \
  -n "7_inch_tablet_android_11" \
  -k "system-images;android-31;google_apis;x86_64" \
  -d "7.0in WSVGA Tablet"
```

### Step 4: Edit AVD Configuration (Optional)
Edit the AVD config file to customize screen size:
```bash
# Location: ~/.android/avd/7_inch_tablet_android_11.avd/config.ini
# Or: $ANDROID_HOME/.android/avd/7_inch_tablet_android_11.avd/config.ini
```

Add or modify these lines:
```ini
hw.lcd.density=160
hw.lcd.width=1024
hw.lcd.height=600
skin.name=1024x600
skin.path=1024x600
```

### Step 5: Launch Emulator
```bash
# Launch the emulator
$ANDROID_HOME/emulator/emulator -avd 7_inch_tablet_android_11

# Or with specific options
$ANDROID_HOME/emulator/emulator -avd 7_inch_tablet_android_11 \
  -skin 1024x600 \
  -gpu host
```

---

## Method 3: Quick Script Setup

I'll create a script to automate the setup process.

### Run the Setup Script
```bash
cd /Users/ibraheem.designer/Documents/hosoony2/hosoony_flutter
chmod +x setup_7inch_tablet_emulator.sh
./setup_7inch_tablet_emulator.sh
```

---

## Recommended Specifications for 7-Inch Tablet

| Property | Value |
|---------|-------|
| Screen Size | 7.0 inches |
| Resolution | 1024 x 600 (WXGA) |
| Density | mdpi (160 dpi) or hdpi (240 dpi) |
| RAM | 2048 MB (minimum), 4096 MB (recommended) |
| Storage | 2048 MB (minimum) |
| Android Version | Android 11 (API 31) or Android 12 (API 32) |

---

## Testing with Flutter

### List Available Devices
```bash
cd /Users/ibraheem.designer/Documents/hosoony2/hosoony_flutter
flutter devices
```

### Run Flutter App on 7-Inch Tablet Emulator
```bash
# Make sure emulator is running first
flutter run -d <device-id>

# Or if only one device is available
flutter run
```

### Build APK for Testing
```bash
flutter build apk --debug
```

---

## Troubleshooting

### Emulator Won't Start
```bash
# Check if emulator is already running
adb devices

# Kill existing emulator processes
pkill -9 qemu-system-x86_64

# Check available AVDs
$ANDROID_HOME/cmdline-tools/latest/bin/avdmanager list avd
```

### Screen Size Issues
- Make sure the AVD configuration has correct dimensions
- Check `config.ini` file in the AVD directory
- Restart Android Studio if changes don't apply

### Performance Issues
- Enable hardware acceleration in AVD settings
- Allocate more RAM (4096 MB recommended)
- Use x86_64 system images for better performance

### Flutter Can't Detect Emulator
```bash
# Restart ADB
adb kill-server
adb start-server

# Check devices
flutter devices
```

---

## Quick Reference Commands

```bash
# List all AVDs
$ANDROID_HOME/cmdline-tools/latest/bin/avdmanager list avd

# Delete an AVD
$ANDROID_HOME/cmdline-tools/latest/bin/avdmanager delete avd -n <avd-name>

# Launch emulator
$ANDROID_HOME/emulator/emulator -avd <avd-name> &

# Check running devices
adb devices

# Flutter run on specific device
flutter run -d <device-id>
```

---

## Next Steps

1. ✅ Create the 7-inch tablet emulator using one of the methods above
2. ✅ Launch the emulator
3. ✅ Run your Flutter app: `flutter run`
4. ✅ Test your app's tablet-specific layouts and features

---

## Additional Resources

- [Android Emulator Documentation](https://developer.android.com/studio/run/emulator)
- [Flutter Device Setup](https://docs.flutter.dev/get-started/install/macos)
- [AVD Manager Guide](https://developer.android.com/studio/run/managing-avds)




