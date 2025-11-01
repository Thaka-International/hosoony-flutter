import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../../core/theme/tokens.dart';
import '../../../services/api_service.dart';
import '../../../services/auth_service.dart';
import '../../../shared_ui/widgets/copyright_widget.dart';

class StudentNotificationsPage extends ConsumerStatefulWidget {
  const StudentNotificationsPage({super.key});

  @override
  ConsumerState<StudentNotificationsPage> createState() => _StudentNotificationsPageState();
}

class _StudentNotificationsPageState extends ConsumerState<StudentNotificationsPage> {
  List<Map<String, dynamic>> _notifications = [];
  bool _isLoading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _loadNotifications();
  }

  Future<void> _loadNotifications() async {
    try {
      // Ensure token is set before making API calls
      final authState = ref.read(authStateProvider);
      if (authState.token != null) {
        ApiService.setToken(authState.token!);
      }
      
      final notifications = await ApiService.getNotifications();
      setState(() {
        _notifications = notifications;
        _isLoading = false;
      });
    } catch (e) {
      setState(() {
        _error = e.toString();
        _isLoading = false;
      });
    }
  }

  Future<void> _markAsRead(String notificationId) async {
    try {
      await ApiService.markNotificationAsRead(notificationId);
      // تحديث حالة الإشعار محلياً
      setState(() {
        for (var notification in _notifications) {
          if (notification['id'].toString() == notificationId) {
            notification['is_read'] = true;
            notification['read_at'] = DateTime.now().toIso8601String();
          }
        }
      });
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('خطأ في تحديث الإشعار: ${e.toString()}'),
          backgroundColor: Colors.red,
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppTokens.neutralLight,
      appBar: AppBar(
        backgroundColor: AppTokens.primaryBrown,
        foregroundColor: AppTokens.neutralWhite,
        title: const Text('الإشعارات'),
        leading: IconButton(
          icon: const Icon(Icons.arrow_back),
          onPressed: () => context.go('/student/home'),
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _loadNotifications,
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
            Text('جاري تحميل الإشعارات...'),
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
              'خطأ في تحميل الإشعارات',
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
              onPressed: () {
                setState(() {
                  _isLoading = true;
                  _error = null;
                });
                _loadNotifications();
              },
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

    if (_notifications.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              Icons.notifications_none,
              size: 64,
              color: Colors.grey[400],
            ),
            const SizedBox(height: 16),
            Text(
              'لا توجد إشعارات',
              style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                color: Colors.grey[600],
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 8),
            Text(
              'ستظهر الإشعارات الجديدة هنا',
              style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                color: Colors.grey[500],
              ),
            ),
          ],
        ),
      );
    }

    return Column(
      children: [
        // عداد الإشعارات غير المقروءة
        Container(
          width: double.infinity,
          padding: const EdgeInsets.all(16),
          margin: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            color: AppTokens.neutralWhite,
            borderRadius: BorderRadius.circular(12),
            boxShadow: AppTokens.shadowMD,
          ),
          child: Row(
            children: [
              Icon(
                Icons.notifications,
                color: AppTokens.primaryGold,
                size: 24,
              ),
              const SizedBox(width: 12),
              Text(
                'إجمالي الإشعارات: ${_notifications.length}',
                style: Theme.of(context).textTheme.titleMedium?.copyWith(
                  fontWeight: FontWeight.bold,
                ),
              ),
              const Spacer(),
              Text(
                'غير مقروءة: ${_notifications.where((n) => n['is_read'] == false).length}',
                style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                  color: AppTokens.primaryGreen,
                  fontWeight: FontWeight.bold,
                ),
              ),
            ],
          ),
        ),
        
        // قائمة الإشعارات
        Expanded(
          child: ListView.builder(
            padding: const EdgeInsets.symmetric(horizontal: 16),
            itemCount: _notifications.length,
            itemBuilder: (context, index) {
              final notification = _notifications[index];
              final isRead = notification['is_read'] == true;
              
              return Card(
                margin: const EdgeInsets.only(bottom: 12),
                elevation: isRead ? 2 : 4,
                child: ListTile(
                  leading: CircleAvatar(
                    backgroundColor: isRead ? Colors.grey[300] : AppTokens.primaryGold,
                    child: Icon(
                      _getNotificationIcon(notification['type']),
                      color: isRead ? Colors.grey[600] : AppTokens.neutralWhite,
                    ),
                  ),
                  title: Text(
                    notification['title'] ?? 'بدون عنوان',
                    style: TextStyle(
                      fontWeight: isRead ? FontWeight.normal : FontWeight.bold,
                      color: isRead ? Colors.grey[600] : AppTokens.neutralDark,
                    ),
                  ),
                  subtitle: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const SizedBox(height: 4),
                      Text(
                        notification['message'] ?? 'بدون رسالة',
                        style: TextStyle(
                          color: isRead ? Colors.grey[500] : Colors.grey[700],
                        ),
                      ),
                      const SizedBox(height: 8),
                      Row(
                        children: [
                          Icon(
                            Icons.access_time,
                            size: 14,
                            color: Colors.grey[500],
                          ),
                          const SizedBox(width: 4),
                          Text(
                            _formatDate(notification['created_at']),
                            style: TextStyle(
                              fontSize: 12,
                              color: Colors.grey[500],
                            ),
                          ),
                          const Spacer(),
                          if (!isRead)
                            TextButton(
                              onPressed: () => _markAsRead(notification['id'].toString()),
                              child: const Text('اقرأ'),
                            ),
                        ],
                      ),
                    ],
                  ),
                  onTap: () {
                    if (!isRead) {
                      _markAsRead(notification['id'].toString());
                    }
                  },
                ),
              );
            },
          ),
        ),
        
        // حقوق الملكية
        const Padding(
          padding: EdgeInsets.all(16),
          child: CopyrightWidget(),
        ),
      ],
    );
  }

  IconData _getNotificationIcon(String type) {
    switch (type) {
      case 'info':
        return Icons.info;
      case 'warning':
        return Icons.warning;
      case 'success':
        return Icons.check_circle;
      case 'error':
        return Icons.error;
      default:
        return Icons.notifications;
    }
  }

  String _formatDate(String? dateString) {
    if (dateString == null) return 'تاريخ غير محدد';
    
    try {
      final date = DateTime.parse(dateString);
      final now = DateTime.now();
      final difference = now.difference(date);
      
      if (difference.inDays > 0) {
        return 'منذ ${difference.inDays} يوم';
      } else if (difference.inHours > 0) {
        return 'منذ ${difference.inHours} ساعة';
      } else if (difference.inMinutes > 0) {
        return 'منذ ${difference.inMinutes} دقيقة';
      } else {
        return 'الآن';
      }
    } catch (e) {
      return 'تاريخ غير صحيح';
    }
  }
}


