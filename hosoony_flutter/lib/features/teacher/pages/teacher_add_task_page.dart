import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/router/app_router.dart';
import '../../../core/theme/tokens.dart';
import '../../../services/auth_service.dart';

class TeacherAddTaskPage extends ConsumerStatefulWidget {
  const TeacherAddTaskPage({super.key});

  @override
  ConsumerState<TeacherAddTaskPage> createState() => _TeacherAddTaskPageState();
}

class _TeacherAddTaskPageState extends ConsumerState<TeacherAddTaskPage>
    with TickerProviderStateMixin {
  late AnimationController _animationController;
  late Animation<double> _fadeAnimation;
  late Animation<double> _slideAnimation;
  
  final _formKey = GlobalKey<FormState>();
  final _titleController = TextEditingController();
  final _descriptionController = TextEditingController();
  final _instructionsController = TextEditingController();
  
  String _selectedLevel = 'مبتدئة';
  String _selectedType = 'قراءة';
  DateTime _selectedDate = DateTime.now();
  TimeOfDay _selectedTime = TimeOfDay.now();
  int _estimatedDuration = 30;
  bool _isRequired = true;
  bool _isSubmitting = false;

  final List<String> _levels = ['مبتدئة', 'متوسطة', 'متقدمة'];
  final List<String> _taskTypes = [
    'قراءة',
    'حفظ',
    'مراجعة',
    'تطبيق',
    'اختبار',
    'مشروع',
  ];

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

    _animationController.forward();
  }

  @override
  void dispose() {
    _animationController.dispose();
    _titleController.dispose();
    _descriptionController.dispose();
    _instructionsController.dispose();
    super.dispose();
  }

  Future<void> _submitTask() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() {
      _isSubmitting = true;
    });

    try {
      // Simulate API call
      await Future.delayed(const Duration(seconds: 2));
      
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('تم إضافة المهمة بنجاح'),
            backgroundColor: AppTokens.successGreen,
          ),
        );
        
        // Clear form
        _titleController.clear();
        _descriptionController.clear();
        _instructionsController.clear();
        setState(() {
          _selectedLevel = 'مبتدئة';
          _selectedType = 'قراءة';
          _selectedDate = DateTime.now();
          _selectedTime = TimeOfDay.now();
          _estimatedDuration = 30;
          _isRequired = true;
        });
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('خطأ في إضافة المهمة: $e'),
            backgroundColor: AppTokens.errorRed,
          ),
        );
      }
    } finally {
      if (mounted) {
        setState(() {
          _isSubmitting = false;
        });
      }
    }
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
              'إضافة مهمة يومية',
              style: TextStyle(
                fontFamily: AppTokens.primaryFontFamily,
                fontWeight: AppTokens.fontWeightBold,
              ),
            ),
          ],
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.logout),
            onPressed: () async {
              await ref.read(authStateProvider.notifier).logout();
              AppRouter.goToLogin(context);
            },
          ),
        ],
      ),
      body: AnimatedBuilder(
        animation: _animationController,
        builder: (context, child) {
          return FadeTransition(
            opacity: _fadeAnimation,
            child: SlideTransition(
              position: Tween<Offset>(
                begin: const Offset(0, 0.3),
                end: Offset.zero,
              ).animate(_slideAnimation),
              child: SingleChildScrollView(
                padding: const EdgeInsets.all(AppTokens.spacingMD),
                child: Form(
                  key: _formKey,
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // Header
                      Container(
                        width: double.infinity,
                        padding: const EdgeInsets.all(AppTokens.spacingLG),
                        decoration: BoxDecoration(
                          gradient: LinearGradient(
                            colors: [
                              AppTokens.primaryGreen.withValues(alpha: 0.1),
                              AppTokens.primaryGreen.withValues(alpha: 0.05),
                            ],
                            begin: Alignment.topLeft,
                            end: Alignment.bottomRight,
                          ),
                          borderRadius: BorderRadius.circular(AppTokens.radiusLG),
                          border: Border.all(
                            color: AppTokens.primaryGreen.withValues(alpha: 0.2),
                          ),
                        ),
                        child: Column(
                          children: [
                            Icon(
                              Icons.task_alt,
                              size: 48,
                              color: AppTokens.primaryGreen,
                            ),
                            const SizedBox(height: AppTokens.spacingSM),
                            Text(
                              'إضافة مهمة يومية جديدة',
                              style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                                fontFamily: AppTokens.primaryFontFamily,
                                fontWeight: AppTokens.fontWeightBold,
                                color: AppTokens.primaryGreen,
                              ),
                            ),
                            const SizedBox(height: AppTokens.spacingXS),
                            Text(
                              'أضف مهمة جديدة للطالبات مع التفاصيل المطلوبة',
                              style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                                color: AppTokens.neutralMedium,
                              ),
                              textAlign: TextAlign.center,
                            ),
                          ],
                        ),
                      ),
                      
                      const SizedBox(height: AppTokens.spacingLG),
                      
                      // Task Title
                      Text(
                        'عنوان المهمة',
                        style: Theme.of(context).textTheme.titleMedium?.copyWith(
                          fontFamily: AppTokens.primaryFontFamily,
                          fontWeight: AppTokens.fontWeightBold,
                        ),
                      ),
                      const SizedBox(height: AppTokens.spacingSM),
                      TextFormField(
                        controller: _titleController,
                        decoration: InputDecoration(
                          hintText: 'أدخل عنوان المهمة',
                          prefixIcon: const Icon(Icons.title),
                          border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(AppTokens.radiusMD),
                          ),
                        ),
                        validator: (value) {
                          if (value == null || value.isEmpty) {
                            return 'يرجى إدخال عنوان المهمة';
                          }
                          return null;
                        },
                      ),
                      
                      const SizedBox(height: AppTokens.spacingLG),
                      
                      // Task Description
                      Text(
                        'وصف المهمة',
                        style: Theme.of(context).textTheme.titleMedium?.copyWith(
                          fontFamily: AppTokens.primaryFontFamily,
                          fontWeight: AppTokens.fontWeightBold,
                        ),
                      ),
                      const SizedBox(height: AppTokens.spacingSM),
                      TextFormField(
                        controller: _descriptionController,
                        maxLines: 3,
                        decoration: InputDecoration(
                          hintText: 'أدخل وصف مفصل للمهمة',
                          prefixIcon: const Icon(Icons.description),
                          border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(AppTokens.radiusMD),
                          ),
                        ),
                        validator: (value) {
                          if (value == null || value.isEmpty) {
                            return 'يرجى إدخال وصف المهمة';
                          }
                          return null;
                        },
                      ),
                      
                      const SizedBox(height: AppTokens.spacingLG),
                      
                      // Task Instructions
                      Text(
                        'تعليمات المهمة',
                        style: Theme.of(context).textTheme.titleMedium?.copyWith(
                          fontFamily: AppTokens.primaryFontFamily,
                          fontWeight: AppTokens.fontWeightBold,
                        ),
                      ),
                      const SizedBox(height: AppTokens.spacingSM),
                      TextFormField(
                        controller: _instructionsController,
                        maxLines: 4,
                        decoration: InputDecoration(
                          hintText: 'أدخل التعليمات التفصيلية للطالبات',
                          prefixIcon: const Icon(Icons.list_alt),
                          border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(AppTokens.radiusMD),
                          ),
                        ),
                        validator: (value) {
                          if (value == null || value.isEmpty) {
                            return 'يرجى إدخال تعليمات المهمة';
                          }
                          return null;
                        },
                      ),
                      
                      const SizedBox(height: AppTokens.spacingLG),
                      
                      // Task Details Row
                      Row(
                        children: [
                          // Level
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  'المستوى',
                                  style: Theme.of(context).textTheme.titleMedium?.copyWith(
                                    fontFamily: AppTokens.primaryFontFamily,
                                    fontWeight: AppTokens.fontWeightBold,
                                  ),
                                ),
                                const SizedBox(height: AppTokens.spacingSM),
                                DropdownButtonFormField<String>(
                                  value: _selectedLevel,
                                  decoration: InputDecoration(
                                    border: OutlineInputBorder(
                                      borderRadius: BorderRadius.circular(AppTokens.radiusMD),
                                    ),
                                  ),
                                  items: _levels.map((level) {
                                    return DropdownMenuItem(
                                      value: level,
                                      child: Text(level),
                                    );
                                  }).toList(),
                                  onChanged: (value) {
                                    setState(() {
                                      _selectedLevel = value!;
                                    });
                                  },
                                ),
                              ],
                            ),
                          ),
                          
                          const SizedBox(width: AppTokens.spacingMD),
                          
                          // Type
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  'نوع المهمة',
                                  style: Theme.of(context).textTheme.titleMedium?.copyWith(
                                    fontFamily: AppTokens.primaryFontFamily,
                                    fontWeight: AppTokens.fontWeightBold,
                                  ),
                                ),
                                const SizedBox(height: AppTokens.spacingSM),
                                DropdownButtonFormField<String>(
                                  value: _selectedType,
                                  decoration: InputDecoration(
                                    border: OutlineInputBorder(
                                      borderRadius: BorderRadius.circular(AppTokens.radiusMD),
                                    ),
                                  ),
                                  items: _taskTypes.map((type) {
                                    return DropdownMenuItem(
                                      value: type,
                                      child: Text(type),
                                    );
                                  }).toList(),
                                  onChanged: (value) {
                                    setState(() {
                                      _selectedType = value!;
                                    });
                                  },
                                ),
                              ],
                            ),
                          ),
                        ],
                      ),
                      
                      const SizedBox(height: AppTokens.spacingLG),
                      
                      // Date and Time Row
                      Row(
                        children: [
                          // Date
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  'تاريخ المهمة',
                                  style: Theme.of(context).textTheme.titleMedium?.copyWith(
                                    fontFamily: AppTokens.primaryFontFamily,
                                    fontWeight: AppTokens.fontWeightBold,
                                  ),
                                ),
                                const SizedBox(height: AppTokens.spacingSM),
                                InkWell(
                                  onTap: () async {
                                    final date = await showDatePicker(
                                      context: context,
                                      initialDate: _selectedDate,
                                      firstDate: DateTime.now(),
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
                              ],
                            ),
                          ),
                          
                          const SizedBox(width: AppTokens.spacingMD),
                          
                          // Time
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  'وقت المهمة',
                                  style: Theme.of(context).textTheme.titleMedium?.copyWith(
                                    fontFamily: AppTokens.primaryFontFamily,
                                    fontWeight: AppTokens.fontWeightBold,
                                  ),
                                ),
                                const SizedBox(height: AppTokens.spacingSM),
                                InkWell(
                                  onTap: () async {
                                    final time = await showTimePicker(
                                      context: context,
                                      initialTime: _selectedTime,
                                    );
                                    if (time != null) {
                                      setState(() {
                                        _selectedTime = time;
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
                                        const Icon(Icons.access_time),
                                        const SizedBox(width: AppTokens.spacingSM),
                                        Text(_selectedTime.format(context)),
                                      ],
                                    ),
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ],
                      ),
                      
                      const SizedBox(height: AppTokens.spacingLG),
                      
                      // Duration and Required
                      Row(
                        children: [
                          // Duration
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  'المدة المتوقعة (دقيقة)',
                                  style: Theme.of(context).textTheme.titleMedium?.copyWith(
                                    fontFamily: AppTokens.primaryFontFamily,
                                    fontWeight: AppTokens.fontWeightBold,
                                  ),
                                ),
                                const SizedBox(height: AppTokens.spacingSM),
                                Slider(
                                  value: _estimatedDuration.toDouble(),
                                  min: 15,
                                  max: 120,
                                  divisions: 21,
                                  label: '$_estimatedDuration دقيقة',
                                  onChanged: (value) {
                                    setState(() {
                                      _estimatedDuration = value.round();
                                    });
                                  },
                                ),
                              ],
                            ),
                          ),
                          
                          const SizedBox(width: AppTokens.spacingMD),
                          
                          // Required
                          Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                'مهمة إجبارية',
                                style: Theme.of(context).textTheme.titleMedium?.copyWith(
                                  fontFamily: AppTokens.primaryFontFamily,
                                  fontWeight: AppTokens.fontWeightBold,
                                ),
                              ),
                              const SizedBox(height: AppTokens.spacingSM),
                              Switch(
                                value: _isRequired,
                                onChanged: (value) {
                                  setState(() {
                                    _isRequired = value;
                                  });
                                },
                                activeColor: AppTokens.primaryGreen,
                              ),
                            ],
                          ),
                        ],
                      ),
                      
                      const SizedBox(height: AppTokens.spacingXL),
                      
                      // Submit Button
                      SizedBox(
                        width: double.infinity,
                        height: 56,
                        child: ElevatedButton(
                          onPressed: _isSubmitting ? null : _submitTask,
                          style: ElevatedButton.styleFrom(
                            backgroundColor: AppTokens.primaryGreen,
                            foregroundColor: AppTokens.neutralWhite,
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(AppTokens.radiusMD),
                            ),
                          ),
                          child: _isSubmitting
                              ? const SizedBox(
                                  width: 24,
                                  height: 24,
                                  child: CircularProgressIndicator(
                                    strokeWidth: 2,
                                    valueColor: AlwaysStoppedAnimation<Color>(AppTokens.neutralWhite),
                                  ),
                                )
                              : Row(
                                  mainAxisAlignment: MainAxisAlignment.center,
                                  children: [
                                    const Icon(Icons.add_task),
                                    const SizedBox(width: AppTokens.spacingSM),
                                    Text(
                                      'إضافة المهمة',
                                      style: TextStyle(
                                        fontFamily: AppTokens.primaryFontFamily,
                                        fontSize: AppTokens.fontSizeLG,
                                        fontWeight: AppTokens.fontWeightBold,
                                      ),
                                    ),
                                  ],
                                ),
                        ),
                      ),
                      
                      const SizedBox(height: AppTokens.spacingXL),
                    ],
                  ),
                ),
              ),
            ),
          );
        },
      ),
    );
  }
}





