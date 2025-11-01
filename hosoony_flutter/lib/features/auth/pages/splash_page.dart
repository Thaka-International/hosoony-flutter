import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/router/app_router.dart';
import '../../../core/theme/tokens.dart';
import '../../../services/auth_service.dart';

class SplashPage extends ConsumerStatefulWidget {
  const SplashPage({super.key});

  @override
  ConsumerState<SplashPage> createState() => _SplashPageState();
}

class _SplashPageState extends ConsumerState<SplashPage>
    with TickerProviderStateMixin {
  late AnimationController _logoAnimationController;
  late AnimationController _textAnimationController;
  late AnimationController _loadingAnimationController;
  
  late Animation<double> _logoFadeAnimation;
  late Animation<double> _logoScaleAnimation;
  late Animation<double> _logoRotationAnimation;
  
  late Animation<double> _textFadeAnimation;
  late Animation<double> _textSlideAnimation;
  
  late Animation<double> _loadingFadeAnimation;

  @override
  void initState() {
    super.initState();
    
    // Logo animations
    _logoAnimationController = AnimationController(
      duration: const Duration(milliseconds: 1500),
      vsync: this,
    );

    _logoFadeAnimation = Tween<double>(
      begin: 0.0,
      end: 1.0,
    ).animate(CurvedAnimation(
      parent: _logoAnimationController,
      curve: const Interval(0.0, 0.6, curve: Curves.easeOut),
    ));

    _logoScaleAnimation = Tween<double>(
      begin: 0.5,
      end: 1.0,
    ).animate(CurvedAnimation(
      parent: _logoAnimationController,
      curve: const Interval(0.0, 0.8, curve: Curves.elasticOut),
    ));

    _logoRotationAnimation = Tween<double>(
      begin: -0.1,
      end: 0.0,
    ).animate(CurvedAnimation(
      parent: _logoAnimationController,
      curve: const Interval(0.2, 1.0, curve: Curves.easeOutBack),
    ));

    // Text animations
    _textAnimationController = AnimationController(
      duration: const Duration(milliseconds: 1200),
      vsync: this,
    );

    _textFadeAnimation = Tween<double>(
      begin: 0.0,
      end: 1.0,
    ).animate(CurvedAnimation(
      parent: _textAnimationController,
      curve: const Interval(0.0, 0.8, curve: Curves.easeOut),
    ));

    _textSlideAnimation = Tween<double>(
      begin: 50.0,
      end: 0.0,
    ).animate(CurvedAnimation(
      parent: _textAnimationController,
      curve: const Interval(0.0, 0.8, curve: Curves.easeOutCubic),
    ));

    // Loading animation
    _loadingAnimationController = AnimationController(
      duration: const Duration(milliseconds: 1000),
      vsync: this,
    );

    _loadingFadeAnimation = Tween<double>(
      begin: 0.0,
      end: 1.0,
    ).animate(CurvedAnimation(
      parent: _loadingAnimationController,
      curve: const Interval(0.0, 0.6, curve: Curves.easeOut),
    ));

    // Start animations in sequence
    _startAnimationSequence();
    _checkAuthAndNavigate();
  }

  @override
  void dispose() {
    _logoAnimationController.dispose();
    _textAnimationController.dispose();
    _loadingAnimationController.dispose();
    super.dispose();
  }

  void _startAnimationSequence() {
    _logoAnimationController.forward();
    Future.delayed(const Duration(milliseconds: 500), () {
      _textAnimationController.forward();
    });
    Future.delayed(const Duration(milliseconds: 1000), () {
      _loadingAnimationController.forward();
    });
  }

  void _checkAuthAndNavigate() async {
    final authNotifier = ref.read(authStateProvider.notifier);
    await authNotifier.checkAutoLogin(); // Corrected method call
    
    Future.delayed(const Duration(milliseconds: 3000), () {
      if (mounted) {
        final authState = ref.read(authStateProvider);
        if (authState.isAuthenticated) {
          final user = authState.user;
          switch (user?.role) {
            case 'student':
              AppRouter.goToStudentHome(context);
              break;
            case 'teacher':
              AppRouter.goToTeacherHome(context);
              break;
            case 'assistant':
              AppRouter.goToSupportHome(context);
              break;
            case 'admin':
              AppRouter.goToAdminHome(context);
              break;
            default:
              AppRouter.goToStudentHome(context);
          }
        } else {
          AppRouter.goToLogin(context);
        }
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Container(
        decoration: const BoxDecoration(
          gradient: LinearGradient(
            begin: Alignment.topCenter,
            end: Alignment.bottomCenter,
            colors: [
              Color(0xFF8B4513),
              Color(0xFFDAA520),
            ],
          ),
        ),
        child: const Center(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(
                Icons.mosque,
                size: 100,
                color: Colors.white,
              ),
              SizedBox(height: 20),
              Text(
                'حصوني',
                style: TextStyle(
                  color: Colors.white,
                  fontSize: 32,
                  fontWeight: FontWeight.bold,
                ),
              ),
              SizedBox(height: 10),
              Text(
                'نظام إدارة التعلم القرآني',
                style: TextStyle(
                  color: Colors.white70,
                  fontSize: 16,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

