# Hosoony Dart SDK

حزمة Dart SDK المولدة من وثائق OpenAPI لنظام حصوني القرآني.

## الوصف

هذا المستودع يحتوي على حزمة Dart SDK المولدة تلقائياً من وثائق OpenAPI لنظام حصوني القرآني. يوفر SDK واجهة برمجية سهلة الاستخدام للتفاعل مع API الخادم.

## الميزات

- **مولدة تلقائياً** من OpenAPI 3.1
- **Type-safe** مع Dart null safety
- **دعم كامل** لجميع endpoints
- **مصادقة تلقائية** مع Bearer Token
- **معالجة الأخطاء** المتقدمة
- **دعم التخزين المؤقت**
- **توثيق شامل** باللغة العربية

## التثبيت

### من Git Repository

```yaml
dependencies:
  hosoony_api:
    git:
      url: https://github.com/hosoony/hosoony-dart-sdk.git
      ref: main
```

### من المسار المحلي

```yaml
dependencies:
  hosoony_api:
    path: ../hosoony-dart-sdk
```

## الاستخدام

### 1. إعداد العميل

```dart
import 'package:hosoony_api/hosoony_api.dart';

void main() {
  // إعداد العميل
  final apiClient = ApiClient(
    baseUrl: 'https://api.hosoony.com/api/v1',
    timeout: Duration(seconds: 30),
  );
  
  // إعداد المصادقة
  apiClient.setAuthToken('your-bearer-token');
}
```

### 2. تسجيل الدخول

```dart
Future<void> login() async {
  try {
    final authApi = AuthApi(apiClient);
    
    final response = await authApi.login({
      'email': 'student@hosoony.com',
      'password': 'password123',
    });
    
    if (response.success == true) {
      // حفظ التوكن
      apiClient.setAuthToken(response.token!);
      
      // الانتقال للصفحة الرئيسية
      print('تم تسجيل الدخول بنجاح');
    }
  } catch (e) {
    print('فشل في تسجيل الدخول: $e');
  }
}
```

### 3. الحصول على رفيقات اليوم

```dart
Future<void> getCompanions() async {
  try {
    final companionsApi = CompanionsApi(apiClient);
    
    final companions = await companionsApi.getMeCompanions(
      date: '2025-10-07',
    );
    
    print('رفيقات اليوم: ${companions.companions}');
    print('رقم الغرفة: ${companions.roomNumber}');
    print('رابط الزوم: ${companions.zoomUrl}');
  } catch (e) {
    print('فشل في الحصول على الرفيقات: $e');
  }
}
```

### 4. إنشاء تقييم أداء

```dart
Future<void> createEvaluation() async {
  try {
    final evaluationApi = PerformanceEvaluationApi(apiClient);
    
    final evaluation = await evaluationApi.createPerformanceEvaluation({
      'student_id': 1,
      'session_id': 1,
      'scores': {
        'recitation': 8.5,
        'pronunciation': 9.0,
        'memorization': 7.5,
        'understanding': 8.0,
        'participation': 9.5,
      },
      'recommendations': 'تحسين في التلاوة والتركيز على التجويد',
      'improvement_areas': 'الحفظ والمراجعة',
    });
    
    print('تم إنشاء التقييم: ${evaluation.id}');
  } catch (e) {
    print('فشل في إنشاء التقييم: $e');
  }
}
```

### 5. الحصول على الإشعارات

```dart
Future<void> getNotifications() async {
  try {
    final notificationApi = NotificationApi(apiClient);
    
    final notifications = await notificationApi.getNotifications(
      page: 1,
      perPage: 10,
    );
    
    for (final notification in notifications.data) {
      print('${notification.title}: ${notification.message}');
    }
  } catch (e) {
    print('فشل في الحصول على الإشعارات: $e');
  }
}
```

## APIs المتاحة

### Authentication APIs
- `login()` - تسجيل الدخول
- `logout()` - تسجيل الخروج

### User APIs
- `getMe()` - الحصول على بيانات المستخدم الحالي

### Companions APIs
- `getMeCompanions()` - الحصول على رفيقات اليوم
- `generateCompanions()` - توليد الرفيقات (للمعلمين)
- `publishCompanions()` - نشر الرفيقات (للمعلمين)

### Performance Evaluation APIs
- `getPerformanceEvaluationsSessionsSessionId()` - الحصول على تقييمات الجلسة
- `createPerformanceEvaluation()` - إنشاء تقييم أداء
- `createBulkPerformanceEvaluation()` - إنشاء تقييمات متعددة
- `getPerformanceEvaluationsStudentHistory()` - تاريخ تقييمات الطالب

### Notification APIs
- `getNotifications()` - الحصول على الإشعارات
- `markNotificationAsRead()` - تحديد الإشعار كمقروء

### Payment APIs
- `getStudentPayments()` - الحصول على مدفوعات الطالب
- `createPaypalOrder()` - إنشاء طلب دفع PayPal
- `capturePaypalPayment()` - تأكيد دفع PayPal

## إدارة المصادقة

```dart
class AuthManager {
  static String? _token;
  static ApiClient? _apiClient;
  
  static void initialize(ApiClient apiClient) {
    _apiClient = apiClient;
  }
  
  static void setToken(String token) {
    _token = token;
    _apiClient?.setAuthToken(token);
  }
  
  static String? getToken() => _token;
  
  static void clearToken() {
    _token = null;
    _apiClient?.clearAuthToken();
  }
  
  static bool get isAuthenticated => _token != null;
}
```

## معالجة الأخطاء

```dart
try {
  final response = await api.someMethod();
  // معالجة الاستجابة الناجحة
} on ApiException catch (e) {
  // خطأ من API
  print('خطأ API: ${e.message}');
  print('كود الخطأ: ${e.statusCode}');
} on NetworkException catch (e) {
  // خطأ في الشبكة
  print('خطأ الشبكة: ${e.message}');
} catch (e) {
  // خطأ عام
  print('خطأ غير متوقع: $e');
}
```

## التكوين المتقدم

```dart
final apiClient = ApiClient(
  baseUrl: 'https://api.hosoony.com/api/v1',
  timeout: Duration(seconds: 30),
  retryPolicy: RetryPolicy(
    maxRetries: 3,
    retryDelay: Duration(seconds: 1),
  ),
  interceptors: [
    LoggingInterceptor(),
    AuthInterceptor(),
    CacheInterceptor(),
  ],
);
```

## التطوير

### إعادة توليد SDK

```bash
# من مستودع الباك-إند
make dart-sdk

# أو يدوياً
openapi-generator generate \
  -i ../hosoony-backend/public/openapi.yaml \
  -g dart \
  -o . \
  --additional-properties=pubName=hosoony_api,pubVersion=1.0.0
```

### الاختبار

```bash
# تشغيل الاختبارات
dart test

# تشغيل اختبارات محددة
dart test test/auth_test.dart
```

## الإصدارات

- **v1.0.0** - الإصدار الأولي مع جميع APIs الأساسية
- **v1.1.0** - إضافة دعم التخزين المؤقت
- **v1.2.0** - تحسينات الأداء ومعالجة الأخطاء

## المساهمة

1. Fork المستودع
2. إنشاء فرع للميزة الجديدة
3. Commit التغييرات
4. Push للفرع
5. إنشاء Pull Request

## الدعم

للحصول على الدعم أو الإبلاغ عن مشاكل:
- GitHub Issues: [رابط Issues]
- البريد الإلكتروني: support@hosoony.com

## الترخيص

هذا المشروع مرخص تحت رخصة MIT - راجع ملف [LICENSE](LICENSE) للتفاصيل.

---

**حصوني القرآني** - نحو تعلم قرآني أفضل 📚✨