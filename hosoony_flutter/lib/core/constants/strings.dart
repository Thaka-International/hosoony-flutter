/// Centralized strings for the application
/// This makes future changes easier and prepares for multi-language support
class AppStrings {
  AppStrings._(); // Private constructor to prevent instantiation

  // Task-related strings
  static const String undefinedTask = 'مهمة غير محددة';
  static const String noDescription = 'لا يوجد وصف';
  
  // Task location strings
  static const String inClass = 'في الفصل';
  static const String inSession = 'أثناء الحلقة';
  static const String homework = 'واجب منزلي';
  static const String inMosque = 'في المسجد';
  static const String inHome = 'في المنزل';
  static const String undefinedLocation = 'غير محدد';
  
  // Schedule-related strings
  static const String weeklySchedule = 'جدولك الأسبوعي';
  static const String availableDays = 'الأيام المتاحة في جدول الفصل';
  static const String day = 'يوم';
  static const String normalSession = 'جلسة عادية';
  static const String noSchedule = 'لا يوجد جدول محدد حالياً';
  static const String className = 'الفصل';
  static const String daysAvailable = 'يوم متاح في هذا الأسبوع';
  
  // Attendance strings
  static const String checkIn = 'تسجيل الحضور';
  static const String viewAttendance = 'سجل الحضور';
  static const String alreadyCheckedIn = '(تم التسجيل)';
  static const String checkInSuccess = 'تم تسجيل الحضور بنجاح';
  static const String checkInError = 'خطأ في تسجيل الحضور';
  static const String alreadyCheckedInMessage = 'تم تسجيل الحضور مسبقاً';
  
  // Day names
  static const String sunday = 'الأحد';
  static const String monday = 'الاثنين';
  static const String tuesday = 'الثلاثاء';
  static const String wednesday = 'الأربعاء';
  static const String thursday = 'الخميس';
  static const String friday = 'الجمعة';
  static const String saturday = 'السبت';
  
  // Date-related strings
  static const String today = 'اليوم';
  static const String tomorrow = 'غداً';
  static const String yesterday = 'أمس';
  
  // Common strings
  static const String close = 'إغلاق';
  static const String ok = 'حسناً';
}

