# 🚀 Quick Start - Debug Tools & Login Bypass

## 📱 **Project Overview**
Flutter app for Hosoony Quran Learning Management System with comprehensive debug tools and login bypass for testing all user types and pages.

## ⚡ **Quick Setup**

### **1. Clone & Install:**
```bash
git clone https://github.com/Thaka-International/hosoony-flutter.git
cd hosoony-flutter
flutter pub get
```

### **2. Run the App:**
```bash
# Android (with debug tools)
flutter run

# Web  
flutter run -d chrome

# iOS
flutter run -d ios
```

### **3. Build Debug APK:**
```bash
# Debug APK with all debug tools
flutter build apk --debug
```

---

## 🔧 **Debug Tools Usage**

### **Login Bypass (Skip Authentication):**
1. Open the app in debug mode
2. On login page, tap "تجاوز تسجيل الدخول" (Login Bypass)
3. Choose user type:
   - 👩‍🎓 **Student** - Access student pages
   - 👩‍🏫 **Teacher** - Access teacher pages
   - 👩‍💼 **Assistant** - Access assistant pages
   - 👨‍💼 **Admin** - Access admin pages
   - 👨‍🔧 **Sub Admin** - Access sub-admin pages
   - 👩‍🔧 **Teacher Support** - Access teacher support pages

### **Debug Tools:**
- Tap "أدوات التصحيح" (Debug Tools) for interactive debug overlay
- All operations are logged automatically to console
- API requests/responses are monitored
- Performance metrics are tracked

---

## 🛠️ **Available User Types**

| User Type | Role | Access Level | Pages Available |
|-----------|------|--------------|-----------------|
| Student | `student` | Student features | Dashboard, Tasks, Companions, Points |
| Teacher | `teacher` | Teaching features | Dashboard, Reports, Evaluations |
| Assistant | `assistant` | Support features | Dashboard, Student management |
| Admin | `admin` | Full access | All admin features |
| Sub Admin | `sub_admin` | Limited admin | Admin features (limited) |
| Teacher Support | `teacher_support` | Teacher support | Support features |

---

## 🔍 **Debug Features**

### **Comprehensive Logging:**
- 📝 **Info Logging** - General operations
- ⚠️ **Warning Logging** - Potential issues
- ❌ **Error Logging** - Errors with details
- ✅ **Success Logging** - Successful operations
- 🌐 **API Logging** - All API requests/responses
- 🔐 **Auth Logging** - Authentication states
- 🧭 **Navigation Logging** - Page transitions
- ⚡ **Performance Logging** - Execution times

### **Interactive Debug Overlay:**
- App information display
- Network status
- Authentication status
- App restart functionality

---

## 📋 **Testing Checklist**

### **Test All User Types:**
- [ ] Login as Student - Test student features
- [ ] Login as Teacher - Test teacher features
- [ ] Login as Assistant - Test assistant features
- [ ] Login as Admin - Test admin features
- [ ] Login as Sub Admin - Test sub-admin features
- [ ] Login as Teacher Support - Test support features

### **Test All Pages:**
- [ ] Dashboard pages for each user type
- [ ] Task management pages
- [ ] Companion system pages
- [ ] Payment pages
- [ ] Report pages
- [ ] Profile pages

### **Debug Tools Testing:**
- [ ] Debug overlay functionality
- [ ] Console logging
- [ ] API monitoring
- [ ] Performance tracking

---

## 🎯 **Key Benefits**

### **For Developers:**
- Quick testing of all pages and features
- Comprehensive error logging
- Better understanding of app flow
- Time-saving testing process

### **For Team:**
- Easier testing of new features
- Reduced production errors
- Better problem documentation
- Faster development cycle

---

## 🔧 **Configuration**

### **Debug Settings:**
```dart
// lib/core/config/env.dart
static const bool isDebugMode = true;
static const bool enableDebugLogging = true;
static const bool enableApiLogging = true;
static const bool enablePerformanceLogging = true;
static const bool enableLoginBypass = true;
```

### **API Configuration:**
```dart
static const String baseUrl = 'https://thakaa.me/api/v1';
```

---

## 🚨 **Security Notes**

### **⚠️ Important:**
- Debug tools work only in debug mode (`kDebugMode`)
- Login bypass uses test data only
- All debug features are disabled in production builds
- Never share debug APK with end users

---

## 📱 **Build Commands**

### **Debug Build (with all tools):**
```bash
flutter build apk --debug
```

### **Release Build (production):**
```bash
flutter build apk --release
```

### **Web Build:**
```bash
flutter build web --release
```

---

## 🧪 **Testing Workflow**

### **1. Start Testing:**
```bash
flutter run
```

### **2. Use Login Bypass:**
- Tap "تجاوز تسجيل الدخول"
- Select user type
- Test all pages for that user type

### **3. Monitor Debug Info:**
- Check console logs
- Use debug overlay
- Monitor API calls

### **4. Test Different User Types:**
- Repeat process for each user type
- Verify all features work correctly
- Check navigation and permissions

---

## 📞 **Support**

### **For Help:**
- **Email:** dev@hosoony.com
- **GitHub Issues:** For technical problems
- **Debug Tools:** Use built-in debug overlay

### **Documentation:**
- **README_DEBUG.md** - Complete debug guide
- **DEBUG_TOOLS_GUIDE.md** - Detailed usage guide

---

**Ready to test? Start with `flutter run` and use login bypass to explore all features!** 🚀











