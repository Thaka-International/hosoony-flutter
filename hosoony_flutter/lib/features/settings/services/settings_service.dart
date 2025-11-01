import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'dart:convert';
import '../models/settings_model.dart';

class SettingsService {
  static const String settingsKey = 'app_settings';
  static final FlutterSecureStorage _storage = const FlutterSecureStorage();

  // Load settings from storage
  static Future<AppSettings> getSettings() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final settingsJson = prefs.getString(settingsKey);
      
      if (settingsJson != null) {
        final Map<String, dynamic> jsonMap = jsonDecode(settingsJson);
        return AppSettings.fromJson(jsonMap);
      }
      
      // Return default settings if none found
      return AppSettings();
    } catch (e) {
      print('Error loading settings: $e');
      return AppSettings();
    }
  }

  // Save settings to storage
  static Future<void> saveSettings(AppSettings settings) async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final settingsJson = jsonEncode(settings.toJson());
      await prefs.setString(settingsKey, settingsJson);
    } catch (e) {
      print('Error saving settings: $e');
    }
  }

  // Update individual setting
  static Future<void> updateSetting(String key, dynamic value) async {
    final currentSettings = await getSettings();
    final updatedSettings = currentSettings.copyWith(
      reminderBeforeClass: key == 'reminderBeforeClass' ? value : currentSettings.reminderBeforeClass,
      soundOnActivity: key == 'soundOnActivity' ? value : currentSettings.soundOnActivity,
      soundOnExam: key == 'soundOnExam' ? value : currentSettings.soundOnExam,
      reminderMinutes: key == 'reminderMinutes' ? value : currentSettings.reminderMinutes,
    );
    await saveSettings(updatedSettings);
  }

  // Reset to default settings
  static Future<void> resetToDefaults() async {
    await saveSettings(AppSettings());
  }
}



