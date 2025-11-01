import 'dart:async';
import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';
import '../../../core/theme/tokens.dart';
import '../../../services/auth_service.dart';
import '../../../services/api_service.dart';

class StudentDailyTasksPageNew extends ConsumerStatefulWidget {
  const StudentDailyTasksPageNew({super.key});

  @override
  ConsumerState<StudentDailyTasksPageNew> createState() => _StudentDailyTasksPageNewState();
}

class _StudentDailyTasksPageNewState extends ConsumerState<StudentDailyTasksPageNew>
    with TickerProviderStateMixin {
  late AnimationController _animationController;
  late AnimationController _timerController;
  late AnimationController _achievementController;
  
  List<Map<String, dynamic>> _dailyTasks = [];
  Map<int, bool> _expandedTasks = {}; // Track which tasks are expanded
  Map<int, bool> _activeTasks = {}; // Track which tasks are currently active
  Map<int, Timer?> _taskTimers = {}; // Track timers for each task
  Map<int, int> _taskElapsedSeconds = {}; // Track elapsed seconds for each task
  Map<int, DateTime?> _taskStartTime = {}; // Track when task started
  Map<int, String?> _taskStatus = {}; // Track task status
  
  bool _isLoading = true;
  String? _error;
  int _currentExpandedIndex = 0; // First task is expanded by default
  String? _logDate; // Store the log date
  int? _classId; // Store the class ID
  bool _hasCheckedAttendance = false; // Track if attendance was checked for first task
  Map<String, dynamic>? _companionData; // Store companion data for evaluation

  @override
  void initState() {
    super.initState();
    
    _animationController = AnimationController(
      duration: const Duration(milliseconds: 800),
      vsync: this,
    );

    _timerController = AnimationController(
      duration: const Duration(seconds: 1),
      vsync: this,
    );

    _achievementController = AnimationController(
      duration: const Duration(milliseconds: 1500),
      vsync: this,
    );

    _timerController.repeat();

    _loadDailyTasks();
    _checkAttendanceOnLoad(); // Pre-check attendance when page loads
  }

  Future<void> _checkAttendanceOnLoad() async {
    // Pre-check attendance when page loads to avoid blocking user later
    if (_hasCheckedAttendance) return;
    
    try {
      final authState = ref.read(authStateProvider);
      if (authState.token != null) {
        ApiService.setToken(authState.token!);
      }

      final today = DateTime.now().toIso8601String().split('T')[0];
      final attendanceData = await ApiService.getAttendance(today, today);
      
      // API returns { summary: {...}, logs: [...] }
      // Check if today's date exists in logs
      final logs = attendanceData['logs'] ?? [];
      final hasAttendanceToday = logs.any((log) {
        // Handle different date field names
        String? logDateStr;
        if (log['date'] != null) {
          logDateStr = log['date'].toString().split('T')[0];
        } else if (log['attendance_date'] != null) {
          logDateStr = log['attendance_date'].toString().split('T')[0];
        }
        
        return logDateStr == today;
      });

      if (hasAttendanceToday) {
        setState(() {
          _hasCheckedAttendance = true;
        });
      }
    } catch (e) {
      print('Error pre-checking attendance: $e');
      // Don't block user if check fails - allow them to proceed
    }
  }

  @override
  void dispose() {
    for (var timer in _taskTimers.values) {
      timer?.cancel();
    }
    _animationController.dispose();
    _timerController.dispose();
    _achievementController.dispose();
    super.dispose();
  }

  Future<void> _loadDailyTasks() async {
    try {
      setState(() {
        _isLoading = true;
        _error = null;
      });

      final authState = ref.read(authStateProvider);
      if (authState.user?.id != null) {
        if (authState.token != null) {
          ApiService.setToken(authState.token!);
        }
        
        final response = await ApiService.getDailyTasks(authState.user!.id.toString());
        final tasks = response['tasks'] ?? [];
        
        // Store date and class_id from response
        final logDate = response['date'];
        final classId = response['class_id'];
        
        // Initialize task states
        final states = <int, bool>{};
        for (int i = 0; i < tasks.length; i++) {
          states[i] = i == 0; // First task expanded
        }
        
        setState(() {
          _dailyTasks = List<Map<String, dynamic>>.from(tasks);
          _expandedTasks = states;
          _logDate = logDate;
          _classId = classId;
          _isLoading = false;
        });
        _animationController.forward();
      } else {
        setState(() {
          _error = 'لم يتم العثور على بيانات المستخدم';
          _isLoading = false;
        });
      }
    } catch (e) {
      setState(() {
        if (e.toString().contains('422')) {
          _error = 'لا توجد مهام متاحة لهذا الطالب';
        } else if (e.toString().contains('401')) {
          _error = 'انتهت صلاحية الجلسة، يرجى تسجيل الدخول مرة أخرى';
        } else {
          _error = 'خطأ في تحميل المهام: ${e.toString()}';
        }
        _isLoading = false;
      });
    }
  }

  void _toggleTaskExpansion(int index) {
    setState(() {
      if (_expandedTasks[index] == true) {
        // Collapse
        _expandedTasks[index] = false;
      } else {
        // Expand - collapse all others first
        for (var key in _expandedTasks.keys) {
          _expandedTasks[key] = false;
        }
        _expandedTasks[index] = true;
        _currentExpandedIndex = index;
      }
    });
  }

  Future<bool> _checkAttendanceBeforeFirstTask() async {
    if (_hasCheckedAttendance) return true;
    
    try {
      final authState = ref.read(authStateProvider);
      if (authState.token != null) {
        ApiService.setToken(authState.token!);
      }

      // Check if attendance was checked in today
      final today = DateTime.now().toIso8601String().split('T')[0];
      final attendanceData = await ApiService.getAttendance(today, today);
      
      // API returns { summary: {...}, logs: [...] }
      // Check if today's date exists in logs
      final logs = attendanceData['logs'] ?? [];
      
      // Also check summary for attended count as fallback
      final summary = attendanceData['summary'] ?? {};
      final attendedToday = summary['attended'] ?? 0;
      
      bool hasAttendanceToday = false;
      
      // First try: Check logs array
      if (logs.isNotEmpty) {
        hasAttendanceToday = logs.any((log) {
          // Handle different date field names
          String? logDateStr;
          if (log['date'] != null) {
            logDateStr = log['date'].toString().split('T')[0];
          } else if (log['attendance_date'] != null) {
            logDateStr = log['attendance_date'].toString().split('T')[0];
          }
          
          return logDateStr == today && 
                 (log['status'] == 'present' || log['status'] == 'late');
        });
      }
      
      // Fallback: If logs check fails but summary shows attendance today
      if (!hasAttendanceToday && attendedToday > 0 && logs.isNotEmpty) {
        hasAttendanceToday = true;
      }

      if (!hasAttendanceToday) {
        // Show dialog to check in
        final shouldCheckIn = await showDialog<bool>(
          context: context,
          barrierDismissible: false,
          builder: (context) => AlertDialog(
            title: const Row(
              children: [
                Icon(Icons.access_time, color: AppTokens.warningOrange),
                SizedBox(width: 8),
                Text('تسجيل الحضور مطلوب'),
              ],
            ),
            content: const Text(
              'يجب تسجيل الحضور قبل بدء المهمة الأولى. هل تريد الانتقال إلى صفحة تسجيل الحضور؟',
            ),
            actions: [
              TextButton(
                onPressed: () => Navigator.pop(context, false),
                child: const Text('إلغاء'),
              ),
              ElevatedButton(
                onPressed: () => Navigator.pop(context, true),
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppTokens.primaryGreen,
                  foregroundColor: Colors.white,
                ),
                child: const Text('تسجيل الحضور'),
              ),
            ],
          ),
        );

        if (shouldCheckIn == true) {
          if (mounted) {
            context.go('/student/home/schedule');
          }
          return false;
        }
        return false;
      }

      _hasCheckedAttendance = true;
      return true;
    } catch (e) {
      // If check fails, allow task to start but show warning
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('تحذير: لم يتم التحقق من الحضور: $e'),
            backgroundColor: Colors.orange,
            duration: const Duration(seconds: 3),
          ),
        );
      }
      _hasCheckedAttendance = true; // Don't block again
      return true;
    }
  }

  void _startTask(int index) async {
    if (_activeTasks[index] == true) return; // Already active
    
    // Check attendance before first task
    if (index == 0 && !_hasCheckedAttendance) {
      final canProceed = await _checkAttendanceBeforeFirstTask();
      if (!canProceed) {
        return; // User needs to check in first
      }
    }
    
    final task = _dailyTasks[index];
    final duration = task['duration_minutes'] ?? 20;
    
    setState(() {
      _activeTasks[index] = true;
      _taskStartTime[index] = DateTime.now();
      _taskElapsedSeconds[index] = 0;
      _taskStatus[index] = 'in_progress';
    });

    // Start timer
    _taskTimers[index] = Timer.periodic(const Duration(seconds: 1), (timer) {
      if (!mounted) {
        timer.cancel();
        return;
      }
      setState(() {
        _taskElapsedSeconds[index] = _taskElapsedSeconds[index]! + 1;
      });
    });
  }

  void _showCompleteDialog(int index) {
    final task = _dailyTasks[index];
    final elapsedSeconds = _taskElapsedSeconds[index] ?? 0;
    final minutesSpent = elapsedSeconds ~/ 60;
    
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Row(
          children: [
            Icon(Icons.check_circle, color: AppTokens.primaryGreen),
            SizedBox(width: 8),
            Text('إتمام المهمة'),
          ],
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Text(
              'هل أكملت هذه المهمة؟',
              style: Theme.of(context).textTheme.bodyLarge,
            ),
            const SizedBox(height: 16),
            Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: AppTokens.primaryBrown.withOpacity(0.1),
                borderRadius: BorderRadius.circular(12),
              ),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  const Icon(Icons.timer, color: AppTokens.primaryBrown),
                  const SizedBox(width: 8),
                  Text(
                    'الوقت المستغرق: ${_formatTimer(elapsedSeconds)}',
                    style: const TextStyle(
                      fontSize: 18,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('إلغاء'),
          ),
          ElevatedButton(
            onPressed: () {
              Navigator.pop(context);
              _completeTask(index);
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: AppTokens.primaryGreen,
              foregroundColor: Colors.white,
            ),
            child: const Text('تأكيد الإتمام'),
          ),
        ],
      ),
    );
  }

  Future<void> _completeTask(int index) async {
    final task = _dailyTasks[index];
    final taskKey = task['task_key'];
    final startTime = _taskStartTime[index];
    final elapsedSeconds = _taskElapsedSeconds[index] ?? 0;
    final duration = elapsedSeconds ~/ 60;
    
    if (_taskTimers[index] != null) {
      _taskTimers[index]!.cancel();
      _taskTimers[index] = null;
    }

    try {
      // Update task in local state first
      setState(() {
        _activeTasks[index] = false;
        _taskStatus[index] = 'completed';
        _taskElapsedSeconds[index] = 0;
        _taskStartTime[index] = null;
        task['completed'] = true;
        task['status'] = 'completed';
      });
      
      // Submit to API
      try {
        await _submitTaskToAPI(task, duration);
      } catch (apiError) {
        print('API submission failed: $apiError');
        // Show error but don't prevent local completion
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text('تم الإكمال محلياً. يرجى المحاولة لاحقاً: $apiError'),
              backgroundColor: Colors.orange,
              duration: const Duration(seconds: 3),
            ),
          );
        }
      }

      // Check if task was completed on time
      final expectedDuration = task['duration_minutes'] ?? 20;
      final wasOnTime = elapsedSeconds ~/ 60 <= expectedDuration;
      
      // Show achievement animation
      if (mounted) {
        _showAchievementEffect(wasOnTime, expectedDuration);
        
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Row(
              children: [
                Icon(
                  wasOnTime ? Icons.emoji_events : Icons.check_circle,
                  color: Colors.white,
                ),
                const SizedBox(width: 8),
                Text(wasOnTime 
                  ? 'مبروك! أكملت المهمة في الوقت المحدد!' 
                  : 'تم إكمال المهمة بنجاح'),
              ],
            ),
            backgroundColor: wasOnTime ? AppTokens.successGreen : AppTokens.primaryGreen,
            duration: const Duration(seconds: 3),
          ),
        );

        // Check if all tasks are completed
        final allTasksCompleted = _dailyTasks.every((task) => task['completed'] == true);
        
        if (allTasksCompleted) {
          // Wait a bit before navigating to evaluation
          await Future.delayed(const Duration(seconds: 2));
          
          // Load companion data for evaluation
          try {
            final companionsResponse = await ApiService.getMyCompanions();
            print('Companions response: $companionsResponse');
            
            if (companionsResponse['success'] == true && 
                (companionsResponse['companions'] as List).isNotEmpty) {
              final companions = companionsResponse['companions'] as List;
              final sessionId = companionsResponse['session_id'];
              
              print('Found ${companions.length} companion(s), session_id: $sessionId');
              
              // Use first companion for evaluation
              final companion = companions.first as Map<String, dynamic>;
              print('Companion data: $companion');
              print('Companion ID: ${companion['id']}');
              
              if (companion['id'] != null && sessionId != null) {
                if (mounted) {
                  // Ensure companion has id as int or string
                  final companionData = {
                    'id': companion['id'] is int ? companion['id'] : int.tryParse(companion['id'].toString()) ?? companion['id'],
                    'name': companion['name'] ?? 'الرفيقة',
                  };
                  
                  final companionJson = Uri.encodeComponent(jsonEncode(companionData));
                  final finalSessionId = sessionId.toString();
                  
                  print('Navigating to evaluation with session_id: $finalSessionId, companion: $companionData');
                  
                  context.go('/student/home/companion-evaluation?session_id=$finalSessionId&companion=$companionJson');
                }
              } else {
                print('Invalid companion data - missing id or session_id');
                if (mounted) {
                  ScaffoldMessenger.of(context).showSnackBar(
                    const SnackBar(
                      content: Text('لا يمكن تقييم الرفيقة: بيانات غير مكتملة'),
                      backgroundColor: AppTokens.warningOrange,
                    ),
                  );
                  _loadDailyTasks();
                }
              }
            } else {
              print('No companions available for evaluation');
              if (mounted) {
                ScaffoldMessenger.of(context).showSnackBar(
                  const SnackBar(
                    content: Text('لا توجد رفيقات متاحة للتقييم'),
                    backgroundColor: AppTokens.infoBlue,
                  ),
                );
                _loadDailyTasks();
              }
            }
          } catch (e) {
            print('Error loading companion data: $e');
            if (mounted) {
              ScaffoldMessenger.of(context).showSnackBar(
                SnackBar(
                  content: Text('خطأ في تحميل بيانات الرفيقة: $e'),
                  backgroundColor: AppTokens.errorRed,
                ),
              );
              _loadDailyTasks();
            }
          }
        } else {
          // Not all tasks completed - find next uncompleted task and open it
          final nextTaskIndex = _dailyTasks.indexWhere((task) => task['completed'] != true);
          
          if (nextTaskIndex != -1 && mounted) {
            // Expand and scroll to next task
            setState(() {
              // Collapse all tasks
              for (var key in _expandedTasks.keys) {
                _expandedTasks[key] = false;
              }
              // Expand next task
              _expandedTasks[nextTaskIndex] = true;
              _currentExpandedIndex = nextTaskIndex;
            });
            
            // Wait a moment then scroll to next task
            await Future.delayed(const Duration(milliseconds: 500));
            if (mounted) {
              // The task list will automatically show the expanded task
              ScaffoldMessenger.of(context).showSnackBar(
                SnackBar(
                  content: Text('افتح المهمة التالية'),
                  duration: const Duration(seconds: 2),
                  backgroundColor: AppTokens.infoBlue,
                ),
              );
            }
          }
        }
      }
    } catch (e) {
      // Error handling
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('خطأ في إكمال المهمة: $e'),
            backgroundColor: Colors.red,
            duration: const Duration(seconds: 2),
          ),
        );
      }
      print('Error completing task: $e');
    }
  }

  void _showAchievementEffect(bool onTime, int duration) {
    _achievementController.reset();
    _achievementController.forward();
    
    if (onTime) {
      // Show confetti or celebration effect
      _showCelebrationAnimation();
    }
  }

  void _showCelebrationAnimation() {
    // Celebration effect with a simple overlay
    showDialog(
      context: context,
      barrierDismissible: true,
      barrierColor: Colors.black26,
      builder: (dialogContext) => Dialog(
        backgroundColor: Colors.transparent,
        child: Container(
          padding: const EdgeInsets.all(20),
          decoration: BoxDecoration(
            gradient: AppTokens.primaryGradient,
            borderRadius: BorderRadius.circular(20),
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              const Icon(
                Icons.emoji_events,
                size: 60,
                color: AppTokens.primaryGold,
              ),
              const SizedBox(height: 16),
              const Text(
                'ممتاز!',
                style: TextStyle(
                  fontSize: 24,
                  fontWeight: FontWeight.bold,
                  color: Colors.white,
                ),
              ),
              const SizedBox(height: 8),
              const Text(
                'أكملت المهمة في الوقت المحدد',
                style: TextStyle(
                  fontSize: 16,
                  color: Colors.white70,
                ),
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: 20),
              ElevatedButton(
                onPressed: () => Navigator.of(dialogContext).pop(),
                child: const Text('حسناً'),
              ),
            ],
          ),
        ),
      ),
    );
    
    // Auto-close after 2 seconds
    Future.delayed(const Duration(seconds: 2), () {
      if (mounted) {
        // Check if the dialog is still showing
        if (Navigator.of(context).canPop()) {
          Navigator.of(context).pop();
        }
      }
    });
  }

  Future<void> _submitTaskToAPI(Map<String, dynamic> task, int duration) async {
    if (_logDate == null || _classId == null) {
      throw Exception('Missing log date or class ID');
    }
    
    // Controller validates: tasks.*.id, tasks.*.status, etc.
    // Then it passes tasks as 'items' to submitWebDailyLog
    // Service expects items with 'task_key' for lookup
    final taskData = {
      'id': task['task_id'], // Required by controller validation (definition ID)
      'task_key': task['task_key'], // Required by submitWebDailyLog service (for lookup)
      'class_task_assignment_id': task['class_task_assignment_id'], // ⭐ للربط بـ weekly_task_schedule
      'status': 'completed',
      'completed': true, // Service checks item['completed']
      'notes': null,
      'quantity': 1,
      'duration_minutes': duration,
      'proof_type': 'none',
    };
    
    // Prepare submission data (controller expects this format)
    final submissionData = {
      'log_date': _logDate,
      'class_id': _classId,
      'tasks': [taskData], // Controller passes this as 'items' to submitWebDailyLog
    };
    
    // Submit to API
    final response = await ApiService.submitDailyTasks(submissionData);
    
    if (response['success'] == false) {
      throw Exception(response['message'] ?? 'Task submission failed');
    }
    
    print('Task submitted successfully: ${task['task_key']}');
  }

  String _formatTimer(int seconds) {
    final minutes = seconds ~/ 60;
    final secs = seconds % 60;
    return '${minutes.toString().padLeft(2, '0')}:${secs.toString().padLeft(2, '0')}';
  }

  String _formatTaskDate() {
    if (_logDate == null) return '';
    
    try {
      // Parse the date
      final date = DateTime.parse(_logDate!);
      
      // Format Gregorian date: "Monday, 28 October"
      final gregorianFormat = DateFormat('EEEE, d MMMM', 'ar');
      final gregorianDate = gregorianFormat.format(date);
      
      // Calculate Hijri date manually (approximate conversion)
      final hijriDate = _gregorianToHijri(date);
      final hijriFormatted = '${hijriDate['day']} ${_getHijriMonthName(hijriDate['month'] ?? 1)}';
      
      return '$gregorianDate، $hijriFormatted';
    } catch (e) {
      return _logDate ?? '';
    }
  }

  Map<String, int> _gregorianToHijri(DateTime gregorianDate) {
    // Approximate conversion algorithm for Gregorian to Hijri
    final julianDay = _gregorianToJulian(gregorianDate.year, gregorianDate.month, gregorianDate.day);
    final hijriJulianDay = julianDay - 1948083; // Julian day for 15 October 622 (start of Hijri calendar)
    
    final hijriYear = ((hijriJulianDay / 354.37).floor() + 1).toInt();
    final remainingDays = hijriJulianDay - ((hijriYear - 1) * 354.37).floor();
    
    int hijriMonth = 1;
    int daysInMonth = 30;
    int daysElapsed = remainingDays;
    
    while (daysElapsed > daysInMonth) {
      daysElapsed -= daysInMonth;
      hijriMonth++;
      // Hijri months alternate 30/29 days, with some adjustments
      daysInMonth = (hijriMonth % 2 == 0) ? 29 : 30;
    }
    
    return {
      'day': daysElapsed.toInt(),
      'month': hijriMonth,
      'year': hijriYear,
    };
  }

  int _gregorianToJulian(int year, int month, int day) {
    // Convert Gregorian date to Julian day number
    final a = (14 - month) ~/ 12;
    final y = year + 4800 - a;
    final m = month + 12 * a - 3;
    return day + ((153 * m + 2) ~/ 5) + 365 * y + (y ~/ 4) - (y ~/ 100) + (y ~/ 400) - 32045;
  }

  String _getHijriMonthName(int month) {
    const hijriMonths = [
      'محرم',
      'صفر',
      'ربيع الأول',
      'ربيع الآخر',
      'جمادى الأولى',
      'جمادى الآخرة',
      'رجب',
      'شعبان',
      'رمضان',
      'شوال',
      'ذو القعدة',
      'ذو الحجة',
    ];
    if (month >= 1 && month <= 12) {
      return hijriMonths[month - 1];
    }
    return hijriMonths[0];
  }

  String _formatLocation(String location) {
    switch (location.toLowerCase()) {
      case 'in_class':
        return 'أثناء الحلقة';
      case 'homework':
        return 'واجب منزلي';
      case 'home':
        return 'في المنزل';
      case 'mosque':
        return 'في المسجد';
      default:
        return 'غير محدد';
    }
  }

  IconData _getLocationIcon(String location) {
    switch (location.toLowerCase()) {
      case 'in_class':
        return Icons.school;
      case 'homework':
      case 'home':
        return Icons.home;
      case 'mosque':
        return Icons.mosque;
      default:
        return Icons.location_on;
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppTokens.neutralLight,
      appBar: AppBar(
        backgroundColor: AppTokens.primaryBrown,
        foregroundColor: AppTokens.neutralWhite,
        title: const Text('المهام اليومية'),
        leading: IconButton(
          icon: const Icon(Icons.arrow_back),
          onPressed: () => context.go('/student/home'),
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _loadDailyTasks,
          ),
        ],
      ),
      body: _buildBody(),
    );
  }

  Widget _buildBody() {
    if (_isLoading) {
      return const Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            CircularProgressIndicator(),
            SizedBox(height: 16),
            Text('جاري تحميل المهام...'),
          ],
        ),
      );
    }

    if (_error != null) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const Icon(Icons.error_outline, size: 64, color: Colors.red),
            const SizedBox(height: 16),
            Text(
              'خطأ في تحميل المهام',
              style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                color: Colors.red,
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 8),
            Text(
              _error!,
              style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                color: Colors.grey[600],
              ),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 24),
            ElevatedButton.icon(
              onPressed: _loadDailyTasks,
              icon: const Icon(Icons.refresh),
              label: const Text('إعادة المحاولة'),
              style: ElevatedButton.styleFrom(
                backgroundColor: AppTokens.primaryGreen,
                foregroundColor: AppTokens.neutralWhite,
                padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
              ),
            ),
          ],
        ),
      );
    }

    if (_dailyTasks.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.assignment_outlined, size: 64, color: Colors.grey[400]),
            const SizedBox(height: 16),
            Text(
              'لا توجد مهام اليوم',
              style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                color: Colors.grey[600],
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 8),
            Text(
              'ستظهر المهام الجديدة هنا',
              style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                color: Colors.grey[500],
              ),
            ),
          ],
        ),
      );
    }

    final completedCount = _dailyTasks.where((t) => t['completed'] == true).length;
    final totalCount = _dailyTasks.length;

    return SingleChildScrollView(
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Date header
          Container(
            width: double.infinity,
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              gradient: AppTokens.primaryGradient,
              borderRadius: BorderRadius.circular(16),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Icon(Icons.calendar_today, color: Colors.white, size: 24),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Text(
                        _formatTaskDate(),
                        style: Theme.of(context).textTheme.titleLarge?.copyWith(
                          color: Colors.white,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
          const SizedBox(height: 16),
          // Summary cards
          Row(
            children: [
              Expanded(
                child: _buildSummaryCard(
                  'المكتملة',
                  completedCount.toString(),
                  Icons.check_circle,
                  AppTokens.primaryGreen,
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: _buildSummaryCard(
                  'المتبقية',
                  (totalCount - completedCount).toString(),
                  Icons.pending,
                  AppTokens.warningOrange,
                ),
              ),
            ],
          ),
          
          const SizedBox(height: 24),
          
          // Task title
          Text(
            'قائمة المهام',
            style: Theme.of(context).textTheme.titleLarge?.copyWith(
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(height: 16),
          
          // Tasks list
          ...(_dailyTasks.asMap().entries.map((entry) {
            final index = entry.key;
            final task = entry.value;
            final isExpanded = _expandedTasks[index] ?? false;
            final isActive = _activeTasks[index] ?? false;
            final isCompleted = task['completed'] == true || task['status'] == 'completed';
            final elapsedSeconds = _taskElapsedSeconds[index] ?? 0;
            
            if (isExpanded) {
              return _buildExpandedTaskCard(task, index, isActive, isCompleted, elapsedSeconds);
            } else {
              return _buildCollapsedTaskCard(task, index, isCompleted);
            }
          })),
        ],
      ),
    );
  }

  Widget _buildSummaryCard(String title, String value, IconData icon, Color color) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.05),
            blurRadius: 10,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Row(
        children: [
          Container(
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: color.withOpacity(0.1),
              borderRadius: BorderRadius.circular(8),
            ),
            child: Icon(icon, color: color, size: 24),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  value,
                  style: TextStyle(
                    fontSize: 24,
                    fontWeight: FontWeight.bold,
                    color: color,
                  ),
                ),
                Text(
                  title,
                  style: TextStyle(
                    fontSize: 12,
                    color: Colors.grey[600],
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildExpandedTaskCard(Map<String, dynamic> task, int index, bool isActive, bool isCompleted, int elapsedSeconds) {
    final taskType = task['task_type'] ?? 'general';
    final title = task['task_name'] ?? 'مهمة غير محددة';
    // ⭐ استخدام weekly_task_details إن وُجدت، وإلا task_key
    final weeklyDetails = task['weekly_task_details'];
    final description = weeklyDetails != null && weeklyDetails.toString().isNotEmpty
        ? weeklyDetails.toString()
        : (task['task_key'] ?? 'لا يوجد وصف');
    final points = task['points_weight'] ?? 0;
    final duration = task['duration_minutes'] ?? 20;
    final location = task['task_location'] ?? 'unknown';
    
    return Card(
      elevation: 4,
      margin: const EdgeInsets.only(bottom: 16),
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(16),
        side: BorderSide(
          color: isActive ? AppTokens.primaryGreen : Colors.transparent,
          width: 2,
        ),
      ),
      child: Padding(
        padding: const EdgeInsets.all(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Task header
            Row(
              children: [
                Container(
                  width: 60,
                  height: 60,
                  decoration: BoxDecoration(
                    color: isCompleted 
                        ? AppTokens.primaryGreen 
                        : (isActive 
                            ? AppTokens.primaryBrown 
                            : _getTaskColor(taskType).withOpacity(0.1)),
                    borderRadius: BorderRadius.circular(30),
                  ),
                  child: Icon(
                    isCompleted 
                        ? Icons.check 
                        : (isActive ? Icons.play_circle_filled : _getTaskIcon(taskType)),
                    color: isCompleted || isActive ? Colors.white : _getTaskColor(taskType),
                    size: 30,
                  ),
                ),
                const SizedBox(width: 16),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // Description (larger and darker)
                      Text(
                        description,
                        style: Theme.of(context).textTheme.titleLarge?.copyWith(
                          fontWeight: FontWeight.bold,
                          fontSize: 18,
                          decoration: isCompleted ? TextDecoration.lineThrough : null,
                        ),
                      ),
                      const SizedBox(height: 4),
                      // Task name (smaller and lighter)
                      Text(
                        title,
                        style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                          color: Colors.grey[600],
                          fontSize: 14,
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
            
            const SizedBox(height: 16),
            
            // Task details
            Wrap(
              spacing: 16,
              runSpacing: 8,
              children: [
                _buildDetailChip(Icons.star, '$points نقطة', AppTokens.primaryGold),
                _buildDetailChip(Icons.access_time, '$duration دقيقة', Colors.grey[700]!),
                _buildDetailChip(
                  _getLocationIcon(location),
                  _formatLocation(location),
                  Colors.grey[700]!,
                ),
              ],
            ),
            
            const SizedBox(height: 20),
            
            // Timer section (if active)
            if (isActive) ...[
              Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: AppTokens.primaryBrown.withOpacity(0.1),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Icon(Icons.timer, color: AppTokens.primaryBrown, size: 24),
                    const SizedBox(width: 8),
                    Text(
                      _formatTimer(elapsedSeconds),
                      style: TextStyle(
                        fontSize: 24,
                        fontWeight: FontWeight.bold,
                        color: AppTokens.primaryBrown,
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 16),
            ],
            
            // Action buttons
            Row(
              children: [
                if (!isCompleted && !isActive) ...[
                  Expanded(
                    child: ElevatedButton.icon(
                      onPressed: () => _startTask(index),
                      icon: const Icon(Icons.play_circle_filled),
                      label: const Text('بدء المهمة'),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: AppTokens.primaryGreen,
                        foregroundColor: Colors.white,
                        padding: const EdgeInsets.symmetric(vertical: 14),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(12),
                        ),
                      ),
                    ),
                  ),
                ],
                
                if (isActive) ...[
                  Expanded(
                    child: ElevatedButton.icon(
                      onPressed: () => _showCompleteDialog(index),
                      icon: const Icon(Icons.check_circle),
                      label: const Text('إتمام المهمة'),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: AppTokens.primaryBrown,
                        foregroundColor: Colors.white,
                        padding: const EdgeInsets.symmetric(vertical: 14),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(12),
                        ),
                      ),
                    ),
                  ),
                ],
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildCollapsedTaskCard(Map<String, dynamic> task, int index, bool isCompleted) {
    return GestureDetector(
      onTap: () => _toggleTaskExpansion(index),
      child: Card(
        elevation: 2,
        margin: const EdgeInsets.only(bottom: 8),
        child: ListTile(
          leading: Container(
            width: 40,
            height: 40,
            decoration: BoxDecoration(
              color: isCompleted ? AppTokens.primaryGreen : AppTokens.primaryBrown,
              borderRadius: BorderRadius.circular(20),
            ),
            child: Icon(
              isCompleted ? Icons.check : Icons.add,
              color: Colors.white,
              size: 20,
            ),
          ),
          title: Text(
            'المهمة ${index + 1}',
            style: TextStyle(
              fontWeight: FontWeight.bold,
              decoration: isCompleted ? TextDecoration.lineThrough : null,
            ),
          ),
          trailing: Icon(
            Icons.chevron_left,
            color: isCompleted ? AppTokens.primaryGreen : AppTokens.primaryBrown,
          ),
        ),
      ),
    );
  }

  Widget _buildDetailChip(IconData icon, String label, Color color) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
      decoration: BoxDecoration(
        color: color.withOpacity(0.1),
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: color.withOpacity(0.3)),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 16, color: color),
          const SizedBox(width: 6),
          Text(
            label,
            style: TextStyle(
              color: color,
              fontWeight: FontWeight.w500,
            ),
          ),
        ],
      ),
    );
  }

  Color _getTaskColor(String taskType) {
    switch (taskType.toLowerCase()) {
      case 'hifz':
        return AppTokens.primaryGreen;
      case 'murajaah':
        return AppTokens.primaryGold;
      case 'tajweed':
        return AppTokens.primaryBrown;
      default:
        return AppTokens.neutralMedium;
    }
  }

  IconData _getTaskIcon(String taskType) {
    switch (taskType.toLowerCase()) {
      case 'hifz':
        return Icons.book;
      case 'murajaah':
        return Icons.repeat;
      case 'tajweed':
        return Icons.record_voice_over;
      default:
        return Icons.assignment;
    }
  }
}

