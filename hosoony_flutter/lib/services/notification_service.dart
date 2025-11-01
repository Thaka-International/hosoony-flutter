import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter/foundation.dart';
import 'dart:io' show Platform;
import '../services/api_service.dart';

class NotificationService {
  FirebaseMessaging? _firebaseMessaging;

  FirebaseMessaging get firebaseMessaging {
    _firebaseMessaging ??= FirebaseMessaging.instance;
    return _firebaseMessaging!;
  }

  Future<void> initialize() async {
    try {
      await _requestPermissions();
      final fcmToken = await getFcmToken();
      debugPrint('FCM Token: $fcmToken');

      // Register device with FCM token to backend
      if (fcmToken != null) {
        try {
          final platform = Platform.isAndroid ? 'android' : (Platform.isIOS ? 'ios' : 'web');
          await ApiService.registerDevice(fcmToken, platform);
          debugPrint('Device registered successfully with FCM token');
        } catch (e) {
          debugPrint('Failed to register device: $e');
          // Continue even if registration fails
        }
      }

      // Handle foreground messages
      FirebaseMessaging.onMessage.listen((RemoteMessage message) {
        debugPrint('Got a message whilst in the foreground!');
        debugPrint('Message data: ${message.data}');

        if (message.notification != null) {
          debugPrint('Message also contained a notification: ${message.notification}');
        }
      });

      // Handle background messages
      FirebaseMessaging.onBackgroundMessage(_firebaseMessagingBackgroundHandler);

      // Listen for token refresh
      firebaseMessaging.onTokenRefresh.listen((newToken) async {
        debugPrint('FCM Token refreshed: $newToken');
        try {
          final platform = Platform.isAndroid ? 'android' : (Platform.isIOS ? 'ios' : 'web');
          await ApiService.registerDevice(newToken, platform);
          debugPrint('Device token updated successfully');
        } catch (e) {
          debugPrint('Failed to update device token: $e');
        }
      });
    } catch (e) {
      debugPrint('NotificationService initialization failed: $e');
    }
  }

  Future<void> _requestPermissions() async {
    try {
      NotificationSettings settings = await firebaseMessaging.requestPermission(
        alert: true,
        announcement: false,
        badge: true,
        carPlay: false,
        criticalAlert: false,
        provisional: false,
        sound: true,
      );

      debugPrint('User granted permission: ${settings.authorizationStatus}');
    } catch (e) {
      debugPrint('Permission request failed: $e');
    }
  }

  Future<String?> getFcmToken() async {
    try {
      return await firebaseMessaging.getToken();
    } catch (e) {
      debugPrint('FCM token retrieval failed: $e');
      return null;
    }
  }
}

// Background message handler
Future<void> _firebaseMessagingBackgroundHandler(RemoteMessage message) async {
  print("Handling a background message: ${message.messageId}");
}
