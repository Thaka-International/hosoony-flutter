import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/theme/tokens.dart';

class BuildProgressPage extends ConsumerStatefulWidget {
  const BuildProgressPage({super.key});

  @override
  ConsumerState<BuildProgressPage> createState() => _BuildProgressPageState();
}

class _BuildProgressPageState extends ConsumerState<BuildProgressPage>
    with TickerProviderStateMixin {
  late AnimationController _animationController;
  late Animation<double> _fadeAnimation;
  late Animation<double> _slideAnimation;
  
  final List<String> _steps = [
    'تحضير البيئة',
    'تحميل التبعيات',
    'تحليل الكود',
    'بناء التطبيق',
    'إنهاء البناء',
  ];
  
  int _currentStep = 0;
  double _progress = 0.0;

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
    _startProgress();
  }

  @override
  void dispose() {
    _animationController.dispose();
    super.dispose();
  }

  void _startProgress() {
    Future.delayed(const Duration(milliseconds: 500), () {
      if (mounted) {
        setState(() {
          _currentStep = 1;
          _progress = 0.2;
        });
      }
    });
    
    Future.delayed(const Duration(milliseconds: 1000), () {
      if (mounted) {
        setState(() {
          _currentStep = 2;
          _progress = 0.4;
        });
      }
    });
    
    Future.delayed(const Duration(milliseconds: 1500), () {
      if (mounted) {
        setState(() {
          _currentStep = 3;
          _progress = 0.6;
        });
      }
    });
    
    Future.delayed(const Duration(milliseconds: 2000), () {
      if (mounted) {
        setState(() {
          _currentStep = 4;
          _progress = 0.8;
        });
      }
    });
    
    Future.delayed(const Duration(milliseconds: 2500), () {
      if (mounted) {
        setState(() {
          _currentStep = 5;
          _progress = 1.0;
        });
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppTokens.neutralWhite,
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
              child: Center(
                child: Padding(
                  padding: const EdgeInsets.all(AppTokens.spacingLG),
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      // Logo
                      ClipRRect(
                        borderRadius: BorderRadius.circular(AppTokens.radiusLG),
                        child: Image.asset(
                          'assets/images/hosoony-logo.png',
                          width: AppTokens.iconSize2XL,
                          height: AppTokens.iconSize2XL,
                          fit: BoxFit.contain,
                          errorBuilder: (context, error, stackTrace) {
                            return const Icon(
                              Icons.mosque,
                              size: AppTokens.iconSize2XL,
                              color: AppTokens.primaryGreen,
                            );
                          },
                        ),
                      ),
                      
                      const SizedBox(height: AppTokens.spacingLG),
                      
                      // Title
                      Text(
                        'حصوني القرآني',
                        style: Theme.of(context).textTheme.headlineMedium?.copyWith(
                          fontFamily: AppTokens.primaryFontFamily,
                          fontWeight: AppTokens.fontWeightBold,
                          color: AppTokens.primaryGreen,
                        ),
                      ),
                      
                      const SizedBox(height: AppTokens.spacingMD),
                      
                      // Progress Indicator
                      Container(
                        width: double.infinity,
                        height: 8,
                        decoration: BoxDecoration(
                          color: AppTokens.neutralLight,
                          borderRadius: BorderRadius.circular(AppTokens.radiusSM),
                        ),
                        child: LinearProgressIndicator(
                          value: _progress,
                          backgroundColor: Colors.transparent,
                          valueColor: AlwaysStoppedAnimation<Color>(AppTokens.primaryGreen),
                        ),
                      ),
                      
                      const SizedBox(height: AppTokens.spacingMD),
                      
                      // Current Step
                      Text(
                        _currentStep < _steps.length ? _steps[_currentStep - 1] : 'تم الانتهاء',
                        style: Theme.of(context).textTheme.titleMedium?.copyWith(
                          fontFamily: AppTokens.primaryFontFamily,
                          fontWeight: AppTokens.fontWeightBold,
                          color: AppTokens.primaryGreen,
                        ),
                      ),
                      
                      const SizedBox(height: AppTokens.spacingSM),
                      
                      // Progress Percentage
                      Text(
                        '${(_progress * 100).round()}%',
                        style: Theme.of(context).textTheme.bodyLarge?.copyWith(
                          fontFamily: AppTokens.primaryFontFamily,
                          color: AppTokens.neutralMedium,
                        ),
                      ),
                      
                      const SizedBox(height: AppTokens.spacingLG),
                      
                      // Steps List
                      Container(
                        width: double.infinity,
                        padding: const EdgeInsets.all(AppTokens.spacingMD),
                        decoration: BoxDecoration(
                          color: AppTokens.neutralLight,
                          borderRadius: BorderRadius.circular(AppTokens.radiusMD),
                        ),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: _steps.asMap().entries.map((entry) {
                            final index = entry.key;
                            final step = entry.value;
                            final isCompleted = index < _currentStep - 1;
                            final isCurrent = index == _currentStep - 1;
                            
                            return Padding(
                              padding: const EdgeInsets.symmetric(vertical: AppTokens.spacingXS),
                              child: Row(
                                children: [
                                  Icon(
                                    isCompleted 
                                        ? Icons.check_circle
                                        : isCurrent
                                            ? Icons.radio_button_checked
                                            : Icons.radio_button_unchecked,
                                    color: isCompleted 
                                        ? AppTokens.successGreen
                                        : isCurrent
                                            ? AppTokens.primaryGreen
                                            : AppTokens.neutralGray,
                                    size: AppTokens.iconSizeSM,
                                  ),
                                  const SizedBox(width: AppTokens.spacingSM),
                                  Text(
                                    step,
                                    style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                                      fontFamily: AppTokens.primaryFontFamily,
                                      color: isCompleted 
                                          ? AppTokens.successGreen
                                          : isCurrent
                                              ? AppTokens.primaryGreen
                                              : AppTokens.neutralGray,
                                      fontWeight: isCurrent ? AppTokens.fontWeightBold : AppTokens.fontWeightRegular,
                                    ),
                                  ),
                                ],
                              ),
                            );
                          }).toList(),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ),
          );
        },
      ),
    );
  }
}





