import 'dart:developer' as developer;
import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:logger/logger.dart';

/// 🔧 خدمة التصحيح الشاملة للتطبيق
class DebugService {
  static final Logger _logger = Logger(
    printer: PrettyPrinter(
      methodCount: 2,
      errorMethodCount: 8,
      lineLength: 120,
      colors: true,
      printEmojis: true,
      dateTimeFormat: DateTimeFormat.onlyTimeAndSinceStart,
    ),
  );

  static bool _isDebugMode = kDebugMode;

  /// تفعيل/إلغاء وضع التصحيح
  static void setDebugMode(bool enabled) {
    _isDebugMode = enabled;
    _logger.i('🔧 وضع التصحيح: ${enabled ? "مفعل" : "معطل"}');
  }

  /// تسجيل معلومات عامة
  static void info(String message, [String? tag]) {
    if (_isDebugMode) {
      _logger.i('ℹ️ ${tag != null ? '[$tag] ' : ''}$message');
      developer.log(message, name: tag ?? 'INFO');
    }
  }

  /// تسجيل تحذير
  static void warning(String message, [String? tag]) {
    if (_isDebugMode) {
      _logger.w('⚠️ ${tag != null ? '[$tag] ' : ''}$message');
      developer.log(message, name: tag ?? 'WARNING', level: 900);
    }
  }

  /// تسجيل خطأ
  static void error(String message, [dynamic error, StackTrace? stackTrace, String? tag]) {
    if (_isDebugMode) {
      _logger.e('❌ ${tag != null ? '[$tag] ' : ''}$message', error: error, stackTrace: stackTrace);
      developer.log(message, name: tag ?? 'ERROR', level: 1000, error: error, stackTrace: stackTrace);
    }
  }

  /// تسجيل نجاح عملية
  static void success(String message, [String? tag]) {
    if (_isDebugMode) {
      _logger.i('✅ ${tag != null ? '[$tag] ' : ''}$message');
      developer.log(message, name: tag ?? 'SUCCESS');
    }
  }

  /// تسجيل طلب API
  static void apiRequest(String method, String url, [Map<String, dynamic>? data]) {
    if (_isDebugMode) {
      _logger.d('🌐 API Request: $method $url');
      if (data != null) {
        _logger.d('📤 Data: $data');
      }
    }
  }

  /// تسجيل استجابة API
  static void apiResponse(String method, String url, int statusCode, [dynamic data]) {
    if (_isDebugMode) {
      final emoji = statusCode >= 200 && statusCode < 300 ? '✅' : '❌';
      _logger.d('$emoji API Response: $method $url - $statusCode');
      if (data != null) {
        _logger.d('📥 Response: $data');
      }
    }
  }

  /// تسجيل خطأ API
  static void apiError(String method, String url, dynamic error) {
    if (_isDebugMode) {
      _logger.e('❌ API Error: $method $url', error: error);
    }
  }

  /// تسجيل حالة المصادقة
  static void authState(String state, [Map<String, dynamic>? data]) {
    if (_isDebugMode) {
      _logger.i('🔐 Auth State: $state');
      if (data != null) {
        _logger.d('👤 User Data: $data');
      }
    }
  }

  /// تسجيل التنقل
  static void navigation(String from, String to) {
    if (_isDebugMode) {
      _logger.i('🧭 Navigation: $from → $to');
    }
  }

  /// تسجيل أداء التطبيق
  static void performance(String operation, Duration duration) {
    if (_isDebugMode) {
      _logger.i('⚡ Performance: $operation took ${duration.inMilliseconds}ms');
    }
  }

  /// عرض نافذة تصحيح تفاعلية
  static void showDebugOverlay(BuildContext context) {
    if (!_isDebugMode) return;
    
    showDialog(
      context: context,
      builder: (context) => const DebugOverlay(),
    );
  }
}

/// نافذة التصحيح التفاعلية
class DebugOverlay extends StatefulWidget {
  const DebugOverlay({super.key});

  @override
  State<DebugOverlay> createState() => _DebugOverlayState();
}

class _DebugOverlayState extends State<DebugOverlay> {
  @override
  Widget build(BuildContext context) {
    return Dialog(
      child: Container(
        width: MediaQuery.of(context).size.width * 0.9,
        height: MediaQuery.of(context).size.height * 0.8,
        padding: const EdgeInsets.all(16),
        child: Column(
          children: [
            Text(
              '🔧 أدوات التصحيح',
              style: Theme.of(context).textTheme.headlineSmall,
            ),
            const SizedBox(height: 16),
            Expanded(
              child: SingleChildScrollView(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    _buildDebugSection('معلومات التطبيق', [
                      'الإصدار: 1.0.0',
                      'وضع التصحيح: ${kDebugMode ? "مفعل" : "معطل"}',
                      'المنصة: ${Theme.of(context).platform.name}',
                    ]),
                    const SizedBox(height: 16),
                    _buildDebugSection('حالة الشبكة', [
                      'URL الأساسي: https://thakaa.me/api/v1',
                      'الحالة: غير متاح',
                    ]),
                    const SizedBox(height: 16),
                    _buildDebugSection('حالة المصادقة', [
                      'مصادق: لا',
                      'المستخدم: غير محدد',
                    ]),
                  ],
                ),
              ),
            ),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceEvenly,
              children: [
                ElevatedButton(
                  onPressed: () => Navigator.pop(context),
                  child: const Text('إغلاق'),
                ),
                ElevatedButton(
                  onPressed: () {
                    // إعادة تشغيل التطبيق
                  },
                  child: const Text('إعادة تشغيل'),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildDebugSection(String title, List<String> items) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          title,
          style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
        ),
        const SizedBox(height: 8),
        ...items.map((item) => Padding(
          padding: const EdgeInsets.only(left: 16, bottom: 4),
          child: Text(item),
        )),
      ],
    );
  }
}
