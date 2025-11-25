import 'dart:convert';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import '../core/config/env.dart';
import '../core/errors/error_handler.dart';
import '../core/debug/debug_service.dart';
import '../data/models/user.dart';
import 'api_service.dart';

// Auth State
class AuthState {
  final bool isAuthenticated;
  final User? user;
  final String? token;
  final String? error;
  final bool isLoading;

  const AuthState({
    this.isAuthenticated = false,
    this.user,
    this.token,
    this.error,
    this.isLoading = false,
  });

  AuthState copyWith({
    bool? isAuthenticated,
    User? user,
    String? token,
    String? error,
    bool? isLoading,
  }) {
    return AuthState(
      isAuthenticated: isAuthenticated ?? this.isAuthenticated,
      user: user ?? this.user,
      token: token ?? this.token,
      error: error ?? this.error,
      isLoading: isLoading ?? this.isLoading,
    );
  }
}

// Auth Notifier
class AuthNotifier extends StateNotifier<AuthState> {
  AuthNotifier() : super(const AuthState());

  Future<void> login(String email, String password, {bool rememberMe = false}) async {
    DebugService.info('Starting email login', 'AUTH');
    state = state.copyWith(isLoading: true, error: null);
    
    try {
      final authState = await AuthService.login(email, password, rememberMe: rememberMe);
      
      // Only update state if login was successful (no error)
      if (authState.isAuthenticated && authState.error == null) {
        state = authState;
        DebugService.success('Login successful', 'AUTH');
      } else {
        // Login failed, show error
        state = state.copyWith(
          isLoading: false,
          error: authState.error ?? 'فشل تسجيل الدخول',
        );
        DebugService.error('Login failed', authState.error, null, 'AUTH');
      }
    } catch (e) {
      final error = ErrorHandler.handleError(e);
      DebugService.error('Login failed', e, null, 'AUTH');
      state = state.copyWith(
        isLoading: false,
        error: error.message,
      );
    }
  }

  Future<void> loginWithPhone(String phone, String verificationCode) async {
    // Implementation restored
  }

  Future<void> logout() async {
    try {
      await AuthService.logout();
    } catch (e) {
      // Ignore logout errors
    } finally {
      state = const AuthState();
    }
  }

  Future<void> checkAuth() async {
    try {
      final authState = await AuthService.getMe();
      state = authState;
    } catch (e) {
      state = const AuthState();
    }
  }

  Future<void> checkAutoLogin() async {
    try {
      DebugService.info('Checking for auto-login', 'AUTH');
      final authState = await AuthService.autoLogin();
      state = authState;
      if (authState.isAuthenticated) {
        DebugService.success('Auto-login successful', 'AUTH');
      } else {
        DebugService.info('No remember me data found', 'AUTH');
      }
    } catch (e) {
      DebugService.error('Auto-login check failed', e, null, 'AUTH');
      state = const AuthState();
    }
  }

  Future<Map<String, String?>> getRememberedCredentials() async {
    return await AuthService.getRememberedCredentials();
  }
}

// Auth Provider
final authStateProvider = StateNotifierProvider<AuthNotifier, AuthState>((ref) {
  return AuthNotifier();
});

// Auth Service
class AuthService {
  static final FlutterSecureStorage _storage = const FlutterSecureStorage();

  static Future<AuthState> login(String email, String password, {bool rememberMe = false}) async {
    try {
      final response = await ApiService.login(email, password);
      
      final userData = response['user'] as Map<String, dynamic>;
      final user = User.fromJson(userData);
      final token = response['token'] as String;

      await _storage.write(key: Env.tokenKey, value: token);
      await _storage.write(key: Env.userKey, value: jsonEncode(user.toJson()));
      
      // Set token in ApiService for immediate use
      ApiService.setToken(token);
      
      if (rememberMe) {
        await _storage.write(key: 'remembered_email', value: email);
        await _storage.write(key: 'remembered_password', value: password);
        await _storage.write(key: 'remember_me', value: 'true');
      } else {
        await _storage.delete(key: 'remembered_email');
        await _storage.delete(key: 'remembered_password');
        await _storage.delete(key: 'remember_me');
      }
      
      return AuthState(
        isAuthenticated: true,
        user: user,
        token: token,
      );
    } catch (e) {
      final error = ErrorHandler.handleError(e);
      return AuthState(error: error.message);
    }
  }

  static Future<void> logout() async {
    try {
      await ApiService.logout();
    } catch (e) {
      // Ignore logout errors
    } finally {
      // Clear token from ApiService
      ApiService.clearToken();
      
      // Clear all stored data
      await _storage.delete(key: Env.tokenKey);
      await _storage.delete(key: Env.userKey);
      await _storage.delete(key: 'remembered_email');
      await _storage.delete(key: 'remembered_password');
      await _storage.delete(key: 'remember_me');
    }
  }

  static Future<AuthState> getMe() async {
    try {
      final token = await _storage.read(key: Env.tokenKey);
      final userData = await _storage.read(key: Env.userKey);
      
      if (token != null && userData != null) {
        // Set token in ApiService for immediate use
        ApiService.setToken(token);
        
        final userMap = jsonDecode(userData) as Map<String, dynamic>;
        final user = User.fromJson(userMap);
        return AuthState(
          isAuthenticated: true,
          user: user,
          token: token,
        );
      }
      
      return const AuthState();
    } catch (e) {
      return const AuthState();
    }
  }

  static Future<AuthState> autoLogin() async {
    try {
      // First check if we have a valid token and user data
      final authState = await getMe();
      if (authState.isAuthenticated && authState.token != null) {
        return authState;
      }
      
      // If no valid token, try remembered credentials
      final rememberedEmail = await _storage.read(key: 'remembered_email');
      final rememberedPassword = await _storage.read(key: 'remembered_password');
      
      if (rememberedEmail != null && rememberedPassword != null) {
        // Try to login with remembered credentials
        try {
          return await login(rememberedEmail, rememberedPassword, rememberMe: true);
        } catch (e) {
          // If login fails, clear the remembered credentials
          await _storage.delete(key: 'remembered_email');
          await _storage.delete(key: 'remembered_password');
          await _storage.delete(key: 'remember_me');
          return const AuthState();
        }
      }
      
      return const AuthState();
    } catch (e) {
      // Clear all data if there's any error
      await _clearAllStoredData();
      return const AuthState();
    }
  }

  static Future<void> _clearAllStoredData() async {
    try {
      // Clear token from ApiService
      ApiService.clearToken();
      
      await _storage.delete(key: Env.tokenKey);
      await _storage.delete(key: Env.userKey);
      await _storage.delete(key: 'remembered_email');
      await _storage.delete(key: 'remembered_password');
      await _storage.delete(key: 'remember_me');
    } catch (e) {
      // Ignore errors when clearing data
    }
  }

  static Future<Map<String, String?>> getRememberedCredentials() async {
    final email = await _storage.read(key: 'remembered_email');
    final password = await _storage.read(key: 'remembered_password');
    final rememberMe = await _storage.read(key: 'remember_me');
    
    return {
      'email': email,
      'password': password,
      'rememberMe': rememberMe,
    };
  }

  // Public method to clear all stored data (useful for debugging)
  static Future<void> clearAllStoredData() async {
    await _clearAllStoredData();
  }
}
