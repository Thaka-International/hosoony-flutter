import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../../core/debug/login_bypass_service.dart';
import '../../../core/debug/debug_service.dart';
import '../../../core/theme/tokens.dart';
import '../../../data/models/user.dart';
import '../../../core/router/app_router.dart';

/// 🚀 صفحة تجاوز تسجيل الدخول للاختبار
class LoginBypassPage extends ConsumerStatefulWidget {
  const LoginBypassPage({super.key});

  @override
  ConsumerState<LoginBypassPage> createState() => _LoginBypassPageState();
}

class _LoginBypassPageState extends ConsumerState<LoginBypassPage> {
  bool _isBypassEnabled = LoginBypassService.isBypassEnabled;
  User? _currentUser;
  bool _isLoading = false;

  @override
  void initState() {
    super.initState();
    _loadCurrentUser();
  }

  Future<void> _loadCurrentUser() async {
    final user = await LoginBypassService.getCurrentUser();
    setState(() {
      _currentUser = user;
    });
  }

  Future<void> _loginAsUser(String role) async {
    setState(() {
      _isLoading = true;
    });

    try {
      DebugService.info('محاولة تسجيل الدخول كـ $role', 'LOGIN_BYPASS');
      
      switch (role) {
        case 'student':
          await LoginBypassService.loginAsStudent();
          break;
        case 'teacher':
          await LoginBypassService.loginAsTeacher();
          break;
        case 'assistant':
          await LoginBypassService.loginAsAssistant();
          break;
        case 'admin':
          await LoginBypassService.loginAsAdmin();
          break;
        case 'sub_admin':
          await LoginBypassService.loginAsSubAdmin();
          break;
        case 'teacher_support':
          await LoginBypassService.loginAsTeacherSupport();
          break;
      }

      await _loadCurrentUser();
      
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('تم تسجيل الدخول كـ ${_currentUser?.name}'),
            backgroundColor: Colors.green,
          ),
        );
        
        // التوجيه إلى الصفحة المناسبة باستخدام GoRouter
        if (mounted) {
          switch (role) {
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
      }
    } catch (e) {
      DebugService.error('فشل في تسجيل الدخول التجريبي', e, null, 'LOGIN_BYPASS');
      
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('خطأ في تسجيل الدخول: $e'),
            backgroundColor: Colors.red,
          ),
        );
      }
    } finally {
      setState(() {
        _isLoading = false;
      });
    }
  }

  Future<void> _logout() async {
    setState(() {
      _isLoading = true;
    });

    try {
      await LoginBypassService.bypassLogout();
      await _loadCurrentUser();
      
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('تم تسجيل الخروج'),
            backgroundColor: Colors.orange,
          ),
        );
      }
    } catch (e) {
      DebugService.error('فشل في تسجيل الخروج', e, null, 'LOGIN_BYPASS');
    } finally {
      setState(() {
        _isLoading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppTokens.neutralLight,
      appBar: AppBar(
        title: const Text('🚀 تجاوز تسجيل الدخول'),
        backgroundColor: AppTokens.primaryGreen,
        foregroundColor: AppTokens.neutralWhite,
        actions: [
          IconButton(
            icon: const Icon(Icons.bug_report),
            onPressed: () => DebugService.showDebugOverlay(context),
          ),
        ],
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            // معلومات الحالة الحالية
            Card(
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      '📊 الحالة الحالية',
                      style: Theme.of(context).textTheme.titleLarge?.copyWith(
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(height: 8),
                    Text('وضع التجاوز: ${_isBypassEnabled ? "مفعل" : "معطل"}'),
                    Text('المستخدم الحالي: ${_currentUser?.name ?? "غير مسجل"}'),
                    Text('الدور: ${_currentUser?.role ?? "غير محدد"}'),
                  ],
                ),
              ),
            ),
            
            const SizedBox(height: 16),
            
            // تفعيل/إلغاء التجاوز
            Card(
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      '⚙️ إعدادات التجاوز',
                      style: Theme.of(context).textTheme.titleLarge?.copyWith(
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(height: 8),
                    SwitchListTile(
                      title: const Text('تفعيل تجاوز تسجيل الدخول'),
                      subtitle: const Text('للاختبار والتطوير فقط'),
                      value: _isBypassEnabled,
                      onChanged: (value) {
                        setState(() {
                          _isBypassEnabled = value;
                        });
                        LoginBypassService.setBypassEnabled(value);
                      },
                    ),
                  ],
                ),
              ),
            ),
            
            const SizedBox(height: 16),
            
            // أزرار تسجيل الدخول التجريبي
            Text(
              '👥 تسجيل الدخول التجريبي',
              style: Theme.of(context).textTheme.titleLarge?.copyWith(
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 8),
            
            ...LoginBypassService.testUsers.entries.map((entry) {
              final role = entry.key;
              final user = entry.value;
              final roleName = _getRoleName(role);
              
              return Padding(
                padding: const EdgeInsets.only(bottom: 8),
                child: ElevatedButton.icon(
                  onPressed: _isLoading || !_isBypassEnabled 
                      ? null 
                      : () => _loginAsUser(role),
                  icon: Icon(_getRoleIcon(role)),
                  label: Text('تسجيل الدخول كـ $roleName'),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: _getRoleColor(role),
                    foregroundColor: AppTokens.neutralWhite,
                    padding: const EdgeInsets.symmetric(vertical: 12),
                  ),
                ),
              );
            }),
            
            const SizedBox(height: 16),
            
            // زر تسجيل الخروج
            if (_currentUser != null)
              ElevatedButton.icon(
                onPressed: _isLoading ? null : _logout,
                icon: const Icon(Icons.logout),
                label: const Text('تسجيل الخروج'),
                style: ElevatedButton.styleFrom(
                  backgroundColor: Colors.orange,
                  foregroundColor: AppTokens.neutralWhite,
                  padding: const EdgeInsets.symmetric(vertical: 12),
                ),
              ),
            
            const SizedBox(height: 16),
            
            // معلومات إضافية
            Card(
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'ℹ️ معلومات مهمة',
                      style: Theme.of(context).textTheme.titleMedium?.copyWith(
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(height: 8),
                    const Text('• هذا الميزة متاحة فقط في وضع التطوير'),
                    const Text('• البيانات المستخدمة هي بيانات تجريبية'),
                    const Text('• لا يتم إرسال طلبات حقيقية إلى السيرفر'),
                    const Text('• يمكنك اختبار جميع الصفحات والميزات'),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  String _getRoleName(String role) {
    switch (role) {
      case 'student':
        return 'طالبة';
      case 'teacher':
        return 'معلمة';
      case 'assistant':
        return 'مساعدة';
      case 'admin':
        return 'مدير';
      case 'sub_admin':
        return 'مدير فرعي';
      case 'teacher_support':
        return 'دعم المعلمين';
      default:
        return role;
    }
  }

  IconData _getRoleIcon(String role) {
    switch (role) {
      case 'student':
        return Icons.school;
      case 'teacher':
        return Icons.person;
      case 'assistant':
        return Icons.support_agent;
      case 'admin':
        return Icons.admin_panel_settings;
      case 'sub_admin':
        return Icons.supervisor_account;
      case 'teacher_support':
        return Icons.support;
      default:
        return Icons.person;
    }
  }

  Color _getRoleColor(String role) {
    switch (role) {
      case 'student':
        return Colors.blue;
      case 'teacher':
        return Colors.green;
      case 'assistant':
        return Colors.purple;
      case 'admin':
        return Colors.red;
      case 'sub_admin':
        return Colors.orange;
      case 'teacher_support':
        return Colors.teal;
      default:
        return Colors.grey;
    }
  }
}
