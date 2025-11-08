import 'package:flutter/material.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'core/config/env.dart';
import 'core/theme/app_theme.dart';
import 'core/router/app_router.dart';
import 'core/debug/debug_service.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  
  // Initialize debug services
  if (kDebugMode) {
    DebugService.setDebugMode(true);
    DebugService.info('Debug services initialized (Web Build)', 'MAIN');
  }
  
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
      debugShowCheckedModeBanner: false, // Always false for production - no debug banners
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
