import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';
import '../../../core/router/app_router.dart';
import '../../../core/theme/tokens.dart';
import '../../../services/auth_service.dart';
import '../../../services/api_service.dart';

class TeacherEvaluationPage extends ConsumerStatefulWidget {
  const TeacherEvaluationPage({super.key});

  @override
  ConsumerState<TeacherEvaluationPage> createState() => _TeacherEvaluationPageState();
}

class _TeacherEvaluationPageState extends ConsumerState<TeacherEvaluationPage>
    with TickerProviderStateMixin {
  late AnimationController _animationController;
  late Animation<double> _fadeAnimation;
  late Animation<Offset> _slideAnimation;
  
  List<Map<String, dynamic>> _students = [];
  List<Map<String, dynamic>> _evaluations = [];
  List<Map<String, dynamic>> _classes = [];
  bool _isLoading = true;
  String? _error;
  String _selectedStudent = '';
  String _selectedPeriod = 'هذا الأسبوع';
  int? _selectedClassId;

  final List<String> _periods = [
    'هذا الأسبوع',
    'هذا الشهر',
    'هذا الفصل',
    'هذا العام',
  ];

  @override
  void initState() {
    super.initState();
    
    _animationController = AnimationController(
      duration: const Duration(milliseconds: 800),
      vsync: this,
    );

    _fadeAnimation = Tween<double>(
      begin: 0.0,
      end: 1.0,
    ).animate(CurvedAnimation(
      parent: _animationController,
      curve: Curves.easeOut,
    ));

    _slideAnimation = Tween<Offset>(
      begin: const Offset(0, 0.1),
      end: Offset.zero,
    ).animate(CurvedAnimation(
      parent: _animationController,
      curve: Curves.easeOutCubic,
    ));

    _loadData();
  }

  @override
  void dispose() {
    _animationController.dispose();
    super.dispose();
  }

  Future<void> _loadData() async {
    try {
      setState(() {
        _isLoading = true;
        _error = null;
      });

      final authState = ref.read(authStateProvider);
      if (authState.token != null) {
        ApiService.setToken(authState.token!);
      }

      // تحميل الفصول أولاً
      await _loadClasses();
      
      // تحميل الطلاب والتقييمات
      if (_selectedClassId != null) {
        await Future.wait([
          _loadStudents(),
          _loadEvaluations(),
        ]);
      }
      
      setState(() {
        _isLoading = false;
      });
      
      _animationController.forward();
    } catch (e) {
      setState(() {
        _error = 'خطأ في تحميل البيانات: ${e.toString()}';
        _isLoading = false;
      });
    }
  }

  Future<void> _loadClasses() async {
    try {
      final classes = await ApiService.getMyClasses();
      setState(() {
        _classes = classes;
        if (classes.isNotEmpty && _selectedClassId == null) {
          _selectedClassId = classes.first['id'] as int?;
        }
      });
    } catch (e) {
      print('خطأ في تحميل الفصول: $e');
    }
  }

  Future<void> _loadStudents() async {
    try {
      final students = await ApiService.getClassStudents(classId: _selectedClassId);
      setState(() {
        _students = students;
      });
    } catch (e) {
      // خطأ في تحميل الطلاب - سيتم التعامل معه في _loadData
      setState(() {
        _students = [];
      });
    }
  }

  Future<void> _loadEvaluations() async {
    try {
      final now = DateTime.now();
      String? startDate;
      String? endDate = DateFormat('yyyy-MM-dd').format(now);

      switch (_selectedPeriod) {
        case 'هذا الأسبوع':
          startDate = DateFormat('yyyy-MM-dd').format(now.subtract(Duration(days: now.weekday - 1)));
          break;
        case 'هذا الشهر':
          startDate = DateFormat('yyyy-MM-dd').format(DateTime(now.year, now.month, 1));
          break;
        case 'هذا الفصل':
          // تقريباً 3 أشهر
          startDate = DateFormat('yyyy-MM-dd').format(now.subtract(const Duration(days: 90)));
          break;
        case 'هذا العام':
          startDate = DateFormat('yyyy-MM-dd').format(DateTime(now.year, 1, 1));
          break;
      }

      final response = await ApiService.getRecitationEvaluations(
        startDate: startDate,
        endDate: endDate,
        classId: _selectedClassId,
      );

      if (response['success'] == true) {
        final evaluations = List<Map<String, dynamic>>.from(response['evaluations'] ?? []);
        
        // تحويل البيانات إلى التنسيق المطلوب
        setState(() {
          _evaluations = evaluations.map((eval) {
            final studentId = eval['student_id'] as int?;
            Map<String, dynamic> student;
            if (_students.isNotEmpty && studentId != null) {
              try {
                student = _students.firstWhere(
                  (s) => s['id'] == studentId,
                  orElse: () => {'name': eval['student']?['name'] ?? 'طالبة غير معروفة'},
                );
              } catch (e) {
                student = {'name': eval['student']?['name'] ?? 'طالبة غير معروفة'};
              }
            } else {
              student = {'name': eval['student']?['name'] ?? 'طالبة غير معروفة'};
            }
            
            return {
              'id': eval['id'],
              'student_id': studentId,
              'student_name': student['name'] ?? 'طالبة غير معروفة',
              'date': eval['evaluated_at'] ?? eval['created_at'] ?? DateFormat('yyyy-MM-dd').format(now),
              'recitation_score': eval['recitation_score'] ?? 0,
              'memorization_score': eval['memorization_score'] ?? 0,
              'understanding_score': eval['understanding_score'] ?? 0,
              'participation_score': eval['participation_score'] ?? 0,
              'overall_score': _calculateOverallScore(eval),
              'comments': eval['notes'] ?? eval['comments'] ?? '',
              'teacher_name': eval['teacher']?['name'] ?? '',
            };
          }).toList();
        });
      } else {
        setState(() {
          _evaluations = [];
        });
      }
    } catch (e) {
      // خطأ في تحميل التقييمات - سيتم التعامل معه في _loadData
      setState(() {
        _evaluations = [];
      });
    }
  }

  double _calculateOverallScore(Map<String, dynamic> eval) {
    final recitation = (eval['recitation_score'] ?? 0) as int;
    final memorization = (eval['memorization_score'] ?? 0) as int;
    final understanding = (eval['understanding_score'] ?? 0) as int;
    final participation = (eval['participation_score'] ?? 0) as int;
    
    return (recitation + memorization + understanding + participation) / 4.0;
  }

  List<Map<String, dynamic>> get _filteredEvaluations {
    if (_selectedStudent.isEmpty) return _evaluations;
    return _evaluations.where((eval) => 
        eval['student_name'].toString().contains(_selectedStudent)).toList();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Row(
          children: [
            ClipRRect(
              borderRadius: BorderRadius.circular(AppTokens.radiusSM),
              child: Image.asset(
                'assets/images/hosoony-logo.png',
                width: AppTokens.iconSizeMD,
                height: AppTokens.iconSizeMD,
                fit: BoxFit.contain,
                errorBuilder: (context, error, stackTrace) {
                  return const Icon(
                    Icons.mosque,
                    size: AppTokens.iconSizeMD,
                    color: AppTokens.neutralWhite,
                  );
                },
              ),
            ),
            const SizedBox(width: AppTokens.spacingSM),
            const Text(
              'تقييم الطالبات',
              style: TextStyle(
                fontFamily: AppTokens.primaryFontFamily,
                fontWeight: AppTokens.fontWeightBold,
              ),
            ),
          ],
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.add),
            onPressed: () {
              _showAddEvaluationDialog();
            },
          ),
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _loadData,
          ),
          IconButton(
            icon: const Icon(Icons.logout),
            onPressed: () async {
              await ref.read(authStateProvider.notifier).logout();
              if (mounted) {
                AppRouter.goToLogin(context);
              }
            },
          ),
        ],
      ),
      body: _isLoading
          ? const Center(
              child: CircularProgressIndicator(
                valueColor: AlwaysStoppedAnimation<Color>(AppTokens.primaryGreen),
              ),
            )
          : _error != null
              ? Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Icon(
                        Icons.error_outline,
                        size: 64,
                        color: AppTokens.errorRed,
                      ),
                      const SizedBox(height: 16),
                      Text(
                        _error!,
                        style: TextStyle(
                          fontFamily: AppTokens.primaryFontFamily,
                          fontSize: 16,
                          color: AppTokens.errorRed,
                        ),
                        textAlign: TextAlign.center,
                      ),
                      const SizedBox(height: 16),
                      ElevatedButton(
                        onPressed: _loadData,
                        style: ElevatedButton.styleFrom(
                          backgroundColor: AppTokens.primaryGreen,
                          foregroundColor: AppTokens.neutralWhite,
                        ),
                        child: const Text(
                          'إعادة المحاولة',
                          style: TextStyle(fontFamily: AppTokens.primaryFontFamily),
                        ),
                      ),
                    ],
                  ),
                )
              : Column(
                  children: [
                    // Filters Section
                    Container(
                      padding: const EdgeInsets.all(AppTokens.spacingMD),
                      color: AppTokens.neutralWhite,
                      child: Column(
                        children: [
                          // Student Filter
                          Row(
                            children: [
                              Expanded(
                                child: DropdownButtonFormField<String>(
                                  value: _selectedStudent.isEmpty ? null : _selectedStudent,
                                  decoration: InputDecoration(
                                    labelText: 'اختر الطالبة',
                                    border: OutlineInputBorder(
                                      borderRadius: BorderRadius.circular(AppTokens.radiusMD),
                                    ),
                                  ),
                                  items: [
                                    const DropdownMenuItem(
                                      value: '',
                                      child: Text('جميع الطالبات'),
                                    ),
                                    ..._students.map((student) {
                                      return DropdownMenuItem(
                                        value: student['name'],
                                        child: Text(student['name']),
                                      );
                                    }),
                                  ],
                                  onChanged: (value) {
                                    setState(() {
                                      _selectedStudent = value ?? '';
                                    });
                                  },
                                ),
                              ),
                              const SizedBox(width: AppTokens.spacingMD),
                              Expanded(
                                child: DropdownButtonFormField<String>(
                                  value: _selectedPeriod,
                                  decoration: InputDecoration(
                                    labelText: 'الفترة الزمنية',
                                    border: OutlineInputBorder(
                                      borderRadius: BorderRadius.circular(AppTokens.radiusMD),
                                    ),
                                  ),
                                  items: _periods.map((period) {
                                    return DropdownMenuItem(
                                      value: period,
                                      child: Text(period),
                                    );
                                  }).toList(),
                                  onChanged: (value) {
                                    setState(() {
                                      _selectedPeriod = value!;
                                    });
                                    _loadEvaluations();
                                  },
                                ),
                              ),
                            ],
                          ),
                        ],
                      ),
                    ),
                    
                    // Evaluations List
                    Expanded(
                      child: AnimatedBuilder(
                        animation: _animationController,
                        builder: (context, child) {
                          return FadeTransition(
                            opacity: _fadeAnimation,
                            child: SlideTransition(
                              position: _slideAnimation,
                              child: _buildEvaluationsList(),
                            ),
                          );
                        },
                      ),
                    ),
                  ],
                ),
    );
  }

  Widget _buildEvaluationsList() {
    if (_filteredEvaluations.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              Icons.assessment_outlined,
              size: 64,
              color: AppTokens.neutralGray,
            ),
            const SizedBox(height: 16),
            Text(
              'لا توجد تقييمات',
              style: TextStyle(
                fontFamily: AppTokens.primaryFontFamily,
                fontSize: 18,
                color: AppTokens.neutralGray,
              ),
            ),
          ],
        ),
      );
    }

    return ListView.builder(
      padding: const EdgeInsets.all(AppTokens.spacingMD),
      itemCount: _filteredEvaluations.length,
      itemBuilder: (context, index) {
        final evaluation = _filteredEvaluations[index];
        return _buildEvaluationCard(evaluation);
      },
    );
  }

  Widget _buildEvaluationCard(Map<String, dynamic> evaluation) {
    final overallScore = evaluation['overall_score'] as double;
    Color scoreColor;
    if (overallScore >= 90) {
      scoreColor = AppTokens.successGreen;
    } else if (overallScore >= 80) {
      scoreColor = AppTokens.infoBlue;
    } else if (overallScore >= 70) {
      scoreColor = AppTokens.warningOrange;
    } else {
      scoreColor = AppTokens.errorRed;
    }

    return Card(
      margin: const EdgeInsets.only(bottom: AppTokens.spacingMD),
      child: Padding(
        padding: const EdgeInsets.all(AppTokens.spacingMD),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Header
            Row(
              children: [
                CircleAvatar(
                  radius: 25,
                  backgroundColor: scoreColor.withValues(alpha: 0.1),
                  child: Icon(
                    Icons.person,
                    color: scoreColor,
                    size: AppTokens.iconSizeMD,
                  ),
                ),
                const SizedBox(width: AppTokens.spacingMD),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        evaluation['student_name'],
                        style: Theme.of(context).textTheme.titleMedium?.copyWith(
                          fontWeight: AppTokens.fontWeightBold,
                        ),
                      ),
                      Text(
                        'تاريخ التقييم: ${evaluation['date']}',
                        style: Theme.of(context).textTheme.bodySmall?.copyWith(
                          color: AppTokens.neutralMedium,
                        ),
                      ),
                    ],
                  ),
                ),
                Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: AppTokens.spacingSM,
                    vertical: AppTokens.spacingXS,
                  ),
                  decoration: BoxDecoration(
                    color: scoreColor.withValues(alpha: 0.1),
                    borderRadius: BorderRadius.circular(AppTokens.radiusSM),
                  ),
                  child: Text(
                    '${overallScore.toStringAsFixed(1)}%',
                    style: TextStyle(
                      color: scoreColor,
                      fontWeight: AppTokens.fontWeightBold,
                      fontSize: AppTokens.fontSizeSM,
                    ),
                  ),
                ),
              ],
            ),
            
            const SizedBox(height: AppTokens.spacingMD),
            
            // Scores Grid
            GridView.count(
              shrinkWrap: true,
              physics: const NeverScrollableScrollPhysics(),
              crossAxisCount: 2,
              childAspectRatio: 3,
              crossAxisSpacing: AppTokens.spacingSM,
              mainAxisSpacing: AppTokens.spacingSM,
              children: [
                _buildScoreItem('القراءة', evaluation['recitation_score'], Icons.book),
                _buildScoreItem('الحفظ', evaluation['memorization_score'], Icons.psychology),
                _buildScoreItem('الفهم', evaluation['understanding_score'], Icons.lightbulb),
                _buildScoreItem('المشاركة', evaluation['participation_score'], Icons.pan_tool),
              ],
            ),
            
            const SizedBox(height: AppTokens.spacingMD),
            
            // Comments
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(AppTokens.spacingMD),
              decoration: BoxDecoration(
                color: AppTokens.neutralLight,
                borderRadius: BorderRadius.circular(AppTokens.radiusMD),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'التعليقات',
                    style: Theme.of(context).textTheme.titleSmall?.copyWith(
                      fontWeight: AppTokens.fontWeightBold,
                    ),
                  ),
                  const SizedBox(height: AppTokens.spacingXS),
                  Text(
                    evaluation['comments'],
                    style: Theme.of(context).textTheme.bodyMedium,
                  ),
                ],
              ),
            ),
            
            const SizedBox(height: AppTokens.spacingMD),
            
            // Actions
            Row(
              mainAxisAlignment: MainAxisAlignment.end,
              children: [
                TextButton.icon(
                  onPressed: () => _editEvaluation(evaluation),
                  icon: const Icon(Icons.edit, size: 16),
                  label: const Text('تعديل'),
                ),
                const SizedBox(width: AppTokens.spacingSM),
                TextButton.icon(
                  onPressed: () => _deleteEvaluation(evaluation),
                  icon: const Icon(Icons.delete, size: 16),
                  label: const Text('حذف'),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildScoreItem(String label, int score, IconData icon) {
    Color scoreColor;
    if (score >= 90) {
      scoreColor = AppTokens.successGreen;
    } else if (score >= 80) {
      scoreColor = AppTokens.infoBlue;
    } else if (score >= 70) {
      scoreColor = AppTokens.warningOrange;
    } else {
      scoreColor = AppTokens.errorRed;
    }

    return Container(
      padding: const EdgeInsets.all(AppTokens.spacingSM),
      decoration: BoxDecoration(
        color: scoreColor.withValues(alpha: 0.1),
        borderRadius: BorderRadius.circular(AppTokens.radiusSM),
        border: Border.all(color: scoreColor.withValues(alpha: 0.3)),
      ),
      child: Row(
        children: [
          Icon(icon, color: scoreColor, size: 16),
          const SizedBox(width: AppTokens.spacingXS),
          Expanded(
            child: Text(
              label,
              style: TextStyle(
                fontSize: AppTokens.fontSizeXS,
                fontWeight: AppTokens.fontWeightMedium,
                color: AppTokens.neutralDark,
              ),
            ),
          ),
          Text(
            '$score',
            style: TextStyle(
              fontSize: AppTokens.fontSizeXS,
              fontWeight: AppTokens.fontWeightBold,
              color: scoreColor,
            ),
          ),
        ],
      ),
    );
  }

  void _showAddEvaluationDialog() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('إضافة تقييم جديد'),
        content: const Text('هذه الميزة ستكون متاحة قريباً'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('موافق'),
          ),
        ],
      ),
    );
  }

  void _editEvaluation(Map<String, dynamic> evaluation) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text('تعديل تقييم ${evaluation['student_name']}')),
    );
  }

  void _deleteEvaluation(Map<String, dynamic> evaluation) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('تأكيد الحذف'),
        content: Text('هل أنت متأكد من حذف تقييم ${evaluation['student_name']}؟'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('إلغاء'),
          ),
          TextButton(
            onPressed: () {
              Navigator.pop(context);
              ScaffoldMessenger.of(context).showSnackBar(
                SnackBar(content: Text('تم حذف تقييم ${evaluation['student_name']}')),
              );
            },
            child: const Text('حذف'),
          ),
        ],
      ),
    );
  }
}
