# Task Date Header Update

## Summary
Added a beautiful date header to the daily tasks page that displays both Gregorian and Hijri dates in the format: "الإثنين، 28 أكتوبر، 12 ربيع الأول"

## Changes Made

### 1. **pubspec.yaml**
- Added `hijri: ^3.0.0` package for Hijri date conversion

### 2. **daily_tasks_page_new.dart**

#### Imports Added:
```dart
import 'package:intl/intl.dart';
import 'package:hijri/hijri.dart';
```

#### New Methods:

**`_formatTaskDate()`** - Formats the date in the required format:
```dart
String _formatTaskDate() {
  if (_logDate == null) return '';
  
  try {
    // Parse the date
    final date = DateTime.parse(_logDate!);
    
    // Format Gregorian date: "Monday, 28 October"
    final gregorianFormat = DateFormat('EEEE, d MMMM', 'ar');
    final gregorianDate = gregorianFormat.format(date);
    
    // Convert to Hijri date using the new API
    final hijriDate = HijriDate.fromDate(date);
    final hijriFormatted = '${hijriDate.hDay} ${_getHijriMonthName(hijriDate.hMonth)}';
    
    return '$gregorianDate، $hijriFormatted';
  } catch (e) {
    return _logDate ?? '';
  }
}
```

**`_getHijriMonthName()`** - Converts Hijri month number to Arabic name:
```dart
String _getHijriMonthName(int month) {
  const hijriMonths = [
    'محرم', 'صفر', 'ربيع الأول', 'ربيع الآخر',
    'جمادى الأولى', 'جمادى الآخرة', 'رجب', 'شعبان',
    'رمضان', 'شوال', 'ذو القعدة', 'ذو الحجة',
  ];
  return hijriMonths[month - 1];
}
```

#### UI Added:
- Added a date header card at the top of the page with:
  - Gradient background matching app theme
  - Calendar icon
  - Formatted date text in white, bold
  - Full width with rounded corners
  - Spacing below it

## Date Format Output Example

For October 28, 2025:
```
الإثنين، 28 أكتوبر، 12 ربيع الأول
```

This translates to: "Monday, 28 October, 12 Rabi' al-Awwal"

## Features

- ✅ Displays day name in Arabic (e.g., الإثنين = Monday)
- ✅ Shows Gregorian date in Arabic format (28 أكتوبر)
- ✅ Shows Hijri date with day and month (12 ربيع الأول)
- ✅ Beautiful gradient card design
- ✅ Responsive and accessible
- ✅ Error handling if date is missing

## Files Modified

1. **hosoony_flutter/pubspec.yaml**
   - Line 56: Added `hijri: ^3.0.0`

2. **hosoony_flutter/lib/features/student/pages/daily_tasks_page_new.dart**
   - Lines 5-6: Added imports for `intl` and `hijri`
   - Lines 431-450: Added `_formatTaskDate()` method
   - Lines 452-468: Added `_getHijriMonthName()` helper method
   - Lines 588-617: Added date header UI in the build method

## Dependencies Installed

- `hijri: ^3.0.0` - For Islamic calendar conversion

