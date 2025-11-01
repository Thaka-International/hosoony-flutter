import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/router/app_router.dart';
import '../../../core/theme/tokens.dart';
import '../../../services/auth_service.dart';

class TeacherEvaluationPage extends ConsumerStatefulWidget {
  const TeacherEvaluationPage({super.key});

  @override
  ConsumerState<TeacherEvaluationPage> createState() => _TeacherEvaluationPageState();
}

class _TeacherEvaluationPageState extends ConsumerState<TeacherEvaluationPage>
    with TickerProviderStateMixin {
  late AnimationController _animationController;
  late Animation<double> _fadeAnimation;
  late Animation<double> _slideAnimation;
  
  List<Map<String, dynamic>> _students = [];
  List<Map<String, dynamic>> _evaluations = [];
  bool _isLoading = true;
  String? _error;
  String _selectedStudent = '';
  String _selectedPeriod = 'هذا الأسبوع';

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

    _slideAnimation = Tween<double>(
      begin: 30.0,
      end: 0.0,
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

      // Mock data
      await Future.delayed(const Duration(milliseconds: 1000));
      
      setState(() {
        _students = [
          {
            'id': 1,
            'name': 'فاطمة أحمد محمد',
            'level': 'مبتدئة',
            'avatar': null,
          },
          {
            'id': 2,
            'name': 'مريم حسن علي',
            'level': 'متوسطة',
            'avatar': null,
          },
          {
            'id': 3,
            'name': 'سارة محمد أحمد',
            'level': 'متقدمة',
            'avatar': null,
          },
          {
            'id': 4,
            'name': 'نور الدين محمد',
            'level': 'مبتدئة',
            'avatar': null,
          },
          {
            'id': 5,
            'name': 'آمنة عبدالله',
            'level': 'متوسطة',
            'avatar': null,
          },
        ];

        _evaluations = [
          {
            'id': 1,
            'student_id': 1,
            'student_name': 'فاطمة أحمد محمد',
            'date': '2024-01-15',
            'recitation_score': 85,
            'memorization_score': 90,
            'understanding_score': 80,
            'participation_score': 95,
            'overall_score': 87.5,
            'comments': 'أداء ممتاز في الحفظ والمشاركة، تحتاج تحسين في الفهم',
            'teacher_name': 'أ. فاطمة',
          },
          {
            'id': 2,
            'student_id': 2,
            'student_name': 'مريم حسن علي',
            'date': '2024-01-14',
            'recitation_score': 95,
            'memorization_score': 88,
            'understanding_score': 92,
            'participation_score': 90,
            'overall_score': 91.25,
            'comments': 'طالبة مجتهدة ومتفوقة في جميع المجالات',
            'teacher_name': 'أ. فاطمة',
          },
          {
            'id': 3,
            'student_id': 3,
            'student_name': 'سارة محمد أحمد',
            'date': '2024-01-13',
            'recitation_score': 90,
            'memorization_score': 95,
            'understanding_score': 88,
            'participation_score': 85,
            'overall_score': 89.5,
            'comments': 'ممتازة في الحفظ، تحتاج تحسين في المشاركة',
            'teacher_name': 'أ. فاطمة',
          },
        ];
        
        _isLoading = false;
      });
      
      _animationController.forward();
    } catch (e) {
      setState(() {
        _error = e.toString();
        _isLoading = false;
      });
    }
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
              AppRouter.goToLogin(context);
            },
          ),
        ],
      ),
      body: _isLoading
          ? Center(
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
                              position: Tween<Offset>(
                                begin: const Offset(0, 0.3),
                                end: Offset.zero,
                              ).animate(_slideAnimation),
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
