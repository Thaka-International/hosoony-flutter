import 'package:flutter/material.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../../core/theme/tokens.dart';
import '../../../services/auth_service.dart';
import '../../../services/api_service.dart';
import '../../../shared_ui/widgets/copyright_widget.dart';
import '../../../core/debug/debug_service.dart';
import 'login_bypass_page.dart';

class LoginPage extends ConsumerStatefulWidget {
  const LoginPage({super.key});

  @override
  ConsumerState<LoginPage> createState() => _LoginPageState();
}

class _LoginPageState extends ConsumerState<LoginPage>
    with TickerProviderStateMixin {
  final _formKey = GlobalKey<FormState>();
  final _emailController = TextEditingController();
  final _phoneController = TextEditingController();
  final _passwordController = TextEditingController();
  final _verificationCodeController = TextEditingController();
  
  late AnimationController _cardAnimationController;
  late AnimationController _logoAnimationController;
  late Animation<double> _cardFadeAnimation;
  late Animation<Offset> _cardSlideAnimation;
  late Animation<double> _logoScaleAnimation;
  
  bool _isLoading = false;
  bool _isPhoneLogin = false;
  bool _isVerificationStep = false;
  bool _rememberMe = false;

  @override
  void initState() {
    super.initState();
    
    _cardAnimationController = AnimationController(
      duration: const Duration(milliseconds: 800),
      vsync: this,
    );
    
    _logoAnimationController = AnimationController(
      duration: const Duration(milliseconds: 600),
      vsync: this,
    );
    
    _cardFadeAnimation = Tween<double>(
      begin: 0.0,
      end: 1.0,
    ).animate(CurvedAnimation(
      parent: _cardAnimationController,
      curve: Curves.easeInOut,
    ));
    
    _cardSlideAnimation = Tween<Offset>(
      begin: const Offset(0, 0.3),
      end: Offset.zero,
    ).animate(CurvedAnimation(
      parent: _cardAnimationController,
      curve: Curves.easeOutCubic,
    ));
    
    _logoScaleAnimation = Tween<double>(
      begin: 0.8,
      end: 1.0,
    ).animate(CurvedAnimation(
      parent: _logoAnimationController,
      curve: Curves.elasticOut,
    ));
    
    _cardAnimationController.forward();
    _logoAnimationController.forward();
    
    _loadRememberedCredentials();
    
    // Listen to auth state changes after first build
    WidgetsBinding.instance.addPostFrameCallback((_) {
      ref.listen<AuthState>(authStateProvider, (previous, next) {
        if (!mounted) return;
        
        // Handle successful login
        if (next.isAuthenticated && next.user != null && previous?.isAuthenticated != true) {
          // Set token in ApiService if not already set
          if (next.token != null) {
            ApiService.setToken(next.token!);
          }
          
          // Navigate based on user role
          switch (next.user!.role) {
            case 'student':
              context.go('/student/home');
              break;
            case 'teacher':
              context.go('/teacher/home');
              break;
            case 'assistant':
              context.go('/support/home');
              break;
            case 'admin':
              context.go('/admin/home');
              break;
            default:
              context.go('/student/home');
          }
        }
        
        // Handle login errors
        if (next.error != null && previous?.error != next.error && !next.isAuthenticated) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text(next.error!),
              backgroundColor: Colors.red,
              duration: const Duration(seconds: 4),
            ),
          );
          if (mounted) {
            setState(() {
              _isLoading = false;
            });
          }
        }
      });
    });
  }


  @override
  void dispose() {
    _cardAnimationController.dispose();
    _logoAnimationController.dispose();
    _emailController.dispose();
    _phoneController.dispose();
    _passwordController.dispose();
    _verificationCodeController.dispose();
    super.dispose();
  }

  Future<void> _loadRememberedCredentials() async {
    try {
      final credentials = await AuthService.getRememberedCredentials();
      
      if (credentials['email'] != null && credentials['password'] != null) {
        _emailController.text = credentials['email']!;
        _passwordController.text = credentials['password']!;
        _rememberMe = credentials['rememberMe'] == 'true';
        
        setState(() {});
      }
    } catch (e) {
      DebugService.error('Failed to load remembered credentials', e, null, 'AUTH');
    }
  }

  Future<void> _handleLogin() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() {
      _isLoading = true;
    });

    try {
      final authNotifier = ref.read(authStateProvider.notifier);
      
      if (_isPhoneLogin) {
        if (!_isVerificationStep) {
          final response = await ApiService.sendPhoneCode(_phoneController.text);
          if (response['success'] == true) {
            setState(() {
              _isVerificationStep = true;
            });
            ScaffoldMessenger.of(context).showSnackBar(
              const SnackBar(
                content: Text('Verification code sent to your WhatsApp'),
                backgroundColor: Colors.green,
              ),
            );
          } else {
            throw Exception(response['message'] ?? 'Failed to send verification code');
          }
        } else {
          await authNotifier.loginWithPhone(
            _phoneController.text,
            _verificationCodeController.text,
          );
        }
      } else {
        await authNotifier.login(
          _emailController.text.trim(), // Trim whitespace including leading dot
          _passwordController.text,
          rememberMe: _rememberMe,
        );
      }
      
      // Note: Navigation and error display are handled by ref.listen in initState
      // Just check if loading should stop (in case of immediate success)
      if (mounted) {
        final authState = ref.read(authStateProvider);
        if (authState.isAuthenticated || authState.error != null) {
          setState(() {
            _isLoading = false;
          });
        }
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Login failed: ${e.toString()}'),
            backgroundColor: Colors.red,
          ),
        );
      }
    } finally {
      if (mounted) {
        setState(() {
          _isLoading = false;
        });
      }
    }
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
        child: SafeArea(
          child: SingleChildScrollView(
            padding: const EdgeInsets.all(24.0),
            child: Column(
              children: [
                const SizedBox(height: 40),
                
                // Logo and Title
                AnimatedBuilder(
                  animation: _logoScaleAnimation,
                  builder: (context, child) {
                    return Transform.scale(
                      scale: _logoScaleAnimation.value,
                      child: Column(
                        children: [
                          Container(
                            width: 120,
                            height: 120,
                            decoration: BoxDecoration(
                              color: Colors.white.withOpacity(0.2),
                              borderRadius: BorderRadius.circular(60),
                            ),
                            child: const Icon(
                              Icons.school,
                              size: 60,
                              color: Colors.white,
                            ),
                          ),
                          const SizedBox(height: 16),
                          const Text(
                            'حصوني',
                            style: TextStyle(
                              color: Colors.white,
                              fontSize: 32,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                          const Text(
                            'منصة التعليم الذكي',
                            style: TextStyle(
                              color: Colors.white70,
                              fontSize: 16,
                            ),
                          ),
                        ],
                      ),
                    );
                  },
                ),
                
                const SizedBox(height: 40),
                
                // Login Form
                AnimatedBuilder(
                  animation: _cardSlideAnimation,
                  builder: (context, child) {
                    return SlideTransition(
                      position: _cardSlideAnimation,
                      child: FadeTransition(
                        opacity: _cardFadeAnimation,
                        child: Container(
                          padding: const EdgeInsets.all(24),
                          decoration: BoxDecoration(
                            color: Colors.white,
                            borderRadius: BorderRadius.circular(16),
                            boxShadow: [
                              BoxShadow(
                                color: Colors.black.withOpacity(0.1),
                                blurRadius: 20,
                                offset: const Offset(0, 10),
                              ),
                            ],
                          ),
                          child: Form(
                            key: _formKey,
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.stretch,
                              children: [
                                // Login Type Toggle
                                Row(
                                  children: [
                                    Expanded(
                                      child: GestureDetector(
                                        onTap: () {
                                          setState(() {
                                            _isPhoneLogin = false;
                                            _isVerificationStep = false;
                                          });
                                        },
                                        child: Container(
                                          padding: const EdgeInsets.symmetric(vertical: 12),
                                          decoration: BoxDecoration(
                                            color: !_isPhoneLogin ? AppTokens.primaryBrown : Colors.grey[200],
                                            borderRadius: BorderRadius.circular(8),
                                          ),
                                          child: Text(
                                            'البريد الإلكتروني',
                                            textAlign: TextAlign.center,
                                            style: TextStyle(
                                              color: !_isPhoneLogin ? Colors.white : Colors.grey[600],
                                              fontWeight: FontWeight.bold,
                                            ),
                                          ),
                                        ),
                                      ),
                                    ),
                                    const SizedBox(width: 8),
                                    Expanded(
                                      child: GestureDetector(
                                        onTap: () {
                                          setState(() {
                                            _isPhoneLogin = true;
                                            _isVerificationStep = false;
                                          });
                                        },
                                        child: Container(
                                          padding: const EdgeInsets.symmetric(vertical: 12),
                                          decoration: BoxDecoration(
                                            color: _isPhoneLogin ? AppTokens.primaryBrown : Colors.grey[200],
                                            borderRadius: BorderRadius.circular(8),
                                          ),
                                          child: Text(
                                            'رقم الهاتف',
                                            textAlign: TextAlign.center,
                                            style: TextStyle(
                                              color: _isPhoneLogin ? Colors.white : Colors.grey[600],
                                              fontWeight: FontWeight.bold,
                                            ),
                                          ),
                                        ),
                                      ),
                                    ),
                                  ],
                                ),
                                
                                const SizedBox(height: 24),
                                
                                // Email/Phone Field
                                if (!_isPhoneLogin) ...[
                                  TextFormField(
                                    controller: _emailController,
                                    keyboardType: TextInputType.emailAddress,
                                    textDirection: TextDirection.ltr,
                                    decoration: InputDecoration(
                                      labelText: 'البريد الإلكتروني',
                                      prefixIcon: const Icon(Icons.email),
                                      border: OutlineInputBorder(
                                        borderRadius: BorderRadius.circular(8),
                                      ),
                                    ),
                                    validator: (value) {
                                      if (value == null || value.isEmpty) {
                                        return 'يرجى إدخال البريد الإلكتروني';
                                      }
                                      if (!RegExp(r'^[\w-\.]+@([\w-]+\.)+[\w-]{2,4}$').hasMatch(value)) {
                                        return 'يرجى إدخال بريد إلكتروني صحيح';
                                      }
                                      return null;
                                    },
                                  ),
                                ] else ...[
                                  TextFormField(
                                    controller: _phoneController,
                                    keyboardType: TextInputType.phone,
                                    textDirection: TextDirection.ltr,
                                    decoration: InputDecoration(
                                      labelText: 'رقم الهاتف',
                                      prefixIcon: const Icon(Icons.phone),
                                      border: OutlineInputBorder(
                                        borderRadius: BorderRadius.circular(8),
                                      ),
                                    ),
                                    validator: (value) {
                                      if (value == null || value.isEmpty) {
                                        return 'يرجى إدخال رقم الهاتف';
                                      }
                                      return null;
                                    },
                                  ),
                                ],
                                
                                const SizedBox(height: 16),
                                
                                // Password Field (only for email login)
                                if (!_isPhoneLogin) ...[
                                  TextFormField(
                                    controller: _passwordController,
                                    obscureText: true,
                                    decoration: InputDecoration(
                                      labelText: 'كلمة المرور',
                                      prefixIcon: const Icon(Icons.lock),
                                      border: OutlineInputBorder(
                                        borderRadius: BorderRadius.circular(8),
                                      ),
                                    ),
                                    validator: (value) {
                                      if (value == null || value.isEmpty) {
                                        return 'يرجى إدخال كلمة المرور';
                                      }
                                      return null;
                                    },
                                  ),
                                  
                                  const SizedBox(height: 16),
                                  
                                  // Remember Me
                                  Row(
                                    children: [
                                      Checkbox(
                                        value: _rememberMe,
                                        onChanged: (value) {
                                          setState(() {
                                            _rememberMe = value ?? false;
                                          });
                                        },
                                      ),
                                      const Text('تذكرني'),
                                    ],
                                  ),
                                ],
                                
                                // Verification Code Field (for phone login)
                                if (_isPhoneLogin && _isVerificationStep) ...[
                                  TextFormField(
                                    controller: _verificationCodeController,
                                    keyboardType: TextInputType.number,
                                    textDirection: TextDirection.ltr,
                                    decoration: InputDecoration(
                                      labelText: 'رمز التحقق',
                                      prefixIcon: const Icon(Icons.security),
                                      border: OutlineInputBorder(
                                        borderRadius: BorderRadius.circular(8),
                                      ),
                                    ),
                                    validator: (value) {
                                      if (value == null || value.isEmpty) {
                                        return 'يرجى إدخال رمز التحقق';
                                      }
                                      return null;
                                    },
                                  ),
                                ],
                                
                                const SizedBox(height: 24),
                                
                                // Login Button
                                ElevatedButton(
                                  onPressed: _isLoading ? null : _handleLogin,
                                  style: ElevatedButton.styleFrom(
                                    backgroundColor: AppTokens.primaryBrown,
                                    foregroundColor: Colors.white,
                                    padding: const EdgeInsets.symmetric(vertical: 16),
                                    shape: RoundedRectangleBorder(
                                      borderRadius: BorderRadius.circular(8),
                                    ),
                                  ),
                                  child: _isLoading
                                      ? const SizedBox(
                                          height: 20,
                                          width: 20,
                                          child: CircularProgressIndicator(
                                            strokeWidth: 2,
                                            valueColor: AlwaysStoppedAnimation<Color>(Colors.white),
                                          ),
                                        )
                                      : Text(_isPhoneLogin && !_isVerificationStep
                                          ? 'إرسال رمز التحقق'
                                          : 'تسجيل الدخول'),
                                ),
                                
                                const SizedBox(height: 16),
                                
                                // Forgot Password Link
                                if (!_isPhoneLogin)
                                  TextButton(
                                    onPressed: () {
                                      // TODO: Implement forgot password
                                      ScaffoldMessenger.of(context).showSnackBar(
                                        const SnackBar(
                                          content: Text('ميزة استعادة كلمة المرور قيد التطوير'),
                                        ),
                                      );
                                    },
                                    child: const Text(
                                      'نسيت كلمة المرور؟',
                                      style: TextStyle(color: AppTokens.primaryBrown),
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
                
                const SizedBox(height: 24),
                
                // Copyright
                const CopyrightWidget(),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
