import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../../core/theme/tokens.dart';
import '../../../services/api_service.dart';
import '../../../services/auth_service.dart';

class ExamResultPage extends ConsumerStatefulWidget {
  final int examId;

  const ExamResultPage({super.key, required this.examId});

  @override
  ConsumerState<ExamResultPage> createState() => _ExamResultPageState();
}

class _ExamResultPageState extends ConsumerState<ExamResultPage> {
  Map<String, dynamic>? _result;
  List<Map<String, dynamic>> _questions = [];
  bool _isLoading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _loadResult();
  }

  Future<void> _loadResult() async {
    try {
      setState(() {
        _isLoading = true;
        _error = null;
      });

      final authState = ref.read(authStateProvider);
      if (authState.token != null) {
        ApiService.setToken(authState.token!);
      }

      final data = await ApiService.getExamResult(widget.examId);
      
      setState(() {
        _result = data['result'];
        _questions = List<Map<String, dynamic>>.from(data['questions'] ?? []);
        _isLoading = false;
      });
    } catch (e) {
      setState(() {
        _error = e.toString();
        _isLoading = false;
      });
    }
  }

  Color _getGradeColor(String? grade) {
    if (grade == null) return Colors.grey;
    
    if (grade.startsWith('A')) return Colors.green;
    if (grade.startsWith('B')) return Colors.blue;
    if (grade.startsWith('C')) return Colors.orange;
    return Colors.red;
  }

  @override
  Widget build(BuildContext context) {
    if (_isLoading) {
      return Scaffold(
        appBar: AppBar(
          title: const Text('نتيجة الاختبار'),
          backgroundColor: AppTokens.primaryBlue,
          foregroundColor: Colors.white,
        ),
        body: const Center(child: CircularProgressIndicator()),
      );
    }

    if (_error != null) {
      return Scaffold(
        appBar: AppBar(
          title: const Text('نتيجة الاختبار'),
          backgroundColor: AppTokens.primaryBlue,
          foregroundColor: Colors.white,
        ),
        body: Center(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(Icons.error_outline, size: 64, color: Colors.red),
              const SizedBox(height: 16),
              Text('خطأ: $_error'),
              const SizedBox(height: 24),
              ElevatedButton(
                onPressed: () => context.pop(),
                child: const Text('العودة'),
              ),
            ],
          ),
        ),
      );
    }

    if (_result == null) {
      return Scaffold(
        appBar: AppBar(
          title: const Text('نتيجة الاختبار'),
          backgroundColor: AppTokens.primaryBlue,
          foregroundColor: Colors.white,
        ),
        body: const Center(child: Text('لا توجد نتيجة متاحة')),
      );
    }

    // Convert to proper types - API may return strings or numbers
    final scoreValue = _result!['score'];
    final score = scoreValue is num ? scoreValue.toDouble() : (double.tryParse(scoreValue?.toString() ?? '0') ?? 0.0);
    
    final totalPointsValue = _result!['total_points'];
    final totalPoints = totalPointsValue is num ? totalPointsValue.toDouble() : (double.tryParse(totalPointsValue?.toString() ?? '0') ?? 0.0);
    
    final percentageValue = _result!['percentage'];
    final percentage = percentageValue is num ? percentageValue.toDouble() : (double.tryParse(percentageValue?.toString() ?? '0') ?? 0.0);
    
    final grade = _result!['grade'] ?? '-';

    return Scaffold(
      appBar: AppBar(
        title: const Text('نتيجة الاختبار'),
        backgroundColor: AppTokens.primaryBlue,
        foregroundColor: Colors.white,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            // Summary card
            Card(
              elevation: 4,
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(16),
              ),
              child: Container(
                padding: const EdgeInsets.all(24),
                decoration: BoxDecoration(
                  gradient: LinearGradient(
                    colors: [
                      AppTokens.primaryBlue,
                      AppTokens.primaryBlue.withOpacity(0.8),
                    ],
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                  ),
                  borderRadius: BorderRadius.circular(16),
                ),
                child: Column(
                  children: [
                    const Text(
                      'النتيجة النهائية',
                      style: TextStyle(
                        fontSize: 24,
                        fontWeight: FontWeight.bold,
                        color: Colors.white,
                      ),
                    ),
                    const SizedBox(height: 24),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceAround,
                      children: [
                        Column(
                          children: [
                            Text(
                              '$score/$totalPoints',
                              style: const TextStyle(
                                fontSize: 32,
                                fontWeight: FontWeight.bold,
                                color: Colors.white,
                              ),
                            ),
                            const SizedBox(height: 4),
                            const Text(
                              'النقاط',
                              style: TextStyle(
                                fontSize: 14,
                                color: Colors.white70,
                              ),
                            ),
                          ],
                        ),
                        Container(
                          width: 1,
                          height: 60,
                          color: Colors.white.withOpacity(0.3),
                        ),
                        Column(
                          children: [
                            Text(
                              '${percentage.toStringAsFixed(1)}%',
                              style: const TextStyle(
                                fontSize: 32,
                                fontWeight: FontWeight.bold,
                                color: Colors.white,
                              ),
                            ),
                            const SizedBox(height: 4),
                            const Text(
                              'النسبة',
                              style: TextStyle(
                                fontSize: 14,
                                color: Colors.white70,
                              ),
                            ),
                          ],
                        ),
                        Container(
                          width: 1,
                          height: 60,
                          color: Colors.white.withOpacity(0.3),
                        ),
                        Column(
                          children: [
                            Container(
                              padding: const EdgeInsets.symmetric(
                                horizontal: 20,
                                vertical: 10,
                              ),
                              decoration: BoxDecoration(
                                color: Colors.white,
                                borderRadius: BorderRadius.circular(20),
                              ),
                              child: Text(
                                grade,
                                style: TextStyle(
                                  fontSize: 24,
                                  fontWeight: FontWeight.bold,
                                  color: _getGradeColor(grade),
                                ),
                              ),
                            ),
                            const SizedBox(height: 4),
                            const Text(
                              'الدرجة',
                              style: TextStyle(
                                fontSize: 14,
                                color: Colors.white70,
                              ),
                            ),
                          ],
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            ),
            
            const SizedBox(height: 24),
            
            // Questions review
            const Text(
              'مراجعة الإجابات',
              style: TextStyle(
                fontSize: 20,
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 16),
            
            ..._questions.asMap().entries.map((entry) {
              final index = entry.key;
              final question = entry.value;
              final questionId = question['id'] as int;
              final questionType = question['question_type'] as String;
              final studentAnswer = question['student_answer'] as Map<String, dynamic>?;
              final isCorrect = question['is_correct'] as bool? ?? false;
              
              // Convert to proper types - API may return strings or numbers
              final pointsAwardedValue = question['points_awarded'];
              final pointsAwarded = pointsAwardedValue is num 
                  ? pointsAwardedValue.toDouble() 
                  : (double.tryParse(pointsAwardedValue?.toString() ?? '0') ?? 0.0);
              
              final questionPointsValue = question['points'];
              final questionPoints = questionPointsValue is num 
                  ? questionPointsValue.toDouble() 
                  : (double.tryParse(questionPointsValue?.toString() ?? '0') ?? 0.0);

              return Card(
                margin: const EdgeInsets.only(bottom: 16),
                elevation: 2,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(12),
                  side: BorderSide(
                    color: isCorrect ? Colors.green : Colors.red,
                    width: 2,
                  ),
                ),
                child: Padding(
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          Container(
                            width: 32,
                            height: 32,
                            decoration: BoxDecoration(
                              color: isCorrect ? Colors.green : Colors.red,
                              shape: BoxShape.circle,
                            ),
                            child: Center(
                              child: Text(
                                '${index + 1}',
                                style: const TextStyle(
                                  color: Colors.white,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                            ),
                          ),
                          const SizedBox(width: 12),
                          Expanded(
                            child: Text(
                              question['question_text'] ?? '',
                              style: const TextStyle(
                                fontSize: 16,
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
                              color: isCorrect ? Colors.green : Colors.red,
                              borderRadius: BorderRadius.circular(20),
                            ),
                            child: Text(
                              '$pointsAwarded/${questionPoints.toStringAsFixed(0)}',
                              style: const TextStyle(
                                color: Colors.white,
                                fontWeight: FontWeight.bold,
                                fontSize: 12,
                              ),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 16),
                      
                      if (questionType == 'multiple_choice') ...[
                        const Text(
                          'إجابتك:',
                          style: TextStyle(
                            fontWeight: FontWeight.bold,
                            color: Colors.grey,
                          ),
                        ),
                        const SizedBox(height: 8),
                        if (studentAnswer?['answer_text'] != null) ...[
                          Container(
                            padding: const EdgeInsets.all(12),
                            decoration: BoxDecoration(
                              color: Colors.grey[100],
                              borderRadius: BorderRadius.circular(8),
                            ),
                            child: Row(
                              children: [
                                Icon(
                                  isCorrect ? Icons.check_circle : Icons.cancel,
                                  color: isCorrect ? Colors.green : Colors.red,
                                ),
                                const SizedBox(width: 8),
                                Expanded(
                                  child: Text(
                                    _getOptionText(question, studentAnswer!['answer_text']),
                                    style: TextStyle(
                                      color: isCorrect ? Colors.green : Colors.red,
                                      fontWeight: FontWeight.bold,
                                    ),
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ],
                        const SizedBox(height: 12),
                        const Text(
                          'الإجابة الصحيحة:',
                          style: TextStyle(
                            fontWeight: FontWeight.bold,
                            color: Colors.grey,
                          ),
                        ),
                        const SizedBox(height: 8),
                        Container(
                          padding: const EdgeInsets.all(12),
                          decoration: BoxDecoration(
                            color: Colors.green[50],
                            borderRadius: BorderRadius.circular(8),
                            border: Border.all(color: Colors.green),
                          ),
                          child: Row(
                            children: [
                              const Icon(Icons.check_circle, color: Colors.green),
                              const SizedBox(width: 8),
                              Expanded(
                                child: Text(
                                  _getCorrectOptionText(question),
                                  style: const TextStyle(
                                    color: Colors.green,
                                    fontWeight: FontWeight.bold,
                                  ),
                                ),
                              ),
                            ],
                          ),
                        ),
                      ] else if (questionType == 'true_false') ...[
                        const Text(
                          'إجابتك:',
                          style: TextStyle(
                            fontWeight: FontWeight.bold,
                            color: Colors.grey,
                          ),
                        ),
                        const SizedBox(height: 8),
                        Container(
                          padding: const EdgeInsets.all(12),
                          decoration: BoxDecoration(
                            color: Colors.grey[100],
                            borderRadius: BorderRadius.circular(8),
                          ),
                          child: Row(
                            children: [
                              Icon(
                                isCorrect ? Icons.check_circle : Icons.cancel,
                                color: isCorrect ? Colors.green : Colors.red,
                              ),
                              const SizedBox(width: 8),
                              Text(
                                studentAnswer?['answer_boolean'] == true ? 'صح' : 'خطأ',
                                style: TextStyle(
                                  color: isCorrect ? Colors.green : Colors.red,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                            ],
                          ),
                        ),
                        const SizedBox(height: 12),
                        const Text(
                          'الإجابة الصحيحة:',
                          style: TextStyle(
                            fontWeight: FontWeight.bold,
                            color: Colors.grey,
                          ),
                        ),
                        const SizedBox(height: 8),
                        Container(
                          padding: const EdgeInsets.all(12),
                          decoration: BoxDecoration(
                            color: Colors.green[50],
                            borderRadius: BorderRadius.circular(8),
                            border: Border.all(color: Colors.green),
                          ),
                          child: Row(
                            children: [
                              const Icon(Icons.check_circle, color: Colors.green),
                              const SizedBox(width: 8),
                              Text(
                                question['correct_answer'] == true ? 'صح' : 'خطأ',
                                style: const TextStyle(
                                  color: Colors.green,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ],
                  ),
                ),
              );
            }).toList(),
            
            const SizedBox(height: 24),
            
            SizedBox(
              width: double.infinity,
              child: ElevatedButton(
                onPressed: () => context.pop(),
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppTokens.primaryBlue,
                  foregroundColor: Colors.white,
                  padding: const EdgeInsets.symmetric(vertical: 16),
                ),
                child: const Text('العودة إلى قائمة الاختبارات'),
              ),
            ),
          ],
        ),
      ),
    );
  }

  String _getOptionText(Map<String, dynamic> question, String answerIndex) {
    try {
      // Convert from 1-based (1, 2, 3, 4) to 0-based (0, 1, 2, 3) index
      final answerNumber = int.parse(answerIndex);
      final arrayIndex = answerNumber - 1; // Convert to 0-based index
      final options = question['options'] as List?;
      if (options != null && arrayIndex >= 0 && arrayIndex < options.length) {
        return options[arrayIndex]['text'] ?? '';
      }
    } catch (e) {
      // Ignore
    }
    return answerIndex;
  }

  String _getCorrectOptionText(Map<String, dynamic> question) {
    final options = question['options'] as List?;
    if (options != null) {
      for (var option in options) {
        if (option['is_correct'] == true) {
          return option['text'] ?? '';
        }
      }
    }
    return '';
  }
}

