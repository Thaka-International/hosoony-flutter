import 'package:flutter/foundation.dart';

// Simple Firebase service that handles web vs mobile platforms
class FirebaseService {
  static bool get isSupported => !kIsWeb;
  
  static Future<void> initialize() async {
    if (kIsWeb) {
      debugPrint('Firebase not supported on web platform');
      return;
    }
    
    debugPrint('Firebase initialization skipped for web builds');
  }
  
  static Future<String?> getToken() async {
    if (kIsWeb) {
      debugPrint('Firebase token not available on web');
      return null;
    }
    
    debugPrint('Firebase token not available on web');
    return null;
  }
  
  static Future<void> requestPermission() async {
    if (kIsWeb) {
      debugPrint('Firebase permission not available on web');
      return;
    }
    
    debugPrint('Firebase permission not available on web');
  }
}
