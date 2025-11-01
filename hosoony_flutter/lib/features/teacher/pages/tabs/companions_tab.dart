import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';
import '../../../../core/theme/tokens.dart';
import '../../../../services/auth_service.dart';
import '../../../../services/api_service.dart';

class CompanionsTab extends ConsumerStatefulWidget {
  final int? classId;

  const CompanionsTab({super.key, required this.classId});

  @override
  ConsumerState<CompanionsTab> createState() => _CompanionsTabState();
}

class _CompanionsTabState extends ConsumerState<CompanionsTab> {
  List<Map<String, dynamic>> _publications = [];
  bool _isLoading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _loadPublications();
  }

  Future<void> _loadPublications() async {
    try {
      setState(() {
        _isLoading = true;
        _error = null;
      });

      final authState = ref.read(authStateProvider);
      if (authState.token != null) {
        ApiService.setToken(authState.token!);
      }

      final response = await ApiService.getCompanionsPublications(classId: widget.classId);
      
      if (response['success'] == true) {
        setState(() {
          _publications = List<Map<String, dynamic>>.from(response['publications'] ?? []);
          _isLoading = false;
        });
      } else {
        setState(() {
          _error = response['message'] ?? 'فشل تحميل نشرة الرفيقات';
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
              onPressed: _loadPublications,
              child: const Text('إعادة المحاولة'),
            ),
          ],
        ),
      );
    }

    return Column(
      children: [
        // زر توليد نشرة جديدة
        Padding(
          padding: const EdgeInsets.all(AppTokens.spacingMD),
          child: ElevatedButton.icon(
            onPressed: () => _showGenerateCompanionsDialog(context),
            icon: const Icon(Icons.add_circle),
            label: const Text('توليد رفيقات جديدة'),
            style: ElevatedButton.styleFrom(
              backgroundColor: AppTokens.infoBlue,
              foregroundColor: Colors.white,
              minimumSize: const Size(double.infinity, 48),
            ),
          ),
        ),
        
        Expanded(
          child: _publications.isEmpty
              ? Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Icon(Icons.people, size: 64, color: Colors.grey),
                      const SizedBox(height: 16),
                      Text(
                        'لا توجد نشرة رفيقات',
                        style: Theme.of(context).textTheme.titleLarge,
                      ),
                    ],
                  ),
                )
              : RefreshIndicator(
                  onRefresh: _loadPublications,
                  child: ListView.builder(
                    padding: const EdgeInsets.all(AppTokens.spacingMD),
                    itemCount: _publications.length,
                    itemBuilder: (context, index) {
                      final publication = _publications[index];
                      final isPublished = publication['is_published'] == true || publication['published_at'] != null;
                      final isLocked = publication['is_locked'] == true;
                      
                      return Card(
                        margin: const EdgeInsets.only(bottom: AppTokens.spacingMD),
                        child: ListTile(
                          leading: CircleAvatar(
                            backgroundColor: isPublished
                                ? AppTokens.successGreen 
                                : isLocked
                                    ? AppTokens.warningOrange
                                    : Colors.grey,
                            child: Icon(
                              isPublished 
                                  ? Icons.published_with_changes
                                  : isLocked
                                      ? Icons.lock
                                      : Icons.edit,
                              color: Colors.white,
                            ),
                          ),
                          title: Text('تاريخ: ${_formatDate(publication['target_date'])}'),
                          subtitle: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text('${publication['pairings_count'] ?? 0} مجموعة رفيقات'),
                              Text('${publication['total_students'] ?? 0} طالبة'),
                              if (isPublished && publication['published_at'] != null)
                                Text(
                                  'نشر في: ${publication['published_at']}',
                                  style: const TextStyle(fontSize: 12),
                                ),
                            ],
                          ),
                          trailing: PopupMenuButton<String>(
                            icon: const Icon(Icons.more_vert),
                            onSelected: (value) {
                              switch (value) {
                                case 'view':
                                  _showPublicationDetails(context, publication);
                                  break;
                                case 'generate':
                                  _generateCompanionsForPublication(context, publication);
                                  break;
                                case 'lock':
                                  _lockPublication(context, publication);
                                  break;
                                case 'unlock':
                                  _unlockPublication(context, publication);
                                  break;
                                case 'publish':
                                  _publishPublication(context, publication);
                                  break;
                                case 'delete':
                                  _deletePublication(context, publication);
                                  break;
                              }
                            },
                            itemBuilder: (context) {
                              return [
                                const PopupMenuItem(
                                  value: 'view',
                                  child: Row(
                                    children: [
                                      Icon(Icons.visibility, size: 20),
                                      SizedBox(width: 8),
                                      Text('عرض التفاصيل'),
                                    ],
                                  ),
                                ),
                                if (!isPublished)
                                  PopupMenuItem(
                                    value: publication['pairings_count'] == 0 || publication['pairings_count'] == null ? 'generate' : 'generate',
                                    child: Row(
                                      children: [
                                        Icon(Icons.auto_awesome, size: 20, color: AppTokens.infoBlue),
                                        const SizedBox(width: 8),
                                        Text(publication['pairings_count'] == 0 || publication['pairings_count'] == null 
                                            ? 'توليد الرفيقات' 
                                            : 'إعادة توليد الرفيقات'),
                                      ],
                                    ),
                                  ),
                                if (!isPublished && !isLocked)
                                  const PopupMenuItem(
                                    value: 'lock',
                                    child: Row(
                                      children: [
                                        Icon(Icons.lock, size: 20, color: AppTokens.warningOrange),
                                        SizedBox(width: 8),
                                        Text('قفل النشرة'),
                                      ],
                                    ),
                                  ),
                                if (!isPublished && isLocked)
                                  const PopupMenuItem(
                                    value: 'unlock',
                                    child: Row(
                                      children: [
                                        Icon(Icons.lock_open, size: 20, color: AppTokens.infoBlue),
                                        SizedBox(width: 8),
                                        Text('فتح النشرة'),
                                      ],
                                    ),
                                  ),
                                if (!isPublished && (publication['pairings_count'] ?? 0) > 0)
                                  const PopupMenuItem(
                                    value: 'publish',
                                    child: Row(
                                      children: [
                                        Icon(Icons.publish, size: 20, color: AppTokens.successGreen),
                                        SizedBox(width: 8),
                                        Text('نشر النشرة'),
                                      ],
                                    ),
                                  ),
                                const PopupMenuItem(
                                  value: 'delete',
                                  child: Row(
                                    children: [
                                      Icon(Icons.delete, size: 20, color: Colors.red),
                                      SizedBox(width: 8),
                                      Text('حذف النشرة'),
                                    ],
                                  ),
                                ),
                              ];
                            },
                          ),
                          isThreeLine: true,
                          onTap: () {
                            _showPublicationDetails(context, publication);
                          },
                        ),
                      );
                    },
                  ),
                ),
        ),
      ],
    );
  }

  void _showPublicationDetails(BuildContext context, Map<String, dynamic> publication) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      builder: (context) => DraggableScrollableSheet(
        initialChildSize: 0.7,
        minChildSize: 0.5,
        maxChildSize: 0.9,
        builder: (context, scrollController) => Column(
          children: [
            Container(
              padding: const EdgeInsets.all(AppTokens.spacingMD),
              decoration: BoxDecoration(
                color: AppTokens.primaryGreen,
                borderRadius: const BorderRadius.vertical(top: Radius.circular(16)),
              ),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Expanded(
                    child: Text(
                      'تفاصيل النشرة - ${_formatDate(publication['target_date'])}',
                      style: const TextStyle(
                        color: Colors.white,
                        fontSize: 18,
                        fontWeight: FontWeight.bold,
                      ),
                      overflow: TextOverflow.ellipsis,
                    ),
                  ),
                  IconButton(
                    icon: const Icon(Icons.close, color: Colors.white),
                    onPressed: () => Navigator.pop(context),
                  ),
                ],
              ),
            ),
            Expanded(
              child: ListView(
                controller: scrollController,
                padding: const EdgeInsets.all(AppTokens.spacingMD),
                children: [
                  _buildDetailRow('الحالة', (publication['is_published'] == true || publication['published_at'] != null) ? 'منشورة' : publication['is_locked'] == true ? 'مقفلة' : 'مسودة'),
                  _buildDetailRow('عدد المجموعات', '${publication['pairings_count'] ?? 0}'),
                  _buildDetailRow('عدد الطالبات', '${publication['total_students'] ?? 0}'),
                  if (publication['published_at'] != null)
                    _buildDetailRow('تاريخ النشر', publication['published_at']),
                  const Divider(),
                  const Text(
                    'المجموعات:',
                    style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                  ),
                  const SizedBox(height: 8),
                  ...(publication['pairings'] as List? ?? []).asMap().entries.map((entry) {
                    final index = entry.key;
                    final group = entry.value as List;
                    return Card(
                      margin: const EdgeInsets.only(bottom: 8),
                      child: ListTile(
                        title: Text('المجموعة ${index + 1}'),
                        subtitle: Text('${group.length} طالبة'),
                        trailing: Icon(Icons.group, color: AppTokens.primaryGreen),
                      ),
                    );
                  }).toList(),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildDetailRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 8),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(
            label,
            style: const TextStyle(fontWeight: FontWeight.bold),
          ),
          Text(value),
        ],
      ),
    );
  }

  Future<void> _showGenerateCompanionsDialog(BuildContext context) async {
    final formKey = GlobalKey<FormState>();
    final targetDateController = TextEditingController();
    String? selectedGrouping;
    String? selectedAlgorithm;
    String? selectedAttendanceSource;

    await showDialog(
      context: context,
      builder: (context) => StatefulBuilder(
        builder: (context, setDialogState) => AlertDialog(
          title: const Text('توليد رفيقات جديدة'),
          content: SingleChildScrollView(
            child: Form(
              key: formKey,
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  TextFormField(
                    controller: targetDateController,
                    decoration: const InputDecoration(
                      labelText: 'تاريخ النشرة *',
                      hintText: 'YYYY-MM-DD',
                      helperText: 'يجب أن يكون تاريخاً في المستقبل',
                    ),
                    validator: (value) {
                      if (value == null || value.isEmpty) {
                        return 'مطلوب';
                      }
                      final date = DateTime.tryParse(value);
                      if (date == null) {
                        return 'تاريخ غير صحيح';
                      }
                      if (date.isBefore(DateTime.now().subtract(const Duration(days: 1)))) {
                        return 'يجب أن يكون تاريخاً في المستقبل';
                      }
                      return null;
                    },
                    onTap: () async {
                      final picked = await showDatePicker(
                        context: context,
                        initialDate: DateTime.now().add(const Duration(days: 1)),
                        firstDate: DateTime.now(),
                        lastDate: DateTime.now().add(const Duration(days: 365)),
                      );
                      if (picked != null) {
                        targetDateController.text = '${picked.year}-${picked.month.toString().padLeft(2, '0')}-${picked.day.toString().padLeft(2, '0')}';
                      }
                    },
                  ),
                  const SizedBox(height: 16),
                  DropdownButtonFormField<String>(
                    value: selectedGrouping ?? 'pairs',
                    decoration: const InputDecoration(labelText: 'نوع التجميع *'),
                    items: const [
                      DropdownMenuItem(value: 'pairs', child: Text('ثنائيات')),
                      DropdownMenuItem(value: 'triplets', child: Text('ثلاثيات')),
                    ],
                    onChanged: (value) {
                      setDialogState(() {
                        selectedGrouping = value;
                      });
                    },
                    validator: (value) => value == null ? 'اختر نوع التجميع' : null,
                  ),
                  const SizedBox(height: 16),
                  DropdownButtonFormField<String>(
                    value: selectedAlgorithm ?? 'rotation',
                    decoration: const InputDecoration(labelText: 'خوارزمية التوزيع *'),
                    items: const [
                      DropdownMenuItem(value: 'random', child: Text('عشوائي')),
                      DropdownMenuItem(value: 'rotation', child: Text('تدوير')),
                      DropdownMenuItem(value: 'manual', child: Text('يدوي')),
                    ],
                    onChanged: (value) {
                      setDialogState(() {
                        selectedAlgorithm = value;
                      });
                    },
                    validator: (value) => value == null ? 'اختر الخوارزمية' : null,
                  ),
                  const SizedBox(height: 16),
                  DropdownButtonFormField<String>(
                    value: selectedAttendanceSource ?? 'committed_only',
                    decoration: const InputDecoration(labelText: 'مصدر الحضور *'),
                    items: const [
                      DropdownMenuItem(value: 'all', child: Text('جميع الطالبات')),
                      DropdownMenuItem(value: 'committed_only', child: Text('الملتزمات فقط')),
                    ],
                    onChanged: (value) {
                      setDialogState(() {
                        selectedAttendanceSource = value;
                      });
                    },
                    validator: (value) => value == null ? 'اختر مصدر الحضور' : null,
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
                if (formKey.currentState!.validate()) {
                  try {
                    final authState = ref.read(authStateProvider);
                    if (authState.token != null) {
                      ApiService.setToken(authState.token!);
                    }

                    await ApiService.generateCompanions(
                      targetDate: targetDateController.text,
                      grouping: selectedGrouping!,
                      algorithm: selectedAlgorithm!,
                      attendanceSource: selectedAttendanceSource!,
                      classId: widget.classId,
                    );

                    _loadPublications();
                    if (context.mounted) {
                      Navigator.pop(context);
                      ScaffoldMessenger.of(context).showSnackBar(
                        const SnackBar(
                          content: Text('تم توليد الرفيقات بنجاح'),
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
              child: const Text('توليد'),
            ),
          ],
        ),
      ),
    );
  }

  Future<void> _generateCompanionsForPublication(BuildContext context, Map<String, dynamic> publication) async {
    final confirm = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('إعادة توليد الرفيقات'),
        content: const Text('سيتم استبدال الرفيقات الحالية. هل أنت متأكد؟'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text('إلغاء'),
          ),
          ElevatedButton(
            onPressed: () => Navigator.pop(context, true),
            child: const Text('تأكيد'),
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

        // نحتاج إلى الحصول على الإعدادات من النشرة الحالية
        await ApiService.generateCompanions(
          targetDate: publication['target_date'],
          grouping: 'pairs', // افتراضياً، يمكن تحسينه لاحقاً
          algorithm: 'rotation', // افتراضياً
          attendanceSource: 'committed_only', // افتراضياً
          classId: widget.classId,
        );

        _loadPublications();
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
              content: Text('تم إعادة توليد الرفيقات بنجاح'),
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

  Future<void> _lockPublication(BuildContext context, Map<String, dynamic> publication) async {
    final confirm = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('قفل النشرة'),
        content: const Text('سيتم قفل النشرة لمنع التعديل. هل أنت متأكد؟'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text('إلغاء'),
          ),
          ElevatedButton(
            onPressed: () => Navigator.pop(context, true),
            child: const Text('قفل'),
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

        await ApiService.lockCompanionsPublication(
          publicationId: publication['id'] as int,
        );

        _loadPublications();
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
              content: Text('تم قفل النشرة بنجاح'),
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

  Future<void> _unlockPublication(BuildContext context, Map<String, dynamic> publication) async {
    try {
      final authState = ref.read(authStateProvider);
      if (authState.token != null) {
        ApiService.setToken(authState.token!);
      }

      await ApiService.unlockCompanionsPublication(
        publication['id'] as int,
      );

      _loadPublications();
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('تم فتح النشرة بنجاح'),
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

  Future<void> _publishPublication(BuildContext context, Map<String, dynamic> publication) async {
    final confirm = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('نشر النشرة'),
        content: const Text('سيتم نشر النشرة للطالبات وإرسال الإشعارات. هل أنت متأكد؟'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text('إلغاء'),
          ),
          ElevatedButton(
            onPressed: () => Navigator.pop(context, true),
            style: ElevatedButton.styleFrom(
              backgroundColor: AppTokens.successGreen,
              foregroundColor: Colors.white,
            ),
            child: const Text('نشر'),
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

        await ApiService.publishCompanionsPublication(
          publication['id'] as int,
        );

        _loadPublications();
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
              content: Text('تم نشر النشرة بنجاح'),
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

  Future<void> _deletePublication(BuildContext context, Map<String, dynamic> publication) async {
    final confirm = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('حذف النشرة'),
        content: Text('هل أنت متأكد من حذف نشرة ${_formatDate(publication['target_date'])}؟'),
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

        await ApiService.deleteCompanionsPublication(
          publication['id'] as int,
        );

        _loadPublications();
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
              content: Text('تم حذف النشرة بنجاح'),
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

  String _formatDate(dynamic dateValue) {
    if (dateValue == null) return 'غير محدد';
    
    try {
      DateTime date;
      if (dateValue is String) {
        date = DateTime.parse(dateValue);
      } else if (dateValue is DateTime) {
        date = dateValue;
      } else {
        return dateValue.toString();
      }
      
      return DateFormat('yyyy-MM-dd', 'ar').format(date);
    } catch (e) {
      return dateValue.toString();
    }
  }
}

