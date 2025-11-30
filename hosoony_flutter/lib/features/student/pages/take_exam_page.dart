import 'dart:async';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../../core/theme/tokens.dart';
import '../../../services/api_service.dart';
import '../../../services/auth_service.dart';

class TakeExamPage extends ConsumerStatefulWidget {
  final int examId;

  const TakeExamPage({super.key, required this.examId});

  @override
  ConsumerState<TakeExamPage> createState() => _TakeExamPageState();
}

class _TakeExamPageState extends ConsumerState<TakeExamPage> {
  Map<String, dynamic>? _examData;
  Map<String, dynamic>? _attemptData;
  List<Map<String, dynamic>> _questions = [];
  Map<int, dynamic> _answers = {};
  int _currentQuestionIndex = 0;
  bool _isLoading = true;
  bool _isSubmitting = false;
  String? _error;
  Timer? _autoSaveTimer;
  Timer? _countdownTimer;
  int _remainingSeconds = 0;

  @override
  void initState() {
    super.initState();
    _startExam();
  }

  @override
  void dispose() {
    _autoSaveTimer?.cancel();
    _countdownTimer?.cancel();
    super.dispose();
  }

  Future<void> _startExam() async {
    try {
      setState(() {
        _isLoading = true;
        _error = null;
      });

      final authState = ref.read(authStateProvider);
      if (authState.token != null) {
        ApiService.setToken(authState.token!);
      }

      final data = await ApiService.startExam(widget.examId);
      
      setState(() {
        _examData = data['exam'];
        _attemptData = data['attempt'];
        _questions = List<Map<String, dynamic>>.from(data['questions'] ?? []);
        
        // Load existing answers
        final answersData = data['answers'];
        Map<String, dynamic> existingAnswers = {};
        if (answersData != null) {
          if (answersData is Map) {
            existingAnswers = Map<String, dynamic>.from(answersData);
          } else if (answersData is List) {
            // If answers come as a list, convert to map
            // This shouldn't happen but handle it gracefully
            existingAnswers = {};
          }
        }
        
        for (var entry in existingAnswers.entries) {
          final questionId = int.tryParse(entry.key);
          if (questionId != null) {
            final answerData = entry.value;
            // Find the question to determine its type
            final question = _questions.firstWhere(
              (q) => q['id'] == questionId,
              orElse: () => <String, dynamic>{},
            );
            final questionType = question['question_type'] ?? 'multiple_choice';
            
            if (questionType == 'true_false') {
              // For true/false questions, convert to '1' (true) or '2' (false)
              if (answerData is Map) {
                final boolValue = answerData['answer_boolean'];
                if (boolValue is bool) {
                  _answers[questionId] = boolValue ? '1' : '2';
                } else if (boolValue is int) {
                  _answers[questionId] = boolValue == 1 ? '1' : '2';
                } else if (boolValue is String) {
                  // Handle '1' or '2' strings
                  _answers[questionId] = (boolValue == '1' || boolValue.toLowerCase() == 'true') ? '1' : '2';
                } else {
                  _answers[questionId] = '2'; // Default to false
                }
              }
            } else {
              // For multiple choice, ensure answer_text is a string (1-based: 1, 2, 3, 4)
              if (answerData is Map) {
                final textValue = answerData['answer_text'];
                _answers[questionId] = textValue?.toString() ?? '';
              } else {
                _answers[questionId] = answerData?.toString() ?? '';
              }
            }
          }
        }
        
        // Calculate remaining time
        if (_examData?['duration_minutes'] != null) {
          final durationMinutes = _examData!['duration_minutes'] as int;
          final startedAt = DateTime.parse(_attemptData!['started_at']);
          final endTime = startedAt.add(Duration(minutes: durationMinutes));
          _remainingSeconds = endTime.difference(DateTime.now()).inSeconds;
          if (_remainingSeconds < 0) _remainingSeconds = 0;
        }
        
        _isLoading = false;
      });

      // Start auto-save timer
      _autoSaveTimer = Timer.periodic(const Duration(seconds: 30), (_) {
        _saveAnswers();
      });

      // Start countdown timer
      _countdownTimer = Timer.periodic(const Duration(seconds: 1), (_) {
        if (_remainingSeconds > 0) {
          setState(() {
            _remainingSeconds--;
          });
        } else {
          _countdownTimer?.cancel();
          _submitExam(autoSubmit: true);
        }
      });
    } catch (e) {
      setState(() {
        _error = e.toString();
        _isLoading = false;
      });
    }
  }

  Future<void> _saveAnswers() async {
    if (_attemptData == null) return;

    try {
      final answersList = _answers.entries.map((entry) {
        final questionId = entry.key;
        final answer = entry.value;
        
        // Find the question
        final question = _questions.firstWhere(
          (q) => q['id'] == questionId,
          orElse: () => <String, dynamic>{},
        );
        
        final questionType = question['question_type'] ?? 'multiple_choice';
        
        if (questionType == 'true_false') {
          // Convert answer to boolean: '1' = true, '2' = false
          bool answerBoolean;
          if (answer is String) {
            answerBoolean = answer == '1';
          } else if (answer is int) {
            answerBoolean = answer == 1;
          } else if (answer is bool) {
            answerBoolean = answer;
          } else {
            answerBoolean = false;
          }
          
          return {
            'question_id': questionId,
            'answer_boolean': answerBoolean,
          };
        } else {
          // For multiple choice, ensure answer_text is always a string (1-based: 1, 2, 3, 4)
          // Convert to string explicitly to prevent JSON serialization issues
          String answerText;
          if (answer is String) {
            answerText = answer;
          } else if (answer is int) {
            // For multiple choice, answer is 1-based index (1, 2, 3, etc.)
            answerText = answer.toString();
          } else if (answer is double) {
            answerText = answer.toString();
          } else {
            answerText = answer?.toString() ?? '';
          }
          
          debugPrint('Saving answer for question $questionId: type=${answer.runtimeType}, value=$answer, answerText=$answerText');
          
          // Explicitly create map with string values to ensure JSON serialization keeps it as string
          return <String, dynamic>{
            'question_id': questionId,
            'answer_text': answerText, // Explicitly string (1-based: "1", "2", "3", "4")
          };
        }
      }).toList();

      await ApiService.saveExamAnswers(_attemptData!['id'], answersList);
    } catch (e) {
      // Silent fail for auto-save
      debugPrint('Auto-save failed: $e');
    }
  }

  Future<void> _submitExam({bool autoSubmit = false}) async {
    if (_isSubmitting) return;

    if (!autoSubmit) {
      final confirmed = await showDialog<bool>(
        context: context,
        builder: (context) => AlertDialog(
          title: const Text('تأكيد الإرسال'),
          content: const Text('هل أنت متأكد من إرسال الإجابات؟ لا يمكنك تعديل الإجابات بعد الإرسال.'),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(context, false),
              child: const Text('إلغاء'),
            ),
            ElevatedButton(
              onPressed: () => Navigator.pop(context, true),
              style: ElevatedButton.styleFrom(
                backgroundColor: AppTokens.primaryBlue,
                foregroundColor: Colors.white,
              ),
              child: const Text('إرسال'),
            ),
          ],
        ),
      );

      if (confirmed != true) return;
    }

    try {
      setState(() {
        _isSubmitting = true;
      });

      _autoSaveTimer?.cancel();
      _countdownTimer?.cancel();

      // Final save
      await _saveAnswers();

      final answersList = _answers.entries.map((entry) {
        final questionId = entry.key;
        final answer = entry.value;
        
        // Find the question
        final question = _questions.firstWhere(
          (q) => q['id'] == questionId,
          orElse: () => <String, dynamic>{},
        );
        
        final questionType = question['question_type'] ?? 'multiple_choice';
        
        if (questionType == 'true_false') {
          // Convert answer to boolean: '1' = true, '2' = false
          bool answerBoolean;
          if (answer is String) {
            answerBoolean = answer == '1';
          } else if (answer is int) {
            answerBoolean = answer == 1;
          } else if (answer is bool) {
            answerBoolean = answer;
          } else {
            answerBoolean = false;
          }
          
          return {
            'question_id': questionId,
            'answer_boolean': answerBoolean,
          };
        } else {
          // For multiple choice, ensure answer_text is always a string (1-based: 1, 2, 3, 4)
          // Convert to string explicitly to prevent JSON serialization issues
          String answerText;
          if (answer is String) {
            answerText = answer;
          } else if (answer is int) {
            // For multiple choice, answer is 1-based index (1, 2, 3, etc.)
            answerText = answer.toString();
          } else if (answer is double) {
            answerText = answer.toString();
          } else {
            answerText = answer?.toString() ?? '';
          }
          
          debugPrint('Submitting answer for question $questionId: type=${answer.runtimeType}, value=$answer, answerText=$answerText');
          
          // Explicitly create map with string values to ensure JSON serialization keeps it as string
          return <String, dynamic>{
            'question_id': questionId,
            'answer_text': answerText, // Explicitly string (1-based: "1", "2", "3", "4")
          };
        }
      }).toList();

      await ApiService.submitExam(_attemptData!['id'], answers: answersList);

      if (mounted) {
        context.pushReplacement('/student/home/exams/${widget.examId}/result');
      }
    } catch (e) {
      setState(() {
        _isSubmitting = false;
        _error = e.toString();
      });
    }
  }

  String _formatTime(int seconds) {
    final hours = seconds ~/ 3600;
    final minutes = (seconds % 3600) ~/ 60;
    final secs = seconds % 60;
    
    if (hours > 0) {
      return '${hours.toString().padLeft(2, '0')}:${minutes.toString().padLeft(2, '0')}:${secs.toString().padLeft(2, '0')}';
    }
    return '${minutes.toString().padLeft(2, '0')}:${secs.toString().padLeft(2, '0')}';
  }

  @override
  Widget build(BuildContext context) {
    if (_isLoading) {
      return Scaffold(
        appBar: AppBar(
          title: const Text('الاختبار'),
          backgroundColor: AppTokens.primaryBlue,
          foregroundColor: Colors.white,
        ),
        body: const Center(child: CircularProgressIndicator()),
      );
    }

    if (_error != null) {
      return Scaffold(
        appBar: AppBar(
          title: const Text('الاختبار'),
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

    if (_questions.isEmpty) {
      return Scaffold(
        appBar: AppBar(
          title: const Text('الاختبار'),
          backgroundColor: AppTokens.primaryBlue,
          foregroundColor: Colors.white,
        ),
        body: const Center(child: Text('لا توجد أسئلة في هذا الاختبار')),
      );
    }

    final currentQuestion = _questions[_currentQuestionIndex];
    final questionId = currentQuestion['id'] as int;
    final questionType = currentQuestion['question_type'] as String;
    final currentAnswer = _answers[questionId];

    return WillPopScope(
      onWillPop: () async {
        final confirmed = await showDialog<bool>(
          context: context,
          builder: (context) => AlertDialog(
            title: const Text('تأكيد الخروج'),
            content: const Text('سيتم حفظ إجاباتك تلقائياً. هل تريد الخروج من الاختبار؟'),
            actions: [
              TextButton(
                onPressed: () => Navigator.pop(context, false),
                child: const Text('إلغاء'),
              ),
              ElevatedButton(
                onPressed: () => Navigator.pop(context, true),
                child: const Text('خروج'),
              ),
            ],
          ),
        );
        
        if (confirmed == true) {
          await _saveAnswers();
        }
        
        return confirmed ?? false;
      },
      child: Scaffold(
        appBar: AppBar(
          title: Text(_examData?['title'] ?? 'الاختبار'),
          backgroundColor: AppTokens.primaryBlue,
          foregroundColor: Colors.white,
          actions: [
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
              margin: const EdgeInsets.only(right: 8),
              decoration: BoxDecoration(
                color: _remainingSeconds < 300 ? Colors.red : Colors.white.withOpacity(0.2),
                borderRadius: BorderRadius.circular(20),
              ),
              child: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  const Icon(Icons.timer, size: 20),
                  const SizedBox(width: 8),
                  Text(
                    _formatTime(_remainingSeconds),
                    style: const TextStyle(
                      fontWeight: FontWeight.bold,
                      fontSize: 16,
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
        body: Column(
          children: [
            // Progress bar
            Container(
              padding: const EdgeInsets.all(16),
              color: Colors.grey[100],
              child: Column(
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text(
                        'السؤال ${_currentQuestionIndex + 1} من ${_questions.length}',
                        style: const TextStyle(fontWeight: FontWeight.bold),
                      ),
                      Text(
                        '${((_currentQuestionIndex + 1) / _questions.length * 100).toStringAsFixed(0)}%',
                        style: const TextStyle(fontWeight: FontWeight.bold),
                      ),
                    ],
                  ),
                  const SizedBox(height: 8),
                  LinearProgressIndicator(
                    value: (_currentQuestionIndex + 1) / _questions.length,
                    backgroundColor: Colors.grey[300],
                    valueColor: AlwaysStoppedAnimation<Color>(AppTokens.primaryBlue),
                  ),
                ],
              ),
            ),
            
            // Question
            Expanded(
              child: SingleChildScrollView(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      currentQuestion['question_text'] ?? '',
                      style: const TextStyle(
                        fontSize: 20,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(height: 8),
                    Text(
                      '${currentQuestion['points']} نقاط',
                      style: TextStyle(
                        color: Colors.grey[600],
                        fontSize: 14,
                      ),
                    ),
                    const SizedBox(height: 24),
                    
                    if (questionType == 'multiple_choice') ...[
                      ...List.generate(
                        (currentQuestion['options'] as List?)?.length ?? 0,
                        (index) {
                          final option = (currentQuestion['options'] as List)[index];
                          final optionText = option['text'] ?? '';
                          // Use 1-based indexing: 1, 2, 3 instead of 0, 1, 2
                          final optionNumber = index + 1;
                          final isSelected = currentAnswer == optionNumber.toString();
                          
                          return Card(
                            margin: const EdgeInsets.only(bottom: 12),
                            elevation: isSelected ? 4 : 1,
                            color: isSelected ? AppTokens.primaryBlue.withOpacity(0.1) : null,
                            child: RadioListTile<int>(
                              title: Text(optionText),
                              value: optionNumber, // Use 1-based: 1, 2, 3, 4
                              groupValue: isSelected ? optionNumber : null,
                              onChanged: (value) {
                                setState(() {
                                  _answers[questionId] = value.toString();
                                });
                                _saveAnswers();
                              },
                            ),
                          );
                        },
                      ),
                    ] else if (questionType == 'true_false') ...[
                      // Use 1 for true, 2 for false instead of boolean values
                      Card(
                        margin: const EdgeInsets.only(bottom: 12),
                        elevation: currentAnswer == '1' ? 4 : 1,
                        color: currentAnswer == '1' ? Colors.green.withOpacity(0.1) : null,
                        child: RadioListTile<String>(
                          title: const Text('صح'),
                          value: '1',
                          groupValue: currentAnswer == '1' ? '1' : null,
                          onChanged: (value) {
                            setState(() {
                              _answers[questionId] = value;
                            });
                            _saveAnswers();
                          },
                        ),
                      ),
                      Card(
                        margin: const EdgeInsets.only(bottom: 12),
                        elevation: currentAnswer == '2' ? 4 : 1,
                        color: currentAnswer == '2' ? Colors.red.withOpacity(0.1) : null,
                        child: RadioListTile<String>(
                          title: const Text('خطأ'),
                          value: '2',
                          groupValue: currentAnswer == '2' ? '2' : null,
                          onChanged: (value) {
                            setState(() {
                              _answers[questionId] = value;
                            });
                            _saveAnswers();
                          },
                        ),
                      ),
                    ],
                  ],
                ),
              ),
            ),
            
            // Navigation buttons
            Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: Colors.white,
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withOpacity(0.1),
                    blurRadius: 4,
                    offset: const Offset(0, -2),
                  ),
                ],
              ),
              child: Row(
                children: [
                  if (_currentQuestionIndex > 0)
                    Expanded(
                      child: OutlinedButton(
                        onPressed: () {
                          setState(() {
                            _currentQuestionIndex--;
                          });
                        },
                        child: const Text('السابق'),
                      ),
                    ),
                  if (_currentQuestionIndex > 0) const SizedBox(width: 16),
                  Expanded(
                    flex: 2,
                    child: ElevatedButton(
                      onPressed: _isSubmitting ? null : () {
                        if (_currentQuestionIndex < _questions.length - 1) {
                          setState(() {
                            _currentQuestionIndex++;
                          });
                        } else {
                          _submitExam();
                        }
                      },
                      style: ElevatedButton.styleFrom(
                        backgroundColor: AppTokens.primaryBlue,
                        foregroundColor: Colors.white,
                        padding: const EdgeInsets.symmetric(vertical: 16),
                      ),
                      child: _isSubmitting
                          ? const SizedBox(
                              height: 20,
                              width: 20,
                              child: CircularProgressIndicator(strokeWidth: 2),
                            )
                          : Text(
                              _currentQuestionIndex < _questions.length - 1
                                  ? 'التالي'
                                  : 'إرسال الاختبار',
                            ),
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}

