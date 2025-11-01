import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/router/app_router.dart';
import '../../../core/theme/tokens.dart';
import '../../../services/auth_service.dart';
import '../../../services/api_service.dart';

class TeacherSchedulePage extends ConsumerStatefulWidget {
  const TeacherSchedulePage({super.key});

  @override
  ConsumerState<TeacherSchedulePage> createState() => _TeacherSchedulePageState();
}

class _TeacherSchedulePageState extends ConsumerState<TeacherSchedulePage>
    with TickerProviderStateMixin {
  late AnimationController _animationController;
  late Animation<double> _fadeAnimation;
  late Animation<Offset> _slideAnimation;
  
  List<Map<String, dynamic>> _schedule = [];
  bool _isLoading = true;
  String? _error;
  DateTime _selectedDate = DateTime.now();
  String _selectedView = 'week';

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

    _slideAnimation = Tween<Offset>(
      begin: const Offset(0, -0.3),
      end: Offset.zero,
    ).animate(CurvedAnimation(
      parent: _animationController,
      curve: Curves.easeOutCubic,
    ));

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
        _isLoading = true;
        _error = null;
      });

      // Ensure token is set before making API calls
      final authState = ref.read(authStateProvider);
      if (authState.token != null) {
        ApiService.setToken(authState.token!);
      }

      // جلب الجدول من API
      final response = await ApiService.getClassSchedule();
      
      if (response['success'] == true && response['schedule'] != null) {
        // تحويل بيانات الجدول من API إلى التنسيق المطلوب
        final scheduleData = List<Map<String, dynamic>>.from(response['schedule'] ?? []);
        
        setState(() {
          _schedule = scheduleData.map((item) {
            return {
              'id': scheduleData.indexOf(item) + 1,
              'title': 'جلسة ${item['day'] ?? ''}',
              'description': '${item['class_name'] ?? 'الفصل'}',
              'start_time': item['start_time'] ?? '00:00',
              'end_time': item['end_time'] ?? '00:00',
              'date': item['date'] ?? DateTime.now().toIso8601String().split('T')[0],
              'day': item['day'] ?? '',
              'day_key': item['day_key'] ?? '',
              'status': 'scheduled',
              'type': 'lesson',
            };
          }).toList();
          _isLoading = false;
        });
      } else {
        setState(() {
          _schedule = [];
          _isLoading = false;
        });
      }
      
      _animationController.forward();
    } catch (e) {
      setState(() {
        _error = 'خطأ في تحميل الجدول: ${e.toString()}';
        _isLoading = false;
      });
      print('خطأ في تحميل الجدول: $e');
    }
  }


  List<Map<String, dynamic>> get _filteredSchedule {
    return _schedule.where((item) {
      final itemDate = DateTime.parse(item['date']);
      if (_selectedView == 'week') {
        final startOfWeek = _selectedDate.subtract(Duration(days: _selectedDate.weekday - 1));
        final endOfWeek = startOfWeek.add(const Duration(days: 6));
        return itemDate.isAfter(startOfWeek.subtract(const Duration(days: 1))) &&
               itemDate.isBefore(endOfWeek.add(const Duration(days: 1)));
      } else {
        return itemDate.year == _selectedDate.year &&
               itemDate.month == _selectedDate.month &&
               itemDate.day == _selectedDate.day;
      }
    }).toList();
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
              'إدارة الجدول الزمني',
              style: TextStyle(
                fontFamily: AppTokens.primaryFontFamily,
                fontWeight: AppTokens.fontWeightBold,
              ),
            ),
          ],
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.add),
            onPressed: () {
              _showAddScheduleDialog();
            },
          ),
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _loadSchedule,
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
                        onPressed: _loadSchedule,
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
                    // Header Section
                    Container(
                      padding: const EdgeInsets.all(AppTokens.spacingMD),
                      color: AppTokens.neutralWhite,
                      child: Column(
                        children: [
                          // Date Selector
                          Row(
                            children: [
                              Expanded(
                                child: InkWell(
                                  onTap: () async {
                                    final date = await showDatePicker(
                                      context: context,
                                      initialDate: _selectedDate,
                                      firstDate: DateTime.now().subtract(const Duration(days: 30)),
                                      lastDate: DateTime.now().add(const Duration(days: 30)),
                                    );
                                    if (date != null) {
                                      setState(() {
                                        _selectedDate = date;
                                      });
                                    }
                                  },
                                  child: Container(
                                    padding: const EdgeInsets.all(AppTokens.spacingMD),
                                    decoration: BoxDecoration(
                                      border: Border.all(color: AppTokens.neutralMedium),
                                      borderRadius: BorderRadius.circular(AppTokens.radiusMD),
                                    ),
                                    child: Row(
                                      children: [
                                        const Icon(Icons.calendar_today),
                                        const SizedBox(width: AppTokens.spacingSM),
                                        Text(
                                          '${_selectedDate.day}/${_selectedDate.month}/${_selectedDate.year}',
                                        ),
                                      ],
                                    ),
                                  ),
                                ),
                              ),
                              const SizedBox(width: AppTokens.spacingMD),
                              // View Toggle
                              ToggleButtons(
                                isSelected: [_selectedView == 'day', _selectedView == 'week'],
                                onPressed: (index) {
                                  setState(() {
                                    _selectedView = index == 0 ? 'day' : 'week';
                                  });
                                },
                                children: const [
                                  Text('يوم'),
                                  Text('أسبوع'),
                                ],
                              ),
                            ],
                          ),
                        ],
                      ),
                    ),
                    
                    // Schedule List
                    Expanded(
                      child: AnimatedBuilder(
                        animation: _animationController,
                        builder: (context, child) {
                          return FadeTransition(
                            opacity: _fadeAnimation,
                            child: SlideTransition(
                              position: _slideAnimation,
                              child: _buildScheduleList(),
                            ),
                          );
                        },
                      ),
                    ),
                  ],
                ),
    );
  }

  Widget _buildScheduleList() {
    if (_filteredSchedule.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              Icons.schedule_outlined,
              size: 64,
              color: AppTokens.neutralGray,
            ),
            const SizedBox(height: 16),
            Text(
              'لا توجد جلسات مجدولة',
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
      itemCount: _filteredSchedule.length,
      itemBuilder: (context, index) {
        final item = _filteredSchedule[index];
        return _buildScheduleCard(item);
      },
    );
  }

  Widget _buildScheduleCard(Map<String, dynamic> item) {
    final status = item['status'];
    Color statusColor;
    IconData statusIcon;
    String statusText;
    
    switch (status) {
      case 'scheduled':
        statusColor = AppTokens.infoBlue;
        statusIcon = Icons.schedule;
        statusText = 'مجدولة';
        break;
      case 'completed':
        statusColor = AppTokens.successGreen;
        statusIcon = Icons.check_circle;
        statusText = 'مكتملة';
        break;
      case 'cancelled':
        statusColor = AppTokens.errorRed;
        statusIcon = Icons.cancel;
        statusText = 'ملغية';
        break;
      default:
        statusColor = AppTokens.neutralGray;
        statusIcon = Icons.help;
        statusText = 'غير محدد';
    }

    return Card(
      margin: const EdgeInsets.only(bottom: AppTokens.spacingMD),
      child: Padding(
        padding: const EdgeInsets.all(AppTokens.spacingMD),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Header
            Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(AppTokens.spacingSM),
                  decoration: BoxDecoration(
                    color: statusColor.withValues(alpha: 0.1),
                    borderRadius: BorderRadius.circular(AppTokens.radiusSM),
                  ),
                  child: Icon(
                    statusIcon,
                    color: statusColor,
                    size: AppTokens.iconSizeMD,
                  ),
                ),
                const SizedBox(width: AppTokens.spacingMD),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        item['title'],
                        style: Theme.of(context).textTheme.titleMedium?.copyWith(
                          fontWeight: AppTokens.fontWeightBold,
                        ),
                      ),
                      Text(
                        '${item['day']} - ${item['date']}',
                        style: Theme.of(context).textTheme.bodySmall?.copyWith(
                          color: AppTokens.neutralMedium,
                        ),
                      ),
                    ],
                  ),
                ),
                Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: AppTokens.spacingSM,
                    vertical: AppTokens.spacingXS,
                  ),
                  decoration: BoxDecoration(
                    color: statusColor.withValues(alpha: 0.1),
                    borderRadius: BorderRadius.circular(AppTokens.radiusSM),
                  ),
                  child: Text(
                    statusText,
                    style: TextStyle(
                      color: statusColor,
                      fontSize: AppTokens.fontSizeXS,
                      fontWeight: AppTokens.fontWeightMedium,
                    ),
                  ),
                ),
              ],
            ),
            
            const SizedBox(height: AppTokens.spacingMD),
            
            // Description
            Text(
              item['description'],
              style: Theme.of(context).textTheme.bodyMedium,
            ),
            
            const SizedBox(height: AppTokens.spacingMD),
            
            // Details Grid
            GridView.count(
              shrinkWrap: true,
              physics: const NeverScrollableScrollPhysics(),
              crossAxisCount: 2,
              childAspectRatio: 4,
              crossAxisSpacing: AppTokens.spacingSM,
              mainAxisSpacing: AppTokens.spacingSM,
              children: [
                _buildDetailItem('الوقت', '${item['start_time']} - ${item['end_time']}', Icons.access_time),
                _buildDetailItem('المستوى', item['level'], Icons.school),
                _buildDetailItem('عدد الطالبات', '${item['students_count']}', Icons.people),
                _buildDetailItem('القاعة', item['room'], Icons.room),
              ],
            ),
            
            const SizedBox(height: AppTokens.spacingMD),
            
            // Actions
            Row(
              mainAxisAlignment: MainAxisAlignment.end,
              children: [
                if (status == 'scheduled') ...[
                  TextButton.icon(
                    onPressed: () => _startSession(item),
                    icon: const Icon(Icons.play_arrow, size: 16),
                    label: const Text('بدء الجلسة'),
                  ),
                  const SizedBox(width: AppTokens.spacingSM),
                ],
                TextButton.icon(
                  onPressed: () => _editSchedule(item),
                  icon: const Icon(Icons.edit, size: 16),
                  label: const Text('تعديل'),
                ),
                const SizedBox(width: AppTokens.spacingSM),
                TextButton.icon(
                  onPressed: () => _deleteSchedule(item),
                  icon: const Icon(Icons.delete, size: 16),
                  label: const Text('حذف'),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildDetailItem(String label, String value, IconData icon) {
    return Container(
      padding: const EdgeInsets.all(AppTokens.spacingSM),
      decoration: BoxDecoration(
        color: AppTokens.neutralLight,
        borderRadius: BorderRadius.circular(AppTokens.radiusSM),
      ),
      child: Row(
        children: [
          Icon(icon, color: AppTokens.neutralMedium, size: 16),
          const SizedBox(width: AppTokens.spacingXS),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  label,
                  style: TextStyle(
                    fontSize: AppTokens.fontSizeXS,
                    color: AppTokens.neutralMedium,
                  ),
                ),
                Text(
                  value,
                  style: TextStyle(
                    fontSize: AppTokens.fontSizeSM,
                    fontWeight: AppTokens.fontWeightMedium,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  void _showAddScheduleDialog() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('إضافة جلسة جديدة'),
        content: const Text('هذه الميزة ستكون متاحة قريباً'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('موافق'),
          ),
        ],
      ),
    );
  }

  void _startSession(Map<String, dynamic> item) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text('بدء جلسة ${item['title']}')),
    );
  }

  void _editSchedule(Map<String, dynamic> item) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text('تعديل ${item['title']}')),
    );
  }

  void _deleteSchedule(Map<String, dynamic> item) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('تأكيد الحذف'),
        content: Text('هل أنت متأكد من حذف ${item['title']}؟'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('إلغاء'),
          ),
          TextButton(
            onPressed: () {
              Navigator.pop(context);
              ScaffoldMessenger.of(context).showSnackBar(
                SnackBar(content: Text('تم حذف ${item['title']}')),
              );
            },
            child: const Text('حذف'),
          ),
        ],
      ),
    );
  }
}





