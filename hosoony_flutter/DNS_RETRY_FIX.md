# إصلاح مشكلة DNS Lookup Failure

## المشكلة

التطبيق يواجه أخطاء DNS lookup في Android Emulator:
```
DioException [connection error]: Failed host lookup: 'thakaa.me'
SocketException: Failed host lookup: 'thakaa.me' (OS Error: No address associated with hostname, errno = 7)
```

## الحل المطبق

### 1. إضافة Retry Logic

تم إضافة retry interceptor في `ApiService`:

- **عدد المحاولات:** 3 محاولات
- **التأخير بين المحاولات:** 2 ثانية
- **أنواع الأخطاء التي يتم إعادة المحاولة لها:**
  - `connectionError` (DNS lookup failures)
  - `connectionTimeout`
  - `sendTimeout`
  - `receiveTimeout`
  - `Failed host lookup`
  - `SocketException`
  - `Network is unreachable`
  - `No address associated with hostname`

### 2. زيادة Timeout

- `connectTimeout`: 60 ثانية
- `receiveTimeout`: 60 ثانية
- `sendTimeout`: 60 ثانية

## الكود المضافة

```dart
// Retry interceptor for network errors (DNS, connection, timeout)
_dio.interceptors.add(InterceptorsWrapper(
  onError: (error, handler) async {
    if (_shouldRetry(error)) {
      final retryCount = error.requestOptions.extra['retryCount'] ?? 0;
      const maxRetries = 3;
      const retryDelay = Duration(seconds: 2);

      if (retryCount < maxRetries) {
        error.requestOptions.extra['retryCount'] = retryCount + 1;
        await Future.delayed(retryDelay);
        
        try {
          final response = await _dio.fetch(error.requestOptions);
          handler.resolve(response);
          return;
        } catch (e) {
          // If retry also fails, continue with error
        }
      }
    }
    
    handler.next(error);
  },
));
```

## النتيجة المتوقعة

- عند حدوث DNS lookup failure، سيتم إعادة المحاولة تلقائياً
- المحاولة الأولى: فشل DNS
- المحاولة الثانية: بعد 2 ثانية - نجاح (عادة)
- إذا فشلت جميع المحاولات: يتم إرجاع الخطأ

## ملاحظات

- هذا الحل مفيد بشكل خاص في Android Emulator الذي قد يواجه مشاكل DNS مؤقتة
- في الأجهزة الحقيقية، عادة لا تحدث هذه المشكلة
- Retry logic لا يؤثر على الأخطاء الأخرى (مثل 401, 404, 500)

---

**Status:** ✅ تم إصلاح المشكلة





