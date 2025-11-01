import 'dart:convert';
import 'package:flutter/foundation.dart';
import '../debug/debug_service.dart';
import '../config/env.dart';
import '../../data/models/user.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';

/// 🚀 خدمة تجاوز تسجيل الدخول للاختبار
class LoginBypassService {
  static bool _isBypassEnabled = kDebugMode;
  
  /// تفعيل/إلغاء تجاوز تسجيل الدخول
  static void setBypassEnabled(bool enabled) {
    _isBypassEnabled = enabled;
    DebugService.info('تجاوز تسجيل الدخول: ${enabled ? "مفعل" : "معطل"}', 'LOGIN_BYPASS');
  }

  /// التحقق من إمكانية تجاوز تسجيل الدخول
  static bool get isBypassEnabled => _isBypassEnabled;

  /// قائمة المستخدمين التجريبية
  static final Map<String, User> _testUsers = {
    'student': const User(
      id: 1,
      name: 'طالبة تجريبية',
      email: 'student@test.com',
      role: 'student',
      gender: 'female',
      status: 'active',
    ),
    'teacher': const User(
      id: 2,
      name: 'معلمة تجريبية',
      email: 'teacher@test.com',
      role: 'teacher',
      gender: 'female',
      status: 'active',
    ),
    'assistant': const User(
      id: 3,
      name: 'مساعدة تجريبية',
      email: 'assistant@test.com',
      role: 'assistant',
      gender: 'female',
      status: 'active',
    ),
    'admin': const User(
      id: 4,
      name: 'مدير تجريبي',
      email: 'admin@test.com',
      role: 'admin',
      gender: 'male',
      status: 'active',
    ),
    'sub_admin': const User(
      id: 5,
      name: 'مدير فرعي تجريبي',
      email: 'sub_admin@test.com',
      role: 'sub_admin',
      gender: 'male',
      status: 'active',
    ),
    'teacher_support': const User(
      id: 6,
      name: 'دعم المعلمين تجريبي',
      email: 'teacher_support@test.com',
      role: 'teacher_support',
      gender: 'female',
      status: 'active',
    ),
  };

  /// الحصول على قائمة المستخدمين التجريبية
  static Map<String, User> get testUsers => Map.unmodifiable(_testUsers);

  /// تسجيل الدخول كطالب
  static Future<void> loginAsStudent() async {
    if (!_isBypassEnabled) {
      DebugService.warning('تجاوز تسجيل الدخول معطل', 'LOGIN_BYPASS');
      return;
    }
    
    await _bypassLogin('student');
  }

  /// تسجيل الدخول كمعلم
  static Future<void> loginAsTeacher() async {
    if (!_isBypassEnabled) {
      DebugService.warning('تجاوز تسجيل الدخول معطل', 'LOGIN_BYPASS');
      return;
    }
    
    await _bypassLogin('teacher');
  }

  /// تسجيل الدخول كمساعدة
  static Future<void> loginAsAssistant() async {
    if (!_isBypassEnabled) {
      DebugService.warning('تجاوز تسجيل الدخول معطل', 'LOGIN_BYPASS');
      return;
    }
    
    await _bypassLogin('assistant');
  }

  /// تسجيل الدخول كمدير
  static Future<void> loginAsAdmin() async {
    if (!_isBypassEnabled) {
      DebugService.warning('تجاوز تسجيل الدخول معطل', 'LOGIN_BYPASS');
      return;
    }
    
    await _bypassLogin('admin');
  }

  /// تسجيل الدخول كمدير فرعي
  static Future<void> loginAsSubAdmin() async {
    if (!_isBypassEnabled) {
      DebugService.warning('تجاوز تسجيل الدخول معطل', 'LOGIN_BYPASS');
      return;
    }
    
    await _bypassLogin('sub_admin');
  }

  /// تسجيل الدخول كدعم المعلمين
  static Future<void> loginAsTeacherSupport() async {
    if (!_isBypassEnabled) {
      DebugService.warning('تجاوز تسجيل الدخول معطل', 'LOGIN_BYPASS');
      return;
    }
    
    await _bypassLogin('teacher_support');
  }

  /// تسجيل الدخول التجريبي العام
  static Future<void> _bypassLogin(String role) async {
    try {
      DebugService.info('بدء تسجيل الدخول التجريبي كـ $role', 'LOGIN_BYPASS');
      
      final user = _testUsers[role];
      if (user == null) {
        throw Exception('نوع المستخدم غير موجود: $role');
      }

      // إنشاء توكن تجريبي
      final testToken = 'test_token_${user.id}_${DateTime.now().millisecondsSinceEpoch}';
      
      // حفظ البيانات في التخزين الآمن
      final storage = const FlutterSecureStorage();
      await storage.write(key: Env.tokenKey, value: testToken);
      await storage.write(key: Env.userKey, value: jsonEncode(user.toJson()));
      
      DebugService.success('تم تسجيل الدخول التجريبي كـ ${user.name} ($role)', 'LOGIN_BYPASS');
      DebugService.authState('تم تسجيل الدخول التجريبي', user.toJson());
      
    } catch (e) {
      DebugService.error('فشل في تسجيل الدخول التجريبي', e, null, 'LOGIN_BYPASS');
      rethrow;
    }
  }

  /// تسجيل الخروج التجريبي
  static Future<void> bypassLogout() async {
    try {
      DebugService.info('تسجيل الخروج التجريبي', 'LOGIN_BYPASS');
      
      final storage = const FlutterSecureStorage();
      await storage.delete(key: Env.tokenKey);
      await storage.delete(key: Env.userKey);
      
      DebugService.success('تم تسجيل الخروج التجريبي', 'LOGIN_BYPASS');
      
    } catch (e) {
      DebugService.error('فشل في تسجيل الخروج التجريبي', e, null, 'LOGIN_BYPASS');
    }
  }

  /// الحصول على معلومات المستخدم الحالي
  static Future<User?> getCurrentUser() async {
    try {
      final storage = const FlutterSecureStorage();
      final userData = await storage.read(key: Env.userKey);
      
      if (userData != null) {
        // تحويل البيانات من JSON string إلى Map
        final userJson = jsonDecode(userData) as Map<String, dynamic>;
        return User.fromJson(userJson);
      }
      
      return null;
    } catch (e) {
      DebugService.error('فشل في قراءة بيانات المستخدم', e, null, 'LOGIN_BYPASS');
      return null;
    }
  }

  /// التحقق من حالة تسجيل الدخول التجريبي
  static Future<bool> isLoggedIn() async {
    try {
      final storage = const FlutterSecureStorage();
      final token = await storage.read(key: Env.tokenKey);
      return token != null && token.startsWith('test_token_');
    } catch (e) {
      DebugService.error('فشل في التحقق من حالة تسجيل الدخول', e, null, 'LOGIN_BYPASS');
      return false;
    }
  }
}
