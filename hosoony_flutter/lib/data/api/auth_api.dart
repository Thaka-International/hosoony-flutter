import '../../services/api_service.dart';

class AuthApi {
  // Login
  Future<Map<String, dynamic>> login(String email, String password) async {
    return await ApiService.login(email, password);
  }

  // Logout
  Future<void> logout() async {
    await ApiService.logout();
  }

  // Get current user
  Future<Map<String, dynamic>> getMe() async {
    return await ApiService.getMe();
  }

  // Health check
  Future<bool> healthCheck() async {
    try {
      await ApiService.getMe();
      return true;
    } catch (e) {
      return false;
    }
  }
}
