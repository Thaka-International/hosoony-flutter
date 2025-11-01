import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/router/app_router.dart';
import '../../../core/theme/tokens.dart';
import '../../../services/auth_service.dart';

class AdminStatisticsPage extends ConsumerStatefulWidget {
  const AdminStatisticsPage({super.key});

  @override
  ConsumerState<AdminStatisticsPage> createState() => _AdminStatisticsPageState();
}

class _AdminStatisticsPageState extends ConsumerState<AdminStatisticsPage>
    with TickerProviderStateMixin {
  late AnimationController _animationController;
  late Animation<double> _fadeAnimation;
  late Animation<double> _slideAnimation;
  
  Map<String, dynamic> _statistics = {};
  bool _isLoading = true;
  String? _error;
  String _selectedPeriod = 'هذا الشهر';

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

    _loadStatistics();
  }

  @override
  void dispose() {
    _animationController.dispose();
    super.dispose();
  }

  Future<void> _loadStatistics() async {
    try {
      setState(() {
        _isLoading = true;
        _error = null;
      });

      // Mock data
      await Future.delayed(const Duration(milliseconds: 1000));
      
      setState(() {
        _statistics = {
          'overview': {
            'total_users': 150,
            'active_users': 142,
            'total_teachers': 8,
            'total_students': 135,
            'total_assistants': 5,
            'total_admins': 2,
          },
          'activity': {
            'daily_logins': 89,
            'weekly_logins': 456,
            'monthly_logins': 1847,
            'average_session_duration': '32 دقيقة',
            'peak_hours': '9:00 - 11:00 صباحاً',
          },
          'academic': {
            'total_tasks': 1250,
            'completed_tasks': 1180,
            'pending_tasks': 70,
            'average_completion_rate': 94.4,
            'total_evaluations': 890,
            'average_score': 87.2,
          },
          'support': {
            'total_tickets': 234,
            'resolved_tickets': 218,
            'pending_tickets': 16,
            'average_resolution_time': '2.3 ساعة',
            'satisfaction_rate': 91.5,
            'total_inquiries': 156,
            'answered_inquiries': 148,
          },
          'system': {
            'uptime': 99.8,
            'storage_used': '2.3 GB',
            'storage_total': '10 GB',
            'last_backup': '2024-01-15 02:00',
            'security_incidents': 0,
            'performance_score': 95.2,
          },
        };
        
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
              'الإحصائيات الشاملة',
              style: TextStyle(
                fontFamily: AppTokens.primaryFontFamily,
                fontWeight: AppTokens.fontWeightBold,
              ),
            ),
          ],
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _loadStatistics,
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
                        onPressed: _loadStatistics,
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
                    // Header Section
                    Container(
                      padding: const EdgeInsets.all(AppTokens.spacingMD),
                      color: AppTokens.neutralWhite,
                      child: Column(
                        children: [
                          // Period Selector
                          Row(
                            children: [
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
                    
                    // Statistics Content
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
                              child: _buildStatisticsContent(),
                            ),
                          );
                        },
                      ),
                    ),
                  ],
                ),
    );
  }

  Widget _buildStatisticsContent() {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(AppTokens.spacingMD),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Overview Section
          _buildSection(
            'نظرة عامة',
            Icons.dashboard,
            AppTokens.primaryGreen,
            _buildOverviewStats(),
          ),
          
          const SizedBox(height: AppTokens.spacingLG),
          
          // Activity Section
          _buildSection(
            'النشاط',
            Icons.trending_up,
            AppTokens.infoBlue,
            _buildActivityStats(),
          ),
          
          const SizedBox(height: AppTokens.spacingLG),
          
          // Academic Section
          _buildSection(
            'الأكاديمي',
            Icons.school,
            AppTokens.successGreen,
            _buildAcademicStats(),
          ),
          
          const SizedBox(height: AppTokens.spacingLG),
          
          // Support Section
          _buildSection(
            'الدعم',
            Icons.support_agent,
            AppTokens.warningOrange,
            _buildSupportStats(),
          ),
          
          const SizedBox(height: AppTokens.spacingLG),
          
          // System Section
          _buildSection(
            'النظام',
            Icons.settings,
            AppTokens.primaryGold,
            _buildSystemStats(),
          ),
          
          const SizedBox(height: AppTokens.spacingXL),
        ],
      ),
    );
  }

  Widget _buildSection(String title, IconData icon, Color color, Widget content) {
    return Container(
      padding: const EdgeInsets.all(AppTokens.spacingMD),
      decoration: BoxDecoration(
        color: AppTokens.neutralWhite,
        borderRadius: BorderRadius.circular(AppTokens.radiusLG),
        border: Border.all(color: color.withValues(alpha: 0.2)),
        boxShadow: [
          BoxShadow(
            color: color.withValues(alpha: 0.1),
            blurRadius: 8,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                padding: const EdgeInsets.all(AppTokens.spacingSM),
                decoration: BoxDecoration(
                  color: color.withValues(alpha: 0.1),
                  borderRadius: BorderRadius.circular(AppTokens.radiusSM),
                ),
                child: Icon(icon, color: color, size: AppTokens.iconSizeMD),
              ),
              const SizedBox(width: AppTokens.spacingMD),
              Text(
                title,
                style: Theme.of(context).textTheme.titleLarge?.copyWith(
                  fontWeight: AppTokens.fontWeightBold,
                  color: color,
                ),
              ),
            ],
          ),
          const SizedBox(height: AppTokens.spacingMD),
          content,
        ],
      ),
    );
  }

  Widget _buildOverviewStats() {
    final overview = _statistics['overview'] as Map<String, dynamic>;
    return GridView.count(
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      crossAxisCount: 2,
      childAspectRatio: 2,
      crossAxisSpacing: AppTokens.spacingSM,
      mainAxisSpacing: AppTokens.spacingSM,
      children: [
        _buildStatItem('إجمالي المستخدمين', '${overview['total_users']}', Icons.people, AppTokens.infoBlue),
        _buildStatItem('المستخدمين النشطين', '${overview['active_users']}', Icons.person, AppTokens.successGreen),
        _buildStatItem('المعلمات', '${overview['total_teachers']}', Icons.school, AppTokens.warningOrange),
        _buildStatItem('الطالبات', '${overview['total_students']}', Icons.person, AppTokens.primaryGold),
        _buildStatItem('المساعدات', '${overview['total_assistants']}', Icons.support_agent, AppTokens.primaryGreen),
        _buildStatItem('المديرين', '${overview['total_admins']}', Icons.admin_panel_settings, AppTokens.errorRed),
      ],
    );
  }

  Widget _buildActivityStats() {
    final activity = _statistics['activity'] as Map<String, dynamic>;
    return Column(
      children: [
        Row(
          children: [
            Expanded(child: _buildStatItem('دخول يومي', '${activity['daily_logins']}', Icons.login, AppTokens.infoBlue)),
            Expanded(child: _buildStatItem('دخول أسبوعي', '${activity['weekly_logins']}', Icons.calendar_view_week, AppTokens.successGreen)),
          ],
        ),
        const SizedBox(height: AppTokens.spacingSM),
        Row(
          children: [
            Expanded(child: _buildStatItem('دخول شهري', '${activity['monthly_logins']}', Icons.calendar_month, AppTokens.warningOrange)),
            Expanded(child: _buildStatItem('متوسط الجلسة', '${activity['average_session_duration']}', Icons.timer, AppTokens.primaryGold)),
          ],
        ),
        const SizedBox(height: AppTokens.spacingSM),
        _buildStatItem('ساعات الذروة', '${activity['peak_hours']}', Icons.access_time, AppTokens.primaryGreen),
      ],
    );
  }

  Widget _buildAcademicStats() {
    final academic = _statistics['academic'] as Map<String, dynamic>;
    return Column(
      children: [
        Row(
          children: [
            Expanded(child: _buildStatItem('إجمالي المهام', '${academic['total_tasks']}', Icons.task_alt, AppTokens.infoBlue)),
            Expanded(child: _buildStatItem('المهام المكتملة', '${academic['completed_tasks']}', Icons.check_circle, AppTokens.successGreen)),
          ],
        ),
        const SizedBox(height: AppTokens.spacingSM),
        Row(
          children: [
            Expanded(child: _buildStatItem('المهام المعلقة', '${academic['pending_tasks']}', Icons.pending, AppTokens.warningOrange)),
            Expanded(child: _buildStatItem('معدل الإكمال', '${academic['average_completion_rate']}%', Icons.trending_up, AppTokens.primaryGold)),
          ],
        ),
        const SizedBox(height: AppTokens.spacingSM),
        Row(
          children: [
            Expanded(child: _buildStatItem('إجمالي التقييمات', '${academic['total_evaluations']}', Icons.assessment, AppTokens.primaryGreen)),
            Expanded(child: _buildStatItem('متوسط النقاط', '${academic['average_score']}', Icons.grade, AppTokens.errorRed)),
          ],
        ),
      ],
    );
  }

  Widget _buildSupportStats() {
    final support = _statistics['support'] as Map<String, dynamic>;
    return Column(
      children: [
        Row(
          children: [
            Expanded(child: _buildStatItem('إجمالي التذاكر', '${support['total_tickets']}', Icons.support_agent, AppTokens.infoBlue)),
            Expanded(child: _buildStatItem('التذاكر المحلولة', '${support['resolved_tickets']}', Icons.check_circle, AppTokens.successGreen)),
          ],
        ),
        const SizedBox(height: AppTokens.spacingSM),
        Row(
          children: [
            Expanded(child: _buildStatItem('التذاكر المعلقة', '${support['pending_tickets']}', Icons.pending, AppTokens.warningOrange)),
            Expanded(child: _buildStatItem('متوسط وقت الحل', '${support['average_resolution_time']}', Icons.timer, AppTokens.primaryGold)),
          ],
        ),
        const SizedBox(height: AppTokens.spacingSM),
        Row(
          children: [
            Expanded(child: _buildStatItem('معدل الرضا', '${support['satisfaction_rate']}%', Icons.sentiment_satisfied, AppTokens.primaryGreen)),
            Expanded(child: _buildStatItem('إجمالي الاستفسارات', '${support['total_inquiries']}', Icons.help_outline, AppTokens.errorRed)),
          ],
        ),
        const SizedBox(height: AppTokens.spacingSM),
        _buildStatItem('الاستفسارات المُجابة', '${support['answered_inquiries']}', Icons.check_circle_outline, AppTokens.successGreen),
      ],
    );
  }

  Widget _buildSystemStats() {
    final system = _statistics['system'] as Map<String, dynamic>;
    return Column(
      children: [
        Row(
          children: [
            Expanded(child: _buildStatItem('وقت التشغيل', '${system['uptime']}%', Icons.schedule, AppTokens.successGreen)),
            Expanded(child: _buildStatItem('التخزين المستخدم', '${system['storage_used']}', Icons.storage, AppTokens.warningOrange)),
          ],
        ),
        const SizedBox(height: AppTokens.spacingSM),
        Row(
          children: [
            Expanded(child: _buildStatItem('إجمالي التخزين', '${system['storage_total']}', Icons.storage, AppTokens.infoBlue)),
            Expanded(child: _buildStatItem('آخر نسخة احتياطية', '${system['last_backup']}', Icons.backup, AppTokens.primaryGold)),
          ],
        ),
        const SizedBox(height: AppTokens.spacingSM),
        Row(
          children: [
            Expanded(child: _buildStatItem('حوادث الأمان', '${system['security_incidents']}', Icons.security, AppTokens.errorRed)),
            Expanded(child: _buildStatItem('درجة الأداء', '${system['performance_score']}', Icons.speed, AppTokens.primaryGreen)),
          ],
        ),
      ],
    );
  }

  Widget _buildStatItem(String label, String value, IconData icon, Color color) {
    return Container(
      padding: const EdgeInsets.all(AppTokens.spacingMD),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.1),
        borderRadius: BorderRadius.circular(AppTokens.radiusMD),
        border: Border.all(color: color.withValues(alpha: 0.3)),
      ),
      child: Column(
        children: [
          Icon(icon, color: color, size: AppTokens.iconSizeMD),
          const SizedBox(height: AppTokens.spacingSM),
          Text(
            value,
            style: TextStyle(
              fontSize: AppTokens.fontSizeLG,
              fontWeight: AppTokens.fontWeightBold,
              color: color,
            ),
          ),
          const SizedBox(height: AppTokens.spacingXS),
          Text(
            label,
            style: TextStyle(
              fontSize: AppTokens.fontSizeSM,
              color: AppTokens.neutralMedium,
            ),
            textAlign: TextAlign.center,
          ),
        ],
      ),
    );
  }
}
