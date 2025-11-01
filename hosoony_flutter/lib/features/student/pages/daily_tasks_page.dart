import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../../core/router/app_router.dart';
import '../../../core/theme/tokens.dart';
import '../../../services/auth_service.dart';
import '../../../services/api_service.dart';

class StudentDailyTasksPage extends ConsumerStatefulWidget {
  const StudentDailyTasksPage({super.key});

  @override
  ConsumerState<StudentDailyTasksPage> createState() => _StudentDailyTasksPageState();
}

class _StudentDailyTasksPageState extends ConsumerState<StudentDailyTasksPage>
    with TickerProviderStateMixin {
  late AnimationController _animationController;
  late Animation<double> _fadeAnimation;
  late Animation<Offset> _slideAnimation;
  
  List<Map<String, dynamic>> _dailyTasks = [];
  bool _isLoading = true;
  String? _error;

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

    _loadDailyTasks();
  }

  @override
  void dispose() {
    _animationController.dispose();
    super.dispose();
  }

  Future<void> _loadDailyTasks() async {
    try {
      setState(() {
        _isLoading = true;
        _error = null;
      });

      final authState = ref.read(authStateProvider);
      if (authState.user?.id != null) {
        // Ensure token is set before making API calls
        if (authState.token != null) {
          ApiService.setToken(authState.token!);
        }
        
        final response = await ApiService.getDailyTasks(authState.user!.id.toString());
        
        // Handle new API response structure
        final tasks = response['tasks'] ?? [];
        
        setState(() {
          _dailyTasks = List<Map<String, dynamic>>.from(tasks);
          _isLoading = false;
        });
        _animationController.forward();
      } else {
        setState(() {
          _error = 'لم يتم العثور على بيانات المستخدم';
          _isLoading = false;
        });
      }
    } catch (e) {
      setState(() {
        // Handle different types of errors
        if (e.toString().contains('422')) {
          _error = 'لا توجد مهام متاحة لهذا الطالب';
        } else if (e.toString().contains('401')) {
          _error = 'انتهت صلاحية الجلسة، يرجى تسجيل الدخول مرة أخرى';
        } else {
          _error = 'خطأ في تحميل المهام: ${e.toString()}';
        }
        _isLoading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppTokens.neutralLight,
      appBar: AppBar(
        backgroundColor: AppTokens.primaryBrown,
        foregroundColor: AppTokens.neutralWhite,
        title: const Text('المهام اليومية'),
        leading: IconButton(
          icon: const Icon(Icons.arrow_back),
          onPressed: () => context.go('/student/home'),
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _loadDailyTasks,
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
            Text('جاري تحميل المهام...'),
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
              'خطأ في تحميل المهام',
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
                _loadDailyTasks();
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

    if (_dailyTasks.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              Icons.assignment_outlined,
              size: 64,
              color: Colors.grey[400],
            ),
            const SizedBox(height: 16),
            Text(
              'لا توجد مهام اليوم',
              style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                color: Colors.grey[600],
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 8),
            Text(
              'ستظهر المهام الجديدة هنا',
              style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                color: Colors.grey[500],
              ),
            ),
          ],
        ),
      );
    }

    return SingleChildScrollView(
      physics: const AlwaysScrollableScrollPhysics(),
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Header Section
          AnimatedBuilder(
            animation: _animationController,
            builder: (context, child) {
              return FadeTransition(
                opacity: _fadeAnimation,
                child: SlideTransition(
                  position: _slideAnimation,
                  child: Container(
                    width: double.infinity,
                    padding: const EdgeInsets.all(20),
                    decoration: BoxDecoration(
                      gradient: AppTokens.primaryGradient,
                      borderRadius: BorderRadius.circular(16),
                      boxShadow: AppTokens.shadowLG,
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: [
                            Container(
                              width: 60,
                              height: 60,
                              decoration: BoxDecoration(
                                color: AppTokens.neutralWhite.withOpacity(0.2),
                                borderRadius: BorderRadius.circular(30),
                              ),
                              child: const Icon(
                                Icons.assignment,
                                color: AppTokens.neutralWhite,
                                size: 30,
                              ),
                            ),
                            const SizedBox(width: 16),
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    'مهام اليوم',
                                    style: Theme.of(context).textTheme.titleMedium?.copyWith(
                                      color: AppTokens.neutralWhite.withOpacity(0.8),
                                    ),
                                  ),
                                  Text(
                                    '${_dailyTasks.length} مهمة',
                                    style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                                      color: AppTokens.neutralWhite,
                                      fontWeight: FontWeight.bold,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 16),
                        Row(
                          children: [
                            Expanded(
                              child: _buildStatCard(
                                'مكتملة',
                                '${_dailyTasks.where((task) => task['completed'] == true).length}',
                                Icons.check_circle,
                                AppTokens.successGreen,
                              ),
                            ),
                            const SizedBox(width: 12),
                            Expanded(
                              child: _buildStatCard(
                                'متبقية',
                                '${_dailyTasks.where((task) => task['completed'] != true).length}',
                                Icons.pending,
                                AppTokens.warningOrange,
                              ),
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),
                ),
              );
            },
          ),
          
          const SizedBox(height: 24),
          
          // Tasks List
          Text(
            'قائمة المهام',
            style: Theme.of(context).textTheme.titleLarge?.copyWith(
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(height: 16),
          
          // Check if all tasks are completed - if so, hide the entire day
          Builder(
            builder: (context) {
              final allTasksCompleted = _dailyTasks.isNotEmpty && 
                _dailyTasks.every((t) => 
                  t['completed'] == true || t['status'] == 'completed'
                );
              
              // Hide the entire day only if all tasks are completed
              if (allTasksCompleted) {
                return Center(
                  child: Padding(
                    padding: const EdgeInsets.all(32.0),
                    child: Column(
                      children: [
                        Icon(
                          Icons.check_circle_outline,
                          size: 64,
                          color: AppTokens.successGreen,
                        ),
                        const SizedBox(height: 16),
                        Text(
                          'جميع المهام مكتملة لهذا اليوم',
                          style: Theme.of(context).textTheme.titleMedium?.copyWith(
                            color: AppTokens.successGreen,
                          ),
                        ),
                      ],
                    ),
                  ),
                );
              }
              
              // Show all tasks (incomplete tasks remain visible)
              return Column(
                children: _dailyTasks.asMap().entries.map((entry) {
                  final index = entry.key;
                  final task = entry.value;
                  
                  return AnimatedBuilder(
              animation: _animationController,
              builder: (context, child) {
                return FadeTransition(
                  opacity: _fadeAnimation,
                  child: SlideTransition(
                    position: Tween<Offset>(
                      begin: Offset(0, 0.3 + (index * 0.1)),
                      end: Offset.zero,
                    ).animate(CurvedAnimation(
                      parent: _animationController,
                      curve: Interval(
                        index * 0.1,
                        1.0,
                        curve: Curves.easeOutCubic,
                      ),
                    )),
                    child: Padding(
                      padding: const EdgeInsets.only(bottom: 12),
                      child: _buildTaskCard(task),
                    ),
                  ),
                );
              },
            );
                }).toList(),
              );
            },
          ),
        ],
      ),
    );
  }

  Widget _buildStatCard(String title, String value, IconData icon, Color color) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: AppTokens.neutralWhite,
        borderRadius: BorderRadius.circular(12),
        boxShadow: AppTokens.shadowMD,
      ),
      child: Column(
        children: [
          Icon(icon, color: color, size: 24),
          const SizedBox(height: 8),
          Text(
            value,
            style: TextStyle(
              fontSize: 20,
              fontWeight: FontWeight.bold,
              color: color,
            ),
          ),
          const SizedBox(height: 4),
          Text(
            title,
            style: TextStyle(
              fontSize: 12,
              color: Colors.grey[600],
            ),
            textAlign: TextAlign.center,
          ),
        ],
      ),
    );
  }

  Widget _buildTaskCard(Map<String, dynamic> task) {
    final isCompleted = task['completed'] == true || task['status'] == 'completed';
    final taskType = task['task_type'] ?? task['type'] ?? 'general';
    final title = task['task_name'] ?? task['name'] ?? task['title'] ?? 'مهمة غير محددة';
    // ⭐ استخدام weekly_task_details إن وُجدت، وإلا task_key
    final weeklyDetails = task['weekly_task_details'];
    final description = weeklyDetails != null && weeklyDetails.toString().isNotEmpty
        ? weeklyDetails.toString()
        : (task['task_key'] ?? task['description'] ?? task['notes'] ?? 'لا يوجد وصف');
    final points = task['points_weight'] ?? task['points'] ?? 0;
    final duration = task['duration_minutes'] ?? 0;
    final location = task['task_location'] ?? task['location'] ?? 'unknown';
    final isHomework = location.toLowerCase() == 'homework';
    
    return Card(
      elevation: isHomework ? 1 : 2, // Lower elevation for homework
      color: isHomework ? Colors.grey[50] : null, // Light grey background for homework
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Row(
          children: [
            Container(
              width: 50,
              height: 50,
              decoration: BoxDecoration(
                color: isCompleted 
                    ? AppTokens.successGreen 
                    : (isHomework 
                        ? Colors.blue[50] // Light blue background for homework
                        : _getTaskColor(taskType).withOpacity(0.1)),
                borderRadius: BorderRadius.circular(25),
                border: isHomework && !isCompleted 
                    ? Border.all(color: Colors.blue[200]!, width: 1.5) 
                    : null, // Light blue border for homework
              ),
              child: Icon(
                isCompleted ? Icons.check : _getTaskIcon(taskType),
                color: isCompleted 
                    ? AppTokens.neutralWhite 
                    : (isHomework 
                        ? Colors.blue[400]! // Blue color for homework icons
                        : _getTaskColor(taskType)),
                size: 24,
              ),
            ),
            
            const SizedBox(width: 16),
            
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    title,
                    style: Theme.of(context).textTheme.titleMedium?.copyWith(
                      fontWeight: FontWeight.bold,
                      decoration: isCompleted ? TextDecoration.lineThrough : null,
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    description,
                    style: Theme.of(context).textTheme.bodySmall?.copyWith(
                      color: Colors.grey[600],
                    ),
                  ),
                  const SizedBox(height: 8),
                  Row(
                    children: [
                      Icon(
                        Icons.star,
                        size: 16,
                        color: AppTokens.primaryGold,
                      ),
                      const SizedBox(width: 4),
                      Text(
                        '$points نقطة',
                        style: Theme.of(context).textTheme.bodySmall?.copyWith(
                          color: AppTokens.primaryGold,
                          fontWeight: FontWeight.w500,
                        ),
                      ),
                      const SizedBox(width: 16),
                      Icon(
                        Icons.access_time,
                        size: 16,
                        color: Colors.grey[500],
                      ),
                      const SizedBox(width: 4),
                      Text(
                        '$duration دقيقة',
                        style: Theme.of(context).textTheme.bodySmall?.copyWith(
                          color: Colors.grey[500],
                        ),
                      ),
                      const SizedBox(width: 16),
                      Icon(
                        _getLocationIcon(location),
                        size: 16,
                        color: Colors.grey[500],
                      ),
                      const SizedBox(width: 4),
                      Text(
                        _getLocationText(location),
                        style: Theme.of(context).textTheme.bodySmall?.copyWith(
                          color: Colors.grey[500],
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
            
            const SizedBox(width: 16),
            
            // Task completion button
            GestureDetector(
              onTap: () => _toggleTaskCompletion(task),
              child: Container(
                width: 40,
                height: 40,
                decoration: BoxDecoration(
                  color: isCompleted ? AppTokens.successGreen : AppTokens.neutralLight,
                  borderRadius: BorderRadius.circular(20),
                  border: Border.all(
                    color: isCompleted ? AppTokens.successGreen : Colors.grey[400]!,
                    width: 2,
                  ),
                ),
                child: Icon(
                  isCompleted ? Icons.check : Icons.add,
                  color: isCompleted ? AppTokens.neutralWhite : Colors.grey[600],
                  size: 24,
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Color _getTaskColor(String taskType) {
    switch (taskType.toLowerCase()) {
      case 'hifz':
        return AppTokens.primaryGreen;
      case 'murajaah':
        return AppTokens.primaryGold;
      case 'tajweed':
        return AppTokens.primaryBrown;
      case 'general':
      default:
        return AppTokens.neutralMedium;
    }
  }

  IconData _getTaskIcon(String taskType) {
    switch (taskType.toLowerCase()) {
      case 'hifz':
        return Icons.book;
      case 'murajaah':
        return Icons.repeat;
      case 'tajweed':
        return Icons.record_voice_over;
      case 'general':
      default:
        return Icons.assignment;
    }
  }

  IconData _getLocationIcon(String location) {
    switch (location.toLowerCase()) {
      case 'in_class':
        return Icons.school;
      case 'homework':
      case 'home':
        return Icons.home;
      case 'mosque':
        return Icons.mosque;
      default:
        return Icons.location_on;
    }
  }

  String _getLocationText(String location) {
    switch (location.toLowerCase()) {
      case 'in_class':
        return 'أثناء الحلقة';
      case 'homework':
        return 'واجب منزلي';
      case 'home':
        return 'في المنزل';
      case 'mosque':
        return 'في المسجد';
      default:
        return 'غير محدد';
    }
  }

  void _toggleTaskCompletion(Map<String, dynamic> task) {
    setState(() {
      task['completed'] = !(task['completed'] == true);
    });
    
    // يمكن إضافة API call هنا لتحديث حالة المهمة
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(
          task['completed'] == true 
            ? 'تم إكمال المهمة!' 
            : 'تم إلغاء إكمال المهمة'
        ),
        backgroundColor: task['completed'] == true 
          ? AppTokens.successGreen 
          : AppTokens.neutralMedium,
      ),
    );
  }
}