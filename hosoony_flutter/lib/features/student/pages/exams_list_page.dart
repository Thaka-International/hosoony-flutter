import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../../core/theme/tokens.dart';
import '../../../services/api_service.dart';
import '../../../services/auth_service.dart';
import '../../../shared_ui/widgets/copyright_widget.dart';
import 'package:intl/intl.dart';

class ExamsListPage extends ConsumerStatefulWidget {
  const ExamsListPage({super.key});

  @override
  ConsumerState<ExamsListPage> createState() => _ExamsListPageState();
}

class _ExamsListPageState extends ConsumerState<ExamsListPage> {
  List<Map<String, dynamic>> _exams = [];
  bool _isLoading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _loadExams();
  }

  Future<void> _loadExams() async {
    try {
      setState(() {
        _isLoading = true;
        _error = null;
      });

      final authState = ref.read(authStateProvider);
      if (authState.token != null) {
        ApiService.setToken(authState.token!);
      }

      final exams = await ApiService.getAvailableExams();
      setState(() {
        _exams = exams;
        _isLoading = false;
      });
    } catch (e) {
      setState(() {
        _error = e.toString();
        _isLoading = false;
      });
    }
  }

  String _formatDate(String? dateString) {
    if (dateString == null) return '';
    try {
      final date = DateTime.parse(dateString);
      return DateFormat('yyyy-MM-dd HH:mm', 'ar').format(date);
    } catch (e) {
      return dateString;
    }
  }

  String _getStatusLabel(String? status) {
    switch (status) {
      case 'not_started':
        return 'لم يبدأ';
      case 'in_progress':
        return 'قيد التنفيذ';
      case 'submitted':
        return 'تم الإرسال';
      case 'graded':
        return 'تم التصحيح';
      default:
        return 'غير معروف';
    }
  }

  Color _getStatusColor(String? status) {
    switch (status) {
      case 'not_started':
        return Colors.blue;
      case 'in_progress':
        return Colors.orange;
      case 'submitted':
        return Colors.purple;
      case 'graded':
        return Colors.green;
      default:
        return Colors.grey;
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('الاختبارات التحريرية'),
        backgroundColor: AppTokens.primaryBlue,
        foregroundColor: Colors.white,
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _loadExams,
          ),
        ],
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _error != null
              ? Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Icon(Icons.error_outline, size: 64, color: Colors.red),
                      const SizedBox(height: 16),
                      Text(
                        'خطأ في تحميل الاختبارات',
                        style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                      ),
                      const SizedBox(height: 8),
                      Text(
                        _error!,
                        textAlign: TextAlign.center,
                        style: TextStyle(color: Colors.grey[600]),
                      ),
                      const SizedBox(height: 24),
                      ElevatedButton(
                        onPressed: _loadExams,
                        child: const Text('إعادة المحاولة'),
                      ),
                    ],
                  ),
                )
              : _exams.isEmpty
                  ? Center(
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Icon(Icons.quiz_outlined, size: 64, color: Colors.grey[400]),
                          const SizedBox(height: 16),
                          Text(
                            'لا توجد اختبارات متاحة',
                            style: TextStyle(fontSize: 18, color: Colors.grey[600]),
                          ),
                        ],
                      ),
                    )
                  : RefreshIndicator(
                      onRefresh: _loadExams,
                      child: ListView.builder(
                        padding: const EdgeInsets.all(16),
                        itemCount: _exams.length,
                        itemBuilder: (context, index) {
                          final exam = _exams[index];
                          final status = exam['status'] as String?;
                          final hasAttempt = exam['has_attempt'] as bool? ?? false;
                          final result = exam['result'] as Map<String, dynamic>?;

                          return Card(
                            margin: const EdgeInsets.only(bottom: 16),
                            elevation: 2,
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(12),
                            ),
                            child: InkWell(
                              onTap: () {
                                if (status == 'graded' && result != null) {
                                  // Navigate to result page
                                  context.push('/student/home/exams/${exam['id']}/result');
                                } else if (status == 'not_started' || status == 'in_progress') {
                                  // Navigate to take exam page
                                  context.push('/student/home/exams/${exam['id']}/take');
                                }
                              },
                              borderRadius: BorderRadius.circular(12),
                              child: Padding(
                                padding: const EdgeInsets.all(16),
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Row(
                                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                      children: [
                                        Expanded(
                                          child: Text(
                                            exam['title'] ?? 'بدون عنوان',
                                            style: const TextStyle(
                                              fontSize: 18,
                                              fontWeight: FontWeight.bold,
                                            ),
                                          ),
                                        ),
                                        Container(
                                          padding: const EdgeInsets.symmetric(
                                            horizontal: 12,
                                            vertical: 6,
                                          ),
                                          decoration: BoxDecoration(
                                            color: _getStatusColor(status).withOpacity(0.1),
                                            borderRadius: BorderRadius.circular(20),
                                            border: Border.all(
                                              color: _getStatusColor(status),
                                              width: 1,
                                            ),
                                          ),
                                          child: Text(
                                            _getStatusLabel(status),
                                            style: TextStyle(
                                              color: _getStatusColor(status),
                                              fontSize: 12,
                                              fontWeight: FontWeight.bold,
                                            ),
                                          ),
                                        ),
                                      ],
                                    ),
                                    if (exam['description'] != null) ...[
                                      const SizedBox(height: 8),
                                      Text(
                                        exam['description'],
                                        style: TextStyle(
                                          color: Colors.grey[600],
                                          fontSize: 14,
                                        ),
                                      ),
                                    ],
                                    const SizedBox(height: 12),
                                    Row(
                                      children: [
                                        Icon(Icons.calendar_today, size: 16, color: Colors.grey[600]),
                                        const SizedBox(width: 8),
                                        Text(
                                          _formatDate(exam['scheduled_at']),
                                          style: TextStyle(
                                            color: Colors.grey[600],
                                            fontSize: 14,
                                          ),
                                        ),
                                      ],
                                    ),
                                    const SizedBox(height: 8),
                                    Row(
                                      children: [
                                        Icon(Icons.timer, size: 16, color: Colors.grey[600]),
                                        const SizedBox(width: 8),
                                        Text(
                                          '${exam['duration_minutes']} دقيقة',
                                          style: TextStyle(
                                            color: Colors.grey[600],
                                            fontSize: 14,
                                          ),
                                        ),
                                        const SizedBox(width: 24),
                                        Icon(Icons.quiz, size: 16, color: Colors.grey[600]),
                                        const SizedBox(width: 8),
                                        Text(
                                          '${exam['question_count']} سؤال',
                                          style: TextStyle(
                                            color: Colors.grey[600],
                                            fontSize: 14,
                                          ),
                                        ),
                                        const SizedBox(width: 24),
                                        Icon(Icons.star, size: 16, color: Colors.grey[600]),
                                        const SizedBox(width: 8),
                                        Text(
                                          '${exam['total_points']} نقطة',
                                          style: TextStyle(
                                            color: Colors.grey[600],
                                            fontSize: 14,
                                          ),
                                        ),
                                      ],
                                    ),
                                    if (result != null) ...[
                                      const SizedBox(height: 12),
                                      Container(
                                        padding: const EdgeInsets.all(12),
                                        decoration: BoxDecoration(
                                          color: Colors.green.withOpacity(0.1),
                                          borderRadius: BorderRadius.circular(8),
                                        ),
                                        child: Row(
                                          mainAxisAlignment: MainAxisAlignment.spaceAround,
                                          children: [
                                            Column(
                                              children: [
                                                Text(
                                                  () {
                                                    // Convert to proper types - API may return strings or numbers
                                                    final scoreValue = result['score'];
                                                    final score = scoreValue is num 
                                                        ? scoreValue.toDouble() 
                                                        : (double.tryParse(scoreValue?.toString() ?? '0') ?? 0.0);
                                                    
                                                    final totalPointsValue = result['total_points'];
                                                    final totalPoints = totalPointsValue is num 
                                                        ? totalPointsValue.toDouble() 
                                                        : (double.tryParse(totalPointsValue?.toString() ?? '0') ?? 0.0);
                                                    
                                                    return '${score.toStringAsFixed(0)}/${totalPoints.toStringAsFixed(0)}';
                                                  }(),
                                                  style: const TextStyle(
                                                    fontSize: 20,
                                                    fontWeight: FontWeight.bold,
                                                    color: Colors.green,
                                                  ),
                                                ),
                                                Text(
                                                  'النقاط',
                                                  style: TextStyle(
                                                    fontSize: 12,
                                                    color: Colors.grey[600],
                                                  ),
                                                ),
                                              ],
                                            ),
                                            Container(
                                              width: 1,
                                              height: 40,
                                              color: Colors.grey[300],
                                            ),
                                            Column(
                                              children: [
                                                Text(
                                                  () {
                                                    // Convert to proper types - API may return strings or numbers
                                                    final percentageValue = result['percentage'];
                                                    final percentage = percentageValue is num 
                                                        ? percentageValue.toDouble() 
                                                        : (double.tryParse(percentageValue?.toString() ?? '0') ?? 0.0);
                                                    
                                                    return '${percentage.toStringAsFixed(1)}%';
                                                  }(),
                                                  style: const TextStyle(
                                                    fontSize: 20,
                                                    fontWeight: FontWeight.bold,
                                                    color: Colors.green,
                                                  ),
                                                ),
                                                Text(
                                                  'النسبة',
                                                  style: TextStyle(
                                                    fontSize: 12,
                                                    color: Colors.grey[600],
                                                  ),
                                                ),
                                              ],
                                            ),
                                            Container(
                                              width: 1,
                                              height: 40,
                                              color: Colors.grey[300],
                                            ),
                                            Column(
                                              children: [
                                                Text(
                                                  result['grade'] ?? '-',
                                                  style: const TextStyle(
                                                    fontSize: 20,
                                                    fontWeight: FontWeight.bold,
                                                    color: Colors.green,
                                                  ),
                                                ),
                                                Text(
                                                  'الدرجة',
                                                  style: TextStyle(
                                                    fontSize: 12,
                                                    color: Colors.grey[600],
                                                  ),
                                                ),
                                              ],
                                            ),
                                          ],
                                        ),
                                      ),
                                    ],
                                    const SizedBox(height: 12),
                                    SizedBox(
                                      width: double.infinity,
                                      child: ElevatedButton(
                                        onPressed: () {
                                          if (status == 'graded' && result != null) {
                                            context.push('/student/home/exams/${exam['id']}/result');
                                          } else if (status == 'not_started' || status == 'in_progress') {
                                            context.push('/student/home/exams/${exam['id']}/take');
                                          }
                                        },
                                        style: ElevatedButton.styleFrom(
                                          backgroundColor: AppTokens.primaryBlue,
                                          foregroundColor: Colors.white,
                                          padding: const EdgeInsets.symmetric(vertical: 12),
                                          shape: RoundedRectangleBorder(
                                            borderRadius: BorderRadius.circular(8),
                                          ),
                                        ),
                                        child: Text(
                                          status == 'graded'
                                              ? 'عرض النتيجة'
                                              : status == 'in_progress'
                                                  ? 'متابعة الاختبار'
                                                  : 'بدء الاختبار',
                                        ),
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                            ),
                          );
                        },
                      ),
                    ),
      bottomNavigationBar: const CopyrightWidget(),
    );
  }
}

