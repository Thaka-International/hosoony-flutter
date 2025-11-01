import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:dio/dio.dart';
import '../../../core/debug/debug_service.dart';
import '../../../core/theme/tokens.dart';
import '../../../services/api_service.dart';

/// 🧪 صفحة اختبار شاملة لجميع APIs
class ComprehensiveApiTestPage extends ConsumerStatefulWidget {
  const ComprehensiveApiTestPage({super.key});

  @override
  ConsumerState<ComprehensiveApiTestPage> createState() => _ComprehensiveApiTestPageState();
}

class _ComprehensiveApiTestPageState extends ConsumerState<ComprehensiveApiTestPage> {
  final List<ApiTestResult> _testResults = [];
  bool _isRunningTests = false;
  final Dio _dio = Dio();

  @override
  void initState() {
    super.initState();
    _initializeDio();
  }

  void _initializeDio() {
    _dio.options.baseUrl = 'https://thakaa.me/api/v1';
    _dio.options.connectTimeout = const Duration(seconds: 30);
    _dio.options.receiveTimeout = const Duration(seconds: 30);
    _dio.options.headers = {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    };
    
    // إضافة token إذا كان متوفراً
    final token = ApiService.getToken();
    if (token != null) {
      _dio.options.headers['Authorization'] = 'Bearer $token';
    }
  }

  Future<void> _runAllTests() async {
    setState(() {
      _isRunningTests = true;
      _testResults.clear();
    });

    DebugService.info('بدء اختبار جميع APIs', 'API_TEST');

    // اختبار APIs المصادقة
    await _testAuthApis();
    
    // اختبار APIs المهام اليومية
    await _testDailyTasksApis();
    
    // اختبار APIs الرفيقات
    await _testCompanionsApis();
    
    // اختبار APIs المدفوعات
    await _testPaymentsApis();
    
    // اختبار APIs التقارير
    await _testReportsApis();
    
    // اختبار APIs الإشعارات
    await _testNotificationsApis();
    
    // اختبار APIs التقييمات
    await _testEvaluationsApis();

    setState(() {
      _isRunningTests = false;
    });

    DebugService.success('انتهى اختبار جميع APIs', 'API_TEST');
  }

  Future<void> _testAuthApis() async {
    DebugService.info('اختبار APIs المصادقة', 'API_TEST');
    
    // اختبار تسجيل الدخول بالإيميل
    await _testApi(
      'POST /auth/login',
      () => _dio.post('/auth/login', data: {
        'email': 'student.female1@hosoony.com',
        'password': 'password'
      }),
      'تسجيل الدخول بالإيميل'
    );

    // اختبار إرسال رمز التحقق للجوال
    await _testApi(
      'POST /phone-auth/send-code',
      () => _dio.post('/phone-auth/send-code', data: {
        'phone': '+966501234567'
      }),
      'إرسال رمز التحقق للجوال'
    );

    // اختبار التحقق من رمز الجوال
    await _testApi(
      'POST /phone-auth/verify-code',
      () => _dio.post('/phone-auth/verify-code', data: {
        'phone': '+966501234567',
        'code': '123456'
      }),
      'التحقق من رمز الجوال'
    );

    // اختبار إعادة إرسال الرمز
    await _testApi(
      'POST /phone-auth/resend-code',
      () => _dio.post('/phone-auth/resend-code', data: {
        'phone': '+966501234567'
      }),
      'إعادة إرسال رمز التحقق'
    );
  }

  Future<void> _testDailyTasksApis() async {
    DebugService.info('اختبار APIs المهام اليومية', 'API_TEST');
    
    // اختبار الحصول على المهام اليومية
    await _testApi(
      'GET /students/1/daily-tasks',
      () => _dio.get('/students/1/daily-tasks'),
      'الحصول على المهام اليومية'
    );

    // اختبار تسليم المهمة اليومية
    await _testApi(
      'POST /daily-logs/submit',
      () => _dio.post('/daily-logs/submit', data: {
        'student_id': 1,
        'task_id': 1,
        'completed_at': DateTime.now().toIso8601String(),
        'notes': 'تم إنجاز المهمة بنجاح'
      }),
      'تسليم المهمة اليومية'
    );

    // اختبار الحصول على سجل المهام اليومية
    await _testApi(
      'GET /students/1/daily-logs',
      () => _dio.get('/students/1/daily-logs'),
      'الحصول على سجل المهام اليومية'
    );
  }

  Future<void> _testCompanionsApis() async {
    DebugService.info('اختبار APIs الرفيقات', 'API_TEST');
    
    // اختبار توليد الرفيقات
    await _testApi(
      'POST /classes/1/companions/generate',
      () => _dio.post('/classes/1/companions/generate', data: {
        'target_date': DateTime.now().toIso8601String().split('T')[0]
      }),
      'توليد الرفيقات'
    );

    // اختبار قفل الرفيقات
    await _testApi(
      'PATCH /classes/1/companions/2025-01-01/lock',
      () => _dio.patch('/classes/1/companions/2025-01-01/lock'),
      'قفل الرفيقات'
    );

    // اختبار نشر الرفيقات
    await _testApi(
      'POST /classes/1/companions/2025-01-01/publish',
      () => _dio.post('/classes/1/companions/2025-01-01/publish'),
      'نشر الرفيقات'
    );

    // اختبار الحصول على رفيقاتي
    await _testApi(
      'GET /me/companions',
      () => _dio.get('/me/companions'),
      'الحصول على رفيقاتي'
    );
  }

  Future<void> _testPaymentsApis() async {
    DebugService.info('اختبار APIs المدفوعات', 'API_TEST');
    
    // اختبار الحصول على الاشتراك
    await _testApi(
      'GET /students/1/subscription',
      () => _dio.get('/students/1/subscription'),
      'الحصول على الاشتراك'
    );

    // اختبار تحديث الاشتراك
    await _testApi(
      'PATCH /students/1/subscription',
      () => _dio.patch('/students/1/subscription', data: {
        'plan': 'premium',
        'status': 'active'
      }),
      'تحديث الاشتراك'
    );

    // اختبار إنشاء دفعة
    await _testApi(
      'POST /students/1/payments',
      () => _dio.post('/students/1/payments', data: {
        'amount': 100.0,
        'method': 'bank_transfer',
        'description': 'دفعة شهرية'
      }),
      'إنشاء دفعة'
    );

    // اختبار الحصول على دفعات الطالب
    await _testApi(
      'GET /students/1/payments',
      () => _dio.get('/students/1/payments'),
      'الحصول على دفعات الطالب'
    );
  }

  Future<void> _testReportsApis() async {
    DebugService.info('اختبار APIs التقارير', 'API_TEST');
    
    // اختبار التقرير اليومي
    await _testApi(
      'GET /reports/daily/1',
      () => _dio.get('/reports/daily/1'),
      'التقرير اليومي'
    );

    // اختبار توليد التقرير الشهري
    await _testApi(
      'POST /reports/monthly/generate',
      () => _dio.post('/reports/monthly/generate', data: {
        'class_id': 1,
        'month': 1,
        'year': 2025
      }),
      'توليد التقرير الشهري'
    );

    // اختبار تصدير التقرير الشهري
    await _testApi(
      'GET /reports/monthly/1/export',
      () => _dio.get('/reports/monthly/1/export'),
      'تصدير التقرير الشهري'
    );
  }

  Future<void> _testNotificationsApis() async {
    DebugService.info('اختبار APIs الإشعارات', 'API_TEST');
    
    // اختبار الحصول على الإشعارات
    await _testApi(
      'GET /notifications',
      () => _dio.get('/notifications'),
      'الحصول على الإشعارات'
    );

    // اختبار تحديد الإشعار كمقروء
    await _testApi(
      'PATCH /notifications/1/read',
      () => _dio.patch('/notifications/1/read'),
      'تحديد الإشعار كمقروء'
    );

    // اختبار إرسال إشعار تجريبي
    await _testApi(
      'POST /notifications/test',
      () => _dio.post('/notifications/test', data: {
        'title': 'إشعار تجريبي',
        'body': 'هذا إشعار تجريبي للاختبار'
      }),
      'إرسال إشعار تجريبي'
    );
  }

  Future<void> _testEvaluationsApis() async {
    DebugService.info('اختبار APIs التقييمات', 'API_TEST');
    
    // اختبار الحصول على تقييمات الجلسة
    await _testApi(
      'GET /sessions/1/evaluations',
      () => _dio.get('/sessions/1/evaluations'),
      'الحصول على تقييمات الجلسة'
    );

    // اختبار إنشاء تقييم
    await _testApi(
      'POST /sessions/1/evaluations',
      () => _dio.post('/sessions/1/evaluations', data: {
        'student_id': 1,
        'score': 85,
        'notes': 'أداء جيد'
      }),
      'إنشاء تقييم'
    );

    // اختبار التقييم المجمع
    await _testApi(
      'POST /sessions/1/evaluations/bulk',
      () => _dio.post('/sessions/1/evaluations/bulk', data: {
        'evaluations': [
          {'student_id': 1, 'score': 85},
          {'student_id': 2, 'score': 90}
        ]
      }),
      'التقييم المجمع'
    );

    // اختبار الحصول على تقييم محدد
    await _testApi(
      'GET /evaluations/1',
      () => _dio.get('/evaluations/1'),
      'الحصول على تقييم محدد'
    );

    // اختبار الحصول على تاريخ أداء الطالب
    await _testApi(
      'GET /students/1/performance',
      () => _dio.get('/students/1/performance'),
      'الحصول على تاريخ أداء الطالب'
    );

    // اختبار الحصول على ملخص أداء الفصل
    await _testApi(
      'GET /classes/1/performance',
      () => _dio.get('/classes/1/performance'),
      'الحصول على ملخص أداء الفصل'
    );

    // اختبار الحصول على توصيات الطالب
    await _testApi(
      'GET /students/1/recommendations',
      () => _dio.get('/students/1/recommendations'),
      'الحصول على توصيات الطالب'
    );
  }

  Future<void> _testApi(String endpoint, Future<Response> Function() apiCall, String description) async {
    try {
      DebugService.info('اختبار: $endpoint', 'API_TEST');
      
      final stopwatch = Stopwatch()..start();
      final response = await apiCall();
      stopwatch.stop();
      
      // حفظ token إذا كان تسجيل دخول ناجح
      if (endpoint.contains('/auth/login') && response.statusCode == 200) {
        final token = response.data['token'];
        if (token != null) {
          ApiService.setToken(token);
          _dio.options.headers['Authorization'] = 'Bearer $token';
          DebugService.info('تم حفظ token للمصادقة', 'API_TEST');
        }
      }
      
      final result = ApiTestResult(
        endpoint: endpoint,
        description: description,
        status: response.statusCode ?? 0,
        success: response.statusCode != null && response.statusCode! >= 200 && response.statusCode! < 300,
        responseTime: stopwatch.elapsedMilliseconds,
        responseData: response.data,
        error: null,
      );
      
      setState(() {
        _testResults.add(result);
      });
      
      DebugService.success('نجح: $endpoint (${response.statusCode})', 'API_TEST');
      
    } catch (e) {
      final result = ApiTestResult(
        endpoint: endpoint,
        description: description,
        status: 0,
        success: false,
        responseTime: 0,
        responseData: null,
        error: e.toString(),
      );
      
      setState(() {
        _testResults.add(result);
      });
      
      DebugService.error('فشل: $endpoint', e, null, 'API_TEST');
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('اختبار شامل لجميع APIs'),
        backgroundColor: AppTokens.primaryGreen,
        foregroundColor: AppTokens.neutralWhite,
        actions: [
          IconButton(
            onPressed: _isRunningTests ? null : _runAllTests,
            icon: _isRunningTests 
                ? const SizedBox(
                    width: 20,
                    height: 20,
                    child: CircularProgressIndicator(strokeWidth: 2),
                  )
                : const Icon(Icons.play_arrow),
            tooltip: 'تشغيل جميع الاختبارات',
          ),
        ],
      ),
      body: Column(
        children: [
          // إحصائيات الاختبارات
          Container(
            padding: const EdgeInsets.all(16),
            color: AppTokens.neutralLight,
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceAround,
              children: [
                _buildStatCard('إجمالي', _testResults.length.toString(), Colors.blue),
                _buildStatCard('نجح', _testResults.where((r) => r.success).length.toString(), Colors.green),
                _buildStatCard('فشل', _testResults.where((r) => !r.success).length.toString(), Colors.red),
                _buildStatCard('متوسط الوقت', _getAverageResponseTime(), Colors.orange),
              ],
            ),
          ),
          
          // قائمة النتائج
          Expanded(
            child: _testResults.isEmpty
                ? const Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(Icons.science, size: 64, color: Colors.grey),
                        SizedBox(height: 16),
                        Text(
                          'اضغط على زر التشغيل لبدء اختبار جميع APIs',
                          style: TextStyle(fontSize: 16, color: Colors.grey),
                        ),
                      ],
                    ),
                  )
                : ListView.builder(
                    itemCount: _testResults.length,
                    itemBuilder: (context, index) {
                      final result = _testResults[index];
                      return _buildTestResultCard(result);
                    },
                  ),
          ),
        ],
      ),
    );
  }

  Widget _buildStatCard(String title, String value, Color color) {
    return Column(
      children: [
        Text(
          value,
          style: TextStyle(
            fontSize: 24,
            fontWeight: FontWeight.bold,
            color: color,
          ),
        ),
        Text(
          title,
          style: const TextStyle(fontSize: 12, color: Colors.grey),
        ),
      ],
    );
  }

  Widget _buildTestResultCard(ApiTestResult result) {
    return Card(
      margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
      child: ListTile(
        leading: Icon(
          result.success ? Icons.check_circle : Icons.error,
          color: result.success ? Colors.green : Colors.red,
        ),
        title: Text(
          result.endpoint,
          style: const TextStyle(fontWeight: FontWeight.bold),
        ),
        subtitle: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(result.description),
            const SizedBox(height: 4),
            Row(
              children: [
                Text('الحالة: ${result.status}'),
                const SizedBox(width: 16),
                Text('الوقت: ${result.responseTime}ms'),
              ],
            ),
            if (result.error != null)
              Text(
                'خطأ: ${result.error}',
                style: const TextStyle(color: Colors.red),
              ),
          ],
        ),
        trailing: IconButton(
          onPressed: () => _showResponseDetails(result),
          icon: const Icon(Icons.info_outline),
        ),
      ),
    );
  }

  void _showResponseDetails(ApiTestResult result) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: Text(result.endpoint),
        content: SingleChildScrollView(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            mainAxisSize: MainAxisSize.min,
            children: [
              Text('الوصف: ${result.description}'),
              const SizedBox(height: 8),
              Text('الحالة: ${result.status}'),
              const SizedBox(height: 8),
              Text('الوقت: ${result.responseTime}ms'),
              const SizedBox(height: 8),
              Text('النجاح: ${result.success ? "نعم" : "لا"}'),
              if (result.responseData != null) ...[
                const SizedBox(height: 8),
                const Text('الاستجابة:', style: TextStyle(fontWeight: FontWeight.bold)),
                const SizedBox(height: 4),
                Container(
                  padding: const EdgeInsets.all(8),
                  decoration: BoxDecoration(
                    color: Colors.grey[100],
                    borderRadius: BorderRadius.circular(4),
                  ),
                  child: Text(
                    result.responseData.toString(),
                    style: const TextStyle(fontFamily: 'monospace', fontSize: 12),
                  ),
                ),
              ],
              if (result.error != null) ...[
                const SizedBox(height: 8),
                const Text('الخطأ:', style: TextStyle(fontWeight: FontWeight.bold, color: Colors.red)),
                const SizedBox(height: 4),
                Container(
                  padding: const EdgeInsets.all(8),
                  decoration: BoxDecoration(
                    color: Colors.red[50],
                    borderRadius: BorderRadius.circular(4),
                  ),
                  child: Text(
                    result.error!,
                    style: const TextStyle(fontFamily: 'monospace', fontSize: 12, color: Colors.red),
                  ),
                ),
              ],
            ],
          ),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('إغلاق'),
          ),
        ],
      ),
    );
  }

  String _getAverageResponseTime() {
    if (_testResults.isEmpty) return '0ms';
    final totalTime = _testResults.fold<int>(0, (sum, result) => sum + result.responseTime);
    final average = totalTime / _testResults.length;
    return '${average.round()}ms';
  }
}

class ApiTestResult {
  final String endpoint;
  final String description;
  final int status;
  final bool success;
  final int responseTime;
  final dynamic responseData;
  final String? error;

  ApiTestResult({
    required this.endpoint,
    required this.description,
    required this.status,
    required this.success,
    required this.responseTime,
    required this.responseData,
    required this.error,
  });
}

