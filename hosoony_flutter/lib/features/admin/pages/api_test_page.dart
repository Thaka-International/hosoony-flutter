import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/theme/tokens.dart';
import '../../../services/api_service.dart';
import '../../../services/auth_service.dart';

class ApiTestPage extends ConsumerStatefulWidget {
  const ApiTestPage({super.key});

  @override
  ConsumerState<ApiTestPage> createState() => _ApiTestPageState();
}

class _ApiTestPageState extends ConsumerState<ApiTestPage> {
  final List<Map<String, dynamic>> _testResults = [];
  bool _isLoading = false;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('اختبار APIs'),
        backgroundColor: AppTokens.primaryBrown,
        foregroundColor: AppTokens.neutralWhite,
      ),
      body: Column(
        children: [
          // أزرار الاختبار
          Container(
            padding: const EdgeInsets.all(16),
            child: Column(
              children: [
                Row(
                  children: [
                    Expanded(
                      child: ElevatedButton(
                        onPressed: _isLoading ? null : _testPhoneAuth,
                        style: ElevatedButton.styleFrom(
                          backgroundColor: AppTokens.primaryGreen,
                          foregroundColor: AppTokens.neutralWhite,
                        ),
                        child: const Text('اختبار Phone Auth'),
                      ),
                    ),
                    const SizedBox(width: 8),
                    Expanded(
                      child: ElevatedButton(
                        onPressed: _isLoading ? null : _testNotifications,
                        style: ElevatedButton.styleFrom(
                          backgroundColor: AppTokens.primaryGold,
                          foregroundColor: AppTokens.neutralWhite,
                        ),
                        child: const Text('اختبار الإشعارات'),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 8),
                Row(
                  children: [
                    Expanded(
                      child: ElevatedButton(
                        onPressed: _isLoading ? null : _testDailyTasks,
                        style: ElevatedButton.styleFrom(
                          backgroundColor: AppTokens.primaryBrown,
                          foregroundColor: AppTokens.neutralWhite,
                        ),
                        child: const Text('اختبار المهام اليومية'),
                      ),
                    ),
                    const SizedBox(width: 8),
                    Expanded(
                      child: ElevatedButton(
                        onPressed: _isLoading ? null : _testCompanions,
                        style: ElevatedButton.styleFrom(
                          backgroundColor: AppTokens.secondaryGold,
                          foregroundColor: AppTokens.neutralWhite,
                        ),
                        child: const Text('اختبار الرفقاء'),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 8),
                Row(
                  children: [
                    Expanded(
                      child: ElevatedButton(
                        onPressed: _isLoading ? null : _testAllApis,
                        style: ElevatedButton.styleFrom(
                          backgroundColor: AppTokens.neutralDark,
                          foregroundColor: AppTokens.neutralWhite,
                        ),
                        child: const Text('اختبار جميع APIs'),
                      ),
                    ),
                    const SizedBox(width: 8),
                    Expanded(
                      child: ElevatedButton(
                        onPressed: _clearResults,
                        style: ElevatedButton.styleFrom(
                          backgroundColor: Colors.red,
                          foregroundColor: AppTokens.neutralWhite,
                        ),
                        child: const Text('مسح النتائج'),
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
            child: ListView.builder(
              itemCount: _testResults.length,
              itemBuilder: (context, index) {
                final result = _testResults[index];
                return Card(
                  margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
                  child: ListTile(
                    leading: Icon(
                      result['success'] ? Icons.check_circle : Icons.error,
                      color: result['success'] ? Colors.green : Colors.red,
                    ),
                    title: Text(result['name']),
                    subtitle: Text(result['message']),
                    trailing: Text(
                      result['status'],
                      style: TextStyle(
                        color: result['success'] ? Colors.green : Colors.red,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ),
                );
              },
            ),
          ),
        ],
      ),
    );
  }

  Future<void> _testPhoneAuth() async {
    setState(() {
      _isLoading = true;
    });

    try {
      // اختبار إرسال رمز التحقق
      final response = await ApiService.sendPhoneCode('966541355804');
      
      setState(() {
        _testResults.add({
          'name': 'Phone Auth - Send Code',
          'success': response['success'] == true,
          'message': response['message'] ?? 'تم الاختبار',
          'status': response['success'] == true ? 'SUCCESS' : 'FAILED',
        });
      });
    } catch (e) {
      setState(() {
        _testResults.add({
          'name': 'Phone Auth - Send Code',
          'success': false,
          'message': e.toString(),
          'status': 'ERROR',
        });
      });
    } finally {
      setState(() {
        _isLoading = false;
      });
    }
  }

  Future<void> _testNotifications() async {
    setState(() {
      _isLoading = true;
    });

    try {
      final notifications = await ApiService.getNotifications();
      
      setState(() {
        _testResults.add({
          'name': 'Notifications - Get',
          'success': true,
          'message': 'تم الحصول على ${notifications.length} إشعار',
          'status': 'SUCCESS',
        });
      });
    } catch (e) {
      setState(() {
        _testResults.add({
          'name': 'Notifications - Get',
          'success': false,
          'message': e.toString(),
          'status': 'ERROR',
        });
      });
    } finally {
      setState(() {
        _isLoading = false;
      });
    }
  }

  Future<void> _testDailyTasks() async {
    setState(() {
      _isLoading = true;
    });

    try {
      final authState = ref.read(authStateProvider);
      final userId = authState.user?.id.toString() ?? '1';
      
      final tasks = await ApiService.getDailyTasks(userId);
      
      setState(() {
        _testResults.add({
          'name': 'Daily Tasks - Get',
          'success': true,
          'message': 'تم الحصول على ${tasks.length} مهمة',
          'status': 'SUCCESS',
        });
      });
    } catch (e) {
      setState(() {
        _testResults.add({
          'name': 'Daily Tasks - Get',
          'success': false,
          'message': e.toString(),
          'status': 'ERROR',
        });
      });
    } finally {
      setState(() {
        _isLoading = false;
      });
    }
  }

  Future<void> _testCompanions() async {
    setState(() {
      _isLoading = true;
    });

    try {
      final companions = await ApiService.getMyCompanions();
      
      setState(() {
        _testResults.add({
          'name': 'Companions - Get',
          'success': true,
          'message': 'تم الحصول على ${companions.length} رفيق',
          'status': 'SUCCESS',
        });
      });
    } catch (e) {
      setState(() {
        _testResults.add({
          'name': 'Companions - Get',
          'success': false,
          'message': e.toString(),
          'status': 'ERROR',
        });
      });
    } finally {
      setState(() {
        _isLoading = false;
      });
    }
  }

  Future<void> _testAllApis() async {
    setState(() {
      _isLoading = true;
      _testResults.clear();
    });

    final tests = [
      _testPhoneAuth,
      _testNotifications,
      _testDailyTasks,
      _testCompanions,
    ];

    for (final test in tests) {
      await test();
      await Future.delayed(const Duration(milliseconds: 500));
    }

    setState(() {
      _isLoading = false;
    });
  }

  void _clearResults() {
    setState(() {
      _testResults.clear();
    });
  }
}





















