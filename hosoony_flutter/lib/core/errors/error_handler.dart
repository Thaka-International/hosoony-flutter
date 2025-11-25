import 'package:dio/dio.dart';

class AppError {
  final String message;
  final String code;
  final int? statusCode;

  const AppError({
    required this.message,
    required this.code,
    this.statusCode,
  });
}

class ErrorHandler {
  static AppError handleError(dynamic error) {
    if (error is AppError) {
      return error;
    }

    if (error is String) {
      return AppError(
        message: error,
        code: 'UNKNOWN_ERROR',
      );
    }

    // Handle DioException (API errors)
    if (error is DioException) {
      return _handleDioException(error);
    }

    return AppError(
      message: 'حدث خطأ غير متوقع',
      code: 'UNKNOWN_ERROR',
    );
  }

  /// Handle DioException and extract error message from API response
  static AppError _handleDioException(DioException error) {
    final responseData = error.response?.data;
    
    // Try to extract error message from response data
    if (responseData is Map<String, dynamic>) {
      String? message;
      
      // First priority: 'message' field (our backend always provides this)
      if (responseData.containsKey('message')) {
        message = responseData['message'].toString();
      }
      // Second priority: 'errors' field (Laravel validation errors)
      else if (responseData.containsKey('errors')) {
        final errors = responseData['errors'];
        if (errors is Map) {
          // Get first error message from errors map
          final firstErrorField = errors.values.first;
          if (firstErrorField is List && firstErrorField.isNotEmpty) {
            message = firstErrorField.first.toString();
          } else if (firstErrorField is String) {
            message = firstErrorField;
          }
        }
      }
      
      // If we found a message, return it
      if (message != null && message.isNotEmpty) {
        final errorCode = responseData['error_code']?.toString();
        
        // Check for ACCOUNT_INACTIVE error code
        if (errorCode == 'ACCOUNT_INACTIVE') {
          return AppError(
            message: message,
            code: 'ACCOUNT_INACTIVE',
            statusCode: error.response?.statusCode,
          );
        }
        
        return AppError(
          message: message,
          code: errorCode ?? 
                (error.response?.statusCode == 422 ? 'VALIDATION_ERROR' : 
                 error.response?.statusCode == 401 || error.response?.statusCode == 403 ? 'AUTH_ERROR' : 
                 'API_ERROR'),
          statusCode: error.response?.statusCode,
        );
      }
    }
    
    // Handle network errors
    if (error.type == DioExceptionType.connectionTimeout ||
        error.type == DioExceptionType.receiveTimeout ||
        error.type == DioExceptionType.sendTimeout ||
        error.type == DioExceptionType.connectionError) {
      return networkError();
    }
    
    // Handle authentication errors
    if (error.response?.statusCode == 401 || error.response?.statusCode == 403) {
      return authenticationError();
    }
    
    // Handle server errors
    if (error.response?.statusCode != null && error.response!.statusCode! >= 500) {
      return serverError(error.response?.statusCode);
    }
    
    // Default error message with status code
    return AppError(
      message: error.message ?? 'حدث خطأ في الاتصال بالخادم',
      code: 'API_ERROR',
      statusCode: error.response?.statusCode,
    );
  }

  static AppError networkError() {
    return const AppError(
      message: 'خطأ في الاتصال بالشبكة',
      code: 'NETWORK_ERROR',
    );
  }

  static AppError serverError(int? statusCode) {
    return AppError(
      message: 'خطأ في الخادم',
      code: 'SERVER_ERROR',
      statusCode: statusCode,
    );
  }

  static AppError authenticationError() {
    return const AppError(
      message: 'خطأ في المصادقة',
      code: 'AUTH_ERROR',
    );
  }

  static AppError validationError(String message) {
    return AppError(
      message: message,
      code: 'VALIDATION_ERROR',
    );
  }
}