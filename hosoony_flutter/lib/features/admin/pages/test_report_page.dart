import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter/services.dart';
import 'dart:convert';
import '../../../core/theme/tokens.dart';

class TestReportPage extends ConsumerStatefulWidget {
  final List<TestResult> testResults;
  final String networkStatus;
  final String serverStatus;
  final String testTime;

  const TestReportPage({
    super.key,
    required this.testResults,
    required this.networkStatus,
    required this.serverStatus,
    required this.testTime,
  });

  @override
  ConsumerState<TestReportPage> createState() => _TestReportPageState();
}

class _TestReportPageState extends ConsumerState<TestReportPage> {
  @override
  Widget build(BuildContext context) {
    final totalTests = widget.testResults.length;
    final passedTests = widget.testResults.where((r) => r.success).length;
    final failedTests = totalTests - passedTests;
    final successRate = totalTests > 0 ? (passedTests / totalTests * 100).round() : 0;
    final avgResponseTime = totalTests > 0 
        ? widget.testResults.map((r) => r.responseTime).reduce((a, b) => a + b) / totalTests
        : 0;

    return Scaffold(
      appBar: AppBar(
        title: const Text('تقرير الاختبار'),
        backgroundColor: AppTokens.primaryBrown,
        foregroundColor: AppTokens.neutralWhite,
        actions: [
          IconButton(
            icon: const Icon(Icons.share),
            onPressed: _shareReport,
          ),
          IconButton(
            icon: const Icon(Icons.download),
            onPressed: _exportReport,
          ),
        ],
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // ملخص الاختبار
            Card(
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'ملخص الاختبار',
                      style: TextStyle(
                        fontSize: 20,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(height: 16),
                    
                    Row(
                      children: [
                        Expanded(
                          child: _buildStatCard(
                            'إجمالي الاختبارات',
                            totalTests.toString(),
                            Icons.science,
                            AppTokens.primaryBlue,
                          ),
                        ),
                        const SizedBox(width: 8),
                        Expanded(
                          child: _buildStatCard(
                            'نجح',
                            passedTests.toString(),
                            Icons.check_circle,
                            AppTokens.primaryGreen,
                          ),
                        ),
                      ],
                    ),
                    
                    const SizedBox(height: 8),
                    
                    Row(
                      children: [
                        Expanded(
                          child: _buildStatCard(
                            'فشل',
                            failedTests.toString(),
                            Icons.error,
                            Colors.red,
                          ),
                        ),
                        const SizedBox(width: 8),
                        Expanded(
                          child: _buildStatCard(
                            'معدل النجاح',
                            '$successRate%',
                            Icons.trending_up,
                            AppTokens.primaryGold,
                          ),
                        ),
                      ],
                    ),
                    
                    const SizedBox(height: 16),
                    
                    Row(
                      children: [
                        Expanded(
                          child: _buildStatCard(
                            'متوسط وقت الاستجابة',
                            '${avgResponseTime.round()}ms',
                            Icons.timer,
                            AppTokens.primaryBrown,
                          ),
                        ),
                        const SizedBox(width: 8),
                        Expanded(
                          child: _buildStatCard(
                            'وقت الاختبار',
                            widget.testTime,
                            Icons.access_time,
                            AppTokens.secondaryGold,
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            ),

            const SizedBox(height: 16),

            // حالة النظام
            Card(
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'حالة النظام',
                      style: TextStyle(
                        fontSize: 18,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(height: 12),
                    
                    Row(
                      children: [
                        const Icon(Icons.network_check, color: AppTokens.primaryGreen),
                        const SizedBox(width: 8),
                        Text('الشبكة: ${widget.networkStatus}'),
                      ],
                    ),
                    
                    const SizedBox(height: 8),
                    
                    Row(
                      children: [
                        const Icon(Icons.dns, color: AppTokens.primaryBlue),
                        const SizedBox(width: 8),
                        Text('الخادم: ${widget.serverStatus}'),
                      ],
                    ),
                  ],
                ),
              ),
            ),

            const SizedBox(height: 16),

            // تفاصيل الاختبارات
            Card(
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'تفاصيل الاختبارات',
                      style: TextStyle(
                        fontSize: 18,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(height: 12),
                    
                    ...widget.testResults.map((result) => Padding(
                      padding: const EdgeInsets.only(bottom: 8),
                      child: Row(
                        children: [
                          Icon(
                            result.success ? Icons.check_circle : Icons.error,
                            color: result.success ? Colors.green : Colors.red,
                            size: 20,
                          ),
                          const SizedBox(width: 8),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  result.name,
                                  style: const TextStyle(fontWeight: FontWeight.bold),
                                ),
                                Text(
                                  result.message,
                                  style: TextStyle(
                                    fontSize: 12,
                                    color: Colors.grey[600],
                                  ),
                                ),
                                if (result.details.isNotEmpty)
                                  Text(
                                    'التفاصيل: ${result.details}',
                                    style: TextStyle(
                                      fontSize: 10,
                                      color: Colors.grey[500],
                                    ),
                                  ),
                              ],
                            ),
                          ),
                          Column(
                            children: [
                              Text(
                                result.status,
                                style: TextStyle(
                                  color: result.success ? Colors.green : Colors.red,
                                  fontWeight: FontWeight.bold,
                                  fontSize: 12,
                                ),
                              ),
                              Text(
                                '${result.responseTime}ms',
                                style: const TextStyle(fontSize: 10),
                              ),
                            ],
                          ),
                        ],
                      ),
                    )),
                  ],
                ),
              ),
            ),

            const SizedBox(height: 16),

            // توصيات
            Card(
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'التوصيات',
                      style: TextStyle(
                        fontSize: 18,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(height: 12),
                    
                    ..._generateRecommendations(successRate, failedTests, avgResponseTime.round().toDouble()),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildStatCard(String title, String value, IconData icon, Color color) {
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: color.withOpacity(0.1),
        borderRadius: BorderRadius.circular(8),
        border: Border.all(color: color.withOpacity(0.3)),
      ),
      child: Column(
        children: [
          Icon(icon, color: color, size: 24),
          const SizedBox(height: 8),
          Text(
            value,
            style: TextStyle(
              fontSize: 16,
              fontWeight: FontWeight.bold,
              color: color,
            ),
          ),
          const SizedBox(height: 4),
          Text(
            title,
            style: const TextStyle(fontSize: 12),
            textAlign: TextAlign.center,
          ),
        ],
      ),
    );
  }

  List<Widget> _generateRecommendations(int successRate, int failedTests, double avgResponseTime) {
    List<Widget> recommendations = [];

    if (successRate < 80) {
      recommendations.add(_buildRecommendation(
        Icons.warning,
        Colors.orange,
        'معدل النجاح منخفض',
        'يجب مراجعة APIs التي فشلت وإصلاحها',
      ));
    }

    if (failedTests > 0) {
      recommendations.add(_buildRecommendation(
        Icons.error,
        Colors.red,
        'اختبارات فاشلة',
        'تحقق من اتصال الشبكة وإعدادات الخادم',
      ));
    }

    if (avgResponseTime > 5000) {
      recommendations.add(_buildRecommendation(
        Icons.timer_off,
        Colors.orange,
        'بطء في الاستجابة',
        'تحسين أداء الخادم أو الشبكة',
      ));
    }

    if (successRate >= 90 && avgResponseTime < 2000) {
      recommendations.add(_buildRecommendation(
        Icons.check_circle,
        Colors.green,
        'أداء ممتاز',
        'جميع الأنظمة تعمل بشكل مثالي',
      ));
    }

    if (recommendations.isEmpty) {
      recommendations.add(_buildRecommendation(
        Icons.info,
        Colors.blue,
        'لا توجد مشاكل',
        'جميع الاختبارات تمت بنجاح',
      ));
    }

    return recommendations;
  }

  Widget _buildRecommendation(IconData icon, Color color, String title, String description) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child: Row(
        children: [
          Icon(icon, color: color, size: 20),
          const SizedBox(width: 8),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  title,
                  style: TextStyle(
                    fontWeight: FontWeight.bold,
                    color: color,
                  ),
                ),
                Text(
                  description,
                  style: const TextStyle(fontSize: 12),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  void _shareReport() {
    final report = _generateTextReport();
    Clipboard.setData(ClipboardData(text: report));
    
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(
        content: Text('تم نسخ التقرير إلى الحافظة'),
        backgroundColor: Colors.green,
      ),
    );
  }

  void _exportReport() {
    final report = _generateJsonReport();
    Clipboard.setData(ClipboardData(text: report));
    
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(
        content: Text('تم نسخ التقرير بصيغة JSON إلى الحافظة'),
        backgroundColor: Colors.blue,
      ),
    );
  }

  String _generateTextReport() {
    final buffer = StringBuffer();
    
    buffer.writeln('تقرير اختبار تطبيق حصوني القرآني');
    buffer.writeln('=' * 50);
    buffer.writeln('وقت الاختبار: ${widget.testTime}');
    buffer.writeln('حالة الشبكة: ${widget.networkStatus}');
    buffer.writeln('حالة الخادم: ${widget.serverStatus}');
    buffer.writeln('');
    
    final totalTests = widget.testResults.length;
    final passedTests = widget.testResults.where((r) => r.success).length;
    final successRate = totalTests > 0 ? (passedTests / totalTests * 100).round() : 0;
    
    buffer.writeln('ملخص النتائج:');
    buffer.writeln('- إجمالي الاختبارات: $totalTests');
    buffer.writeln('- نجح: $passedTests');
    buffer.writeln('- فشل: ${totalTests - passedTests}');
    buffer.writeln('- معدل النجاح: $successRate%');
    buffer.writeln('');
    
    buffer.writeln('تفاصيل الاختبارات:');
    for (final result in widget.testResults) {
      buffer.writeln('- ${result.name}: ${result.success ? "نجح" : "فشل"} (${result.responseTime}ms)');
      if (result.details.isNotEmpty) {
        buffer.writeln('  التفاصيل: ${result.details}');
      }
    }
    
    return buffer.toString();
  }

  String _generateJsonReport() {
    final report = {
      'app_name': 'حصوني القرآني',
      'test_time': widget.testTime,
      'network_status': widget.networkStatus,
      'server_status': widget.serverStatus,
      'summary': {
        'total_tests': widget.testResults.length,
        'passed_tests': widget.testResults.where((r) => r.success).length,
        'failed_tests': widget.testResults.where((r) => !r.success).length,
        'success_rate': widget.testResults.isNotEmpty 
            ? (widget.testResults.where((r) => r.success).length / widget.testResults.length * 100).round()
            : 0,
        'average_response_time': widget.testResults.isNotEmpty
            ? widget.testResults.map((r) => r.responseTime).reduce((a, b) => a + b) / widget.testResults.length
            : 0,
      },
      'test_results': widget.testResults.map((r) => {
        'name': r.name,
        'success': r.success,
        'message': r.message,
        'details': r.details,
        'status': r.status,
        'response_time': r.responseTime,
      }).toList(),
    };
    
    return const JsonEncoder.withIndent('  ').convert(report);
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
