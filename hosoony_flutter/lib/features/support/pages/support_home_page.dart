import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/router/app_router.dart';
import '../../../core/theme/tokens.dart';
import '../../../services/auth_service.dart';
import '../../../shared_ui/widgets/copyright_widget.dart';
import 'support_students_page.dart';
import 'support_inquiries_page.dart';
import 'support_reports_page.dart';

class SupportHomePage extends ConsumerStatefulWidget {
  const SupportHomePage({super.key});

  @override
  ConsumerState<SupportHomePage> createState() => _SupportHomePageState();
}

class _SupportHomePageState extends ConsumerState<SupportHomePage>
    with TickerProviderStateMixin {
  late AnimationController _animationController;
  late Animation<double> _fadeAnimation;
  late Animation<double> _slideAnimation;

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
              'لوحة تحكم المساعدة',
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
              position: Tween<Offset>(
                begin: const Offset(0, 0.3),
                end: Offset.zero,
              ).animate(_slideAnimation),
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
                            'أهلاً وسهلاً، ${user?.name ?? 'المساعدة'}!',
                            style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                              color: AppTokens.neutralWhite,
                              fontWeight: AppTokens.fontWeightBold,
                            ),
                          ),
                          const SizedBox(height: AppTokens.spacingSM),
                          Text(
                            'دعم المستخدمين وحل المشاكل',
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
                            'الاستفسارات',
                            '8 استفسار',
                            Icons.help_outline,
                            AppTokens.infoBlue,
                            () {
                              Navigator.push(
                                context,
                                MaterialPageRoute(
                                  builder: (context) => const SupportInquiriesPage(),
                                ),
                              );
                            },
                          ),
                        ),
                        const SizedBox(width: AppTokens.spacingMD),
                        Expanded(
                          child: _buildActionCard(
                            context,
                            'دعم الطالبات',
                            '12 تذكرة',
                            Icons.support_agent,
                            AppTokens.errorRed,
                            () {
                              Navigator.push(
                                context,
                                MaterialPageRoute(
                                  builder: (context) => const SupportStudentsPage(),
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
                            'التقارير',
                            '5 تقارير',
                            Icons.assessment,
                            AppTokens.successGreen,
                            () {
                              Navigator.push(
                                context,
                                MaterialPageRoute(
                                  builder: (context) => const SupportReportsPage(),
                                ),
                              );
                            },
                          ),
                        ),
                        const SizedBox(width: AppTokens.spacingMD),
                        Expanded(
                          child: _buildActionCard(
                            context,
                            'المستخدمين',
                            '25 مستخدم',
                            Icons.people,
                            AppTokens.primaryGold,
                            () {
                              ScaffoldMessenger.of(context).showSnackBar(
                                const SnackBar(content: Text('المستخدمين')),
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
                        leading: const Icon(Icons.help_outline, color: AppTokens.infoBlue),
                        title: const Text('استفسار جديد'),
                        subtitle: const Text('طالبة تسأل عن كيفية إضافة مهمة'),
                        trailing: const Text('منذ 30 دقيقة'),
                      ),
                    ),
                    
                    Card(
                      child: ListTile(
                        leading: const Icon(Icons.bug_report, color: AppTokens.errorRed),
                        title: const Text('مشكلة تقنية'),
                        subtitle: const Text('تطبيق لا يعمل على الجهاز'),
                        trailing: const Text('منذ ساعة'),
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