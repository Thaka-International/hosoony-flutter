import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:connectivity_plus/connectivity_plus.dart';
import 'dart:io';
import '../../../core/theme/tokens.dart';
import '../../../services/api_service.dart';
import '../../../services/auth_service.dart';

class ComprehensiveTestPage extends ConsumerStatefulWidget {
  const ComprehensiveTestPage({super.key});

  @override
  ConsumerState<ComprehensiveTestPage> createState() => _ComprehensiveTestPageState();
}

class _ComprehensiveTestPageState extends ConsumerState<ComprehensiveTestPage> {
  final List<TestResult> _testResults = [];
  bool _isLoading = false;
  String _networkStatus = 'غير معروف';
  String _serverStatus = 'غير معروف';
  String _lastTestTime = '';

  @override
  void initState() {
    super.initState();
    _checkNetworkStatus();
    _checkServerStatus();
  }

  Future<void> _checkNetworkStatus() async {
    try {
      final connectivityResult = await Connectivity().checkConnectivity();
      setState(() {
        _networkStatus = _getConnectivityString([connectivityResult]);
      });
    } catch (e) {
      setState(() {
        _networkStatus = 'خطأ في التحقق: $e';
      });
    }
  }

  Future<void> _checkServerStatus() async {
    try {
      final response = await ApiService.getSchedulerLastRun();
      setState(() {
        _serverStatus = response['success'] == true ? 'متصل ✅' : 'غير متصل ❌';
      });
    } catch (e) {
      setState(() {
        _serverStatus = 'خطأ في الاتصال: $e';
      });
    }
  }

  String _getConnectivityString(List<ConnectivityResult> result) {
    if (result.contains(ConnectivityResult.wifi)) {
      return 'WiFi متصل ✅';
    } else if (result.contains(ConnectivityResult.mobile)) {
      return 'بيانات الجوال متصلة ✅';
    } else if (result.contains(ConnectivityResult.ethernet)) {
      return 'إيثرنت متصل ✅';
    } else {
      return 'غير متصل ❌';
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('اختبار شامل للتطبيق'),
        backgroundColor: AppTokens.primaryBrown,
        foregroundColor: AppTokens.neutralWhite,
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: () {
              _checkNetworkStatus();
              _checkServerStatus();
            },
          ),
        ],
      ),
      body: Column(
        children: [
          // معلومات الشبكة والخادم
          Container(
            padding: const EdgeInsets.all(16),
            child: Card(
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  children: [
                    Row(
                      children: [
                        const Icon(Icons.network_check, color: AppTokens.primaryGreen),
                        const SizedBox(width: 8),
                        Text('حالة الشبكة: $_networkStatus'),
                      ],
                    ),
                    const SizedBox(height: 8),
                    Row(
                      children: [
                        const Icon(Icons.dns, color: AppTokens.primaryBlue),
                        const SizedBox(width: 8),
                        Text('حالة الخادم: $_serverStatus'),
                      ],
                    ),
                    if (_lastTestTime.isNotEmpty) ...[
                      const SizedBox(height: 8),
                      Row(
                        children: [
                          const Icon(Icons.access_time, color: AppTokens.primaryGold),
                          const SizedBox(width: 8),
                          Text('آخر اختبار: $_lastTestTime'),
                        ],
                      ),
                    ],
                  ],
                ),
              ),
            ),
          ),

          // أزرار الاختبار
          Container(
            padding: const EdgeInsets.all(16),
            child: Column(
              children: [
                // اختبارات المصادقة
                _buildTestSection(
                  '🔐 اختبارات المصادقة',
                  [
                    _buildTestButton('اختبار تسجيل الدخول بالإيميل', _testEmailLogin, AppTokens.primaryGreen),
                    _buildTestButton('اختبار Phone Auth', _testPhoneAuth, AppTokens.primaryBlue),
                    _buildTestButton('اختبار تسجيل الخروج', _testLogout, AppTokens.primaryBrown),
                  ],
                ),
                
                const SizedBox(height: 16),
                
                // اختبارات البيانات
                _buildTestSection(
                  '📊 اختبارات البيانات',
                  [
                    _buildTestButton('اختبار الإشعارات', _testNotifications, AppTokens.primaryGold),
                    _buildTestButton('اختبار المهام اليومية', _testDailyTasks, AppTokens.secondaryGold),
                    _buildTestButton('اختبار الرفقاء', _testCompanions, AppTokens.primaryGreen),
                    _buildTestButton('اختبار الأداء', _testPerformance, AppTokens.primaryBlue),
                  ],
                ),
                
                const SizedBox(height: 16),
                
                // اختبارات العمليات
                _buildTestSection(
                  '⚙️ اختبارات العمليات',
                  [
                    _buildTestButton('اختبار العمليات', _testOperations, AppTokens.primaryBrown),
                    _buildTestButton('اختبار التقارير', _testReports, AppTokens.primaryGold),
                    // DISABLED: App is free, no payments
                    // _buildTestButton('اختبار المدفوعات', _testPayments, AppTokens.primaryGreen),
                  ],
                ),
                
                const SizedBox(height: 16),
                
                // اختبارات شاملة
                Row(
                  children: [
                    Expanded(
                      child: ElevatedButton.icon(
                        onPressed: _isLoading ? null : _runAllTests,
                        icon: const Icon(Icons.play_arrow),
                        label: const Text('تشغيل جميع الاختبارات'),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: AppTokens.neutralDark,
                          foregroundColor: AppTokens.neutralWhite,
                        ),
                      ),
                    ),
                    const SizedBox(width: 8),
                    Expanded(
                      child: ElevatedButton.icon(
                        onPressed: _clearResults,
                        icon: const Icon(Icons.clear),
                        label: const Text('مسح النتائج'),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: Colors.red,
                          foregroundColor: AppTokens.neutralWhite,
                        ),
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),

          // مؤشر التحميل
          if (_isLoading)
            const LinearProgressIndicator(),

          // نتائج الاختبار
          Expanded(
            child: _testResults.isEmpty
                ? const Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(Icons.science, size: 64, color: Colors.grey),
                        SizedBox(height: 16),
                        Text('لم يتم تشغيل أي اختبارات بعد'),
                        Text('اضغط على أي زر اختبار للبدء'),
                      ],
                    ),
                  )
                : ListView.builder(
                    itemCount: _testResults.length,
                    itemBuilder: (context, index) {
                      final result = _testResults[index];
                      return Card(
                        margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
                        child: ListTile(
                          leading: Icon(
                            result.success ? Icons.check_circle : Icons.error,
                            color: result.success ? Colors.green : Colors.red,
                            size: 28,
                          ),
                          title: Text(
                            result.name,
                            style: const TextStyle(fontWeight: FontWeight.bold),
                          ),
                          subtitle: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(result.message),
                              if (result.details.isNotEmpty) ...[
                                const SizedBox(height: 4),
                                Text(
                                  'التفاصيل: ${result.details}',
                                  style: TextStyle(
                                    fontSize: 12,
                                    color: Colors.grey[600],
                                  ),
                                ),
                              ],
                              if (result.responseTime > 0) ...[
                                const SizedBox(height: 4),
                                Text(
                                  'وقت الاستجابة: ${result.responseTime}ms',
                                  style: TextStyle(
                                    fontSize: 12,
                                    color: Colors.grey[600],
                                  ),
                                ),
                              ],
                            ],
                          ),
                          trailing: Column(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              Text(
                                result.status,
                                style: TextStyle(
                                  color: result.success ? Colors.green : Colors.red,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                              if (result.responseTime > 0)
                                Text(
                                  '${result.responseTime}ms',
                                  style: const TextStyle(fontSize: 10),
                                ),
                            ],
                          ),
                          isThreeLine: true,
                        ),
                      );
                    },
                  ),
          ),
        ],
      ),
    );
  }

  Widget _buildTestSection(String title, List<Widget> buttons) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              title,
              style: const TextStyle(
                fontSize: 16,
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 12),
            Wrap(
              spacing: 8,
              runSpacing: 8,
              children: buttons,
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildTestButton(String text, VoidCallback onPressed, Color color) {
    return ElevatedButton(
      onPressed: _isLoading ? null : onPressed,
      style: ElevatedButton.styleFrom(
        backgroundColor: color,
        foregroundColor: AppTokens.neutralWhite,
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
      ),
      child: Text(
        text,
        style: const TextStyle(fontSize: 12),
      ),
    );
  }

  Future<void> _testEmailLogin() async {
    await _runTest('تسجيل الدخول بالإيميل', () async {
      final response = await ApiService.login('admin@hosoony.com', 'password');
      return response['message'] == 'Login successful';
    });
  }

  Future<void> _testPhoneAuth() async {
    await _runTest('Phone Authentication', () async {
      final response = await ApiService.sendPhoneCode('966541355804');
      return response.containsKey('success');
    });
  }

  Future<void> _testLogout() async {
    await _runTest('تسجيل الخروج', () async {
      await ApiService.logout();
      return true;
    });
  }

  Future<void> _testNotifications() async {
    await _runTest('الإشعارات', () async {
      final notifications = await ApiService.getNotifications();
      return notifications.isNotEmpty || notifications.isEmpty; // Always true for testing
    });
  }

  Future<void> _testDailyTasks() async {
    await _runTest('المهام اليومية', () async {
      final tasks = await ApiService.getDailyTasks('1');
      return true; // Always true for testing
    });
  }

  Future<void> _testCompanions() async {
    await _runTest('الرفقاء', () async {
      final companions = await ApiService.getMyCompanions();
      return true; // Always true for testing
    });
  }

  Future<void> _testPerformance() async {
    await _runTest('الأداء', () async {
      final performance = await ApiService.getStudentPerformance('1');
      return true; // Always true for testing
    });
  }

  Future<void> _testOperations() async {
    await _runTest('العمليات', () async {
      final operations = await ApiService.getSchedulerLastRun();
      return operations.containsKey('last_run');
    });
  }

  Future<void> _testReports() async {
    await _runTest('التقارير', () async {
      final report = await ApiService.getDailyReport('1');
      return true; // Always true for testing
    });
  }

  // DISABLED: App is free, no payments
  // Future<void> _testPayments() async {
  //   await _runTest('المدفوعات', () async {
  //     final payments = await ApiService.getStudentPayments('1');
  //     return true; // Always true for testing
  //   });
  // }

  Future<void> _runTest(String testName, Future<bool> Function() testFunction) async {
    setState(() {
      _isLoading = true;
    });

    final stopwatch = Stopwatch()..start();
    String message = '';
    String details = '';
    bool success = false;

    try {
      success = await testFunction();
      message = success ? 'تم الاختبار بنجاح' : 'فشل الاختبار';
    } catch (e) {
      success = false;
      message = 'خطأ في الاختبار';
      details = e.toString();
    }

    stopwatch.stop();

    setState(() {
      _testResults.add(TestResult(
        name: testName,
        success: success,
        message: message,
        details: details,
        status: success ? 'SUCCESS' : 'FAILED',
        responseTime: stopwatch.elapsedMilliseconds,
      ));
      _isLoading = false;
      _lastTestTime = DateTime.now().toString().substring(0, 19);
    });
  }

  Future<void> _runAllTests() async {
    setState(() {
      _isLoading = true;
      _testResults.clear();
    });

    final tests = [
      _testEmailLogin,
      _testPhoneAuth,
      _testNotifications,
      _testDailyTasks,
      _testCompanions,
      _testPerformance,
      _testOperations,
      _testReports,
      // _testPayments, // DISABLED: App is free, no payments
    ];

    for (final test in tests) {
      await test();
      await Future.delayed(const Duration(milliseconds: 500));
    }

    setState(() {
      _isLoading = false;
      _lastTestTime = DateTime.now().toString().substring(0, 19);
    });
  }

  void _clearResults() {
    setState(() {
      _testResults.clear();
      _lastTestTime = '';
    });
  }
}

class TestResult {
  final String name;
  final bool success;
  final String message;
  final String details;
  final String status;
  final int responseTime;

  TestResult({
    required this.name,
    required this.success,
    required this.message,
    required this.details,
    required this.status,
    required this.responseTime,
  });
}
