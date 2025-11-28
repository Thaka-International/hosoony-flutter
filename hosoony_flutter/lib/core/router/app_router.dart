import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'dart:convert';
import '../../features/auth/pages/initialization_page.dart';
import '../../features/auth/pages/splash_page.dart';
import '../../features/auth/pages/login_page.dart';
import '../../features/student/pages/student_home_page.dart';
import '../../features/student/pages/daily_tasks_page_new.dart';
import '../../features/student/pages/companions_page.dart';
import '../../features/student/pages/schedule_page.dart';
import '../../features/student/pages/achievements_page.dart';
import '../../features/student/pages/notifications_page.dart';
import '../../features/student/pages/companion_evaluation_page.dart';
import '../../features/student/pages/exams_list_page.dart';
import '../../features/student/pages/take_exam_page.dart';
import '../../features/student/pages/exam_result_page.dart';
import '../../features/settings/pages/settings_page.dart';
import '../../features/teacher/pages/teacher_home_page.dart';
import '../../features/teacher/pages/companion_evaluations_report_page.dart';
import '../../features/support/pages/support_home_page.dart';
import '../../features/admin/pages/admin_home_page.dart';
import '../../features/admin/pages/api_test_page.dart';
import '../../features/admin/pages/comprehensive_test_page.dart';
import '../../features/admin/pages/comprehensive_api_test_page.dart';
import '../../features/admin/pages/server_info_page.dart';
import '../../features/admin/pages/network_monitor_page.dart';
import '../../features/admin/pages/phone_auth_test_page.dart';
import '../../services/auth_service.dart';
import '../../services/api_service.dart';

final appRouterProvider = Provider<GoRouter>((ref) {
  // فحص تسجيل الدخول التلقائي عند بدء التطبيق
  WidgetsBinding.instance.addPostFrameCallback((_) async {
    // First check auto-login which will handle token setting
    await ref.read(authStateProvider.notifier).checkAutoLogin();
    
    // Then ensure token is set in ApiService if user is authenticated
    final authState = ref.read(authStateProvider);
    if (authState.isAuthenticated && authState.token != null) {
      ApiService.setToken(authState.token!);
    }
  });

  return GoRouter(
    initialLocation: '/init',
    redirect: (context, state) {
      final authState = ref.read(authStateProvider);
      final isLoggedIn = authState.isAuthenticated;
      final location = state.matchedLocation;

      // Allow initialization page to always be shown
      if (location == '/init') {
        return null;
      }

      // Auth flow paths are accessible by anyone
      final authFlowPaths = ['/splash', '/login'];
      final isAuthFlow = authFlowPaths.contains(location);

      // If logged in, handle redirection from auth flow to home page
      if (isLoggedIn) {
        if (isAuthFlow) {
          final user = authState.user;
          switch (user?.role) {
            case 'student':
              return '/student/home';
            case 'teacher':
              return '/teacher/home';
            case 'assistant':
              return '/support/home';
            case 'admin':
              return '/admin/home';
            default:
              return '/student/home'; // Fallback
          }
        }
      } else { // Not logged in
        // If trying to access a protected page, redirect to login
        if (!isAuthFlow && location != '/') { // Also check for root
          return '/login';
        }
      }

      // No redirect needed
      return null;
    },
    routes: [
      GoRoute(
        path: '/init',
        name: 'init',
        builder: (context, state) => const InitializationPage(),
      ),
      GoRoute(
        path: '/',
        name: 'home',
        builder: (context, state) => const SplashPage(),
      ),
      GoRoute(
        path: '/splash',
        name: 'splash',
        builder: (context, state) => const SplashPage(),
      ),
      GoRoute(
        path: '/login',
        name: 'login',
        builder: (context, state) => const LoginPage(),
      ),
      GoRoute(
        path: '/student/home',
        name: 'student-home',
        builder: (context, state) => const StudentHomePage(),
        routes: [
          GoRoute(
            path: 'daily-tasks',
            name: 'student-daily-tasks',
            builder: (context, state) => const StudentDailyTasksPageNew(),
          ),
          GoRoute(
            path: 'companions',
            name: 'student-companions',
            builder: (context, state) => const StudentCompanionsPage(),
          ),
          GoRoute(
            path: 'schedule',
            name: 'student-schedule',
            builder: (context, state) => const StudentSchedulePage(),
          ),
          GoRoute(
            path: 'achievements',
            name: 'student-achievements',
            builder: (context, state) => const StudentAchievementsPage(),
          ),
          GoRoute(
            path: 'notifications',
            name: 'student-notifications',
            builder: (context, state) => const StudentNotificationsPage(),
          ),
          GoRoute(
            path: 'settings',
            name: 'student-settings',
            builder: (context, state) => const SettingsPage(),
          ),
          GoRoute(
            path: 'exams',
            name: 'student-exams',
            builder: (context, state) => const ExamsListPage(),
            routes: [
              GoRoute(
                path: ':examId/take',
                name: 'student-take-exam',
                builder: (context, state) {
                  final examId = int.parse(state.pathParameters['examId']!);
                  return TakeExamPage(examId: examId);
                },
              ),
              GoRoute(
                path: ':examId/result',
                name: 'student-exam-result',
                builder: (context, state) {
                  final examId = int.parse(state.pathParameters['examId']!);
                  return ExamResultPage(examId: examId);
                },
              ),
            ],
          ),
          GoRoute(
            path: 'companion-evaluation',
            name: 'student-companion-evaluation',
            builder: (context, state) {
              final sessionId = state.uri.queryParameters['session_id'] ?? '';
              final companionJson = state.uri.queryParameters['companion'];
              Map<String, dynamic> companion = {};
              
              if (companionJson != null && companionJson.isNotEmpty) {
                try {
                  final decoded = Uri.decodeComponent(companionJson);
                  print('Decoded companion JSON: $decoded');
                  companion = Map<String, dynamic>.from(jsonDecode(decoded));
                  print('Parsed companion: $companion');
                  
                  // Ensure id is present and valid
                  if (companion['id'] == null) {
                    print('Warning: Companion ID is null after parsing');
                  }
                } catch (e) {
                  print('Error parsing companion JSON: $e');
                  print('Companion JSON string: $companionJson');
                }
              } else {
                print('Warning: No companion JSON provided in query parameters');
              }
              
              print('Final companion data: $companion');
              print('Final session ID: $sessionId');
              
              return CompanionEvaluationPage(
                sessionId: sessionId,
                companion: companion,
              );
            },
          ),
        ],
      ),
      GoRoute(
        path: '/teacher/home',
        name: 'teacher-home',
        builder: (context, state) => const TeacherHomePage(),
        routes: [
          GoRoute(
            path: 'companion-evaluations',
            name: 'teacher-companion-evaluations',
            builder: (context, state) {
              final classId = state.uri.queryParameters['class_id'];
              final sessionId = state.uri.queryParameters['session_id'];
              final studentId = state.uri.queryParameters['student_id'];
              
              return CompanionEvaluationsReportPage(
                classId: classId != null ? int.tryParse(classId) : null,
                sessionId: sessionId != null ? int.tryParse(sessionId) : null,
                studentId: studentId != null ? int.tryParse(studentId) : null,
              );
            },
          ),
        ],
      ),
      GoRoute(
        path: '/support/home',
        name: 'support-home',
        builder: (context, state) => const SupportHomePage(),
      ),
      GoRoute(
        path: '/admin/home',
        name: 'admin-home',
        builder: (context, state) => const AdminHomePage(),
        routes: [
          GoRoute(
            path: 'api-test',
            name: 'admin-api-test',
            builder: (context, state) => const ApiTestPage(),
          ),
          GoRoute(
            path: 'comprehensive-test',
            name: 'admin-comprehensive-test',
            builder: (context, state) => const ComprehensiveTestPage(),
          ),
          GoRoute(
            path: 'comprehensive-api-test',
            name: 'admin-comprehensive-api-test',
            builder: (context, state) => const ComprehensiveApiTestPage(),
          ),
          GoRoute(
            path: 'server-info',
            name: 'admin-server-info',
            builder: (context, state) => const ServerInfoPage(),
          ),
          GoRoute(
            path: 'network-monitor',
            name: 'admin-network-monitor',
            builder: (context, state) => const NetworkMonitorPage(),
          ),
          GoRoute(
            path: 'phone-auth-test',
            name: 'admin-phone-auth-test',
            builder: (context, state) => const PhoneAuthTestPage(),
          ),
        ],
      ),
    ],
  );
});

class AppRouter {
  static void goToSplash(BuildContext context) {
    context.go('/splash');
  }
  
  static void goToLogin(BuildContext context) {
    context.go('/login');
  }
  
  static void goToStudentHome(BuildContext context) {
    context.go('/student/home');
  }
  
  static void goToStudentDailyTasks(BuildContext context) {
    context.go('/student/home/daily-tasks');
  }
  
  static void goToStudentCompanions(BuildContext context) {
    context.go('/student/home/companions');
  }
  
  static void goToStudentSchedule(BuildContext context) {
    context.go('/student/home/schedule');
  }
  
  static void goToStudentAchievements(BuildContext context) {
    context.go('/student/home/achievements');
  }
  
  // Mock routes for other roles
  static void goToTeacherHome(BuildContext context) {
    context.go('/teacher/home');
  }
  
  static void goToSupportHome(BuildContext context) {
    context.go('/support/home');
  }
  
  static void goToAdminHome(BuildContext context) {
    context.go('/admin/home');
  }
}
