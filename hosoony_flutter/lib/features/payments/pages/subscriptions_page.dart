import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/router/app_router.dart';
import '../../../core/theme/tokens.dart';
import '../../../services/auth_service.dart';

class SubscriptionsPage extends ConsumerStatefulWidget {
  const SubscriptionsPage({super.key});

  @override
  ConsumerState<SubscriptionsPage> createState() => _SubscriptionsPageState();
}

class _SubscriptionsPageState extends ConsumerState<SubscriptionsPage>
    with TickerProviderStateMixin {
  late AnimationController _animationController;
  late Animation<double> _fadeAnimation;
  late Animation<Offset> _slideAnimation;
  
  Map<String, dynamic> _currentSubscription = {};
  List<Map<String, dynamic>> _availablePlans = [];
  bool _isLoading = true;
  String? _error;

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

    _loadSubscriptionData();
  }

  @override
  void dispose() {
    _animationController.dispose();
    super.dispose();
  }

  Future<void> _loadSubscriptionData() async {
    try {
      setState(() {
        _isLoading = true;
        _error = null;
      });

      // Mock data
      await Future.delayed(const Duration(milliseconds: 1000));
      
      setState(() {
        _currentSubscription = {
          'id': 1,
          'plan_name': 'الخطة الأساسية',
          'plan_type': 'basic',
          'price': 50.0,
          'currency': 'SAR',
          'billing_cycle': 'monthly',
          'status': 'active',
          'start_date': '2024-01-01',
          'end_date': '2024-02-01',
          'auto_renew': true,
          'features': [
            'وصول إلى جميع الدروس',
            'متابعة التقدم',
            'تقييمات شهرية',
            'دعم فني',
          ],
        };

        _availablePlans = [
          {
            'id': 1,
            'name': 'الخطة الأساسية',
            'type': 'basic',
            'price': 50.0,
            'currency': 'SAR',
            'billing_cycle': 'monthly',
            'description': 'مناسبة للمبتدئات',
            'features': [
              'وصول إلى جميع الدروس',
              'متابعة التقدم',
              'تقييمات شهرية',
              'دعم فني',
            ],
            'is_popular': false,
            'is_current': true,
          },
          {
            'id': 2,
            'name': 'الخطة المتقدمة',
            'type': 'premium',
            'price': 80.0,
            'currency': 'SAR',
            'billing_cycle': 'monthly',
            'description': 'مناسبة للمتقدمات',
            'features': [
              'جميع ميزات الخطة الأساسية',
              'جلسات فردية مع المعلمة',
              'تقييمات أسبوعية',
              'مواد إضافية',
              'دعم أولوية',
            ],
            'is_popular': true,
            'is_current': false,
          },
          {
            'id': 3,
            'name': 'الخطة المميزة',
            'type': 'vip',
            'price': 120.0,
            'currency': 'SAR',
            'billing_cycle': 'monthly',
            'description': 'للطالبات المتفوقات',
            'features': [
              'جميع ميزات الخطة المتقدمة',
              'جلسات يومية',
              'تقييمات يومية',
              'مواد حصرية',
              'دعم 24/7',
              'شهادة إنجاز',
            ],
            'is_popular': false,
            'is_current': false,
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
              'الاشتراكات',
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
            onPressed: _loadSubscriptionData,
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
                        onPressed: _loadSubscriptionData,
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
              : AnimatedBuilder(
                  animation: _animationController,
                  builder: (context, child) {
                    return FadeTransition(
                      opacity: _fadeAnimation,
                      child: SlideTransition(
                        position: _slideAnimation,
                        child: _buildSubscriptionContent(),
                      ),
                    );
                  },
                ),
    );
  }

  Widget _buildSubscriptionContent() {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(AppTokens.spacingMD),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Current Subscription Section
          _buildCurrentSubscription(),
          
          const SizedBox(height: AppTokens.spacingXL),
          
          // Available Plans Section
          _buildAvailablePlans(),
          
          const SizedBox(height: AppTokens.spacingXL),
        ],
      ),
    );
  }

  Widget _buildCurrentSubscription() {
    return Container(
      padding: const EdgeInsets.all(AppTokens.spacingLG),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          colors: [
            AppTokens.primaryGreen.withValues(alpha: 0.1),
            AppTokens.primaryGreen.withValues(alpha: 0.05),
          ],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(AppTokens.radiusLG),
        border: Border.all(
          color: AppTokens.primaryGreen.withValues(alpha: 0.2),
        ),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Icon(
                Icons.card_membership,
                color: AppTokens.primaryGreen,
                size: AppTokens.iconSizeLG,
              ),
              const SizedBox(width: AppTokens.spacingMD),
              Text(
                'اشتراكك الحالي',
                style: Theme.of(context).textTheme.titleLarge?.copyWith(
                  fontFamily: AppTokens.primaryFontFamily,
                  fontWeight: AppTokens.fontWeightBold,
                  color: AppTokens.primaryGreen,
                ),
              ),
            ],
          ),
          
          const SizedBox(height: AppTokens.spacingLG),
          
          Row(
            children: [
              Expanded(
                child: _buildSubscriptionInfo(
                  'الخطة',
                  _currentSubscription['plan_name'],
                  Icons.card_membership,
                ),
              ),
              Expanded(
                child: _buildSubscriptionInfo(
                  'السعر',
                  '${_currentSubscription['price']} ${_currentSubscription['currency']}',
                  Icons.attach_money,
                ),
              ),
            ],
          ),
          
          const SizedBox(height: AppTokens.spacingMD),
          
          Row(
            children: [
              Expanded(
                child: _buildSubscriptionInfo(
                  'تاريخ البداية',
                  _currentSubscription['start_date'],
                  Icons.calendar_today,
                ),
              ),
              Expanded(
                child: _buildSubscriptionInfo(
                  'تاريخ الانتهاء',
                  _currentSubscription['end_date'],
                  Icons.event,
                ),
              ),
            ],
          ),
          
          const SizedBox(height: AppTokens.spacingMD),
          
          Row(
            children: [
              Expanded(
                child: _buildSubscriptionInfo(
                  'الحالة',
                  _currentSubscription['status'] == 'active' ? 'نشط' : 'غير نشط',
                  Icons.check_circle,
                ),
              ),
              Expanded(
                child: _buildSubscriptionInfo(
                  'التجديد التلقائي',
                  _currentSubscription['auto_renew'] ? 'مفعل' : 'معطل',
                  Icons.autorenew,
                ),
              ),
            ],
          ),
          
          const SizedBox(height: AppTokens.spacingLG),
          
          Text(
            'الميزات المتاحة',
            style: Theme.of(context).textTheme.titleMedium?.copyWith(
              fontWeight: AppTokens.fontWeightBold,
            ),
          ),
          
          const SizedBox(height: AppTokens.spacingMD),
          
          ...(_currentSubscription['features'] as List).map((feature) => 
            Padding(
              padding: const EdgeInsets.only(bottom: AppTokens.spacingSM),
              child: Row(
                children: [
                  Icon(
                    Icons.check_circle,
                    color: AppTokens.successGreen,
                    size: AppTokens.iconSizeSM,
                  ),
                  const SizedBox(width: AppTokens.spacingSM),
                  Text(
                    feature,
                    style: Theme.of(context).textTheme.bodyMedium,
                  ),
                ],
              ),
            ),
          ),
          
          const SizedBox(height: AppTokens.spacingLG),
          
          Row(
            children: [
              Expanded(
                child: ElevatedButton.icon(
                  onPressed: () => _manageSubscription(),
                  icon: const Icon(Icons.settings),
                  label: const Text('إدارة الاشتراك'),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: AppTokens.primaryGreen,
                    foregroundColor: AppTokens.neutralWhite,
                  ),
                ),
              ),
              const SizedBox(width: AppTokens.spacingMD),
              Expanded(
                child: OutlinedButton.icon(
                  onPressed: () => _cancelSubscription(),
                  icon: const Icon(Icons.cancel),
                  label: const Text('إلغاء الاشتراك'),
                  style: OutlinedButton.styleFrom(
                    foregroundColor: AppTokens.errorRed,
                    side: BorderSide(color: AppTokens.errorRed),
                  ),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildSubscriptionInfo(String label, String value, IconData icon) {
    return Container(
      padding: const EdgeInsets.all(AppTokens.spacingMD),
      decoration: BoxDecoration(
        color: AppTokens.neutralWhite,
        borderRadius: BorderRadius.circular(AppTokens.radiusMD),
        border: Border.all(color: AppTokens.neutralMedium.withValues(alpha: 0.2)),
      ),
      child: Column(
        children: [
          Icon(icon, color: AppTokens.primaryGreen, size: AppTokens.iconSizeMD),
          const SizedBox(height: AppTokens.spacingSM),
          Text(
            label,
            style: Theme.of(context).textTheme.bodySmall?.copyWith(
              color: AppTokens.neutralMedium,
            ),
          ),
          const SizedBox(height: AppTokens.spacingXS),
          Text(
            value,
            style: Theme.of(context).textTheme.titleMedium?.copyWith(
              fontWeight: AppTokens.fontWeightBold,
            ),
            textAlign: TextAlign.center,
          ),
        ],
      ),
    );
  }

  Widget _buildAvailablePlans() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          'الخطط المتاحة',
          style: Theme.of(context).textTheme.titleLarge?.copyWith(
            fontFamily: AppTokens.primaryFontFamily,
            fontWeight: AppTokens.fontWeightBold,
          ),
        ),
        
        const SizedBox(height: AppTokens.spacingMD),
        
        ..._availablePlans.map((plan) => _buildPlanCard(plan)),
      ],
    );
  }

  Widget _buildPlanCard(Map<String, dynamic> plan) {
    final isCurrent = plan['is_current'];
    final isPopular = plan['is_popular'];
    
    Color planColor;
    if (isCurrent) {
      planColor = AppTokens.primaryGreen;
    } else if (isPopular) {
      planColor = AppTokens.primaryGold;
    } else {
      planColor = AppTokens.infoBlue;
    }

    return Container(
      margin: const EdgeInsets.only(bottom: AppTokens.spacingMD),
      padding: const EdgeInsets.all(AppTokens.spacingLG),
      decoration: BoxDecoration(
        color: AppTokens.neutralWhite,
        borderRadius: BorderRadius.circular(AppTokens.radiusLG),
        border: Border.all(
          color: isCurrent 
              ? AppTokens.primaryGreen 
              : isPopular 
                  ? AppTokens.primaryGold 
                  : AppTokens.neutralMedium.withValues(alpha: 0.2),
          width: isCurrent || isPopular ? 2 : 1,
        ),
        boxShadow: [
          BoxShadow(
            color: planColor.withValues(alpha: 0.1),
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
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        Text(
                          plan['name'],
                          style: Theme.of(context).textTheme.titleLarge?.copyWith(
                            fontWeight: AppTokens.fontWeightBold,
                            color: planColor,
                          ),
                        ),
                        if (isPopular) ...[
                          const SizedBox(width: AppTokens.spacingSM),
                          Container(
                            padding: const EdgeInsets.symmetric(
                              horizontal: AppTokens.spacingSM,
                              vertical: AppTokens.spacingXS,
                            ),
                            decoration: BoxDecoration(
                              color: AppTokens.primaryGold,
                              borderRadius: BorderRadius.circular(AppTokens.radiusSM),
                            ),
                            child: Text(
                              'الأكثر شعبية',
                              style: TextStyle(
                                color: AppTokens.neutralWhite,
                                fontSize: AppTokens.fontSizeXS,
                                fontWeight: AppTokens.fontWeightBold,
                              ),
                            ),
                          ),
                        ],
                        if (isCurrent) ...[
                          const SizedBox(width: AppTokens.spacingSM),
                          Container(
                            padding: const EdgeInsets.symmetric(
                              horizontal: AppTokens.spacingSM,
                              vertical: AppTokens.spacingXS,
                            ),
                            decoration: BoxDecoration(
                              color: AppTokens.primaryGreen,
                              borderRadius: BorderRadius.circular(AppTokens.radiusSM),
                            ),
                            child: Text(
                              'الحالية',
                              style: TextStyle(
                                color: AppTokens.neutralWhite,
                                fontSize: AppTokens.fontSizeXS,
                                fontWeight: AppTokens.fontWeightBold,
                              ),
                            ),
                          ),
                        ],
                      ],
                    ),
                    const SizedBox(height: AppTokens.spacingXS),
                    Text(
                      plan['description'],
                      style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                        color: AppTokens.neutralMedium,
                      ),
                    ),
                  ],
                ),
              ),
              Column(
                children: [
                  Text(
                    '${plan['price']} ${plan['currency']}',
                    style: Theme.of(context).textTheme.headlineMedium?.copyWith(
                      fontWeight: AppTokens.fontWeightBold,
                      color: planColor,
                    ),
                  ),
                  Text(
                    'شهرياً',
                    style: Theme.of(context).textTheme.bodySmall?.copyWith(
                      color: AppTokens.neutralMedium,
                    ),
                  ),
                ],
              ),
            ],
          ),
          
          const SizedBox(height: AppTokens.spacingLG),
          
          Text(
            'الميزات',
            style: Theme.of(context).textTheme.titleMedium?.copyWith(
              fontWeight: AppTokens.fontWeightBold,
            ),
          ),
          
          const SizedBox(height: AppTokens.spacingMD),
          
          ...(plan['features'] as List).map((feature) => 
            Padding(
              padding: const EdgeInsets.only(bottom: AppTokens.spacingSM),
              child: Row(
                children: [
                  Icon(
                    Icons.check_circle,
                    color: AppTokens.successGreen,
                    size: AppTokens.iconSizeSM,
                  ),
                  const SizedBox(width: AppTokens.spacingSM),
                  Expanded(
                    child: Text(
                      feature,
                      style: Theme.of(context).textTheme.bodyMedium,
                    ),
                  ),
                ],
              ),
            ),
          ),
          
          const SizedBox(height: AppTokens.spacingLG),
          
          SizedBox(
            width: double.infinity,
            child: ElevatedButton(
              onPressed: isCurrent ? null : () => _subscribeToPlan(plan),
              style: ElevatedButton.styleFrom(
                backgroundColor: planColor,
                foregroundColor: AppTokens.neutralWhite,
                padding: const EdgeInsets.symmetric(
                  vertical: AppTokens.spacingMD,
                ),
              ),
              child: Text(
                isCurrent ? 'الخطة الحالية' : 'اختيار هذه الخطة',
                style: TextStyle(
                  fontFamily: AppTokens.primaryFontFamily,
                  fontWeight: AppTokens.fontWeightBold,
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }

  void _manageSubscription() {
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(content: Text('إدارة الاشتراك')),
    );
  }

  void _cancelSubscription() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('إلغاء الاشتراك'),
        content: const Text('هل أنت متأكد من إلغاء الاشتراك؟'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('إلغاء'),
          ),
          TextButton(
            onPressed: () {
              Navigator.pop(context);
              ScaffoldMessenger.of(context).showSnackBar(
                const SnackBar(content: Text('تم إلغاء الاشتراك')),
              );
            },
            child: const Text('تأكيد'),
          ),
        ],
      ),
    );
  }

  void _subscribeToPlan(Map<String, dynamic> plan) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text('الاشتراك في ${plan['name']}')),
    );
  }
}





