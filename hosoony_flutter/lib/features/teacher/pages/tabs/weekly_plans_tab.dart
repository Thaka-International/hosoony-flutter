import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../../core/theme/tokens.dart';
import '../../../../services/auth_service.dart';
import '../../../../services/api_service.dart';

class WeeklyPlansTab extends ConsumerStatefulWidget {
  final int? classId;

  const WeeklyPlansTab({super.key, required this.classId});

  @override
  ConsumerState<WeeklyPlansTab> createState() => _WeeklyPlansTabState();
}

class _WeeklyPlansTabState extends ConsumerState<WeeklyPlansTab> {
  List<Map<String, dynamic>> _weeklySchedules = [];
  bool _isLoading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _loadWeeklySchedules();
  }

  Future<void> _loadWeeklySchedules() async {
    try {
      setState(() {
        _isLoading = true;
        _error = null;
      });

      final authState = ref.read(authStateProvider);
      if (authState.token != null) {
        ApiService.setToken(authState.token!);
      }

      final response = await ApiService.getWeeklyTaskSchedules(classId: widget.classId);
      
      if (response['success'] == true) {
        setState(() {
          _weeklySchedules = List<Map<String, dynamic>>.from(response['weekly_schedules'] ?? []);
          _isLoading = false;
        });
      } else {
        setState(() {
          _error = response['message'] ?? 'فشل تحميل الخطة الأسبوعية';
          _isLoading = false;
        });
      }
    } catch (e) {
      setState(() {
        _error = 'خطأ: ${e.toString()}';
        _isLoading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    if (_isLoading) {
      return const Center(child: CircularProgressIndicator());
    }

    if (_error != null) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Text(_error!, style: const TextStyle(color: Colors.red)),
            const SizedBox(height: 16),
            ElevatedButton(
              onPressed: _loadWeeklySchedules,
              child: const Text('إعادة المحاولة'),
            ),
          ],
        ),
      );
    }

    return Column(
      children: [
        Padding(
          padding: const EdgeInsets.all(AppTokens.spacingMD),
          child: Wrap(
            spacing: 12,
            runSpacing: 12,
            children: [
              ElevatedButton.icon(
                onPressed: () => _showAddEditWeeklyScheduleDialog(context, null),
                icon: const Icon(Icons.add),
                label: const Text('إضافة جدول'),
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppTokens.infoBlue,
                  foregroundColor: Colors.white,
                ),
              ),
              ElevatedButton.icon(
                onPressed: () => _createNextWeekPlan(context),
                icon: const Icon(Icons.calendar_today),
                label: const Text('الأسبوع القادم'),
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppTokens.successGreen,
                  foregroundColor: Colors.white,
                ),
              ),
              ElevatedButton.icon(
                onPressed: () => _copyToNextWeek(context),
                icon: const Icon(Icons.copy),
                label: const Text('نسخ للأسبوع القادم'),
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppTokens.primaryGold,
                  foregroundColor: Colors.white,
                ),
              ),
            ],
          ),
        ),
        Expanded(
          child: _weeklySchedules.isEmpty
              ? Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Icon(Icons.calendar_view_week, size: 64, color: Colors.grey),
                      const SizedBox(height: 16),
                      Text(
                        'لا توجد خطط أسبوعية',
                        style: Theme.of(context).textTheme.titleLarge,
                      ),
                    ],
                  ),
                )
              : RefreshIndicator(
                  onRefresh: _loadWeeklySchedules,
                  child: _buildWeeklySchedulesList(),
                ),
        ),
      ],
    );
  }

  Widget _buildWeeklySchedulesList() {
    final groupedByWeek = <String, List<Map<String, dynamic>>>{};
    for (var schedule in _weeklySchedules) {
      final weekKey = '${schedule['week_start_date']} - ${schedule['week_end_date']}';
      if (!groupedByWeek.containsKey(weekKey)) {
        groupedByWeek[weekKey] = [];
      }
      groupedByWeek[weekKey]!.add(schedule);
    }

    return ListView.builder(
      padding: const EdgeInsets.all(AppTokens.spacingMD),
      itemCount: groupedByWeek.length,
      itemBuilder: (context, index) {
        final weekKey = groupedByWeek.keys.elementAt(index);
        final schedules = groupedByWeek[weekKey]!;
        
        return Card(
          margin: const EdgeInsets.only(bottom: AppTokens.spacingLG),
          child: ExpansionTile(
            title: Text('الأسبوع: $weekKey'),
            subtitle: Text('${schedules.length} مهام'),
            children: schedules.map((schedule) {
              return ListTile(
                leading: CircleAvatar(
                  backgroundColor: AppTokens.primaryGold,
                  child: Text(
                    schedule['day_name']?.substring(0, 1) ?? '؟',
                    style: const TextStyle(color: Colors.white),
                  ),
                ),
                title: Text(schedule['task_name'] ?? 'غير محدد'),
                subtitle: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text('${schedule['day_name']} - ${schedule['task_date']}'),
                    if (schedule['task_details'] != null && schedule['task_details'].toString().isNotEmpty)
                      Text(
                        'التفاصيل: ${schedule['task_details']}',
                        style: const TextStyle(fontSize: 12),
                      ),
                  ],
                ),
                isThreeLine: schedule['task_details'] != null && schedule['task_details'].toString().isNotEmpty,
                trailing: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    IconButton(
                      icon: const Icon(Icons.edit_note, color: AppTokens.infoBlue),
                      onPressed: () => _showEditDetailsDialog(context, schedule),
                      tooltip: 'تعديل التفاصيل',
                    ),
                    IconButton(
                      icon: Icon(Icons.edit, color: AppTokens.primaryGold),
                      onPressed: () => _showAddEditWeeklyScheduleDialog(context, schedule),
                      tooltip: 'تعديل',
                    ),
                    IconButton(
                      icon: const Icon(Icons.delete, color: Colors.red),
                      onPressed: () => _deleteWeeklySchedule(context, schedule['id'] as int),
                      tooltip: 'حذف',
                    ),
                  ],
                ),
              );
            }).toList(),
          ),
        );
      },
    );
  }

  Future<void> _createNextWeekPlan(BuildContext context) async {
    final confirm = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('إنشاء خطة الأسبوع القادم'),
        content: const Text('سيتم إنشاء جدول أسبوعي جديد للأسبوع القادم مع جميع الأيام المتاحة والمهام المربوطة بالفصل. التفاصيل ستكون فارغة ويمكنك ملؤها لاحقاً.'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text('إلغاء'),
          ),
          ElevatedButton(
            onPressed: () => Navigator.pop(context, true),
            child: const Text('إنشاء'),
          ),
        ],
      ),
    );

    if (confirm == true) {
      try {
        final authState = ref.read(authStateProvider);
        if (authState.token != null) {
          ApiService.setToken(authState.token!);
        }

        final response = await ApiService.createNextWeekPlan(classId: widget.classId);
        _loadWeeklySchedules();
        
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text(response['message'] ?? 'تم الإنشاء بنجاح'),
              backgroundColor: AppTokens.successGreen,
            ),
          );
        }
      } catch (e) {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text('خطأ: ${e.toString()}'),
              backgroundColor: Colors.red,
            ),
          );
        }
      }
    }
  }

  Future<void> _copyToNextWeek(BuildContext context) async {
    final confirm = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('نسخ للأسبوع القادم'),
        content: const Text('سيتم نسخ جميع سجلات الأسبوع الحالي للأسبوع القادم مع التفاصيل.'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text('إلغاء'),
          ),
          ElevatedButton(
            onPressed: () => Navigator.pop(context, true),
            child: const Text('نسخ'),
          ),
        ],
      ),
    );

    if (confirm == true) {
      try {
        final authState = ref.read(authStateProvider);
        if (authState.token != null) {
          ApiService.setToken(authState.token!);
        }

        final response = await ApiService.copyToNextWeek(classId: widget.classId);
        _loadWeeklySchedules();
        
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text(response['message'] ?? 'تم النسخ بنجاح'),
              backgroundColor: AppTokens.successGreen,
            ),
          );
        }
      } catch (e) {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text('خطأ: ${e.toString()}'),
              backgroundColor: Colors.red,
            ),
          );
        }
      }
    }
  }

  Future<void> _deleteWeeklySchedule(BuildContext context, int scheduleId) async {
    final confirm = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('تأكيد الحذف'),
        content: const Text('هل أنت متأكد من حذف هذا الجدول الأسبوعي؟'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text('إلغاء'),
          ),
          TextButton(
            onPressed: () => Navigator.pop(context, true),
            style: TextButton.styleFrom(foregroundColor: Colors.red),
            child: const Text('حذف'),
          ),
        ],
      ),
    );

    if (confirm == true) {
      try {
        final authState = ref.read(authStateProvider);
        if (authState.token != null) {
          ApiService.setToken(authState.token!);
        }

        await ApiService.deleteWeeklyTaskSchedule(scheduleId);
        _loadWeeklySchedules();
        
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
              content: Text('تم الحذف بنجاح'),
              backgroundColor: AppTokens.successGreen,
            ),
          );
        }
      } catch (e) {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text('خطأ: ${e.toString()}'),
              backgroundColor: Colors.red,
            ),
          );
        }
      }
    }
  }

  Future<void> _showEditDetailsDialog(BuildContext context, Map<String, dynamic> schedule) async {
    final formKey = GlobalKey<FormState>();
    final detailsController = TextEditingController(text: schedule['task_details'] ?? '');

    await showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('تعديل التفاصيل'),
        content: Form(
          key: formKey,
          child: TextFormField(
            controller: detailsController,
            decoration: const InputDecoration(
              labelText: 'التفاصيل',
              hintText: 'مثال: صفحة 23، من الآية 1 إلى 5...',
            ),
            maxLines: 4,
            validator: (value) => value == null || value.isEmpty ? 'مطلوب' : null,
          ),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('إلغاء'),
          ),
          ElevatedButton(
            onPressed: () async {
              if (formKey.currentState!.validate()) {
                try {
                  final authState = ref.read(authStateProvider);
                  if (authState.token != null) {
                    ApiService.setToken(authState.token!);
                  }

                  await ApiService.updateWeeklyTaskDetails(
                    scheduleId: schedule['id'] as int,
                    taskDetails: detailsController.text,
                  );

                  _loadWeeklySchedules();
                  if (context.mounted) {
                    Navigator.pop(context);
                    ScaffoldMessenger.of(context).showSnackBar(
                      const SnackBar(
                        content: Text('تم تحديث التفاصيل بنجاح'),
                        backgroundColor: AppTokens.successGreen,
                      ),
                    );
                  }
                } catch (e) {
                  if (context.mounted) {
                    ScaffoldMessenger.of(context).showSnackBar(
                      SnackBar(
                        content: Text('خطأ: ${e.toString()}'),
                        backgroundColor: Colors.red,
                      ),
                    );
                  }
                }
              }
            },
            child: const Text('حفظ'),
          ),
        ],
      ),
    );
  }

  Future<void> _showAddEditWeeklyScheduleDialog(BuildContext context, Map<String, dynamic>? schedule) async {
    if (schedule != null) {
      _showEditDetailsDialog(context, schedule);
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('استخدم "الأسبوع القادم" لإنشاء الخطة تلقائياً'),
          backgroundColor: AppTokens.infoBlue,
        ),
      );
    }
  }
}

