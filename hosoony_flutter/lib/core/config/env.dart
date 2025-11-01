import 'package:flutter/foundation.dart';

class Env {
  // App Info
  static const String appName = 'حصوني';
  static const String version = '1.0.0';
  static const String buildNumber = '1';

  // API Base URL
  static const String baseUrl = 'https://thakaa.me/api/v1';
  static const String productionServer = 'https://thakaa.me/api/v1';
  static const String apiDocumentation = 'https://thakaa.me/docs';

  // Debug & Feature Flags
  static bool get isDebugMode {
    return kDebugMode;
  }

  // Storage Keys
  static const String tokenKey = 'auth_token';
  static const String userKey = 'user_data';
}
