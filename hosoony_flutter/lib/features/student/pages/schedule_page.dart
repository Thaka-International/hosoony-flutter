import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:dio/dio.dart';
import '../../../core/router/app_router.dart';
import '../../../core/theme/tokens.dart';
import '../../../core/constants/strings.dart';
import '../../../core/models/schedule_item.dart';
import '../../../services/auth_service.dart';
import '../../../services/api_service.dart';
import 'package:intl/intl.dart';

class StudentSchedulePage extends ConsumerStatefulWidget {
  const StudentSchedulePage({super.key});

  @override
  ConsumerState<StudentSchedulePage> createState() => _StudentSchedulePageState();
}

class _StudentSchedulePageState extends ConsumerState<StudentSchedulePage>
    with TickerProviderStateMixin {
  late AnimationController _animationController;
  late Animation<double> _fadeAnimation;
  late Animation<Offset> _offsetAnimation;
  
  bool _isCheckingIn = false;
  bool _hasCheckedInToday = false;
  
  List<Map<String, dynamic>> _schedule = [];
  bool _isLoadingSchedule = true;
  Map<String, dynamic>? _classInfo;

  @override
  void initState() {
    super.initState();
    
    _animationController = AnimationController(
      duration: const Duration(milliseconds: 800),
      vsync: this,
    );

    _fadeAnimation = Tween<double>(
      begin: 0.0,
      end: 1.0,
    ).animate(CurvedAnimation(
      parent: _animationController,
      curve: Curves.easeOut,
    ));

    _offsetAnimation = Tween<Offset>(
      begin: const Offset(0, 0.5), // ابدأ من نصف ارتفاع الويدجت للأسفل
      end: Offset.zero,          // وانتهِ في الموضع الأصلي
    ).animate(CurvedAnimation(
      parent: _animationController,
      curve: Curves.easeOutCubic,
    ));

    _animationController.forward();
    _loadSchedule();
  }

  @override
  void dispose() {
    _animationController.dispose();
    super.dispose();
  }

  Future<void> _loadSchedule() async {
    try {
      setState(() {
        _isLoadingSchedule = true;
      });

      // Ensure token is set
      final authState = ref.read(authStateProvider);
      if (authState.token != null) {
        ApiService.setToken(authState.token!);
      }

      final response = await ApiService.getMySchedule();

      if (mounted) {
        setState(() {
          _schedule = List<Map<String, dynamic>>.from(response['schedule'] ?? []);
          _classInfo = response['class'];
          _isLoadingSchedule = false;
        });
      }
    } catch (e) {
      // Log error silently - could use a logging service here
      if (mounted) {
        setState(() {
          _schedule = [];
          _isLoadingSchedule = false;
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final authState = ref.watch(authStateProvider);

    return Scaffold(
      appBar: AppBar(
        leading: IconButton(
          icon: const Icon(Icons.arrow_back),
          onPressed: () => context.go('/student/home'),
        ),
        title: Row(
          children: [
            ClipRRect(
              borderRadius: BorderRadius.circular(AppTokens.radiusSM),
              child: Image.asset(
                'assets/images/hosoony-logo.png',
                width: AppTokens.iconSizeMD,
                height: AppTokens.iconSizeMD,
                fit: BoxFit.contain,
                errorBuilder: (context, error, stackTrace) {
                  return const Icon(
                    Icons.mosque,
                    size: AppTokens.iconSizeMD,
                    color: AppTokens.neutralWhite,
                  );
                },
              ),
            ),
            const SizedBox(width: AppTokens.spacingSM),
            const Text(
              'الجدول',
              style: TextStyle(
                fontFamily: AppTokens.primaryFontFamily,
                fontWeight: AppTokens.fontWeightBold,
              ),
            ),
          ],
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.notifications_outlined),
            onPressed: () {
              ScaffoldMessenger.of(context).showSnackBar(
                const SnackBar(content: Text('الإشعارات')),
              );
            },
          ),
          IconButton(
            icon: const Icon(Icons.logout),
            onPressed: () async {
              await ref.read(authStateProvider.notifier).logout();
              AppRouter.goToLogin(context);
            },
          ),
        ],
      ),
      body: AnimatedBuilder(
        animation: _animationController,
        builder: (context, child) {
          return FadeTransition(
            opacity: _fadeAnimation,
            child: SlideTransition(
              position: _offsetAnimation, // استخدم الأنيميشن الجديد هنا
              child: SingleChildScrollView(
                padding: const EdgeInsets.all(AppTokens.spacingMD),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Welcome Section with Class Info
                    Container(
                      width: double.infinity,
                      padding: const EdgeInsets.symmetric(
                        horizontal: AppTokens.spacingLG,
                        vertical: AppTokens.spacingMD,
                      ),
                      decoration: BoxDecoration(
                        gradient: AppTokens.primaryGradient,
                        borderRadius: BorderRadius.circular(AppTokens.radiusLG),
                        boxShadow: AppTokens.shadowMD,
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            AppStrings.weeklySchedule,
                            style: Theme.of(context).textTheme.titleLarge?.copyWith(
                              color: AppTokens.neutralWhite,
                              fontWeight: AppTokens.fontWeightBold,
                            ),
                          ),
                          if (_classInfo != null) ...[
                            const SizedBox(height: AppTokens.spacingXS),
                            Text(
                              _classInfo!['name']?.toString() ?? AppStrings.className,
                              style: Theme.of(context).textTheme.titleSmall?.copyWith(
                                color: AppTokens.neutralWhite.withValues(alpha: 0.95),
                                fontWeight: AppTokens.fontWeightMedium,
                              ),
                            ),
                          ],
                          if (_schedule.isNotEmpty) ...[
                            const SizedBox(height: AppTokens.spacingXS),
                            Text(
                              '${_schedule.length} ${AppStrings.daysAvailable}',
                              style: Theme.of(context).textTheme.bodySmall?.copyWith(
                                color: AppTokens.neutralWhite.withValues(alpha: 0.9),
                              ),
                            ),
                          ],
                        ],
                      ),
                    ),
                    
                    const SizedBox(height: AppTokens.spacingMD),
                    
                    // Schedule Header
                    if (_schedule.isNotEmpty) ...[
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Text(
                            AppStrings.availableDays,
                            style: Theme.of(context).textTheme.titleLarge?.copyWith(
                              fontWeight: AppTokens.fontWeightBold,
                            ),
                          ),
                          Chip(
                            label: Text(
                              '${_schedule.length} ${AppStrings.day}',
                              style: const TextStyle(fontSize: 12),
                            ),
                            backgroundColor: AppTokens.infoBlue.withValues(alpha: 0.1),
                          ),
                        ],
                      ),
                      const SizedBox(height: AppTokens.spacingMD),
                    ],
                    
                    // Attendance Buttons
                    Row(
                      children: [
                        Expanded(
                          child: _buildAttendanceButton(
                            context,
                            'تسجيل الحضور',
                            Icons.check_circle_outline,
                            AppTokens.successGreen,
                            _handleCheckIn,
                            isLoading: _isCheckingIn,
                            disabled: _hasCheckedInToday,
                          ),
                        ),
                        const SizedBox(width: AppTokens.spacingMD),
                        Expanded(
                          child: _buildAttendanceButton(
                            context,
                            'سجل الحضور',
                            Icons.calendar_month,
                            AppTokens.infoBlue,
                            _viewAttendanceRecord,
                          ),
                        ),
                      ],
                    ),
                    
                    const SizedBox(height: AppTokens.spacingLG),
                    
                    // Schedule Cards
                    if (_isLoadingSchedule)
                      const Center(
                        child: Padding(
                          padding: EdgeInsets.all(AppTokens.spacingLG),
                          child: CircularProgressIndicator(),
                        ),
                      )
                    else if (_schedule.isEmpty)
                      Card(
                        child: Padding(
                          padding: const EdgeInsets.all(AppTokens.spacingLG),
                          child: Center(
                            child: Column(
                              children: [
                                Icon(
                                  Icons.event_busy,
                                  size: 48,
                                  color: AppTokens.neutralMedium,
                                ),
                                const SizedBox(height: AppTokens.spacingMD),
                                Text(
                                  AppStrings.noSchedule,
                                  style: Theme.of(context).textTheme.bodyLarge,
                                ),
                                if (_classInfo != null) ...[
                                  const SizedBox(height: AppTokens.spacingSM),
                                  Text(
                                    '${AppStrings.className}: ${_classInfo!['name'] ?? AppStrings.undefinedLocation}',
                                    style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                                      color: AppTokens.neutralMedium,
                                    ),
                                  ),
                                ],
                              ],
                            ),
                          ),
                        ),
                      )
                    else
                      ...(_buildScheduleList()),
                  ],
                ),
              ),
            ),
          );
        },
      ),
      bottomNavigationBar: BottomNavigationBar(
        type: BottomNavigationBarType.fixed,
        currentIndex: 3,
        onTap: (index) {
          switch (index) {
            case 0:
              AppRouter.goToStudentHome(context);
              break;
            case 1:
              AppRouter.goToStudentDailyTasks(context);
              break;
            case 2:
              AppRouter.goToStudentCompanions(context);
              break;
            case 3:
              AppRouter.goToStudentSchedule(context);
              break;
            case 4:
              AppRouter.goToStudentAchievements(context);
              break;
          }
        },
        items: const [
          BottomNavigationBarItem(
            icon: Icon(Icons.home),
            label: 'الرئيسية',
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.task_alt),
            label: 'المهام',
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.people),
            label: 'الرفيقات',
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.schedule),
            label: 'الجدول',
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.star),
            label: 'الإنجازات',
          ),
        ],
      ),
    );
  }

  Widget _buildScheduleCard(
    BuildContext context,
    String day,
    String? date,
    String time,
    String session,
    String teacher,
    Color color,
  ) {
    return Card(
      elevation: 2,
      child: Padding(
        padding: const EdgeInsets.all(AppTokens.spacingMD),
        child: Row(
          children: [
            Container(
              width: 50,
              height: 50,
              decoration: BoxDecoration(
                color: color.withValues(alpha: 0.1),
                borderRadius: BorderRadius.circular(AppTokens.radiusFull),
              ),
              child: Icon(
                Icons.schedule,
                color: color,
                size: AppTokens.iconSizeMD,
              ),
            ),
            
            const SizedBox(width: AppTokens.spacingMD),
            
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    day,
                    style: Theme.of(context).textTheme.titleMedium?.copyWith(
                      fontWeight: AppTokens.fontWeightBold,
                    ),
                  ),
                  if (date != null && date.isNotEmpty) ...[
                    const SizedBox(height: AppTokens.spacingXS),
                    Text(
                      _formatDate(date),
                      style: Theme.of(context).textTheme.bodySmall?.copyWith(
                        color: color,
                        fontWeight: AppTokens.fontWeightMedium,
                      ),
                    ),
                  ],
                  const SizedBox(height: AppTokens.spacingXS),
                  Text(
                    time,
                    style: Theme.of(context).textTheme.bodySmall?.copyWith(
                      color: AppTokens.neutralMedium,
                    ),
                  ),
                  const SizedBox(height: AppTokens.spacingXS),
                  Text(
                    session,
                    style: Theme.of(context).textTheme.bodySmall?.copyWith(
                      color: AppTokens.neutralMedium,
                    ),
                  ),
                  const SizedBox(height: AppTokens.spacingXS),
                  Text(
                    teacher,
                    style: Theme.of(context).textTheme.bodySmall?.copyWith(
                      color: AppTokens.neutralMedium,
                    ),
                  ),
                ],
              ),
            ),
            
            IconButton(
              icon: const Icon(Icons.notifications_outlined),
              onPressed: () {
                ScaffoldMessenger.of(context).showSnackBar(
                  SnackBar(content: Text('تم تفعيل تذكير لجلسة $day')),
                );
              },
            ),
          ],
        ),
      ),
    );
  }

  /// Get Arabic day name from various input formats
  String _getArabicDayName(Map<String, dynamic> scheduleItem) {
    final dayValue = scheduleItem['day'];
    String dayString = AppStrings.day;
    
    // Handle different data types (fallback safety)
    if (dayValue != null) {
      if (dayValue is String) {
        // Normal case: Backend sends Arabic day name
        dayString = dayValue;
      } else if (dayValue is int) {
        // Fallback: if day comes as index
        dayString = _getDayNameByIndex(dayValue);
      } else {
        dayString = dayValue.toString();
      }
    }
    
    // Final fallback: If day is still numeric, try day_key as last resort
    if (RegExp(r'^\d+$').hasMatch(dayString)) {
      final dayKeyValue = scheduleItem['day_key'];
      
      // Try day_key as String (like "sunday") to convert to Arabic
      if (dayKeyValue is String) {
        dayString = _getDayNameByKey(dayKeyValue) ?? dayString;
      }
      // Try day_key as int (index)
      else if (dayKeyValue is int) {
        dayString = _getDayNameByIndex(dayKeyValue);
      }
    }
    
    return dayString;
  }

  /// Get Arabic day name by index (0-6)
  String _getDayNameByIndex(int index) {
    const dayNamesByIndex = [
      AppStrings.sunday,      // 0
      AppStrings.monday,      // 1
      AppStrings.tuesday,     // 2
      AppStrings.wednesday,   // 3
      AppStrings.thursday,    // 4
      AppStrings.friday,      // 5
      AppStrings.saturday,    // 6
    ];
    if (index >= 0 && index < dayNamesByIndex.length) {
      return dayNamesByIndex[index];
    }
    return index.toString();
  }

  /// Get Arabic day name by key (e.g., "sunday", "monday")
  String? _getDayNameByKey(String key) {
    final dayNamesMap = {
      'sunday': AppStrings.sunday,
      'monday': AppStrings.monday,
      'tuesday': AppStrings.tuesday,
      'wednesday': AppStrings.wednesday,
      'thursday': AppStrings.thursday,
      'friday': AppStrings.friday,
      'saturday': AppStrings.saturday,
    };
    return dayNamesMap[key.toLowerCase()];
  }

  List<Widget> _buildScheduleList() {
    if (_schedule.isEmpty) return [];
    
    return _schedule.asMap().entries.map((entry) {
      final index = entry.key;
      final scheduleData = entry.value;
      
      // Use ScheduleItem model for clean data access
      final scheduleItem = ScheduleItem(scheduleData);
      final dayString = _getArabicDayName(scheduleData);
      
      return Column(
        children: [
          if (index > 0) const SizedBox(height: AppTokens.spacingMD),
          _buildScheduleCard(
            context,
            dayString,
            scheduleItem.formattedDate.isNotEmpty ? scheduleItem.formattedDate : null,
            scheduleItem.formattedTimeRange,
            scheduleItem.sessionType,
            _classInfo?['name']?.toString() ?? AppStrings.className,
            scheduleItem.color,
          ),
        ],
      );
    }).toList();
  }

  Widget _buildAttendanceButton(
    BuildContext context,
    String label,
    IconData icon,
    Color color,
    VoidCallback onPressed, {
    bool isLoading = false,
    bool disabled = false,
  }) {
    return Card(
      elevation: 2,
      child: InkWell(
        onTap: disabled || isLoading ? null : onPressed,
        borderRadius: BorderRadius.circular(AppTokens.radiusMD),
        child: Container(
          padding: const EdgeInsets.all(AppTokens.spacingMD),
          decoration: BoxDecoration(
            color: color.withValues(alpha: 0.1),
            borderRadius: BorderRadius.circular(AppTokens.radiusMD),
            border: Border.all(
              color: color.withValues(alpha: 0.3),
              width: 1.5,
            ),
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              if (isLoading)
                const SizedBox(
                  width: 24,
                  height: 24,
                  child: CircularProgressIndicator(strokeWidth: 2),
                )
              else
                Icon(
                  icon,
                  color: color,
                  size: 32,
                ),
              const SizedBox(height: AppTokens.spacingSM),
              Text(
                label,
                textAlign: TextAlign.center,
                style: TextStyle(
                  color: color,
                  fontWeight: AppTokens.fontWeightBold,
                  fontSize: 14,
                ),
              ),
              if (disabled)
                const SizedBox(height: AppTokens.spacingXS),
              if (disabled)
                Text(
                  '(تم التسجيل)',
                  style: TextStyle(
                    color: AppTokens.neutralMedium,
                    fontSize: 10,
                  ),
                ),
            ],
          ),
        ),
      ),
    );
  }

  Future<void> _handleCheckIn() async {
    try {
      setState(() {
        _isCheckingIn = true;
      });

      // Ensure token is set
      final authState = ref.read(authStateProvider);
      if (authState.token != null) {
        ApiService.setToken(authState.token!);
      }

      // Get current date and time
      final now = DateTime.now();
      final date = DateFormat('yyyy-MM-dd').format(now);
      final time = DateFormat('HH:mm:ss').format(now);

      // Call attendance check-in API
      final response = await ApiService.checkIn(date, time);

      if (response['already_checked_in'] == true) {
        setState(() {
          _hasCheckedInToday = true;
        });
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: const Text('تم تسجيل الحضور مسبقاً'),
              backgroundColor: AppTokens.warningOrange,
            ),
          );
        }
        return;
      }

      // Check for warnings
      if (response['is_late'] == true) {
        setState(() {
          _hasCheckedInToday = true;
        });
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text(
                  'تم تسجيل الحضور مع تأخير ${response['delay_minutes']} دقيقة - لديك ${response['warning_count']} تنبيه'),
              backgroundColor: AppTokens.warningOrange,
              duration: const Duration(seconds: 4),
            ),
          );
        }
      } else {
        setState(() {
          _hasCheckedInToday = true;
        });
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: const Text(AppStrings.checkInSuccess),
              backgroundColor: AppTokens.successGreen,
            ),
          );
        }
      }

      // Check if account was suspended
      if (response['is_account_suspended'] == true) {
        if (mounted) {
          showDialog(
            context: context,
            builder: (context) => AlertDialog(
              title: const Text('تعطيل الحساب'),
              content: const Text(
                  'تم تعطيل حسابك بسبب تجاوز الحد المسموح للتنبيهات. يرجى التواصل مع المعلمة.'),
              actions: [
                TextButton(
                  onPressed: () => Navigator.pop(context),
                  child: const Text('حسناً'),
                ),
              ],
            ),
          );
        }
      }
    } catch (e) {
      if (mounted) {
        String errorMessage = 'خطأ في تسجيل الحضور';
        
        // Extract error message from DioException response
        try {
          if (e is DioException) {
            // Try to get error message from response data
            if (e.response?.data != null) {
              final responseData = e.response!.data;
              if (responseData is Map<String, dynamic>) {
                errorMessage = responseData['message'] ?? 
                              responseData['error'] ?? 
                              'خطأ في تسجيل الحضور';
              } else if (responseData is String) {
                errorMessage = responseData;
              }
            }
          }
          
          // Fallback: check if error string contains Arabic messages
          if (errorMessage == 'خطأ في تسجيل الحضور' && e.toString().contains('لا يوجد جدول')) {
            errorMessage = 'لا يوجد جدول للفصل في هذا اليوم. لا يمكن تسجيل الحضور.';
          }
        } catch (_) {
          errorMessage = 'خطأ في تسجيل الحضور. يرجى التحقق من الجدول الزمني للفصل.';
        }
        
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(errorMessage),
            backgroundColor: AppTokens.errorRed,
            duration: const Duration(seconds: 5),
          ),
        );
      }
    } finally {
      if (mounted) {
        setState(() {
          _isCheckingIn = false;
        });
      }
    }
  }

  void _viewAttendanceRecord() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: Row(
          children: [
            const Icon(Icons.calendar_month, color: AppTokens.infoBlue),
            const SizedBox(width: AppTokens.spacingSM),
            const Text('سجل الحضور الشهري'),
          ],
        ),
        content: SizedBox(
          width: double.maxFinite,
          child: FutureBuilder(
            future: _loadAttendanceRecord(),
            builder: (context, snapshot) {
              if (snapshot.connectionState == ConnectionState.waiting) {
                return const Center(child: CircularProgressIndicator());
              }
              if (snapshot.hasError) {
                return Text('خطأ: ${snapshot.error}');
              }
              final data = snapshot.data ?? {};
              final summary = data['summary'] ?? {};
              
              return SingleChildScrollView(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    _buildStatRow('إجمالي الأيام', summary['total_days'] ?? 0),
                    _buildStatRow('أيام الحضور', summary['attended'] ?? 0, color: AppTokens.successGreen),
                    _buildStatRow('أيام الغياب', summary['absent'] ?? 0, color: AppTokens.errorRed),
                    _buildStatRow('أيام التأخير', summary['late'] ?? 0, color: AppTokens.warningOrange),
                    const SizedBox(height: AppTokens.spacingMD),
                    _buildStatRow('معدل الحضور', '${summary['attendance_rate'] ?? 0}%', color: AppTokens.infoBlue),
                    _buildStatRow('الدرجة الشهرية', '${summary['current_month_score'] ?? 0}/20', color: AppTokens.primaryBrown),
                    _buildStatRow('عدد التنبيهات', '${summary['warnings_count'] ?? 0}',
                        color: summary['warnings_count'] != null && summary['warnings_count'] > 0 ? AppTokens.warningOrange : AppTokens.neutralMedium),
                  ],
                ),
              );
            },
          ),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('إغلاق'),
          ),
        ],
      ),
    );
  }

  Future<Map<String, dynamic>> _loadAttendanceRecord() async {
    final authState = ref.read(authStateProvider);
    if (authState.token != null) {
      ApiService.setToken(authState.token!);
    }

    final today = DateTime.now();
    final startDate = DateFormat('yyyy-MM-dd').format(today.subtract(const Duration(days: 30)));
    final endDate = DateFormat('yyyy-MM-dd').format(today);

    return await ApiService.getAttendance(startDate, endDate);
  }

  Widget _buildStatRow(String label, dynamic value, {Color? color}) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: AppTokens.spacingXS),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(
            label,
            style: const TextStyle(fontWeight: FontWeight.bold),
          ),
          Text(
            value.toString(),
            style: TextStyle(
              color: color ?? AppTokens.neutralDark,
              fontWeight: FontWeight.bold,
            ),
          ),
        ],
      ),
    );
  }

  // Note: Formatting logic moved to ScheduleItem model for better organization
  // Keeping these methods for backward compatibility if needed elsewhere
  @Deprecated('Use ScheduleItem.formattedTimeRange instead')
  String _formatTime(String? time) {
    if (time == null || time.isEmpty) return '';
    // Logic moved to ScheduleItem model
    return '';
  }

  @Deprecated('Use ScheduleItem.formattedDate instead')
  String _formatDate(String? date) {
    if (date == null || date.isEmpty) return '';
    // Logic moved to ScheduleItem model
    return '';
  }
}
