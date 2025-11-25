import 'package:flutter/material.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:firebase_core/firebase_core.dart';
import 'core/config/env.dart';
import 'core/theme/app_theme.dart';
import 'core/router/app_router.dart';
import 'core/debug/debug_service.dart';
import 'services/api_service.dart';
import 'services/auth_service.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  
  // Initialize Firebase only on mobile platforms
  // Firebase will be initialized automatically from native code if config files exist
  if (!kIsWeb) {
    try {
      await Firebase.initializeApp();
      debugPrint('✅ Firebase initialized successfully');
    } catch (e) {
      // If Firebase config is missing, app will continue without Firebase
      debugPrint('⚠️ Firebase initialization skipped: $e');
      debugPrint('   App will continue without Firebase features');
    }
  }

  // Initialize debug services
  if (kDebugMode) {
    DebugService.setDebugMode(true);
    DebugService.info('Debug services initialized', 'MAIN');
  }
  
  // Initialize ApiService
  ApiService.initialize();
  
  runApp(
    const ProviderScope(
      child: HosoonyApp(),
    ),
  );
}

class HosoonyApp extends ConsumerWidget {
  const HosoonyApp({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final router = ref.watch(appRouterProvider);
    final authNotifier = ref.read(authStateProvider.notifier);

    // Set callback for account inactive errors
    ApiService.setAccountInactiveCallback(() {
      // Logout user automatically when account is inactive
      authNotifier.logout();
      
      // Navigate to login page after logout
      // Use a post-frame callback to ensure navigation happens after state update
      WidgetsBinding.instance.addPostFrameCallback((_) {
        router.go('/login');
      });
    });
    
    // Listen to auth state changes to handle logout navigation
    ref.listen<AuthState>(authStateProvider, (previous, next) {
      // If user was logged in and now is not, navigate to login
      if (previous?.isAuthenticated == true && !next.isAuthenticated) {
        WidgetsBinding.instance.addPostFrameCallback((_) {
          router.go('/login');
        });
      }
    });

    return MaterialApp.router(
      title: Env.appName,
      debugShowCheckedModeBanner: false, // Always false for production - no debug banners in screenshots
      theme: AppTheme.lightTheme,
      darkTheme: AppTheme.darkTheme,
      themeMode: ThemeMode.system,
      routerConfig: router,
      builder: (context, child) {
        return Directionality(
          textDirection: TextDirection.rtl,
          child: child!,
        );
      },
    );
  }
}
