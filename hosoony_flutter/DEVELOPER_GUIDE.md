# 👨‍💻 دليل المطور - Flutter App

## 🚀 **بدء سريع للمطورين**

### **1. إعداد البيئة:**
```bash
# استنساخ المشروع
git clone https://github.com/Thaka-International/hosoony-flutter.git
cd hosoony-flutter

# تثبيت التبعيات
flutter pub get

# تشغيل التطبيق
flutter run
```

### **2. البناء للاختبار:**
```bash
# بناء APK للأندرويد
flutter build apk --release

# بناء تطبيق الويب
flutter build web --release

# بناء تطبيق iOS
flutter build ios --release
```

## 📁 **هيكل المشروع المفصل**

```
lib/
├── core/
│   ├── config/
│   │   └── env.dart              # إعدادات البيئة والـ API
│   ├── errors/
│   │   └── error_handler.dart    # معالجة الأخطاء
│   ├── router/
│   │   └── app_router.dart       # توجيه التطبيق
│   └── theme/
│       └── tokens.dart           # ألوان وخطوط التطبيق
├── data/
│   ├── api/
│   │   └── api_client.dart       # عميل HTTP
│   └── models/                   # نماذج البيانات
├── features/
│   ├── auth/
│   │   ├── pages/
│   │   │   ├── login_page.dart   # صفحة تسجيل الدخول
│   │   │   └── splash_page.dart  # صفحة البداية
│   │   └── providers/
│   │       └── auth_provider.dart # مزود المصادقة
│   ├── dashboard/                # لوحة التحكم
│   ├── tasks/                    # المهام اليومية
│   ├── companions/               # نظام الرفيقات
│   ├── payments/                 # المدفوعات
│   └── profile/                  # الملف الشخصي
├── services/
│   └── api_service.dart          # خدمات API
├── shared_ui/                    # مكونات واجهة مشتركة
└── main.dart                     # نقطة البداية
```

## 🔧 **التكوين والتخصيص**

### **تغيير URL الأساسي:**
```dart
// lib/core/config/env.dart
class Env {
  static const String baseUrl = 'https://your-api.com/api/v1';
  // أو للاختبار المحلي:
  // static const String baseUrl = 'http://localhost:8000/api/v1';
}
```

### **تخصيص الألوان:**
```dart
// lib/core/theme/tokens.dart
class AppTokens {
  static const Color primaryGreen = Color(0xFF228B22);
  static const Color successGreen = Color(0xFF32CD32);
  static const Color errorRed = Color(0xFFDC143C);
  // ... باقي الألوان
}
```

## 🔐 **المصادقة والـ API**

### **إعداد المصادقة:**
```dart
// lib/data/api/api_client.dart
class ApiClient {
  static void initialize() {
    _dio = Dio(BaseOptions(
      baseUrl: Env.baseUrl,
      connectTimeout: const Duration(seconds: 30),
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
    ));
    
    // إعداد المصادقة
    _dio.interceptors.add(AuthInterceptor());
  }
}
```

### **استخدام API:**
```dart
// مثال: الحصول على المهام اليومية
final response = await ApiService.getDailyTasks(studentId);
if (response['success']) {
  final tasks = response['tasks'];
  // معالجة البيانات
}
```

## 📱 **إدارة الحالة**

### **استخدام Riverpod:**
```dart
// إنشاء Provider
final authStateProvider = StateNotifierProvider<AuthNotifier, AuthState>((ref) {
  return AuthNotifier();
});

// استخدام Provider في الواجهة
class LoginPage extends ConsumerWidget {
  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final authState = ref.watch(authStateProvider);
    final authNotifier = ref.read(authStateProvider.notifier);
    
    return Scaffold(
      // واجهة المستخدم
    );
  }
}
```

## 🧪 **الاختبار**

### **اختبار الوحدات:**
```dart
// test/services/api_service_test.dart
void main() {
  group('ApiService', () {
    test('should login successfully', () async {
      // اختبار تسجيل الدخول
      final result = await ApiService.login('test@example.com', 'password');
      expect(result['success'], true);
    });
  });
}
```

### **اختبار الواجهات:**
```dart
// test/widget_test.dart
void main() {
  testWidgets('Login page should display correctly', (WidgetTester tester) async {
    await tester.pumpWidget(MyApp());
    
    expect(find.text('تسجيل الدخول'), findsOneWidget);
    expect(find.byType(TextField), findsNWidgets(2));
  });
}
```

## 🐛 **استكشاف الأخطاء**

### **مشاكل شائعة:**

1. **خطأ في الاتصال:**
   ```dart
   // تحقق من إعدادات الشبكة
   try {
     final response = await dio.get('/test');
   } on DioException catch (e) {
     print('خطأ في الاتصال: ${e.message}');
   }
   ```

2. **مشاكل المصادقة:**
   ```dart
   // تحقق من صحة التوكن
   if (token == null || token.isEmpty) {
     // إعادة توجيه لصفحة تسجيل الدخول
     context.go('/login');
   }
   ```

3. **مشاكل البناء:**
   ```bash
   # امسح الكاش
   flutter clean
   
   # أعد تثبيت التبعيات
   flutter pub get
   
   # أعد البناء
   flutter build apk --release
   ```

## 📊 **مراقبة الأداء**

### **تحليل الأداء:**
```bash
# تشغيل في وضع Profile
flutter run --profile

# تحليل الحجم
flutter build apk --analyze-size

# تحليل الأداء
flutter run --trace-startup
```

### **تحسين الأداء:**
```dart
// استخدام const constructors
const Text('نص ثابت')

// استخدام ListView.builder للقوائم الكبيرة
ListView.builder(
  itemCount: items.length,
  itemBuilder: (context, index) => ItemWidget(items[index]),
)

// استخدام FutureBuilder للبيانات غير المتزامنة
FutureBuilder<List<Task>>(
  future: ApiService.getTasks(),
  builder: (context, snapshot) {
    if (snapshot.hasData) {
      return ListView.builder(/* ... */);
    }
    return CircularProgressIndicator();
  },
)
```

## 🔄 **سير العمل**

### **إضافة ميزة جديدة:**
1. أنشئ فرع جديد: `git checkout -b feature/new-feature`
2. اعمل التغييرات
3. اكتب الاختبارات
4. اعمل commit: `git commit -m "Add new feature"`
5. ادفع الفرع: `git push origin feature/new-feature`
6. أنشئ Pull Request

### **إصلاح خطأ:**
1. أنشئ فرع للإصلاح: `git checkout -b fix/bug-description`
2. أصلح الخطأ
3. اختبر الإصلاح
4. اعمل commit: `git commit -m "Fix: bug description"`
5. ادفع الفرع وأنشئ Pull Request

## 📚 **المراجع المفيدة**

### **وثائق Flutter:**
- [Flutter Documentation](https://flutter.dev/docs)
- [Dart Language Tour](https://dart.dev/guides/language/language-tour)
- [Riverpod Documentation](https://riverpod.dev/)

### **أدوات التطوير:**
- [Flutter Inspector](https://flutter.dev/docs/development/tools/flutter-inspector)
- [Dart DevTools](https://dart.dev/tools/dart-devtools)
- [Flutter Performance](https://flutter.dev/docs/perf)

### **مكتبات مفيدة:**
- [Dio](https://pub.dev/packages/dio) - HTTP client
- [Riverpod](https://pub.dev/packages/flutter_riverpod) - State management
- [Flutter Secure Storage](https://pub.dev/packages/flutter_secure_storage) - Secure storage

## 🤝 **المساهمة**

### **معايير الكود:**
- استخدم `dart format` لتنسيق الكود
- اكتب تعليقات واضحة باللغة العربية
- اتبع مبادئ Clean Architecture
- اكتب اختبارات للكود الجديد

### **إرشادات الـ Commit:**
```bash
# للميزات الجديدة
git commit -m "✨ Add new feature: feature name"

# للإصلاحات
git commit -m "🐛 Fix: bug description"

# للوثائق
git commit -m "📚 Update documentation"

# للتحسينات
git commit -m "⚡ Improve: performance optimization"
```

## 📞 **الدعم**

### **للحصول على المساعدة:**
- 📧 **البريد الإلكتروني:** dev@hosoony.com
- 💬 **Slack:** #flutter-dev
- 🐛 **GitHub Issues:** للمشاكل التقنية

### **للمراجعة:**
- 👥 **Code Review:** مطلوب لجميع التغييرات
- 🧪 **Testing:** اختبار شامل قبل الدمج
- 📋 **Documentation:** تحديث الوثائق عند الحاجة

---

**تم إعداده بواسطة:** Development Team  
**لأكاديمية ذكاء للتدريب** 🎓  
**© 2025 Thaka International** 🌟




















