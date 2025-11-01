import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import '../constants/strings.dart';
import '../theme/tokens.dart';

/// Data model for schedule items
/// Converts Map<String, dynamic> to a structured object with formatted getters
class ScheduleItem {
  final Map<String, dynamic> _data;

  ScheduleItem(this._data);

  String get day => _data['day']?.toString() ?? '';
  String? get date => _data['date']?.toString() ?? _data['date_formatted']?.toString();
  String? get startTime => _data['start_time']?.toString();
  String? get endTime => _data['end_time']?.toString();
  String get sessionType => _data['session_type']?.toString() ?? AppStrings.normalSession;
  Color get color {
    if (_data['color'] != null) {
      final hexColor = _data['color'] as String;
      try {
        return Color(int.parse(hexColor.replaceAll('#', '0xFF')));
      } catch (e) {
        return AppTokens.successGreen;
      }
    }
    return AppTokens.successGreen;
  }
  
  String? get dayKey => _data['day_key']?.toString();

  /// Get formatted time range (e.g., "08:00 ص - 10:00 ص")
  String get formattedTimeRange {
    final start = _formatTime(startTime);
    final end = _formatTime(endTime);
    if (start.isEmpty || end.isEmpty) return '';
    return '$start - $end';
  }

  /// Get formatted date string
  String get formattedDate {
    if (date == null || date!.isEmpty) return '';
    return _formatDate(date!);
  }

  /// Format time from "HH:mm:ss" or "HH:mm" to Arabic format
  String _formatTime(String? time) {
    if (time == null || time.isEmpty) return '';
    
    try {
      final parts = time.split(':');
      final hour = int.parse(parts[0]);
      final minute = parts.length > 1 ? int.parse(parts[1]) : 0;
      
      if (hour == 0) {
        return '12:${minute.toString().padLeft(2, '0')} ص';
      } else if (hour < 12) {
        return '$hour:${minute.toString().padLeft(2, '0')} ص';
      } else if (hour == 12) {
        return '12:${minute.toString().padLeft(2, '0')} ظ';
      } else {
        return '${hour - 12}:${minute.toString().padLeft(2, '0')} م';
      }
    } catch (e) {
      return time;
    }
  }

  /// Format date to Arabic format
  String _formatDate(String date) {
    try {
      if (date.contains('،') || date.contains('أ')) {
        return date; // Already formatted
      }
      
      final parsedDate = DateTime.parse(date);
      final now = DateTime.now();
      final today = DateTime(now.year, now.month, now.day);
      final dateOnly = DateTime(parsedDate.year, parsedDate.month, parsedDate.day);
      
      final difference = dateOnly.difference(today).inDays;
      
      if (difference == 0) {
        return AppStrings.today;
      } else if (difference == 1) {
        return AppStrings.tomorrow;
      } else if (difference == -1) {
        return AppStrings.yesterday;
      } else {
        return DateFormat('EEEE، d MMMM yyyy', 'ar').format(parsedDate);
      }
    } catch (e) {
      return date;
    }
  }
}

