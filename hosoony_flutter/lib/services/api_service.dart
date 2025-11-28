import 'dart:convert';
import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import '../core/config/env.dart';
import '../core/debug/debug_service.dart';

class ApiService {
  static const String baseUrl = Env.baseUrl;
  static String? _token;
  static late Dio _dio;
  static VoidCallback? _onAccountInactive;

  static void initialize() {
    _dio = Dio(BaseOptions(
      baseUrl: baseUrl,
      connectTimeout: const Duration(seconds: 60),
      receiveTimeout: const Duration(seconds: 60),
      sendTimeout: const Duration(seconds: 60),
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
    ));

    // Retry interceptor for network errors (DNS, connection, timeout)
    _dio.interceptors.add(InterceptorsWrapper(
      onError: (error, handler) async {
        // Retry logic for network errors
        // Note: error is always DioException in Dio interceptors
        if (_shouldRetry(error)) {
          final retryCount = error.requestOptions.extra['retryCount'] ?? 0;
          const maxRetries = 3;
          const retryDelay = Duration(seconds: 2);

          if (retryCount < maxRetries) {
            error.requestOptions.extra['retryCount'] = retryCount + 1;
            
            DebugService.info(
              '[API] Retrying request (${retryCount + 1}/$maxRetries) after ${retryDelay.inSeconds}s delay',
            );
            
            await Future.delayed(retryDelay);
            
            try {
              final response = await _dio.fetch(error.requestOptions);
              handler.resolve(response);
              return;
            } catch (e) {
              // If retry also fails, continue with error
              if (e is DioException) {
                // Update error with new exception if it's also a DioException
                handler.next(e);
                return;
              }
            }
          }
        }
        
        handler.next(error);
      },
    ));

    _dio.interceptors.add(InterceptorsWrapper(
      onRequest: (options, handler) {
        if (_token != null) {
          options.headers['Authorization'] = 'Bearer $_token';
        }
        
        // Normalize exam answers to ensure answer_text is always string
        // This interceptor runs BEFORE JSON serialization, so we can force string type
        if (options.data is Map<String, dynamic>) {
          final data = options.data as Map<String, dynamic>;
          if (data.containsKey('answers') && data['answers'] is List) {
            final answers = data['answers'] as List;
            final normalizedAnswers = answers.map((answer) {
              if (answer is Map<String, dynamic>) {
                final normalized = <String, dynamic>{};
                normalized['question_id'] = answer['question_id'];
                
                // Force answer_text to be string - use JSON encoding trick to preserve string type
                if (answer.containsKey('answer_text') && answer['answer_text'] != null) {
                  final textValue = answer['answer_text'];
                  // Always convert to string, even if it's 0
                  if (textValue is String) {
                    normalized['answer_text'] = textValue;
                  } else {
                    // Convert to string explicitly
                    final strValue = textValue.toString();
                    // Use JSON encoding/decoding to force string type preservation
                    // This prevents Dio from converting "0" back to 0
                    final jsonEncoded = jsonEncode(strValue);
                    normalized['answer_text'] = jsonDecode(jsonEncoded) as String;
                  }
                } else {
                  normalized['answer_text'] = null;
                }
                
                // Handle answer_boolean
                if (answer.containsKey('answer_boolean')) {
                  normalized['answer_boolean'] = answer['answer_boolean'];
                }
                
                return normalized;
              }
              return answer;
            }).toList();
            data['answers'] = normalizedAnswers;
            // Don't re-encode here, let Dio handle it but with normalized string values
            options.data = data;
          }
        }
        
        DebugService.apiRequest(
          options.method,
          '${options.baseUrl}${options.path}',
          options.data,
        );
        handler.next(options);
      },
      onResponse: (response, handler) {
        DebugService.apiResponse(
          response.requestOptions.method,
          '${response.requestOptions.baseUrl}${response.requestOptions.path}',
          response.statusCode ?? 0,
          response.data,
        );
        handler.next(response);
      },
      onError: (error, handler) {
        DebugService.apiError(
          error.requestOptions.method,
          '${error.requestOptions.baseUrl}${error.requestOptions.path}',
          error,
        );
        
        // Handle ACCOUNT_INACTIVE error - logout user automatically
        if (error.response?.statusCode == 403) {
          final responseData = error.response?.data;
          if (responseData is Map<String, dynamic>) {
            final errorCode = responseData['error_code']?.toString();
            if (errorCode == 'ACCOUNT_INACTIVE') {
              // Logout user automatically
              _handleAccountInactive();
            }
          }
        }
        
        handler.next(error);
      },
    ));
  }

  /// Check if error should be retried
  static bool _shouldRetry(DioException error) {
    // Retry on connection errors (DNS, network, timeout)
    if (error.type == DioExceptionType.connectionError ||
        error.type == DioExceptionType.connectionTimeout ||
        error.type == DioExceptionType.sendTimeout ||
        error.type == DioExceptionType.receiveTimeout) {
      return true;
    }
    
    // Retry on specific error messages
    final errorMessage = error.message?.toLowerCase() ?? '';
    if (errorMessage.contains('failed host lookup') ||
        errorMessage.contains('socketexception') ||
        errorMessage.contains('network is unreachable') ||
        errorMessage.contains('no address associated with hostname')) {
      return true;
    }
    
    return false;
  }

  static void setToken(String token) {
    _token = token;
  }

  static String? getToken() {
    return _token;
  }

  static void clearToken() {
    _token = null;
  }

  /// Set callback to handle account inactive errors
  static void setAccountInactiveCallback(VoidCallback? callback) {
    _onAccountInactive = callback;
  }

  /// Handle account inactive error - logout user automatically
  static void _handleAccountInactive() {
    // Clear token immediately
    clearToken();
    
    // Call callback if set (should logout user via AuthNotifier)
    _onAccountInactive?.call();
  }

  static Future<Map<String, dynamic>> login(String email, String password) async {
    final response = await _dio.post('/auth/login', data: {
      'email': email,
      'password': password,
    });
    return response.data;
  }

  static Future<void> logout() async {
    // Ensure token is set before making the request
    if (_token == null) {
      throw Exception('No authentication token available');
    }
    await _dio.post('/auth/logout');
  }

  static Future<Map<String, dynamic>> getMe() async {
    final response = await _dio.get('/me');
    return response.data;
  }

  static Future<Map<String, dynamic>> sendPhoneCode(String phone) async {
    final response = await _dio.post('/phone-auth/send-code', data: {'phone': phone});
    return response.data;
  }

  static Future<Map<String, dynamic>> verifyPhoneCode(String phone, String code) async {
    final response = await _dio.post('/phone-auth/verify-code', data: {'phone': phone, 'code': code});
    return response.data;
  }

  static Future<Map<String, dynamic>> resendPhoneCode(String phone) async {
    final response = await _dio.post('/phone-auth/resend-code', data: {'phone': phone});
    return response.data;
  }

  static Future<Map<String, dynamic>> getDailyTasks(String studentId, {String? date}) async {
    // Ensure token is set before making the request
    if (_token == null) {
      throw Exception('No authentication token available');
    }
    
    // Build query parameters
    final queryParams = <String, dynamic>{};
    if (date != null) {
      queryParams['date'] = date;
    }
    
    final response = await _dio.get('/students/daily-tasks', queryParameters: queryParams);
    
    // DailyTasksService returns: { date, class_id, tasks, existing_log, message }
    // Map to expected Flutter format
    final data = response.data;
    return {
      'tasks': List<Map<String, dynamic>>.from(data['tasks'] ?? []),
      'date': data['date'] ?? data['log_date'],
      'class_id': data['class_id'], // Include class_id in response
      'status': data['existing_log']?['status'] ?? 'pending',
      'daily_log_id': data['existing_log']?['id'],
      'message': data['message'],
    };
  }
  
  static Future<Map<String, dynamic>> submitDailyTasks(Map<String, dynamic> data) async {
    if (_token == null) {
      throw Exception('No authentication token available');
    }
    
    try {
      final response = await _dio.post('/students/daily-tasks/submit', data: data);
      return response.data;
    } catch (e) {
      // Log the error for debugging
      print('Submit endpoint error: $e');
      // Re-throw to handle in the calling function
      rethrow;
    }
  }
  
  static Future<List<Map<String, dynamic>>> getStudentDailyLogs({String? startDate, String? endDate}) async {
    if (_token == null) {
      throw Exception('No authentication token available');
    }
    
    final queryParams = <String, dynamic>{};
    if (startDate != null) queryParams['start_date'] = startDate;
    if (endDate != null) queryParams['end_date'] = endDate;
    
    final response = await _dio.get('/students/daily-logs', queryParameters: queryParams);
    
    // Handle wrapped response
    if (response.data['data'] != null && response.data['data']['logs'] != null) {
      return List<Map<String, dynamic>>.from(response.data['data']['logs'] ?? []);
    }
    
    return List<Map<String, dynamic>>.from(response.data['data'] ?? []);
  }

  static Future<Map<String, dynamic>> getMyCompanions({String? date}) async {
    // Ensure token is set before making the request
    if (_token == null) {
      throw Exception('No authentication token available');
    }
    
    try {
      // Use today's date if not provided
      final queryDate = date ?? DateTime.now().toIso8601String().split('T')[0];
      
      final response = await _dio.get('/me/companions', queryParameters: {
        'date': queryDate,
      });
      final data = response.data;
      
      // Convert room_number and session_id to String if they are int
      String? roomNumber = data['room_number']?.toString();
      String? sessionId = data['session_id']?.toString();
      
      // Return the full response structure
      return {
        'success': data['success'] ?? true,
        'message': data['message'] ?? 'Success',
        'date': data['date'],
        'room_number': roomNumber,
        'zoom_url': data['zoom_url'],
        'zoom_password': data['zoom_password'],
        'session_id': sessionId,
        'companions': List<Map<String, dynamic>>.from(data['companions'] ?? []),
      };
    } catch (e) {
      // If the endpoint doesn't exist, return structured error
      if (e is DioException && e.response?.statusCode == 404) {
        final queryDate = date ?? DateTime.now().toIso8601String().split('T')[0];
        return {
          'success': false,
          'message': 'لا توجد رفيقات متاحة حالياً',
          'date': queryDate,
          'room_number': null,
          'zoom_url': null,
          'zoom_password': null,
          'companions': [],
        };
      }
      rethrow;
    }
  }

  static Future<List<Map<String, dynamic>>> getNotifications() async {
    // Ensure token is set before making the request
    if (_token == null) {
      throw Exception('No authentication token available');
    }
    final response = await _dio.get('/notifications');
    return List<Map<String, dynamic>>.from(response.data['data']?['notifications'] ?? []);
  }
  
  static Future<void> markNotificationAsRead(String notificationId) async {
    await _dio.patch('/notifications/$notificationId/read');
  }

  static Future<void> registerDevice(String fcmToken, String platform) async {
    // Ensure token is set before making the request
    if (_token == null) {
      throw Exception('No authentication token available');
    }
    await _dio.post('/devices/register', data: {
      'fcm_token': fcmToken,
      'platform': platform,
    });
  }

  static Future<void> unregisterDevice(String platform) async {
    // Ensure token is set before making the request
    if (_token == null) {
      throw Exception('No authentication token available');
    }
    await _dio.post('/devices/unregister', data: {
      'platform': platform,
    });
  }

  static Future<List<Map<String, dynamic>>> getStudentPerformance(String studentId) async {
    final response = await _dio.get('/students/$studentId/performance');
    return List<Map<String, dynamic>>.from(response.data['data'] ?? []);
  }

  static Future<Map<String, dynamic>> getSchedulerLastRun() async {
    final response = await _dio.get('/ops/scheduler/last-run');
    return response.data;
  }
  
  static Future<Map<String, dynamic>> getDailyReport(String classId) async {
    final response = await _dio.get('/reports/daily/$classId');
    return response.data;
  }

  // DISABLED: App is free, no payments
  // static Future<List<Map<String, dynamic>>> getStudentPayments(String studentId) async {
  //   final response = await _dio.get('/students/$studentId/payments');
  //   return List<Map<String, dynamic>>.from(response.data['data'] ?? []);
  // }

  // Attendance APIs
  static Future<Map<String, dynamic>> checkIn(String date, String time) async {
    if (_token == null) {
      throw Exception('No authentication token available');
    }

    final response = await _dio.post('/me/attendance/check-in', data: {
      'date': date,
      'time': time,
    });

    return response.data;
  }

  static Future<Map<String, dynamic>> getAttendance(String startDate, String endDate) async {
    if (_token == null) {
      throw Exception('No authentication token available');
    }

    final response = await _dio.get('/me/attendance', queryParameters: {
      'start_date': startDate,
      'end_date': endDate,
    });

    return response.data;
  }

  static Future<Map<String, dynamic>> getMySchedule() async {
    if (_token == null) {
      throw Exception('No authentication token available');
    }

    final response = await _dio.get('/me/schedule');

    return response.data;
  }

  // Memorization API - صفحة الحفظ
  static Future<Map<String, dynamic>> getClassMemorization({int? classId}) async {
    if (_token == null) {
      throw Exception('No authentication token available');
    }
    
    final path = classId != null ? '/classes/$classId/memorization' : '/me/memorization';
    final response = await _dio.get(path);
    return response.data;
  }

  // Companion Evaluation APIs
  static Future<Map<String, dynamic>> submitCompanionEvaluation({
    required String sessionId,
    required String companionId,
    required int memorizationQuality,
    required int focusLevel,
    String? notes,
  }) async {
    if (_token == null) {
      throw Exception('No authentication token available');
    }

    final response = await _dio.post('/companions/evaluate', data: {
      'session_id': sessionId,
      'companion_id': companionId,
      'memorization_quality': memorizationQuality,
      'focus_level': focusLevel,
      'notes': notes,
    });

    return response.data;
  }

  static Future<Map<String, dynamic>> canEvaluateCompanion(String sessionId) async {
    if (_token == null) {
      throw Exception('No authentication token available');
    }

    final response = await _dio.get('/companions/can-evaluate', queryParameters: {
      'session_id': sessionId,
    });

    return response.data;
  }

  static Future<Map<String, dynamic>> shouldShowCompanionEvaluation({
    required String date,
  }) async {
    if (_token == null) {
      throw Exception('No authentication token available');
    }

    final response = await _dio.get('/companions/should-show-evaluation', queryParameters: {
      'date': date,
    });

    return response.data;
  }

  // Teacher APIs - APIs للمعلمات
  /// الحصول على طلاب الفصل
  static Future<List<Map<String, dynamic>>> getClassStudents({int? classId}) async {
    if (_token == null) {
      throw Exception('No authentication token available');
    }

    // إذا لم يتم تحديد class_id، احصل عليه من معلومات المستخدم
    int? finalClassId = classId;
    if (finalClassId == null) {
      final meData = await getMe();
      finalClassId = meData['data']?['class_id'] as int?;
    }

    if (finalClassId == null) {
      throw Exception('No class_id available');
    }

    // استخدام endpoint جديد للمعلمات
    try {
      final response = await _dio.get('/teacher/class/students');
      if (response.data['success'] == true) {
        return List<Map<String, dynamic>>.from(response.data['students'] ?? []);
      }
      return [];
    } catch (e) {
      // Fallback: محاولة مع class_id مباشرة
      try {
        final response = await _dio.get('/teacher/class/$finalClassId/students');
        if (response.data['success'] == true) {
          return List<Map<String, dynamic>>.from(response.data['students'] ?? []);
        }
        return [];
      } catch (_) {
        rethrow;
      }
    }
  }

  /// الحصول على جدول الفصل
  static Future<Map<String, dynamic>> getClassSchedule({int? classId}) async {
    if (_token == null) {
      throw Exception('No authentication token available');
    }

    int? finalClassId = classId;
    if (finalClassId == null) {
      final meData = await getMe();
      finalClassId = meData['data']?['class_id'] as int?;
    }

    if (finalClassId == null) {
      throw Exception('No class_id available');
    }

    try {
      // استخدام endpoint جديد للمعلمات
      final response = await _dio.get('/teacher/class/schedule');
      return response.data;
    } catch (e) {
      // Fallback: محاولة مع class_id مباشرة
      try {
        final response = await _dio.get('/teacher/class/$finalClassId/schedule');
        return response.data;
      } catch (_) {
        // Fallback أخير: استخدام /me/schedule
        if (e is DioException && e.response?.statusCode == 404) {
          final response = await _dio.get('/me/schedule');
          return response.data;
        }
        rethrow;
      }
    }
  }

  /// الحصول على معلومات الفصل
  static Future<Map<String, dynamic>> getClassInfo({int? classId}) async {
    if (_token == null) {
      throw Exception('No authentication token available');
    }

    int? finalClassId = classId;
    if (finalClassId == null) {
      final meData = await getMe();
      finalClassId = meData['data']?['class_id'] as int?;
    }

    if (finalClassId == null) {
      throw Exception('No class_id available');
    }

    try {
      final response = await _dio.get('/classes/$finalClassId');
      return response.data;
    } catch (e) {
      // Fallback: استخدام memorization endpoint للحصول على معلومات الفصل
      try {
        final response = await _dio.get('/classes/$finalClassId/memorization');
        return {
          'id': finalClassId,
          'name': response.data['data']?['class_name'],
        };
      } catch (_) {
        rethrow;
      }
    }
  }

  /// الحصول على تقرير اليومي للفصل
  static Future<Map<String, dynamic>> getClassDailyReport({int? classId, String? date}) async {
    if (_token == null) {
      throw Exception('No authentication token available');
    }

    int? finalClassId = classId;
    if (finalClassId == null) {
      final meData = await getMe();
      finalClassId = meData['data']?['class_id'] as int?;
    }

    if (finalClassId == null) {
      throw Exception('No class_id available');
    }

    final queryParams = <String, dynamic>{};
    if (date != null) {
      queryParams['date'] = date;
    }

    final response = await _dio.get('/reports/daily/$finalClassId', queryParameters: queryParams);
    return response.data;
  }

  /// الحصول على فصول المعلمة
  static Future<List<Map<String, dynamic>>> getMyClasses() async {
    if (_token == null) {
      throw Exception('No authentication token available');
    }

    final response = await _dio.get('/teacher/classes');
    if (response.data['success'] == true) {
      return List<Map<String, dynamic>>.from(response.data['classes'] ?? []);
    }
    return [];
  }

  /// الحصول على جداول الفصل (مفصلة)
  static Future<Map<String, dynamic>> getClassSchedules({int? classId}) async {
    if (_token == null) {
      throw Exception('No authentication token available');
    }

    int? finalClassId = classId;
    if (finalClassId == null) {
      final meData = await getMe();
      finalClassId = meData['data']?['class_id'] as int?;
    }

    if (finalClassId == null) {
      throw Exception('No class_id available');
    }

    try {
      final response = await _dio.get('/teacher/class/schedules');
      return response.data;
    } catch (e) {
      if (e is DioException && e.response?.statusCode == 404) {
        final response = await _dio.get('/teacher/class/$finalClassId/schedules');
        return response.data;
      }
      rethrow;
    }
  }

  /// الحصول على المهام الموكلة
  static Future<Map<String, dynamic>> getTaskAssignments({int? classId}) async {
    if (_token == null) {
      throw Exception('No authentication token available');
    }

    int? finalClassId = classId;
    if (finalClassId == null) {
      final meData = await getMe();
      finalClassId = meData['data']?['class_id'] as int?;
    }

    if (finalClassId == null) {
      throw Exception('No class_id available');
    }

    try {
      final response = await _dio.get('/teacher/class/task-assignments');
      return response.data;
    } catch (e) {
      if (e is DioException && e.response?.statusCode == 404) {
        final response = await _dio.get('/teacher/class/$finalClassId/task-assignments');
        return response.data;
      }
      rethrow;
    }
  }

  /// الحصول على الخطة الأسبوعية
  static Future<Map<String, dynamic>> getWeeklyTaskSchedules({int? classId, String? weekStartDate, String? weekEndDate}) async {
    if (_token == null) {
      throw Exception('No authentication token available');
    }

    int? finalClassId = classId;
    if (finalClassId == null) {
      final meData = await getMe();
      finalClassId = meData['data']?['class_id'] as int?;
    }

    if (finalClassId == null) {
      throw Exception('No class_id available');
    }

    final queryParams = <String, dynamic>{};
    if (weekStartDate != null) queryParams['week_start_date'] = weekStartDate;
    if (weekEndDate != null) queryParams['week_end_date'] = weekEndDate;

    try {
      final response = await _dio.get('/teacher/class/weekly-schedules', queryParameters: queryParams);
      return response.data;
    } catch (e) {
      if (e is DioException && e.response?.statusCode == 404) {
        final response = await _dio.get('/teacher/class/$finalClassId/weekly-schedules', queryParameters: queryParams);
        return response.data;
      }
      rethrow;
    }
  }

  /// إنشاء جدول أسبوعي جديد
  static Future<Map<String, dynamic>> createWeeklyTaskSchedule({
    required String weekStartDate,
    required String weekEndDate,
    required String dayOfWeek,
    required String taskDate,
    required int classTaskAssignmentId,
    String? taskDetails,
    int? classId,
  }) async {
    if (_token == null) {
      throw Exception('No authentication token available');
    }

    int? finalClassId = classId;
    if (finalClassId == null) {
      final meData = await getMe();
      finalClassId = meData['data']?['class_id'] as int?;
    }

    if (finalClassId == null) {
      throw Exception('No class_id available');
    }

    final data = <String, dynamic>{
      'week_start_date': weekStartDate,
      'week_end_date': weekEndDate,
      'day_of_week': dayOfWeek,
      'task_date': taskDate,
      'class_task_assignment_id': classTaskAssignmentId,
      if (taskDetails != null && taskDetails.isNotEmpty) 'task_details': taskDetails,
    };

    try {
      final response = await _dio.post('/teacher/class/weekly-schedules', data: data);
      return response.data;
    } catch (e) {
      if (e is DioException && e.response?.statusCode == 404) {
        final response = await _dio.post('/teacher/class/$finalClassId/weekly-schedules', data: data);
        return response.data;
      }
      rethrow;
    }
  }

  /// تحديث جدول أسبوعي
  static Future<Map<String, dynamic>> updateWeeklyTaskSchedule({
    required int scheduleId,
    String? weekStartDate,
    String? weekEndDate,
    String? dayOfWeek,
    String? taskDate,
    int? classTaskAssignmentId,
    String? taskDetails,
  }) async {
    if (_token == null) {
      throw Exception('No authentication token available');
    }

    final data = <String, dynamic>{};
    if (weekStartDate != null) data['week_start_date'] = weekStartDate;
    if (weekEndDate != null) data['week_end_date'] = weekEndDate;
    if (dayOfWeek != null) data['day_of_week'] = dayOfWeek;
    if (taskDate != null) data['task_date'] = taskDate;
    if (classTaskAssignmentId != null) data['class_task_assignment_id'] = classTaskAssignmentId;
    if (taskDetails != null) data['task_details'] = taskDetails;

    final response = await _dio.put('/teacher/class/weekly-schedules/$scheduleId', data: data);
    return response.data;
  }

  /// إنشاء خطة الأسبوع القادم تلقائياً
  static Future<Map<String, dynamic>> createNextWeekPlan({int? classId}) async {
    if (_token == null) {
      throw Exception('No authentication token available');
    }

    int? finalClassId = classId;
    if (finalClassId == null) {
      final meData = await getMe();
      finalClassId = meData['data']?['class_id'] as int?;
    }

    if (finalClassId == null) {
      throw Exception('No class_id available');
    }

    try {
      final response = await _dio.post('/teacher/class/weekly-schedules/create-next-week');
      return response.data;
    } catch (e) {
      if (e is DioException && e.response?.statusCode == 404) {
        final response = await _dio.post('/teacher/class/$finalClassId/weekly-schedules/create-next-week');
        return response.data;
      }
      rethrow;
    }
  }

  /// نسخ الأسبوع الحالي للأسبوع القادم
  static Future<Map<String, dynamic>> copyToNextWeek({int? classId}) async {
    if (_token == null) {
      throw Exception('No authentication token available');
    }

    int? finalClassId = classId;
    if (finalClassId == null) {
      final meData = await getMe();
      finalClassId = meData['data']?['class_id'] as int?;
    }

    if (finalClassId == null) {
      throw Exception('No class_id available');
    }

    try {
      final response = await _dio.post('/teacher/class/weekly-schedules/copy-to-next-week');
      return response.data;
    } catch (e) {
      if (e is DioException && e.response?.statusCode == 404) {
        final response = await _dio.post('/teacher/class/$finalClassId/weekly-schedules/copy-to-next-week');
        return response.data;
      }
      rethrow;
    }
  }

  /// الحصول على نشرة الرفيقات
  static Future<Map<String, dynamic>> getCompanionsPublications({int? classId}) async {
    if (_token == null) {
      throw Exception('No authentication token available');
    }

    int? finalClassId = classId;
    if (finalClassId == null) {
      final meData = await getMe();
      finalClassId = meData['data']?['class_id'] as int?;
    }

    if (finalClassId == null) {
      throw Exception('No class_id available');
    }

    try {
      final response = await _dio.get('/teacher/class/companions-publications');
      return response.data;
    } catch (e) {
      if (e is DioException && e.response?.statusCode == 404) {
        final response = await _dio.get('/teacher/class/$finalClassId/companions-publications');
        return response.data;
      }
      rethrow;
    }
  }

  // ========== Class Schedules CRUD ==========
  
  /// إنشاء جدول جديد للفصل
  static Future<Map<String, dynamic>> createClassSchedule({
    required String dayOfWeek,
    required String startTime,
    required String endTime,
    String? zoomLink,
    String? zoomMeetingId,
    String? zoomPassword,
    String? notes,
    bool? isActive,
    int? classId,
  }) async {
    if (_token == null) {
      throw Exception('No authentication token available');
    }

    int? finalClassId = classId;
    if (finalClassId == null) {
      final meData = await getMe();
      finalClassId = meData['data']?['class_id'] as int?;
    }

    if (finalClassId == null) {
      throw Exception('No class_id available');
    }

    final data = {
      'day_of_week': dayOfWeek,
      'start_time': startTime,
      'end_time': endTime,
      if (zoomLink != null) 'zoom_link': zoomLink,
      if (zoomMeetingId != null) 'zoom_meeting_id': zoomMeetingId,
      if (zoomPassword != null) 'zoom_password': zoomPassword,
      if (notes != null) 'notes': notes,
      if (isActive != null) 'is_active': isActive,
    };

    try {
      final response = await _dio.post('/teacher/class/schedules', data: data);
      return response.data;
    } catch (e) {
      if (e is DioException && e.response?.statusCode == 404) {
        final response = await _dio.post('/teacher/class/$finalClassId/schedules', data: data);
        return response.data;
      }
      rethrow;
    }
  }

  /// تحديث جدول الفصل
  static Future<Map<String, dynamic>> updateClassSchedule({
    required int scheduleId,
    String? dayOfWeek,
    String? startTime,
    String? endTime,
    String? zoomLink,
    String? zoomMeetingId,
    String? zoomPassword,
    String? notes,
    bool? isActive,
  }) async {
    if (_token == null) {
      throw Exception('No authentication token available');
    }

    final data = <String, dynamic>{};
    if (dayOfWeek != null) data['day_of_week'] = dayOfWeek;
    if (startTime != null) data['start_time'] = startTime;
    if (endTime != null) data['end_time'] = endTime;
    if (zoomLink != null) data['zoom_link'] = zoomLink;
    if (zoomMeetingId != null) data['zoom_meeting_id'] = zoomMeetingId;
    if (zoomPassword != null) data['zoom_password'] = zoomPassword;
    if (notes != null) data['notes'] = notes;
    if (isActive != null) data['is_active'] = isActive;

    final response = await _dio.put('/teacher/class/schedules/$scheduleId', data: data);
    return response.data;
  }

  /// حذف جدول الفصل
  static Future<Map<String, dynamic>> deleteClassSchedule(int scheduleId) async {
    if (_token == null) {
      throw Exception('No authentication token available');
    }

    final response = await _dio.delete('/teacher/class/schedules/$scheduleId');
    return response.data;
  }

  /// تفعيل/إلغاء تفعيل جدول الفصل
  static Future<Map<String, dynamic>> toggleClassSchedule(int scheduleId) async {
    if (_token == null) {
      throw Exception('No authentication token available');
    }

    final response = await _dio.patch('/teacher/class/schedules/$scheduleId/toggle');
    return response.data;
  }

  // ========== Task Assignments CRUD ==========

  /// إنشاء مهمة موكلة جديدة
  static Future<Map<String, dynamic>> createTaskAssignment({
    required int dailyTaskDefinitionId,
    required int order,
    bool? isActive,
    int? classId,
  }) async {
    if (_token == null) {
      throw Exception('No authentication token available');
    }

    int? finalClassId = classId;
    if (finalClassId == null) {
      final meData = await getMe();
      finalClassId = meData['data']?['class_id'] as int?;
    }

    if (finalClassId == null) {
      throw Exception('No class_id available');
    }

    final data = {
      'daily_task_definition_id': dailyTaskDefinitionId,
      'order': order,
      if (isActive != null) 'is_active': isActive,
    };

    try {
      final response = await _dio.post('/teacher/class/task-assignments', data: data);
      return response.data;
    } catch (e) {
      if (e is DioException && e.response?.statusCode == 404) {
        final response = await _dio.post('/teacher/class/$finalClassId/task-assignments', data: data);
        return response.data;
      }
      rethrow;
    }
  }

  /// تحديث مهمة موكلة
  static Future<Map<String, dynamic>> updateTaskAssignment({
    required int assignmentId,
    int? dailyTaskDefinitionId,
    int? order,
    bool? isActive,
  }) async {
    if (_token == null) {
      throw Exception('No authentication token available');
    }

    final data = <String, dynamic>{};
    if (dailyTaskDefinitionId != null) data['daily_task_definition_id'] = dailyTaskDefinitionId;
    if (order != null) data['order'] = order;
    if (isActive != null) data['is_active'] = isActive;

    final response = await _dio.put('/teacher/class/task-assignments/$assignmentId', data: data);
    return response.data;
  }

  /// حذف مهمة موكلة
  static Future<Map<String, dynamic>> deleteTaskAssignment(int assignmentId) async {
    if (_token == null) {
      throw Exception('No authentication token available');
    }

    final response = await _dio.delete('/teacher/class/task-assignments/$assignmentId');
    return response.data;
  }

  /// إنشاء مهمة يومية جديدة وربطها بالفصل
  static Future<Map<String, dynamic>> createTaskDefinitionAndAssign({
    required String name,
    String? description,
    required String type,
    String? taskLocation,
    int? pointsWeight,
    int? durationMinutes,
    bool? isActive,
    int? order,
    int? classId,
  }) async {
    if (_token == null) {
      throw Exception('No authentication token available');
    }

    int? finalClassId = classId;
    if (finalClassId == null) {
      final meData = await getMe();
      finalClassId = meData['data']?['class_id'] as int?;
    }

    if (finalClassId == null) {
      throw Exception('No class_id available');
    }

    final data = <String, dynamic>{
      'name': name,
      'type': type,
      if (description != null && description.isNotEmpty) 'description': description,
      if (taskLocation != null) 'task_location': taskLocation,
      if (pointsWeight != null) 'points_weight': pointsWeight,
      if (durationMinutes != null) 'duration_minutes': durationMinutes,
      if (isActive != null) 'is_active': isActive,
      if (order != null) 'order': order,
    };

    try {
      final response = await _dio.post('/teacher/class/create-task-and-assign', data: data);
      return response.data;
    } catch (e) {
      if (e is DioException && e.response?.statusCode == 404) {
        final response = await _dio.post('/teacher/class/$finalClassId/create-task-and-assign', data: data);
        return response.data;
      }
      rethrow;
    }
  }

  /// الحصول على المهام المتاحة (التي لم يتم تعيينها للفصل)
  static Future<List<Map<String, dynamic>>> getAvailableTaskDefinitions({int? classId}) async {
    if (_token == null) {
      throw Exception('No authentication token available');
    }

    int? finalClassId = classId;
    if (finalClassId == null) {
      final meData = await getMe();
      finalClassId = meData['data']?['class_id'] as int?;
    }

    if (finalClassId == null) {
      throw Exception('No class_id available');
    }

    try {
      final response = await _dio.get('/teacher/class/available-task-definitions');
      if (response.data['success'] == true) {
        return List<Map<String, dynamic>>.from(response.data['tasks'] ?? []);
      }
      return [];
    } catch (e) {
      if (e is DioException && e.response?.statusCode == 404) {
        final response = await _dio.get('/teacher/class/$finalClassId/available-task-definitions');
        if (response.data['success'] == true) {
          return List<Map<String, dynamic>>.from(response.data['tasks'] ?? []);
        }
      }
      return [];
    }
  }

  // ========== Weekly Task Schedules CRUD ==========

  /// تحديث تفاصيل الخطة الأسبوعية
  static Future<Map<String, dynamic>> updateWeeklyTaskDetails({
    required int scheduleId,
    required String taskDetails,
  }) async {
    if (_token == null) {
      throw Exception('No authentication token available');
    }

    final response = await _dio.put(
      '/teacher/class/weekly-schedules/$scheduleId/details',
      data: {'task_details': taskDetails},
    );
    return response.data;
  }

  /// حذف جدول أسبوعي
  static Future<Map<String, dynamic>> deleteWeeklyTaskSchedule(int scheduleId) async {
    if (_token == null) {
      throw Exception('No authentication token available');
    }

    final response = await _dio.delete('/teacher/class/weekly-schedules/$scheduleId');
    return response.data;
  }

  // ========== Companions Publications Management ==========

  /// توليد رفيقات للفصل
  static Future<Map<String, dynamic>> generateCompanions({
    required String targetDate,
    required String grouping,
    required String algorithm,
    required String attendanceSource,
    List<List<int>>? lockedPairs,
    int? classId,
  }) async {
    if (_token == null) {
      throw Exception('No authentication token available');
    }

    int? finalClassId = classId;
    if (finalClassId == null) {
      final meData = await getMe();
      finalClassId = meData['data']?['class_id'] as int?;
    }

    if (finalClassId == null) {
      throw Exception('No class_id available');
    }

    final data = {
      'target_date': targetDate,
      'grouping': grouping,
      'algorithm': algorithm,
      'attendance_source': attendanceSource,
      if (lockedPairs != null) 'locked_pairs': lockedPairs,
    };

    try {
      final response = await _dio.post('/teacher/class/companions-publications/generate', data: data);
      return response.data;
    } catch (e) {
      if (e is DioException && e.response?.statusCode == 404) {
        final response = await _dio.post('/teacher/class/$finalClassId/companions-publications/generate', data: data);
        return response.data;
      }
      rethrow;
    }
  }

  /// قفل نشرة الرفيقات
  static Future<Map<String, dynamic>> lockCompanionsPublication({
    required int publicationId,
    List<List<int>>? lockedPairs,
  }) async {
    if (_token == null) {
      throw Exception('No authentication token available');
    }

    final data = <String, dynamic>{};
    if (lockedPairs != null) data['locked_pairs'] = lockedPairs;

    final response = await _dio.patch('/teacher/class/companions-publications/$publicationId/lock', data: data);
    return response.data;
  }

  /// فتح نشرة الرفيقات
  static Future<Map<String, dynamic>> unlockCompanionsPublication(int publicationId) async {
    if (_token == null) {
      throw Exception('No authentication token available');
    }

    final response = await _dio.patch('/teacher/class/companions-publications/$publicationId/unlock');
    return response.data;
  }

  /// نشر نشرة الرفيقات
  static Future<Map<String, dynamic>> publishCompanionsPublication(int publicationId) async {
    if (_token == null) {
      throw Exception('No authentication token available');
    }

    final response = await _dio.post('/teacher/class/companions-publications/$publicationId/publish');
    return response.data;
  }

  /// حذف نشرة الرفيقات
  static Future<Map<String, dynamic>> deleteCompanionsPublication(int publicationId) async {
    if (_token == null) {
      throw Exception('No authentication token available');
    }

    final response = await _dio.delete('/teacher/class/companions-publications/$publicationId');
    return response.data;
  }

  /// جلب تقييمات الرفيقات لفصل معين (للمعلمات)
  static Future<Map<String, dynamic>> getClassCompanionEvaluations({
    required int classId,
  }) async {
    if (_token == null) {
      throw Exception('No authentication token available');
    }

    final response = await _dio.get('/teacher/companions/class/$classId/evaluations');
    return response.data;
  }

  /// جلب تقييمات الرفيقات لجلسة معينة (للمعلمات)
  static Future<Map<String, dynamic>> getSessionCompanionEvaluations({
    required int sessionId,
  }) async {
    if (_token == null) {
      throw Exception('No authentication token available');
    }

    final response = await _dio.get('/teacher/companions/session/$sessionId/evaluations');
    return response.data;
  }

  /// جلب تقييمات الرفيقات لطالبة معينة (للمعلمات)
  static Future<Map<String, dynamic>> getStudentCompanionEvaluations({
    required int studentId,
  }) async {
    if (_token == null) {
      throw Exception('No authentication token available');
    }

    final response = await _dio.get('/teacher/companions/student/$studentId/evaluations');
    return response.data;
  }

  /// جلب الطالبات الحاضرات لتقييم التلاوة
  static Future<Map<String, dynamic>> getPresentStudentsForRecitation({int? classId}) async {
    if (_token == null) {
      throw Exception('No authentication token available');
    }

    int? finalClassId = classId;
    if (finalClassId == null) {
      final meData = await getMe();
      finalClassId = meData['data']?['class_id'] as int?;
    }

    if (finalClassId == null) {
      throw Exception('No class_id available');
    }

    try {
      final response = await _dio.get('/teacher/class/present-students');
      return response.data;
    } catch (e) {
      if (e is DioException && e.response?.statusCode == 404) {
        final response = await _dio.get('/teacher/class/$finalClassId/present-students');
        return response.data;
      }
      rethrow;
    }
  }

  /// إنشاء تقييم تلاوة
  static Future<Map<String, dynamic>> createRecitationEvaluation({
    required int studentId,
    required int recitationScore,
    String? evaluationDate,
    String? notes,
    int? sessionId,
    int? classId,
  }) async {
    if (_token == null) {
      throw Exception('No authentication token available');
    }

    int? finalClassId = classId;
    if (finalClassId == null) {
      final meData = await getMe();
      finalClassId = meData['data']?['class_id'] as int?;
    }

    if (finalClassId == null) {
      throw Exception('No class_id available');
    }

    final data = {
      'student_id': studentId,
      'recitation_score': recitationScore,
      if (evaluationDate != null) 'evaluation_date': evaluationDate,
      if (notes != null && notes.isNotEmpty) 'notes': notes,
      if (sessionId != null) 'session_id': sessionId,
    };

    try {
      final response = await _dio.post('/teacher/class/recitation-evaluations', data: data);
      return response.data;
    } catch (e) {
      if (e is DioException && e.response?.statusCode == 404) {
        final response = await _dio.post('/teacher/class/$finalClassId/recitation-evaluations', data: data);
        return response.data;
      }
      rethrow;
    }
  }

  /// جلب تقييمات التلاوة
  static Future<Map<String, dynamic>> getRecitationEvaluations({
    String? startDate,
    String? endDate,
    int? classId,
  }) async {
    if (_token == null) {
      throw Exception('No authentication token available');
    }

    int? finalClassId = classId;
    if (finalClassId == null) {
      final meData = await getMe();
      finalClassId = meData['data']?['class_id'] as int?;
    }

    final queryParams = <String, dynamic>{};
    if (startDate != null) queryParams['start_date'] = startDate;
    if (endDate != null) queryParams['end_date'] = endDate;

    try {
      final response = await _dio.get('/teacher/class/recitation-evaluations', queryParameters: queryParams);
      return response.data;
    } catch (e) {
      if (e is DioException && e.response?.statusCode == 404) {
        final response = await _dio.get('/teacher/class/$finalClassId/recitation-evaluations', queryParameters: queryParams);
        return response.data;
      }
      rethrow;
    }
  }

  /// تحديث تقييم تلاوة
  static Future<Map<String, dynamic>> updateRecitationEvaluation({
    required int evaluationId,
    int? recitationScore,
    String? notes,
  }) async {
    if (_token == null) {
      throw Exception('No authentication token available');
    }

    final data = <String, dynamic>{};
    if (recitationScore != null) data['recitation_score'] = recitationScore;
    if (notes != null) data['notes'] = notes;

    final response = await _dio.put('/teacher/class/recitation-evaluations/$evaluationId', data: data);
    return response.data;
  }

  /// حذف تقييم تلاوة
  static Future<Map<String, dynamic>> deleteRecitationEvaluation(int evaluationId) async {
    if (_token == null) {
      throw Exception('No authentication token available');
    }

    final response = await _dio.delete('/teacher/class/recitation-evaluations/$evaluationId');
    return response.data;
  }

  /// حساب التقييمات الشهرية
  static Future<Map<String, dynamic>> calculateMonthlyEvaluations({
    required String monthReference,
    int? classId,
  }) async {
    if (_token == null) {
      throw Exception('No authentication token available');
    }

    int? finalClassId = classId;
    if (finalClassId == null) {
      final meData = await getMe();
      finalClassId = meData['data']?['class_id'] as int?;
    }

    final data = {
      'month_reference': monthReference,
    };

    try {
      final response = await _dio.post('/teacher/class/monthly-evaluations/calculate', data: data);
      return response.data;
    } catch (e) {
      if (e is DioException && e.response?.statusCode == 404) {
        final response = await _dio.post('/teacher/class/$finalClassId/monthly-evaluations/calculate', data: data);
        return response.data;
      }
      rethrow;
    }
  }

  /// جلب التقييمات الشهرية
  static Future<Map<String, dynamic>> getMonthlyEvaluations({
    String? monthReference,
    int? classId,
  }) async {
    if (_token == null) {
      throw Exception('No authentication token available');
    }

    int? finalClassId = classId;
    if (finalClassId == null) {
      final meData = await getMe();
      finalClassId = meData['data']?['class_id'] as int?;
    }

    final queryParams = <String, dynamic>{};
    if (monthReference != null) queryParams['month_reference'] = monthReference;

    try {
      final response = await _dio.get('/teacher/class/monthly-evaluations', queryParameters: queryParams);
      return response.data;
    } catch (e) {
      if (e is DioException && e.response?.statusCode == 404) {
        final response = await _dio.get('/teacher/class/$finalClassId/monthly-evaluations', queryParameters: queryParams);
        return response.data;
      }
      rethrow;
    }
  }

  /// تحديث التقييم الشهري
  static Future<Map<String, dynamic>> updateMonthlyEvaluation({
    required int evaluationId,
    double? attendanceScore,
    double? recitationScore,
    double? writtenExamScore,
    double? oralExamScore,
    String? notes,
  }) async {
    if (_token == null) {
      throw Exception('No authentication token available');
    }

    final data = <String, dynamic>{};
    if (attendanceScore != null) data['attendance_score'] = attendanceScore;
    if (recitationScore != null) data['recitation_score'] = recitationScore;
    if (writtenExamScore != null) data['written_exam_score'] = writtenExamScore;
    if (oralExamScore != null) data['oral_exam_score'] = oralExamScore;
    if (notes != null) data['notes'] = notes;

    final response = await _dio.put('/teacher/class/monthly-evaluations/$evaluationId', data: data);
    return response.data;
  }

  /// إصدار النتائج الشهرية
  static Future<Map<String, dynamic>> publishMonthlyEvaluations({
    required String monthReference,
    int? classId,
  }) async {
    if (_token == null) {
      throw Exception('No authentication token available');
    }

    int? finalClassId = classId;
    if (finalClassId == null) {
      final meData = await getMe();
      finalClassId = meData['data']?['class_id'] as int?;
    }

    final data = {
      'month_reference': monthReference,
    };

    try {
      final response = await _dio.post('/teacher/class/monthly-evaluations/publish', data: data);
      return response.data;
    } catch (e) {
      if (e is DioException && e.response?.statusCode == 404) {
        final response = await _dio.post('/teacher/class/$finalClassId/monthly-evaluations/publish', data: data);
        return response.data;
      }
      rethrow;
    }
  }

  // ==================== Exam Methods ====================

  /// Get available exams for the authenticated student
  static Future<List<Map<String, dynamic>>> getAvailableExams() async {
    if (_token == null) {
      throw Exception('No authentication token available');
    }

    final response = await _dio.get('/students/exams');
    final data = response.data;
    
    if (data['success'] == true) {
      return List<Map<String, dynamic>>.from(data['exams'] ?? []);
    }
    
    throw Exception(data['message'] ?? 'Failed to fetch exams');
  }

  /// Start an exam attempt
  static Future<Map<String, dynamic>> startExam(int examId) async {
    if (_token == null) {
      throw Exception('No authentication token available');
    }

    final response = await _dio.post('/students/exams/$examId/start');
    final data = response.data;
    
    if (data['success'] == true) {
      return data;
    }
    
    throw Exception(data['message'] ?? 'Failed to start exam');
  }

  /// Save exam answers (auto-save)
  static Future<void> saveExamAnswers(int attemptId, List<Map<String, dynamic>> answers) async {
    if (_token == null) {
      throw Exception('No authentication token available');
    }

    // Ensure answer_text values are strings (not numbers) for JSON serialization
    final normalizedAnswers = answers.map((answer) {
      final normalized = Map<String, dynamic>.from(answer);
      if (normalized.containsKey('answer_text') && normalized['answer_text'] != null) {
        // Force conversion to string to prevent JSON from converting "0" to 0
        normalized['answer_text'] = normalized['answer_text'].toString();
      }
      return normalized;
    }).toList();

    await _dio.post('/students/exams/attempts/$attemptId/save', data: {
      'answers': normalizedAnswers,
    });
  }

  /// Submit exam attempt
  static Future<Map<String, dynamic>> submitExam(int attemptId, {List<Map<String, dynamic>>? answers}) async {
    if (_token == null) {
      throw Exception('No authentication token available');
    }

    final data = <String, dynamic>{};
    if (answers != null) {
      // Ensure answer_text values are strings (not numbers) for JSON serialization
      // Use jsonEncode/jsonDecode to force string type preservation
      final normalizedAnswers = answers.map((answer) {
        final normalized = <String, dynamic>{};
        normalized['question_id'] = answer['question_id'];
        
        // Force answer_text to be string, even if it's "0"
        if (answer.containsKey('answer_text') && answer['answer_text'] != null) {
          final textValue = answer['answer_text'];
          // Convert to string explicitly, handling all cases
          if (textValue is String) {
            normalized['answer_text'] = textValue;
          } else {
            normalized['answer_text'] = textValue.toString();
          }
        } else {
          normalized['answer_text'] = null;
        }
        
        // Handle answer_boolean
        if (answer.containsKey('answer_boolean')) {
          normalized['answer_boolean'] = answer['answer_boolean'];
        }
        
        return normalized;
      }).toList();
      data['answers'] = normalizedAnswers;
    }

    final response = await _dio.post('/students/exams/attempts/$attemptId/submit', data: data);
    final responseData = response.data;
    
    if (responseData['success'] == true) {
      return responseData;
    }
    
    throw Exception(responseData['message'] ?? 'Failed to submit exam');
  }

  /// Get exam result
  static Future<Map<String, dynamic>> getExamResult(int examId) async {
    if (_token == null) {
      throw Exception('No authentication token available');
    }

    final response = await _dio.get('/students/exams/$examId/result');
    final data = response.data;
    
    if (data['success'] == true) {
      return data;
    }
    
    throw Exception(data['message'] ?? 'Failed to fetch exam result');
  }
}
