import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/router/app_router.dart';
import '../../../core/theme/tokens.dart';
import '../../../services/auth_service.dart';
import '../../../shared_ui/widgets/copyright_widget.dart';
import 'admin_users_page.dart';
import 'admin_statistics_page.dart';
import 'admin_system_page.dart';
import 'api_test_page.dart';
import 'comprehensive_test_page.dart';
import 'comprehensive_api_test_page.dart';
import 'server_info_page.dart';
import 'network_monitor_page.dart';
import 'phone_auth_test_page.dart';
import 'test_report_page.dart';

class AdminHomePage extends ConsumerStatefulWidget {
  const AdminHomePage({super.key});

  @override
  ConsumerState<AdminHomePage> createState() => _AdminHomePageState();
}

class _AdminHomePageState extends ConsumerState<AdminHomePage>
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
              'لوحة تحكم المدير',
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
                            'أهلاً وسهلاً، ${user?.name ?? 'المدير'}!',
                            style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                              color: AppTokens.neutralWhite,
                              fontWeight: AppTokens.fontWeightBold,
                            ),
                          ),
                          const SizedBox(height: AppTokens.spacingSM),
                          Text(
                            'إدارة النظام والإشراف الشامل',
                            style: Theme.of(context).textTheme.bodyLarge?.copyWith(
                              color: AppTokens.neutralWhite.withValues(alpha: 0.9),
                            ),
                          ),
                        ],
                      ),
                    ),
                    
                    const SizedBox(height: AppTokens.spacingLG),
                    
                    // Statistics
                    Text(
                      'الإحصائيات العامة',
                      style: Theme.of(context).textTheme.titleLarge?.copyWith(
                        fontWeight: AppTokens.fontWeightBold,
                      ),
                    ),
                    
                    const SizedBox(height: AppTokens.spacingMD),
                    
                    Row(
                      children: [
                        Expanded(
                          child: _buildStatCard(
                            context,
                            'إجمالي المستخدمين',
                            '150',
                            Icons.people,
                            AppTokens.successGreen,
                          ),
                        ),
                        const SizedBox(width: AppTokens.spacingMD),
                        Expanded(
                          child: _buildStatCard(
                            context,
                            'الطالبات',
                            '120',
                            Icons.school,
                            AppTokens.infoBlue,
                          ),
                        ),
                      ],
                    ),
                    
                    const SizedBox(height: AppTokens.spacingMD),
                    
                    Row(
                      children: [
                        Expanded(
                          child: _buildStatCard(
                            context,
                            'المعلمات',
                            '15',
                            Icons.person,
                            AppTokens.warningOrange,
                          ),
                        ),
                        const SizedBox(width: AppTokens.spacingMD),
                        Expanded(
                          child: _buildStatCard(
                            context,
                            'المساعدات',
                            '10',
                            Icons.support_agent,
                            AppTokens.primaryGold,
                          ),
                        ),
                      ],
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
                            'إدارة المستخدمين',
                            Icons.admin_panel_settings,
                            AppTokens.successGreen,
                            () {
                              Navigator.push(
                                context,
                                MaterialPageRoute(
                                  builder: (context) => const AdminUsersPage(),
                                ),
                              );
                            },
                          ),
                        ),
                        const SizedBox(width: AppTokens.spacingMD),
                        Expanded(
                          child: _buildActionCard(
                            context,
                            'الإحصائيات الشاملة',
                            Icons.analytics,
                            AppTokens.infoBlue,
                            () {
                              Navigator.push(
                                context,
                                MaterialPageRoute(
                                  builder: (context) => const AdminStatisticsPage(),
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
                            'إدارة النظام',
                            Icons.settings,
                            AppTokens.warningOrange,
                            () {
                              Navigator.push(
                                context,
                                MaterialPageRoute(
                                  builder: (context) => const AdminSystemPage(),
                                ),
                              );
                            },
                          ),
                        ),
                        const SizedBox(width: AppTokens.spacingMD),
                        Expanded(
                          child: _buildActionCard(
                            context,
                            'اختبار APIs',
                            Icons.api,
                            AppTokens.primaryGold,
                            () {
                              Navigator.push(
                                context,
                                MaterialPageRoute(
                                  builder: (context) => const ApiTestPage(),
                                ),
                              );
                            },
                          ),
                        ),
                      ],
                    ),
                    
                    const SizedBox(height: AppTokens.spacingMD),
                    
                    // قسم الاختبارات الشاملة
                    Text(
                      'أدوات الاختبار والمراقبة',
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
                            'اختبار شامل',
                            Icons.science,
                            AppTokens.primaryGreen,
                            () {
                              Navigator.push(
                                context,
                                MaterialPageRoute(
                                  builder: (context) => const ComprehensiveTestPage(),
                                ),
                              );
                            },
                          ),
                        ),
                        const SizedBox(width: AppTokens.spacingMD),
                        Expanded(
                          child: _buildActionCard(
                            context,
                            'مراقب الشبكة',
                            Icons.network_check,
                            AppTokens.primaryBlue,
                            () {
                              Navigator.push(
                                context,
                                MaterialPageRoute(
                                  builder: (context) => const NetworkMonitorPage(),
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
                            'اختبار APIs',
                            Icons.api,
                            AppTokens.warningOrange,
                            () {
                              Navigator.push(
                                context,
                                MaterialPageRoute(
                                  builder: (context) => const ComprehensiveApiTestPage(),
                                ),
                              );
                            },
                          ),
                        ),
                        const SizedBox(width: AppTokens.spacingMD),
                        Expanded(
                          child: _buildActionCard(
                            context,
                            'معلومات الخادم',
                            Icons.info,
                            AppTokens.primaryBlue,
                            () {
                              Navigator.push(
                                context,
                                MaterialPageRoute(
                                  builder: (context) => const ServerInfoPage(),
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
                        title: const Text('تم إضافة مستخدم جديد'),
                        subtitle: const Text('معلمة جديدة'),
                        trailing: const Text('منذ ساعة'),
                      ),
                    ),
                    
                    Card(
                      child: ListTile(
                        leading: const Icon(Icons.assessment, color: AppTokens.infoBlue),
                        title: const Text('تقرير شهري جديد'),
                        subtitle: const Text('تقرير شهر أكتوبر'),
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

  Widget _buildStatCard(
    BuildContext context,
    String title,
    String value,
    IconData icon,
    Color color,
  ) {
    return Card(
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
    );
  }

  Widget _buildActionCard(
    BuildContext context,
    String title,
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
                title,
                style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                  fontWeight: AppTokens.fontWeightMedium,
                  color: AppTokens.neutralDark,
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
