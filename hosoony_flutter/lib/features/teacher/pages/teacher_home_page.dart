import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/router/app_router.dart';
import '../../../core/theme/tokens.dart';
import '../../../services/auth_service.dart';
import '../../../services/api_service.dart';
import 'teacher_students_page.dart';
import 'teacher_add_task_page.dart';
import 'teacher_evaluation_page.dart';
import 'teacher_schedule_page.dart';
import 'teacher_class_management_page.dart';
import 'teacher_recitation_evaluation_page.dart';

class TeacherHomePage extends ConsumerStatefulWidget {
  const TeacherHomePage({super.key});

  @override
  ConsumerState<TeacherHomePage> createState() => _TeacherHomePageState();
}

class _TeacherHomePageState extends ConsumerState<TeacherHomePage>
    with TickerProviderStateMixin {
  late AnimationController _animationController;
  late Animation<double> _fadeAnimation;
  late Animation<Offset> _slideAnimation;

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
      begin: const Offset(0, -0.3),
      end: Offset.zero,
    ).animate(CurvedAnimation(
      parent: _animationController,
      curve: Curves.easeOutCubic,
    ));

    _animationController.forward();
    _loadHomeData();
  }

  // متغيرات البيانات
  int _studentsCount = 0;
  int _tasksCount = 0;
  int _evaluationsCount = 0;
  int _sessionsCount = 0;
  bool _isLoadingData = true;

  Future<void> _loadHomeData() async {
    try {
      // Ensure token is set before making API calls
      final authState = ref.read(authStateProvider);
      if (authState.token != null) {
        ApiService.setToken(authState.token!);
      }

      // جلب بيانات الطلاب
      try {
        final students = await ApiService.getClassStudents();
        setState(() {
          _studentsCount = students.length;
        });
      } catch (e) {
        print('خطأ في تحميل الطلاب: $e');
      }

      // جلب بيانات الجدول
      try {
        final schedule = await ApiService.getClassSchedule();
        if (schedule['success'] == true && schedule['schedule'] != null) {
          setState(() {
            _sessionsCount = (schedule['schedule'] as List).length;
          });
        }
      } catch (e) {
        print('خطأ في تحميل الجدول: $e');
      }

      // عدد المهام (يمكن جلبها من API لاحقاً)
      setState(() {
        _tasksCount = 8; // مؤقتاً
        _evaluationsCount = 12; // مؤقتاً
        _isLoadingData = false;
      });
    } catch (e) {
      setState(() {
        _isLoadingData = false;
      });
      print('خطأ في تحميل بيانات الصفحة الرئيسية: $e');
    }
  }

  @override
  void dispose() {
    _animationController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final authState = ref.watch(authStateProvider);
    final user = authState.user;

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
              'لوحة تحكم المعلمة',
              style: TextStyle(
                fontFamily: AppTokens.primaryFontFamily,
                fontWeight: AppTokens.fontWeightBold,
              ),
            ),
          ],
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.notifications_outlined),
            onPressed: () {
              ScaffoldMessenger.of(context).showSnackBar(
                const SnackBar(content: Text('الإشعارات')),
              );
            },
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
      body: AnimatedBuilder(
        animation: _animationController,
        builder: (context, child) {
          return FadeTransition(
            opacity: _fadeAnimation,
            child: SlideTransition(
              position: _slideAnimation,
              child: SingleChildScrollView(
                padding: const EdgeInsets.all(AppTokens.spacingMD),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Welcome Section
                    Container(
                      width: double.infinity,
                      padding: const EdgeInsets.all(AppTokens.spacingLG),
                      decoration: BoxDecoration(
                        gradient: AppTokens.primaryGradient,
                        borderRadius: BorderRadius.circular(AppTokens.radiusLG),
                        boxShadow: AppTokens.shadowMD,
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            'أهلاً وسهلاً، ${user?.name ?? 'المعلمة'}!',
                            style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                              color: AppTokens.neutralWhite,
                              fontWeight: AppTokens.fontWeightBold,
                            ),
                          ),
                          const SizedBox(height: AppTokens.spacingSM),
                          Text(
                            'إدارة طالباتك ومتابعة تقدمهن',
                            style: Theme.of(context).textTheme.bodyLarge?.copyWith(
                              color: AppTokens.neutralWhite.withValues(alpha: 0.9),
                            ),
                          ),
                        ],
                      ),
                    ),
                    
                    const SizedBox(height: AppTokens.spacingLG),
                    
                    // Quick Actions
                    Text(
                      'الإجراءات السريعة',
                      style: Theme.of(context).textTheme.titleLarge?.copyWith(
                        fontWeight: AppTokens.fontWeightBold,
                      ),
                    ),
                    
                    const SizedBox(height: AppTokens.spacingMD),
                    
                    Row(
                      children: [
                        Expanded(
                          child: _buildActionCard(
                            context,
                            'إدارة الطالبات',
                            _isLoadingData ? '...' : '$_studentsCount طالبة',
                            Icons.people,
                            AppTokens.successGreen,
                            () {
                              Navigator.push(
                                context,
                                MaterialPageRoute(
                                  builder: (context) => const TeacherStudentsPage(),
                                ),
                              );
                            },
                          ),
                        ),
                        const SizedBox(width: AppTokens.spacingMD),
                        Expanded(
                          child: _buildActionCard(
                            context,
                            'المهام اليومية',
                            _isLoadingData ? '...' : '$_tasksCount مهام',
                            Icons.task_alt,
                            AppTokens.infoBlue,
                            () {
                              Navigator.push(
                                context,
                                MaterialPageRoute(
                                  builder: (context) => const TeacherAddTaskPage(),
                                ),
                              );
                            },
                          ),
                        ),
                      ],
                    ),
                    
                    const SizedBox(height: AppTokens.spacingMD),
                    
                    Row(
                      children: [
                        Expanded(
                          child: _buildActionCard(
                            context,
                            'التقييمات',
                            _isLoadingData ? '...' : '$_evaluationsCount تقييم',
                            Icons.grade,
                            AppTokens.warningOrange,
                            () {
                              Navigator.push(
                                context,
                                MaterialPageRoute(
                                  builder: (context) => const TeacherEvaluationPage(),
                                ),
                              );
                            },
                          ),
                        ),
                        const SizedBox(width: AppTokens.spacingMD),
                        Expanded(
                          child: _buildActionCard(
                            context,
                            'الجدول الزمني',
                            _isLoadingData ? '...' : '$_sessionsCount جلسات',
                            Icons.schedule,
                            AppTokens.primaryGold,
                            () {
                              Navigator.push(
                                context,
                                MaterialPageRoute(
                                  builder: (context) => const TeacherSchedulePage(),
                                ),
                              );
                            },
                          ),
                        ),
                      ],
                    ),
                    
                    const SizedBox(height: AppTokens.spacingMD),
                    
                    Row(
                      children: [
                        Expanded(
                          child: _buildActionCard(
                            context,
                            'تقييم التلاوة',
                            'خلال الحلقة',
                            Icons.mic,
                            AppTokens.primaryGreen,
                            () {
                              Navigator.push(
                                context,
                                MaterialPageRoute(
                                  builder: (context) => const TeacherRecitationEvaluationPage(),
                                ),
                              );
                            },
                          ),
                        ),
                        const SizedBox(width: AppTokens.spacingMD),
                        Expanded(
                          child: _buildActionCard(
                            context,
                            'إدارة الفصول',
                            'الفصل',
                            Icons.class_,
                            AppTokens.primaryBrown,
                            () {
                              Navigator.push(
                                context,
                                MaterialPageRoute(
                                  builder: (context) => const TeacherClassManagementPage(),
                                ),
                              );
                            },
                          ),
                        ),
                      ],
                    ),
                    
                    const SizedBox(height: AppTokens.spacingLG),
                    
                    // Recent Activity
                    Text(
                      'النشاط الأخير',
                      style: Theme.of(context).textTheme.titleLarge?.copyWith(
                        fontWeight: AppTokens.fontWeightBold,
                      ),
                    ),
                    
                    const SizedBox(height: AppTokens.spacingMD),
                    
                    Card(
                      child: ListTile(
                        leading: const Icon(Icons.person_add, color: AppTokens.successGreen),
                        title: const Text('انضمت طالبة جديدة'),
                        subtitle: const Text('فاطمة أحمد'),
                        trailing: const Text('منذ ساعة'),
                      ),
                    ),
                    
                    Card(
                      child: ListTile(
                        leading: const Icon(Icons.task_alt, color: AppTokens.infoBlue),
                        title: const Text('تم إضافة مهمة جديدة'),
                        subtitle: const Text('حفظ سورة الفاتحة'),
                        trailing: const Text('منذ ساعتين'),
                      ),
                    ),
                  ],
                ),
              ),
            ),
          );
        },
      ),
      
      // Copyright Footer
      bottomSheet: Container(
        width: double.infinity,
        padding: const EdgeInsets.symmetric(
          vertical: AppTokens.spacingSM,
          horizontal: AppTokens.spacingMD,
        ),
        decoration: BoxDecoration(
          color: AppTokens.neutralWhite,
          border: Border(
            top: BorderSide(
              color: AppTokens.neutralMedium.withValues(alpha: 0.2),
              width: 1,
            ),
          ),
        ),
        child: Text(
          'حقوق الملكية لأكاديمية ذكاء للتدريب ٢٠٢٥',
          style: Theme.of(context).textTheme.bodySmall?.copyWith(
            fontFamily: AppTokens.primaryFontFamily,
            color: AppTokens.neutralMedium,
            fontSize: AppTokens.fontSizeXS,
          ),
          textAlign: TextAlign.center,
        ),
      ),
    );
  }

  Widget _buildActionCard(
    BuildContext context,
    String title,
    String value,
    IconData icon,
    Color color,
    VoidCallback onTap,
  ) {
    return GestureDetector(
      onTap: onTap,
      child: Card(
        elevation: 2,
        child: Padding(
          padding: const EdgeInsets.all(AppTokens.spacingMD),
          child: Column(
            children: [
              Icon(
                icon,
                size: AppTokens.iconSizeLG,
                color: color,
              ),
              const SizedBox(height: AppTokens.spacingSM),
              Text(
                value,
                style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                  fontWeight: AppTokens.fontWeightBold,
                  color: color,
                ),
              ),
              const SizedBox(height: AppTokens.spacingXS),
              Text(
                title,
                style: Theme.of(context).textTheme.bodySmall?.copyWith(
                  color: AppTokens.neutralMedium,
                ),
                textAlign: TextAlign.center,
              ),
            ],
          ),
        ),
      ),
    );
  }
}