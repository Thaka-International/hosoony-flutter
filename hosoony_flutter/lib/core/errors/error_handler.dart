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

    return AppError(
      message: 'حدث خطأ غير متوقع',
      code: 'UNKNOWN_ERROR',
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