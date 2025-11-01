import 'package:flutter/material.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/router/app_router.dart';
import '../../../services/api_service.dart';
import '../../../services/notification_service.dart';

class InitializationPage extends ConsumerStatefulWidget {
  const InitializationPage({super.key});

  @override
  ConsumerState<InitializationPage> createState() => _InitializationPageState();
}

class _InitializationPageState extends ConsumerState<InitializationPage> {
  @override
  void initState() {
    super.initState();
    _initializeServices();
  }

  Future<void> _initializeServices() async {
    try {
      // Initialize API service first (synchronous)
      ApiService.initialize();
      
      // Wait a bit to ensure Firebase is fully initialized
      await Future.delayed(const Duration(milliseconds: 500));
      
      // Initialize notification service only on mobile platforms
      if (!kIsWeb) {
        try {
          await NotificationService().initialize();
        } catch (e) {
          debugPrint('NotificationService initialization failed: $e');
        }
      }

      // After initialization, navigate to the correct page
      if (mounted) {
        AppRouter.goToSplash(context);
      }
    } catch (e) {
      debugPrint('Service initialization failed: $e');
      // Still navigate even if some services fail
      if (mounted) {
        AppRouter.goToSplash(context);
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return const Scaffold(
      body: Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            CircularProgressIndicator(),
            SizedBox(height: 16),
            Text('Initializing...'),
          ],
        ),
      ),
    );
  }
}
