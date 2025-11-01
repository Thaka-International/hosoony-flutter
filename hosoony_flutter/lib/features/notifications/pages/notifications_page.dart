import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/router/app_router.dart';
import '../../../core/theme/tokens.dart';
import '../../../services/auth_service.dart';

class NotificationsPage extends ConsumerStatefulWidget {
  const NotificationsPage({super.key});

  @override
  ConsumerState<NotificationsPage> createState() => _NotificationsPageState();
}

class _NotificationsPageState extends ConsumerState<NotificationsPage>
    with TickerProviderStateMixin {
  late AnimationController _animationController;
  late Animation<double> _fadeAnimation;
  late Animation<double> _slideAnimation;
  
  List<Map<String, dynamic>> _notifications = [];
  bool _isLoading = true;
  String? _error;
  String _selectedFilter = 'all';

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

    _slideAnimation = Tween<double>(
      begin: 30.0,
      end: 0.0,
    ).animate(CurvedAnimation(
      parent: _animationController,
      curve: Curves.easeOutCubic,
    ));

    _loadNotifications();
  }

  @override
  void dispose() {
    _animationController.dispose();
    super.dispose();
  }

  Future<void> _loadNotifications() async {
    try {
      setState(() {
        _isLoading = true;
        _error = null;
      });

      // Mock data
      await Future.delayed(const Duration(milliseconds: 1000));
      
      setState(() {
        _notifications = [
          {
            'id': 1,
            'title': 'مهمة جديدة',
            'message': 'تم إضافة مهمة جديدة: حفظ سورة الفاتحة',
            'type': 'task',
            'priority': 'high',
            'is_read': false,
            'created_at': '2024-01-15 10:30',
            'sender': 'أ. فاطمة',
            'action_url': '/student/home/daily-tasks',
          },
          {
            'id': 2,
            'title': 'تقييم جديد',
            'message': 'تم تقييم أدائك في درس القرآن الكريم',
            'type': 'evaluation',
            'priority': 'medium',
            'is_read': false,
            'created_at': '2024-01-15 09:15',
            'sender': 'أ. فاطمة',
            'action_url': '/student/home/achievements',
          },
          {
            'id': 3,
            'title': 'تذكير بالجلسة',
            'message': 'تذكير: جلسة القرآن الكريم في الساعة 10:00 صباحاً',
            'type': 'reminder',
            'priority': 'high',
            'is_read': true,
            'created_at': '2024-01-15 08:00',
            'sender': 'النظام',
            'action_url': '/student/home/schedule',
          },
          {
            'id': 4,
            'title': 'رسالة من المعلمة',
            'message': 'أتمنى لك التوفيق في حفظ السورة الجديدة',
            'type': 'message',
            'priority': 'low',
            'is_read': true,
            'created_at': '2024-01-14 16:45',
            'sender': 'أ. فاطمة',
            'action_url': null,
          },
          {
            'id': 5,
            'title': 'إنجاز جديد',
            'message': 'تهانينا! لقد أكملت حفظ 5 سور',
            'type': 'achievement',
            'priority': 'high',
            'is_read': false,
            'created_at': '2024-01-14 14:20',
            'sender': 'النظام',
            'action_url': '/student/home/achievements',
          },
          {
            'id': 6,
            'title': 'تحديث النظام',
            'message': 'تم تحديث التطبيق إلى الإصدار الجديد',
            'type': 'system',
            'priority': 'medium',
            'is_read': true,
            'created_at': '2024-01-13 12:00',
            'sender': 'النظام',
            'action_url': null,
          },
        ];
        
        _isLoading = false;
      });
      
      _animationController.forward();
    } catch (e) {
      setState(() {
        _error = e.toString();
        _isLoading = false;
      });
    }
  }

  List<Map<String, dynamic>> get _filteredNotifications {
    if (_selectedFilter == 'all') return _notifications;
    if (_selectedFilter == 'unread') {
      return _notifications.where((notification) => !notification['is_read']).toList();
    }
    return _notifications.where((notification) => notification['type'] == _selectedFilter).toList();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
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
              'الإشعارات',
              style: TextStyle(
                fontFamily: AppTokens.primaryFontFamily,
                fontWeight: AppTokens.fontWeightBold,
              ),
            ),
          ],
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.mark_email_read),
            onPressed: () {
              _markAllAsRead();
            },
          ),
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _loadNotifications,
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
      body: _isLoading
          ? Center(
              child: CircularProgressIndicator(
                valueColor: AlwaysStoppedAnimation<Color>(AppTokens.primaryGreen),
              ),
            )
          : _error != null
              ? Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Icon(
                        Icons.error_outline,
                        size: 64,
                        color: AppTokens.errorRed,
                      ),
                      const SizedBox(height: 16),
                      Text(
                        _error!,
                        style: TextStyle(
                          fontFamily: AppTokens.primaryFontFamily,
                          fontSize: 16,
                          color: AppTokens.errorRed,
                        ),
                        textAlign: TextAlign.center,
                      ),
                      const SizedBox(height: 16),
                      ElevatedButton(
                        onPressed: _loadNotifications,
                        style: ElevatedButton.styleFrom(
                          backgroundColor: AppTokens.primaryGreen,
                          foregroundColor: AppTokens.neutralWhite,
                        ),
                        child: const Text(
                          'إعادة المحاولة',
                          style: TextStyle(fontFamily: AppTokens.primaryFontFamily),
                        ),
                      ),
                    ],
                  ),
                )
              : Column(
                  children: [
                    // Filters Section
                    Container(
                      padding: const EdgeInsets.all(AppTokens.spacingMD),
                      color: AppTokens.neutralWhite,
                      child: Column(
                        children: [
                          // Filter Chips
                          Row(
                            children: [
                              _buildFilterChip('الكل', 'all'),
                              const SizedBox(width: AppTokens.spacingSM),
                              _buildFilterChip('غير مقروء', 'unread'),
                              const SizedBox(width: AppTokens.spacingSM),
                              _buildFilterChip('مهام', 'task'),
                              const SizedBox(width: AppTokens.spacingSM),
                              _buildFilterChip('تقييمات', 'evaluation'),
                              const SizedBox(width: AppTokens.spacingSM),
                              _buildFilterChip('تذكيرات', 'reminder'),
                            ],
                          ),
                        ],
                      ),
                    ),
                    
                    // Notifications List
                    Expanded(
                      child: AnimatedBuilder(
                        animation: _animationController,
                        builder: (context, child) {
                          return FadeTransition(
                            opacity: _fadeAnimation,
                            child: SlideTransition(
                              position: Tween<Offset>(
                                begin: const Offset(0, 0.3),
                                end: Offset.zero,
                              ).animate(_slideAnimation),
                              child: _buildNotificationsList(),
                            ),
                          );
                        },
                      ),
                    ),
                  ],
                ),
    );
  }

  Widget _buildFilterChip(String label, String value) {
    final isSelected = _selectedFilter == value;
    return FilterChip(
      label: Text(label),
      selected: isSelected,
      onSelected: (selected) {
        setState(() {
          _selectedFilter = value;
        });
      },
      selectedColor: AppTokens.primaryGreen.withValues(alpha: 0.2),
      checkmarkColor: AppTokens.primaryGreen,
    );
  }

  Widget _buildNotificationsList() {
    if (_filteredNotifications.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              Icons.notifications_none,
              size: 64,
              color: AppTokens.neutralGray,
            ),
            const SizedBox(height: 16),
            Text(
              'لا توجد إشعارات',
              style: TextStyle(
                fontFamily: AppTokens.primaryFontFamily,
                fontSize: 18,
                color: AppTokens.neutralGray,
              ),
            ),
          ],
        ),
      );
    }

    return ListView.builder(
      padding: const EdgeInsets.all(AppTokens.spacingMD),
      itemCount: _filteredNotifications.length,
      itemBuilder: (context, index) {
        final notification = _filteredNotifications[index];
        return _buildNotificationCard(notification);
      },
    );
  }

  Widget _buildNotificationCard(Map<String, dynamic> notification) {
    final isRead = notification['is_read'];
    final type = notification['type'];
    final priority = notification['priority'];
    
    Color typeColor;
    IconData typeIcon;
    String typeText;
    
    switch (type) {
      case 'task':
        typeColor = AppTokens.infoBlue;
        typeIcon = Icons.task_alt;
        typeText = 'مهمة';
        break;
      case 'evaluation':
        typeColor = AppTokens.successGreen;
        typeIcon = Icons.assessment;
        typeText = 'تقييم';
        break;
      case 'reminder':
        typeColor = AppTokens.warningOrange;
        typeIcon = Icons.schedule;
        typeText = 'تذكير';
        break;
      case 'message':
        typeColor = AppTokens.primaryGold;
        typeIcon = Icons.message;
        typeText = 'رسالة';
        break;
      case 'achievement':
        typeColor = AppTokens.primaryGreen;
        typeIcon = Icons.emoji_events;
        typeText = 'إنجاز';
        break;
      case 'system':
        typeColor = AppTokens.neutralGray;
        typeIcon = Icons.settings;
        typeText = 'نظام';
        break;
      default:
        typeColor = AppTokens.neutralGray;
        typeIcon = Icons.notifications;
        typeText = 'عام';
    }

    Color priorityColor;
    switch (priority) {
      case 'high':
        priorityColor = AppTokens.errorRed;
        break;
      case 'medium':
        priorityColor = AppTokens.warningOrange;
        break;
      case 'low':
        priorityColor = AppTokens.successGreen;
        break;
      default:
        priorityColor = AppTokens.neutralGray;
    }

    return Card(
      margin: const EdgeInsets.only(bottom: AppTokens.spacingMD),
      child: InkWell(
        onTap: () => _handleNotificationTap(notification),
        child: Container(
          padding: const EdgeInsets.all(AppTokens.spacingMD),
          decoration: BoxDecoration(
            color: isRead ? AppTokens.neutralWhite : AppTokens.neutralLight,
            borderRadius: BorderRadius.circular(AppTokens.radiusMD),
            border: Border.all(
              color: isRead 
                  ? AppTokens.neutralMedium.withValues(alpha: 0.2)
                  : typeColor.withValues(alpha: 0.3),
            ),
          ),
          child: Row(
            children: [
              // Notification Icon
              Container(
                padding: const EdgeInsets.all(AppTokens.spacingSM),
                decoration: BoxDecoration(
                  color: typeColor.withValues(alpha: 0.1),
                  borderRadius: BorderRadius.circular(AppTokens.radiusSM),
                ),
                child: Icon(
                  typeIcon,
                  color: typeColor,
                  size: AppTokens.iconSizeMD,
                ),
              ),
              
              const SizedBox(width: AppTokens.spacingMD),
              
              // Notification Content
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        Expanded(
                          child: Text(
                            notification['title'],
                            style: Theme.of(context).textTheme.titleMedium?.copyWith(
                              fontWeight: isRead 
                                  ? AppTokens.fontWeightMedium 
                                  : AppTokens.fontWeightBold,
                            ),
                          ),
                        ),
                        if (!isRead)
                          Container(
                            width: 8,
                            height: 8,
                            decoration: BoxDecoration(
                              color: priorityColor,
                              shape: BoxShape.circle,
                            ),
                          ),
                      ],
                    ),
                    
                    const SizedBox(height: AppTokens.spacingXS),
                    
                    Text(
                      notification['message'],
                      style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                        color: AppTokens.neutralMedium,
                      ),
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                    ),
                    
                    const SizedBox(height: AppTokens.spacingSM),
                    
                    Row(
                      children: [
                        Container(
                          padding: const EdgeInsets.symmetric(
                            horizontal: AppTokens.spacingSM,
                            vertical: AppTokens.spacingXS,
                          ),
                          decoration: BoxDecoration(
                            color: typeColor.withValues(alpha: 0.1),
                            borderRadius: BorderRadius.circular(AppTokens.radiusSM),
                          ),
                          child: Text(
                            typeText,
                            style: TextStyle(
                              color: typeColor,
                              fontSize: AppTokens.fontSizeXS,
                              fontWeight: AppTokens.fontWeightMedium,
                            ),
                          ),
                        ),
                        const SizedBox(width: AppTokens.spacingSM),
                        Text(
                          notification['created_at'],
                          style: Theme.of(context).textTheme.bodySmall?.copyWith(
                            color: AppTokens.neutralMedium,
                          ),
                        ),
                        const Spacer(),
                        Text(
                          'من: ${notification['sender']}',
                          style: Theme.of(context).textTheme.bodySmall?.copyWith(
                            color: AppTokens.neutralMedium,
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
              
              // Actions
              PopupMenuButton<String>(
                onSelected: (value) {
                  switch (value) {
                    case 'mark_read':
                      _markAsRead(notification);
                      break;
                    case 'mark_unread':
                      _markAsUnread(notification);
                      break;
                    case 'delete':
                      _deleteNotification(notification);
                      break;
                  }
                },
                itemBuilder: (context) => [
                  if (!isRead)
                    const PopupMenuItem(
                      value: 'mark_read',
                      child: Row(
                        children: [
                          Icon(Icons.mark_email_read),
                          SizedBox(width: 8),
                          Text('تعيين كمقروء'),
                        ],
                      ),
                    ),
                  if (isRead)
                    const PopupMenuItem(
                      value: 'mark_unread',
                      child: Row(
                        children: [
                          Icon(Icons.mark_email_unread),
                          SizedBox(width: 8),
                          Text('تعيين كغير مقروء'),
                        ],
                      ),
                    ),
                  const PopupMenuItem(
                    value: 'delete',
                    child: Row(
                      children: [
                        Icon(Icons.delete),
                        SizedBox(width: 8),
                        Text('حذف'),
                      ],
                    ),
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }

  void _handleNotificationTap(Map<String, dynamic> notification) {
    // Mark as read if not already read
    if (!notification['is_read']) {
      _markAsRead(notification);
    }
    
    // Navigate to action URL if available
    if (notification['action_url'] != null) {
      // Handle navigation based on URL
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('الانتقال إلى: ${notification['action_url']}')),
      );
    }
  }

  void _markAsRead(Map<String, dynamic> notification) {
    setState(() {
      notification['is_read'] = true;
    });
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(content: Text('تم تعيين الإشعار كمقروء')),
    );
  }

  void _markAsUnread(Map<String, dynamic> notification) {
    setState(() {
      notification['is_read'] = false;
    });
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(content: Text('تم تعيين الإشعار كغير مقروء')),
    );
  }

  void _markAllAsRead() {
    setState(() {
      for (var notification in _notifications) {
        notification['is_read'] = true;
      }
    });
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(content: Text('تم تعيين جميع الإشعارات كمقروءة')),
    );
  }

  void _deleteNotification(Map<String, dynamic> notification) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('تأكيد الحذف'),
        content: const Text('هل أنت متأكد من حذف هذا الإشعار؟'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('إلغاء'),
          ),
          TextButton(
            onPressed: () {
              Navigator.pop(context);
              setState(() {
                _notifications.removeWhere((n) => n['id'] == notification['id']);
              });
              ScaffoldMessenger.of(context).showSnackBar(
                const SnackBar(content: Text('تم حذف الإشعار')),
              );
            },
            child: const Text('حذف'),
          ),
        ],
      ),
    );
  }
}





