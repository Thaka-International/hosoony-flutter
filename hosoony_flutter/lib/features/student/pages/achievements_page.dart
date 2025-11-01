import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../../core/router/app_router.dart';
import '../../../core/theme/tokens.dart';
import '../../../services/auth_service.dart';

class StudentAchievementsPage extends ConsumerStatefulWidget {
  const StudentAchievementsPage({super.key});

  @override
  ConsumerState<StudentAchievementsPage> createState() => _StudentAchievementsPageState();
}

class _StudentAchievementsPageState extends ConsumerState<StudentAchievementsPage>
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
        leading: IconButton(
          icon: const Icon(Icons.arrow_back),
          onPressed: () => context.go('/student/home'),
        ),
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
              'الإنجازات',
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
                            'إنجازاتك، ${user?.name ?? 'الطالب'}!',
                            style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                              color: AppTokens.neutralWhite,
                              fontWeight: AppTokens.fontWeightBold,
                            ),
                          ),
                          const SizedBox(height: AppTokens.spacingSM),
                          Text(
                            'تابع تقدمك وإنجازاتك في رحلتك القرآنية',
                            style: Theme.of(context).textTheme.bodyLarge?.copyWith(
                              color: AppTokens.neutralWhite.withValues(alpha: 0.9),
                            ),
                          ),
                        ],
                      ),
                    ),
                    
                    const SizedBox(height: AppTokens.spacingLG),
                    
                    // Achievements Cards
                    _buildAchievementCard(
                      context,
                      'أول حافظ',
                      'حفظت أول سورة كاملة',
                      'سورة الفاتحة',
                      Icons.emoji_events,
                      AppTokens.primaryGold,
                      true,
                    ),
                    
                    const SizedBox(height: AppTokens.spacingMD),
                    
                    _buildAchievementCard(
                      context,
                      'مثابر',
                      'أكملت 10 مهام متتالية',
                      '10 مهام',
                      Icons.star,
                      AppTokens.successGreen,
                      true,
                    ),
                    
                    const SizedBox(height: AppTokens.spacingMD),
                    
                    _buildAchievementCard(
                      context,
                      'منتظم',
                      'حضرت 5 جلسات متتالية',
                      '5 جلسات',
                      Icons.schedule,
                      AppTokens.infoBlue,
                      false,
                    ),
                    
                    const SizedBox(height: AppTokens.spacingMD),
                    
                    _buildAchievementCard(
                      context,
                      'متميز',
                      'حصلت على تقييم ممتاز',
                      'تقييم ممتاز',
                      Icons.grade,
                      AppTokens.warningOrange,
                      false,
                    ),
                  ],
                ),
              ),
            ),
          );
        },
      ),
      bottomNavigationBar: BottomNavigationBar(
        type: BottomNavigationBarType.fixed,
        currentIndex: 4,
        onTap: (index) {
          switch (index) {
            case 0:
              AppRouter.goToStudentHome(context);
              break;
            case 1:
              AppRouter.goToStudentDailyTasks(context);
              break;
            case 2:
              AppRouter.goToStudentCompanions(context);
              break;
            case 3:
              AppRouter.goToStudentSchedule(context);
              break;
            case 4:
              AppRouter.goToStudentAchievements(context);
              break;
          }
        },
        items: const [
          BottomNavigationBarItem(
            icon: Icon(Icons.home),
            label: 'الرئيسية',
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.task_alt),
            label: 'المهام',
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.people),
            label: 'الرفيقات',
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.schedule),
            label: 'الجدول',
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.star),
            label: 'الإنجازات',
          ),
        ],
      ),
    );
  }

  Widget _buildAchievementCard(
    BuildContext context,
    String title,
    String description,
    String detail,
    IconData icon,
    Color color,
    bool isUnlocked,
  ) {
    return Card(
      elevation: 2,
      child: Padding(
        padding: const EdgeInsets.all(AppTokens.spacingMD),
        child: Row(
          children: [
            Container(
              width: 50,
              height: 50,
              decoration: BoxDecoration(
                color: isUnlocked 
                    ? color.withValues(alpha: 0.1)
                    : AppTokens.neutralGray.withValues(alpha: 0.1),
                borderRadius: BorderRadius.circular(AppTokens.radiusFull),
              ),
              child: Icon(
                isUnlocked ? icon : Icons.lock,
                color: isUnlocked ? color : AppTokens.neutralGray,
                size: AppTokens.iconSizeMD,
              ),
            ),
            
            const SizedBox(width: AppTokens.spacingMD),
            
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    title,
                    style: Theme.of(context).textTheme.titleMedium?.copyWith(
                      fontWeight: AppTokens.fontWeightBold,
                      color: isUnlocked ? AppTokens.neutralDark : AppTokens.neutralGray,
                    ),
                  ),
                  const SizedBox(height: AppTokens.spacingXS),
                  Text(
                    description,
                    style: Theme.of(context).textTheme.bodySmall?.copyWith(
                      color: AppTokens.neutralMedium,
                    ),
                  ),
                  const SizedBox(height: AppTokens.spacingXS),
                  Text(
                    detail,
                    style: Theme.of(context).textTheme.bodySmall?.copyWith(
                      color: AppTokens.neutralMedium,
                    ),
                  ),
                ],
              ),
            ),
            
            if (isUnlocked)
              Icon(
                Icons.check_circle,
                color: AppTokens.successGreen,
                size: AppTokens.iconSizeMD,
              )
            else
              Icon(
                Icons.lock_outline,
                color: AppTokens.neutralGray,
                size: AppTokens.iconSizeMD,
              ),
          ],
        ),
      ),
    );
  }
}
