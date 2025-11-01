import 'package:dio/dio.dart';
import 'package:flutter/foundation.dart' show kIsWeb;
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:logger/logger.dart';
import '../../core/config/env.dart';
import '../../core/errors/error_handler.dart';

// Import for web support
import 'package:dio/browser.dart';

class ApiClient {
  static final ApiClient _instance = ApiClient._internal();
  factory ApiClient() => _instance;
  ApiClient._internal();

  late Dio _dio;
  final FlutterSecureStorage _storage = const FlutterSecureStorage();
  final Logger _logger = Logger();

  void initialize() {
    _dio = Dio(BaseOptions(
      baseUrl: Env.baseUrl,
      connectTimeout: const Duration(seconds: 30),
      receiveTimeout: const Duration(seconds: 30),
      sendTimeout: const Duration(seconds: 30),
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
    ));

    // Configure for web support
    if (kIsWeb) {
      _dio.httpClientAdapter = BrowserHttpClientAdapter()..withCredentials = true;
    }

    _setupInterceptors();
  }

  void _setupInterceptors() {
    // Request interceptor
    _dio.interceptors.add(
      InterceptorsWrapper(
        onRequest: (options, handler) async {
          // Add auth token
          final token = await _storage.read(key: Env.tokenKey);
          if (token != null) {
            options.headers['Authorization'] = 'Bearer $token';
          }

          if (Env.isDebugMode) {
            _logger.d('Request: ${options.method} ${options.uri}');
            _logger.d('Headers: ${options.headers}');
            if (options.data != null) {
              _logger.d('Data: ${options.data}');
            }
          }

          handler.next(options);
        },
        onResponse: (response, handler) {
          if (Env.isDebugMode) {
            _logger.d('Response: ${response.statusCode} ${response.requestOptions.uri}');
            _logger.d('Data: ${response.data}');
          }
          handler.next(response);
        },
        onError: (error, handler) {
          if (Env.isDebugMode) {
            _logger.e('Error: ${error.message}');
            _logger.e('Response: ${error.response?.data}');
          }

          // Handle auth errors
          if (error.response?.statusCode == 401) {
            _handleAuthError();
          }

          handler.next(error);
        },
      ),
    );

    // Logging interceptor (only in debug mode)
    if (Env.isDebugMode) {
      _dio.interceptors.add(LogInterceptor(
        requestBody: true,
        responseBody: true,
        logPrint: (object) => _logger.d(object),
      ));
    }
  }

  void _handleAuthError() async {
    // Clear stored token
    await _storage.delete(key: Env.tokenKey);
    await _storage.delete(key: Env.userKey);
    
    // TODO: Navigate to login page
    _logger.w('Auth token expired, user logged out');
  }

  // GET request
  Future<Response<T>> get<T>(
    String path, {
    Map<String, dynamic>? queryParameters,
    Options? options,
  }) async {
    try {
      return await _dio.get<T>(
        path,
        queryParameters: queryParameters,
        options: options,
      );
    } catch (e) {
      throw ErrorHandler.handleError(e);
    }
  }

  // POST request
  Future<Response<T>> post<T>(
    String path, {
    dynamic data,
    Map<String, dynamic>? queryParameters,
    Options? options,
  }) async {
    try {
      return await _dio.post<T>(
        path,
        data: data,
        queryParameters: queryParameters,
        options: options,
      );
    } catch (e) {
      throw ErrorHandler.handleError(e);
    }
  }

  // PUT request
  Future<Response<T>> put<T>(
    String path, {
    dynamic data,
    Map<String, dynamic>? queryParameters,
    Options? options,
  }) async {
    try {
      return await _dio.put<T>(
        path,
        data: data,
        queryParameters: queryParameters,
        options: options,
      );
    } catch (e) {
      throw ErrorHandler.handleError(e);
    }
  }

  // PATCH request
  Future<Response<T>> patch<T>(
    String path, {
    dynamic data,
    Map<String, dynamic>? queryParameters,
    Options? options,
  }) async {
    try {
      return await _dio.patch<T>(
        path,
        data: data,
        queryParameters: queryParameters,
        options: options,
      );
    } catch (e) {
      throw ErrorHandler.handleError(e);
    }
  }

  // DELETE request
  Future<Response<T>> delete<T>(
    String path, {
    dynamic data,
    Map<String, dynamic>? queryParameters,
    Options? options,
  }) async {
    try {
      return await _dio.delete<T>(
        path,
        data: data,
        queryParameters: queryParameters,
        options: options,
      );
    } catch (e) {
      throw ErrorHandler.handleError(e);
    }
  }

  // Upload file
  Future<Response<T>> uploadFile<T>(
    String path,
    String filePath, {
    String fieldName = 'file',
    Map<String, dynamic>? data,
    ProgressCallback? onSendProgress,
  }) async {
    try {
      FormData formData = FormData.fromMap({
        fieldName: await MultipartFile.fromFile(filePath),
        ...?data,
      });

      return await _dio.post<T>(
        path,
        data: formData,
        onSendProgress: onSendProgress,
      );
    } catch (e) {
      throw ErrorHandler.handleError(e);
    }
  }

  // Health check
  Future<bool> healthCheck() async {
    try {
      final response = await Dio().get('${Env.baseUrl}/ops/scheduler/last-run');
      return response.statusCode == 200;
    } catch (e) {
      return false;
    }
  }

  // Clear auth data
  Future<void> clearAuth() async {
    await _storage.delete(key: Env.tokenKey);
    await _storage.delete(key: Env.userKey);
  }

  // Set auth token
  Future<void> setAuthToken(String token) async {
    await _storage.write(key: Env.tokenKey, value: token);
  }

  // Get auth token
  Future<String?> getAuthToken() async {
    return await _storage.read(key: Env.tokenKey);
  }
}







