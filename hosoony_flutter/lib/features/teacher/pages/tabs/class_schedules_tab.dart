import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../../core/theme/tokens.dart';
import '../../../../services/auth_service.dart';
import '../../../../services/api_service.dart';

class ClassSchedulesTab extends ConsumerStatefulWidget {
  final int? classId;

  const ClassSchedulesTab({super.key, required this.classId});

  @override
  ConsumerState<ClassSchedulesTab> createState() => _ClassSchedulesTabState();
}

class _ClassSchedulesTabState extends ConsumerState<ClassSchedulesTab> {
  List<Map<String, dynamic>> _schedules = [];
  bool _isLoading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _loadSchedules();
  }

  Future<void> _loadSchedules() async {
    try {
      setState(() {
        _isLoading = true;
        _error = null;
      });

      final authState = ref.read(authStateProvider);
      if (authState.token != null) {
        ApiService.setToken(authState.token!);
      }

      final response = await ApiService.getClassSchedules(classId: widget.classId);
      
      if (response['success'] == true) {
        setState(() {
          _schedules = List<Map<String, dynamic>>.from(response['schedules'] ?? []);
          _isLoading = false;
        });
      } else {
        setState(() {
          _error = response['message'] ?? 'فشل تحميل الجداول';
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

  Future<void> _toggleSchedule(int scheduleId) async {
    try {
      final authState = ref.read(authStateProvider);
      if (authState.token != null) {
        ApiService.setToken(authState.token!);
      }

      await ApiService.toggleClassSchedule(scheduleId);
      _loadSchedules();
      
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('تم تحديث حالة الجدول'),
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

  Future<void> _deleteSchedule(BuildContext context, int scheduleId) async {
    final confirm = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('تأكيد الحذف'),
        content: const Text('هل أنت متأكد من حذف هذا الجدول؟'),
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

        await ApiService.deleteClassSchedule(scheduleId);
        _loadSchedules();
        
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
              content: Text('تم حذف الجدول بنجاح'),
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

  Future<void> _showAddEditScheduleDialog(BuildContext context, Map<String, dynamic>? schedule) async {
    final formKey = GlobalKey<FormState>();
    final startTimeController = TextEditingController();
    final endTimeController = TextEditingController();
    final zoomLinkController = TextEditingController();
    final zoomMeetingIdController = TextEditingController();
    final zoomPasswordController = TextEditingController();
    final notesController = TextEditingController();
    bool isActive = true;

    if (schedule != null) {
      startTimeController.text = schedule['start_time'] ?? '';
      endTimeController.text = schedule['end_time'] ?? '';
      zoomLinkController.text = schedule['zoom_link'] ?? '';
      zoomMeetingIdController.text = schedule['zoom_meeting_id'] ?? '';
      zoomPasswordController.text = schedule['zoom_password'] ?? '';
      notesController.text = schedule['notes'] ?? '';
      isActive = schedule['is_active'] == true;
    }

    String? selectedDay = schedule?['day_of_week'];

    await showDialog(
      context: context,
      builder: (context) => StatefulBuilder(
        builder: (context, setDialogState) => AlertDialog(
          title: Text(schedule == null ? 'إضافة جدول' : 'تعديل جدول'),
          content: SingleChildScrollView(
            child: Form(
              key: formKey,
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  DropdownButtonFormField<String>(
                    value: selectedDay,
                    decoration: const InputDecoration(labelText: 'يوم الأسبوع'),
                    items: const [
                      DropdownMenuItem(value: 'sunday', child: Text('الأحد')),
                      DropdownMenuItem(value: 'monday', child: Text('الاثنين')),
                      DropdownMenuItem(value: 'tuesday', child: Text('الثلاثاء')),
                      DropdownMenuItem(value: 'wednesday', child: Text('الأربعاء')),
                      DropdownMenuItem(value: 'thursday', child: Text('الخميس')),
                      DropdownMenuItem(value: 'friday', child: Text('الجمعة')),
                      DropdownMenuItem(value: 'saturday', child: Text('السبت')),
                    ],
                    onChanged: (value) {
                      setDialogState(() {
                        selectedDay = value;
                      });
                    },
                    validator: (value) => value == null ? 'اختر اليوم' : null,
                  ),
                  const SizedBox(height: 16),
                  TextFormField(
                    controller: startTimeController,
                    decoration: const InputDecoration(
                      labelText: 'وقت البداية (HH:mm)',
                      hintText: '09:00',
                    ),
                    validator: (value) => value?.isEmpty ?? true ? 'مطلوب' : null,
                  ),
                  const SizedBox(height: 16),
                  TextFormField(
                    controller: endTimeController,
                    decoration: const InputDecoration(
                      labelText: 'وقت النهاية (HH:mm)',
                      hintText: '10:00',
                    ),
                    validator: (value) => value?.isEmpty ?? true ? 'مطلوب' : null,
                  ),
                  const SizedBox(height: 16),
                  TextFormField(
                    controller: zoomLinkController,
                    decoration: const InputDecoration(
                      labelText: 'رابط Zoom',
                      hintText: 'https://zoom.us/j/...',
                    ),
                  ),
                  const SizedBox(height: 16),
                  TextFormField(
                    controller: zoomMeetingIdController,
                    decoration: const InputDecoration(labelText: 'معرف الاجتماع'),
                  ),
                  const SizedBox(height: 16),
                  TextFormField(
                    controller: zoomPasswordController,
                    decoration: const InputDecoration(labelText: 'كلمة مرور الاجتماع'),
                    obscureText: true,
                  ),
                  const SizedBox(height: 16),
                  TextFormField(
                    controller: notesController,
                    decoration: const InputDecoration(labelText: 'ملاحظات'),
                    maxLines: 3,
                  ),
                  const SizedBox(height: 16),
                  SwitchListTile(
                    title: const Text('نشط'),
                    value: isActive,
                    onChanged: (value) {
                      setDialogState(() {
                        isActive = value;
                      });
                    },
                  ),
                ],
              ),
            ),
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(context),
              child: const Text('إلغاء'),
            ),
            ElevatedButton(
              onPressed: () async {
                if (formKey.currentState!.validate() && selectedDay != null) {
                  try {
                    final authState = ref.read(authStateProvider);
                    if (authState.token != null) {
                      ApiService.setToken(authState.token!);
                    }

                    if (schedule == null) {
                      await ApiService.createClassSchedule(
                        dayOfWeek: selectedDay!,
                        startTime: startTimeController.text,
                        endTime: endTimeController.text,
                        zoomLink: zoomLinkController.text.isEmpty ? null : zoomLinkController.text,
                        zoomMeetingId: zoomMeetingIdController.text.isEmpty ? null : zoomMeetingIdController.text,
                        zoomPassword: zoomPasswordController.text.isEmpty ? null : zoomPasswordController.text,
                        notes: notesController.text.isEmpty ? null : notesController.text,
                        isActive: isActive,
                        classId: widget.classId,
                      );
                    } else {
                      await ApiService.updateClassSchedule(
                        scheduleId: schedule['id'] as int,
                        dayOfWeek: selectedDay,
                        startTime: startTimeController.text,
                        endTime: endTimeController.text,
                        zoomLink: zoomLinkController.text.isEmpty ? null : zoomLinkController.text,
                        zoomMeetingId: zoomMeetingIdController.text.isEmpty ? null : zoomMeetingIdController.text,
                        zoomPassword: zoomPasswordController.text.isEmpty ? null : zoomPasswordController.text,
                        notes: notesController.text.isEmpty ? null : notesController.text,
                        isActive: isActive,
                      );
                    }

                    _loadSchedules();
                    if (context.mounted) {
                      Navigator.pop(context);
                      ScaffoldMessenger.of(context).showSnackBar(
                        SnackBar(
                          content: Text(schedule == null ? 'تم الإضافة بنجاح' : 'تم التحديث بنجاح'),
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
              child: Text(schedule == null ? 'إضافة' : 'تحديث'),
            ),
          ],
        ),
      ),
    );
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
              onPressed: _loadSchedules,
              child: const Text('إعادة المحاولة'),
            ),
          ],
        ),
      );
    }

    if (_schedules.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.schedule, size: 64, color: Colors.grey),
            const SizedBox(height: 16),
            Text(
              'لا توجد جداول متاحة',
              style: Theme.of(context).textTheme.titleLarge,
            ),
          ],
        ),
      );
    }

    return Column(
      children: [
        // إضافة زر
        Padding(
          padding: const EdgeInsets.all(AppTokens.spacingMD),
          child: ElevatedButton.icon(
            onPressed: () => _showAddEditScheduleDialog(context, null),
            icon: const Icon(Icons.add),
            label: const Text('إضافة جدول'),
            style: ElevatedButton.styleFrom(
              backgroundColor: AppTokens.successGreen,
              foregroundColor: Colors.white,
              minimumSize: const Size(double.infinity, 48),
            ),
          ),
        ),
        
        Expanded(
          child: RefreshIndicator(
            onRefresh: _loadSchedules,
            child: ListView.builder(
              padding: const EdgeInsets.symmetric(horizontal: AppTokens.spacingMD),
              itemCount: _schedules.length,
              itemBuilder: (context, index) {
                final schedule = _schedules[index];
                return Card(
                  margin: const EdgeInsets.only(bottom: AppTokens.spacingMD),
                  child: ListTile(
                    leading: CircleAvatar(
                      backgroundColor: schedule['is_active'] == true 
                          ? AppTokens.successGreen 
                          : Colors.grey,
                      child: Icon(
                        schedule['is_active'] == true ? Icons.check : Icons.close,
                        color: Colors.white,
                      ),
                    ),
                    title: Text(schedule['day_name'] ?? 'غير محدد'),
                    subtitle: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text('${schedule['start_time']} - ${schedule['end_time']}'),
                        if (schedule['zoom_meeting_id'] != null)
                          Text('معرف الاجتماع: ${schedule['zoom_meeting_id']}'),
                        if (schedule['notes'] != null && schedule['notes'].toString().isNotEmpty)
                          Text('ملاحظات: ${schedule['notes']}'),
                      ],
                    ),
                    isThreeLine: true,
                    trailing: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        IconButton(
                          icon: Icon(Icons.edit, color: AppTokens.infoBlue),
                          onPressed: () => _showAddEditScheduleDialog(context, schedule),
                        ),
                        IconButton(
                          icon: Icon(
                            schedule['is_active'] == true ? Icons.toggle_on : Icons.toggle_off,
                            color: schedule['is_active'] == true 
                                ? AppTokens.successGreen 
                                : Colors.grey,
                          ),
                          onPressed: () => _toggleSchedule(schedule['id'] as int),
                        ),
                        IconButton(
                          icon: const Icon(Icons.delete, color: Colors.red),
                          onPressed: () => _deleteSchedule(context, schedule['id'] as int),
                        ),
                      ],
                    ),
                  ),
                );
              },
            ),
          ),
        ),
      ],
    );
  }
}

