import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';
import '../../../core/theme/tokens.dart';
import '../../../services/auth_service.dart';
import '../../../services/api_service.dart';

class TeacherRecitationEvaluationPage extends ConsumerStatefulWidget {
  const TeacherRecitationEvaluationPage({super.key});

  @override
  ConsumerState<TeacherRecitationEvaluationPage> createState() => _TeacherRecitationEvaluationPageState();
}

class _TeacherRecitationEvaluationPageState extends ConsumerState<TeacherRecitationEvaluationPage> {
  List<Map<String, dynamic>> _presentStudents = [];
  List<Map<String, dynamic>> _evaluations = [];
  List<Map<String, dynamic>> _classes = [];
  bool _isLoadingStudents = true;
  bool _isLoadingEvaluations = false;
  bool _isLoadingClasses = true;
  String? _error;
  int? _selectedClassId;
  String? _evaluationDate;

  @override
  void initState() {
    super.initState();
    _evaluationDate = DateFormat('yyyy-MM-dd').format(DateTime.now());
    _loadClasses().then((_) {
      if (_selectedClassId != null) {
        _loadPresentStudents();
        _loadEvaluations();
      }
    });
  }

  Future<void> _loadClasses() async {
    try {
      setState(() {
        _isLoadingClasses = true;
      });

      final authState = ref.read(authStateProvider);
      if (authState.token != null) {
        ApiService.setToken(authState.token!);
      }

      final classes = await ApiService.getMyClasses();
      
      setState(() {
        _classes = classes;
        if (classes.isNotEmpty) {
          _selectedClassId = classes.first['id'] as int?;
        }
        _isLoadingClasses = false;
      });
    } catch (e) {
      setState(() {
        _isLoadingClasses = false;
        _error = 'خطأ في تحميل الفصول: $e';
      });
    }
  }

  Future<void> _loadPresentStudents() async {
    try {
      setState(() {
        _isLoadingStudents = true;
        _error = null;
      });

      final authState = ref.read(authStateProvider);
      if (authState.token != null) {
        ApiService.setToken(authState.token!);
      }

      final response = await ApiService.getPresentStudentsForRecitation(classId: _selectedClassId);
      
      if (response['success'] == true) {
        setState(() {
          _presentStudents = List<Map<String, dynamic>>.from(response['students'] ?? []);
          _selectedClassId = response['class']?['id'] as int?;
          _isLoadingStudents = false;
        });
      } else {
        setState(() {
          _error = response['message'] ?? 'فشل تحميل الطالبات';
          _isLoadingStudents = false;
        });
      }
    } catch (e) {
      setState(() {
        _error = 'خطأ: ${e.toString()}';
        _isLoadingStudents = false;
      });
    }
  }

  Future<void> _loadEvaluations() async {
    try {
      setState(() {
        _isLoadingEvaluations = true;
      });

      final authState = ref.read(authStateProvider);
      if (authState.token != null) {
        ApiService.setToken(authState.token!);
      }

      final today = DateFormat('yyyy-MM-dd').format(DateTime.now());
      final response = await ApiService.getRecitationEvaluations(
        startDate: today,
        endDate: today,
        classId: _selectedClassId,
      );
      
      if (response['success'] == true) {
        setState(() {
          _evaluations = List<Map<String, dynamic>>.from(response['evaluations'] ?? []);
          _isLoadingEvaluations = false;
        });
      } else {
        setState(() {
          _isLoadingEvaluations = false;
        });
      }
    } catch (e) {
      setState(() {
        _isLoadingEvaluations = false;
      });
      print('خطأ في تحميل التقييمات: $e');
    }
  }

  Future<void> _showEvaluationDialog(BuildContext context, Map<String, dynamic> student) async {
    final formKey = GlobalKey<FormState>();
    final scoreController = TextEditingController();
    final notesController = TextEditingController();
    
    // تحقق من وجود تقييم سابق
    final existingEvaluation = _evaluations.firstWhere(
      (eval) => eval['student_id'] == student['id'],
      orElse: () => {},
    );

    if (existingEvaluation.isNotEmpty) {
      scoreController.text = existingEvaluation['recitation_score'].toString();
      notesController.text = existingEvaluation['notes'] ?? '';
    }

    await showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: Text('تقييم تلاوة: ${student['name']}'),
        content: SingleChildScrollView(
          child: Form(
            key: formKey,
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                TextFormField(
                  controller: scoreController,
                  decoration: const InputDecoration(
                    labelText: 'درجة الأداء القرآني (من 20) *',
                    hintText: '0-20',
                  ),
                  keyboardType: TextInputType.number,
                  validator: (value) {
                    if (value == null || value.isEmpty) {
                      return 'مطلوب';
                    }
                    final score = int.tryParse(value);
                    if (score == null || score < 0 || score > 20) {
                      return 'يجب أن يكون بين 0 و 20';
                    }
                    return null;
                  },
                ),
                const SizedBox(height: 16),
                TextFormField(
                  controller: notesController,
                  decoration: const InputDecoration(
                    labelText: 'ملاحظات',
                    hintText: 'ملاحظات المعلمة للطالبة...',
                  ),
                  maxLines: 4,
                ),
              ],
            ),
          ),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('إلغاء'),
          ),
          ElevatedButton(
            onPressed: () async {
              if (formKey.currentState!.validate()) {
                try {
                  final authState = ref.read(authStateProvider);
                  if (authState.token != null) {
                    ApiService.setToken(authState.token!);
                  }

                  if (existingEvaluation.isNotEmpty) {
                    // تحديث التقييم الموجود
                    await ApiService.updateRecitationEvaluation(
                      evaluationId: existingEvaluation['id'] as int,
                      recitationScore: int.parse(scoreController.text),
                      notes: notesController.text.isEmpty ? null : notesController.text,
                    );
                  } else {
                    // إنشاء تقييم جديد
                    await ApiService.createRecitationEvaluation(
                      studentId: student['id'] as int,
                      recitationScore: int.parse(scoreController.text),
                      evaluationDate: _evaluationDate,
                      notes: notesController.text.isEmpty ? null : notesController.text,
                      classId: _selectedClassId,
                    );
                  }

                  _loadEvaluations();
                  if (context.mounted) {
                    Navigator.pop(context);
                    ScaffoldMessenger.of(context).showSnackBar(
                      SnackBar(
                        content: Text(existingEvaluation.isNotEmpty 
                            ? 'تم تحديث التقييم بنجاح' 
                            : 'تم إضافة التقييم بنجاح'),
                        backgroundColor: AppTokens.successGreen,
                      ),
                    );
                  }
                } catch (e) {
                  if (context.mounted) {
                    ScaffoldMessenger.of(context).showSnackBar(
                      SnackBar(
                        content: Text('خطأ: ${e.toString()}'),
                        backgroundColor: Colors.red,
                      ),
                    );
                  }
                }
              }
            },
            child: Text(existingEvaluation.isNotEmpty ? 'تحديث' : 'حفظ'),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('تقييم التلاوة'),
        backgroundColor: AppTokens.primaryBrown,
        foregroundColor: AppTokens.neutralWhite,
        elevation: 0,
      ),
      body: _isLoadingClasses
          ? const Center(child: CircularProgressIndicator())
          : _classes.isEmpty
              ? Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Icon(Icons.class_, size: 64, color: Colors.grey),
                      const SizedBox(height: 16),
                      Text(
                        'لا توجد فصول متاحة',
                        style: Theme.of(context).textTheme.titleLarge,
                      ),
                      const SizedBox(height: 8),
                      Text(
                        'المعلم غير مرتبط بأي فصل. يرجى ربط المعلمة بفصل أو إضافة جلسات.',
                        textAlign: TextAlign.center,
                        style: Theme.of(context).textTheme.bodyMedium,
                      ),
                    ],
                  ),
                )
          : _isLoadingStudents
              ? const Center(child: CircularProgressIndicator())
          : _error != null
              ? Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Text(_error!, style: const TextStyle(color: Colors.red)),
                      const SizedBox(height: 16),
                      ElevatedButton(
                        onPressed: _loadPresentStudents,
                        child: const Text('إعادة المحاولة'),
                      ),
                    ],
                  ),
                )
              : Column(
                  children: [
                    // Class Selector
                    if (_classes.isNotEmpty)
                      Container(
                        padding: const EdgeInsets.all(AppTokens.spacingMD),
                        color: AppTokens.primaryGreen.withOpacity(0.1),
                        child: Row(
                          children: [
                            Icon(Icons.class_, color: AppTokens.primaryBrown),
                            const SizedBox(width: AppTokens.spacingSM),
                            Expanded(
                              child: DropdownButtonFormField<int>(
                                value: _selectedClassId,
                                decoration: InputDecoration(
                                  labelText: _classes.length > 1 ? 'اختر الفصل' : 'الفصل',
                                  border: OutlineInputBorder(
                                    borderRadius: BorderRadius.circular(8),
                                  ),
                                  filled: true,
                                  fillColor: Colors.white,
                                ),
                                items: _classes.map((class_) {
                                  return DropdownMenuItem<int>(
                                    value: class_['id'] as int?,
                                    child: Text(
                                      class_['name'] ?? 'غير محدد',
                                      style: const TextStyle(fontWeight: FontWeight.bold),
                                    ),
                                  );
                                }).toList(),
                                onChanged: (value) {
                                  setState(() {
                                    _selectedClassId = value;
                                  });
                                  _loadPresentStudents();
                                  _loadEvaluations();
                                },
                              ),
                            ),
                          ],
                        ),
                      ),
                    // معلومات اليوم
                    Container(
                      width: double.infinity,
                      padding: const EdgeInsets.all(AppTokens.spacingMD),
                      color: AppTokens.primaryGreen.withOpacity(0.1),
                      child: Column(
                        children: [
                          Text(
                            'تاريخ التقييم: ${_formatDate(_evaluationDate!)}',
                            style: Theme.of(context).textTheme.titleMedium?.copyWith(
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                          const SizedBox(height: 8),
                          Text(
                            'عدد الطالبات الحاضرات: ${_presentStudents.length}',
                            style: Theme.of(context).textTheme.bodyMedium,
                          ),
                        ],
                      ),
                    ),
                    
                    Expanded(
                      child: _presentStudents.isEmpty
                          ? Center(
                              child: Column(
                                mainAxisAlignment: MainAxisAlignment.center,
                                children: [
                                  Icon(Icons.people_outline, size: 64, color: Colors.grey),
                                  const SizedBox(height: 16),
                                  Text(
                                    'لا توجد طالبات حاضرات اليوم',
                                    style: Theme.of(context).textTheme.titleLarge,
                                  ),
                                ],
                              ),
                            )
                          : RefreshIndicator(
                              onRefresh: () async {
                                await _loadPresentStudents();
                                await _loadEvaluations();
                              },
                              child: ListView.builder(
                                padding: const EdgeInsets.all(AppTokens.spacingMD),
                                itemCount: _presentStudents.length,
                                itemBuilder: (context, index) {
                                  final student = _presentStudents[index];
                                  final evaluation = _evaluations.firstWhere(
                                    (eval) => eval['student_id'] == student['id'],
                                    orElse: () => {},
                                  );

                                  final hasEvaluation = evaluation.isNotEmpty;
                                  final score = hasEvaluation ? evaluation['recitation_score'] as int? : null;

                                  return Card(
                                    margin: const EdgeInsets.only(bottom: AppTokens.spacingMD),
                                    child: ListTile(
                                      leading: CircleAvatar(
                                        backgroundColor: hasEvaluation
                                            ? AppTokens.successGreen
                                            : AppTokens.primaryGold,
                                        child: Icon(
                                          hasEvaluation ? Icons.check : Icons.mic,
                                          color: Colors.white,
                                        ),
                                      ),
                                      title: Text(student['name'] ?? 'غير معروف'),
                                      subtitle: Column(
                                        crossAxisAlignment: CrossAxisAlignment.start,
                                        children: [
                                          if (student['attendance_time'] != null)
                                            Text('حضور: ${student['attendance_time']}'),
                                          if (hasEvaluation && score != null)
                                            Text(
                                              'الدرجة: $score/20',
                                              style: TextStyle(
                                                fontWeight: FontWeight.bold,
                                                color: AppTokens.successGreen,
                                              ),
                                            ),
                                          if (hasEvaluation && evaluation['notes'] != null)
                                            Text(
                                              'ملاحظات: ${evaluation['notes']}',
                                              style: const TextStyle(fontSize: 12),
                                              maxLines: 2,
                                              overflow: TextOverflow.ellipsis,
                                            ),
                                        ],
                                      ),
                                      trailing: IconButton(
                                        icon: Icon(
                                          hasEvaluation ? Icons.edit : Icons.add_circle,
                                          color: hasEvaluation 
                                              ? AppTokens.infoBlue 
                                              : AppTokens.successGreen,
                                        ),
                                        onPressed: () => _showEvaluationDialog(context, student),
                                      ),
                                      isThreeLine: hasEvaluation && evaluation['notes'] != null,
                                      onTap: () => _showEvaluationDialog(context, student),
                                    ),
                                  );
                                },
                              ),
                            ),
                    ),
                  ],
                ),
    );
  }

  String _formatDate(String dateString) {
    try {
      final date = DateTime.parse(dateString);
      return DateFormat('yyyy-MM-dd', 'ar').format(date);
    } catch (e) {
      return dateString;
    }
  }
}

