import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../../core/theme/tokens.dart';
import '../../../../services/auth_service.dart';
import '../../../../services/api_service.dart';

class TaskAssignmentsTab extends ConsumerStatefulWidget {
  final int? classId;

  const TaskAssignmentsTab({super.key, required this.classId});

  @override
  ConsumerState<TaskAssignmentsTab> createState() => _TaskAssignmentsTabState();
}

class _TaskAssignmentsTabState extends ConsumerState<TaskAssignmentsTab> {
  List<Map<String, dynamic>> _assignments = [];
  bool _isLoading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _loadAssignments();
  }

  Future<void> _loadAssignments() async {
    try {
      setState(() {
        _isLoading = true;
        _error = null;
      });

      final authState = ref.read(authStateProvider);
      if (authState.token != null) {
        ApiService.setToken(authState.token!);
      }

      final response = await ApiService.getTaskAssignments(classId: widget.classId);
      
      if (response['success'] == true) {
        setState(() {
          _assignments = List<Map<String, dynamic>>.from(response['task_assignments'] ?? []);
          _isLoading = false;
        });
      } else {
        setState(() {
          _error = response['message'] ?? 'فشل تحميل المهام';
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

  Future<void> _toggleTaskAssignment(BuildContext context, int assignmentId, bool isActive) async {
    try {
      final authState = ref.read(authStateProvider);
      if (authState.token != null) {
        ApiService.setToken(authState.token!);
      }

      await ApiService.updateTaskAssignment(
        assignmentId: assignmentId,
        isActive: isActive,
      );
      
      _loadAssignments();
      
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(isActive ? 'تم تفعيل المهمة' : 'تم إلغاء ربط المهمة بالفصل'),
            backgroundColor: AppTokens.successGreen,
          ),
        );
      }
    } catch (e) {
      _loadAssignments();
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

  Future<void> _showAddEditTaskDialog(BuildContext context, Map<String, dynamic>? assignment) async {
    final formKey = GlobalKey<FormState>();
    final orderController = TextEditingController();
    bool isActive = true;
    List<Map<String, dynamic>> availableTasks = [];
    bool isLoadingTasks = true;
    int? selectedTaskId;

    if (assignment != null) {
      orderController.text = (assignment['order'] ?? 0).toString();
      isActive = assignment['is_active'] == true;
      selectedTaskId = assignment['task_definition_id'] as int?;
    }

    try {
      final authState = ref.read(authStateProvider);
      if (authState.token != null) {
        ApiService.setToken(authState.token!);
      }
      availableTasks = await ApiService.getAvailableTaskDefinitions(classId: widget.classId);
      
      if (assignment != null && selectedTaskId != null) {
        final currentTask = {
          'id': selectedTaskId,
          'name': assignment['task_name'] ?? 'المهمة الحالية',
        };
        availableTasks.insert(0, currentTask);
      }
      
      isLoadingTasks = false;
    } catch (e) {
      isLoadingTasks = false;
      print('خطأ في تحميل المهام المتاحة: $e');
    }

    await showDialog(
      context: context,
      builder: (context) => StatefulBuilder(
        builder: (context, setDialogState) => AlertDialog(
          title: Text(assignment == null ? 'إضافة مهمة موكلة' : 'تعديل مهمة موكلة'),
          content: SingleChildScrollView(
            child: Form(
              key: formKey,
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  if (isLoadingTasks)
                    const Padding(
                      padding: EdgeInsets.all(16.0),
                      child: CircularProgressIndicator(),
                    )
                  else
                    DropdownButtonFormField<int>(
                      value: selectedTaskId,
                      decoration: const InputDecoration(labelText: 'المهمة'),
                      items: availableTasks.map((task) {
                        return DropdownMenuItem<int>(
                          value: task['id'] as int?,
                          child: Text(task['name'] ?? 'غير محدد'),
                        );
                      }).toList(),
                      onChanged: (value) {
                        setDialogState(() {
                          selectedTaskId = value;
                        });
                      },
                      validator: (value) => value == null ? 'اختر المهمة' : null,
                    ),
                  const SizedBox(height: 16),
                  TextFormField(
                    controller: orderController,
                    decoration: const InputDecoration(
                      labelText: 'ترتيب المهمة',
                      hintText: '0',
                    ),
                    keyboardType: TextInputType.number,
                    validator: (value) {
                      if (value == null || value.isEmpty) {
                        return 'مطلوب';
                      }
                      if (int.tryParse(value) == null) {
                        return 'يجب أن يكون رقماً';
                      }
                      return null;
                    },
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
                if (formKey.currentState!.validate() && selectedTaskId != null) {
                  try {
                    final authState = ref.read(authStateProvider);
                    if (authState.token != null) {
                      ApiService.setToken(authState.token!);
                    }

                    if (assignment == null) {
                      await ApiService.createTaskAssignment(
                        dailyTaskDefinitionId: selectedTaskId!,
                        order: int.parse(orderController.text),
                        isActive: isActive,
                        classId: widget.classId,
                      );
                    } else {
                      await ApiService.updateTaskAssignment(
                        assignmentId: assignment['id'] as int,
                        dailyTaskDefinitionId: selectedTaskId,
                        order: int.parse(orderController.text),
                        isActive: isActive,
                      );
                    }

                    _loadAssignments();
                    if (context.mounted) {
                      Navigator.pop(context);
                      ScaffoldMessenger.of(context).showSnackBar(
                        SnackBar(
                          content: Text(assignment == null ? 'تم الإضافة بنجاح' : 'تم التحديث بنجاح'),
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
              child: Text(assignment == null ? 'إضافة' : 'تحديث'),
            ),
          ],
        ),
      ),
    );
  }

  Future<void> _showCreateNewTaskDialog(BuildContext context) async {
    final formKey = GlobalKey<FormState>();
    final nameController = TextEditingController();
    final descriptionController = TextEditingController();
    final pointsWeightController = TextEditingController(text: '1');
    final durationMinutesController = TextEditingController();
    final orderController = TextEditingController();
    
    String? selectedType;
    String? selectedTaskLocation;
    bool isActive = true;

    await showDialog(
      context: context,
      builder: (context) => StatefulBuilder(
        builder: (context, setDialogState) => AlertDialog(
          title: const Text('إنشاء مهمة يومية جديدة'),
          content: SingleChildScrollView(
            child: Form(
              key: formKey,
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  TextFormField(
                    controller: nameController,
                    decoration: const InputDecoration(
                      labelText: 'اسم المهمة *',
                      hintText: 'مثال: حفظ سورة الفاتحة',
                    ),
                    validator: (value) => value?.isEmpty ?? true ? 'مطلوب' : null,
                  ),
                  const SizedBox(height: 16),
                  TextFormField(
                    controller: descriptionController,
                    decoration: const InputDecoration(
                      labelText: 'الوصف',
                      hintText: 'وصف تفصيلي للمهمة',
                    ),
                    maxLines: 3,
                  ),
                  const SizedBox(height: 16),
                  DropdownButtonFormField<String>(
                    value: selectedType,
                    decoration: const InputDecoration(labelText: 'نوع المهمة *'),
                    items: const [
                      DropdownMenuItem(value: 'hifz', child: Text('حفظ')),
                      DropdownMenuItem(value: 'murajaah', child: Text('مراجعة')),
                      DropdownMenuItem(value: 'tilawah', child: Text('تلاوة')),
                      DropdownMenuItem(value: 'tajweed', child: Text('تجويد')),
                      DropdownMenuItem(value: 'tafseer', child: Text('تفسير')),
                      DropdownMenuItem(value: 'other', child: Text('أخرى')),
                    ],
                    onChanged: (value) {
                      setDialogState(() {
                        selectedType = value;
                      });
                    },
                    validator: (value) => value == null ? 'اختر نوع المهمة' : null,
                  ),
                  const SizedBox(height: 16),
                  DropdownButtonFormField<String>(
                    value: selectedTaskLocation ?? 'in_class',
                    decoration: const InputDecoration(labelText: 'مكان المهمة'),
                    items: const [
                      DropdownMenuItem(value: 'in_class', child: Text('أثناء الحلقة')),
                      DropdownMenuItem(value: 'homework', child: Text('واجب منزلي')),
                    ],
                    onChanged: (value) {
                      setDialogState(() {
                        selectedTaskLocation = value;
                      });
                    },
                  ),
                  const SizedBox(height: 16),
                  TextFormField(
                    controller: pointsWeightController,
                    decoration: const InputDecoration(
                      labelText: 'وزن النقاط',
                      hintText: '1',
                    ),
                    keyboardType: TextInputType.number,
                    validator: (value) {
                      if (value == null || value.isEmpty) {
                        return 'مطلوب';
                      }
                      final weight = int.tryParse(value);
                      if (weight == null || weight < 1 || weight > 100) {
                        return 'يجب أن يكون رقماً بين 1 و 100';
                      }
                      return null;
                    },
                  ),
                  const SizedBox(height: 16),
                  TextFormField(
                    controller: durationMinutesController,
                    decoration: const InputDecoration(
                      labelText: 'المدة بالدقائق',
                      hintText: '30',
                    ),
                    keyboardType: TextInputType.number,
                    validator: (value) {
                      if (value != null && value.isNotEmpty) {
                        final duration = int.tryParse(value);
                        if (duration == null || duration < 1) {
                          return 'يجب أن يكون رقماً أكبر من 0';
                        }
                      }
                      return null;
                    },
                  ),
                  const SizedBox(height: 16),
                  TextFormField(
                    controller: orderController,
                    decoration: const InputDecoration(
                      labelText: 'ترتيب المهمة',
                      hintText: 'سيتم إضافة آخر ترتيب تلقائياً',
                    ),
                    keyboardType: TextInputType.number,
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
                if (formKey.currentState!.validate() && selectedType != null) {
                  try {
                    final authState = ref.read(authStateProvider);
                    if (authState.token != null) {
                      ApiService.setToken(authState.token!);
                    }

                    await ApiService.createTaskDefinitionAndAssign(
                      name: nameController.text,
                      description: descriptionController.text.isEmpty 
                          ? null 
                          : descriptionController.text,
                      type: selectedType!,
                      taskLocation: selectedTaskLocation,
                      pointsWeight: int.tryParse(pointsWeightController.text),
                      durationMinutes: durationMinutesController.text.isEmpty 
                          ? null 
                          : int.tryParse(durationMinutesController.text),
                      isActive: isActive,
                      order: orderController.text.isEmpty 
                          ? null 
                          : int.tryParse(orderController.text),
                      classId: widget.classId,
                    );

                    _loadAssignments();
                    if (context.mounted) {
                      Navigator.pop(context);
                      ScaffoldMessenger.of(context).showSnackBar(
                        const SnackBar(
                          content: Text('تم إنشاء المهمة وربطها بالفصل بنجاح'),
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
              child: const Text('إنشاء وربط'),
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
              onPressed: _loadAssignments,
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
          child: Row(
            children: [
              Expanded(
                child: ElevatedButton.icon(
                  onPressed: () => _showAddEditTaskDialog(context, null),
                  icon: const Icon(Icons.add_task),
                  label: const Text('ربط مهمة موجودة'),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: AppTokens.infoBlue,
                    foregroundColor: Colors.white,
                    minimumSize: const Size(0, 48),
                  ),
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: ElevatedButton.icon(
                  onPressed: () => _showCreateNewTaskDialog(context),
                  icon: const Icon(Icons.add_circle),
                  label: const Text('مهمة جديدة'),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: AppTokens.successGreen,
                    foregroundColor: Colors.white,
                    minimumSize: const Size(0, 48),
                  ),
                ),
              ),
            ],
          ),
        ),
        
        Expanded(
          child: _assignments.isEmpty
              ? Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Icon(Icons.task_alt, size: 64, color: Colors.grey),
                      const SizedBox(height: 16),
                      Text(
                        'لا توجد مهام موكلة',
                        style: Theme.of(context).textTheme.titleLarge,
                      ),
                    ],
                  ),
                )
              : RefreshIndicator(
                  onRefresh: _loadAssignments,
                  child: ListView.builder(
                    padding: const EdgeInsets.symmetric(horizontal: AppTokens.spacingMD),
                    itemCount: _assignments.length,
                    itemBuilder: (context, index) {
                      final assignment = _assignments[index];
                      return Card(
                        margin: const EdgeInsets.only(bottom: AppTokens.spacingMD),
                        child: ListTile(
                          leading: CircleAvatar(
                            backgroundColor: assignment['is_active'] == true 
                                ? AppTokens.successGreen 
                                : Colors.grey,
                            child: Text(
                              '${assignment['order'] ?? 0}',
                              style: const TextStyle(color: Colors.white),
                            ),
                          ),
                          title: Text(assignment['task_name'] ?? 'غير محدد'),
                          subtitle: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                assignment['is_active'] == true ? 'نشط' : 'غير نشط',
                              ),
                              if (assignment['task_type'] != null)
                                Text(
                                  'نوع: ${assignment['task_type']}',
                                  style: const TextStyle(fontSize: 12),
                                ),
                            ],
                          ),
                          isThreeLine: assignment['task_type'] != null,
                          trailing: Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Switch(
                                value: assignment['is_active'] == true,
                                onChanged: (value) => _toggleTaskAssignment(context, assignment['id'] as int, value),
                                activeColor: AppTokens.successGreen,
                              ),
                              const SizedBox(width: 8),
                              IconButton(
                                icon: Icon(Icons.edit, color: AppTokens.infoBlue),
                                onPressed: () => _showAddEditTaskDialog(context, assignment),
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

