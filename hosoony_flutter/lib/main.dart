import 'package:flutter/material.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:firebase_core/firebase_core.dart';
import 'core/config/env.dart';
import 'core/theme/app_theme.dart';
import 'core/router/app_router.dart';
import 'core/debug/debug_service.dart';
import 'services/api_service.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  
  // Initialize Firebase only on mobile platforms
  if (!kIsWeb) {
    try {
      await Firebase.initializeApp();
      debugPrint('Firebase initialized successfully');
    } catch (e) {
      debugPrint('Firebase initialization failed: $e');
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

    return MaterialApp.router(
      title: Env.appName,
      debugShowCheckedModeBanner: Env.isDebugMode,
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
