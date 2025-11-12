# 🚀 Quick Start for Flutter Developers

## 📱 **Project Overview**
This is the Flutter app for Hosoony Quran Learning Management System - a comprehensive educational platform for Quranic studies.

## ⚡ **Quick Setup**

### **1. Clone & Install:**
```bash
git clone https://github.com/Thaka-International/hosoony-flutter.git
cd hosoony-flutter
flutter pub get
```

### **2. Run the App:**
```bash
# Android
flutter run

# Web  
flutter run -d chrome

# iOS
flutter run -d ios
```

### **3. Build for Production:**
```bash
# Android APK
flutter build apk --release

# Web App
flutter build web --release

# iOS App
flutter build ios --release
```

## 🔧 **Key Configuration**

### **API Base URL:**
```dart
// lib/core/config/env.dart
static const String baseUrl = 'https://thakaa.me/api/v1';
```

### **Authentication:**
- Email/Password login
- Phone number verification
- Bearer token authentication

## 📁 **Main Features**

- 🔐 **Authentication:** Login with email or phone
- 📚 **Daily Tasks:** Task management and tracking
- 👥 **Companions:** Virtual room assignments
- 💳 **Payments:** Subscription and billing
- 📊 **Performance:** Student evaluation system
- 🔔 **Notifications:** Multi-channel notifications

## 🛠️ **Tech Stack**

- **Flutter 3.0+**
- **Dart 3.0+**
- **Riverpod** (State Management)
- **Dio** (HTTP Client)
- **Flutter Secure Storage**

## 📚 **Documentation**

- **README.md** - Complete project overview
- **DEVELOPER_GUIDE.md** - Detailed development guide
- **API Documentation** - Available at `/docs` endpoint

## 🐛 **Common Issues**

### **Build Errors:**
```bash
flutter clean
flutter pub get
flutter build apk --release
```

### **Connection Issues:**
- Check network connectivity
- Verify API base URL
- Check authentication token

## 🤝 **Contributing**

1. Fork the repository
2. Create feature branch
3. Make changes
4. Test thoroughly
5. Submit pull request

## 📞 **Support**

- **Email:** dev@hosoony.com
- **GitHub Issues:** For technical problems
- **Documentation:** Check project docs

---

**Ready to develop? Start with `flutter run` and explore the codebase!** 🚀




















