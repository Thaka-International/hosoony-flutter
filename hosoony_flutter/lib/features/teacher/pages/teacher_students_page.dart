import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/router/app_router.dart';
import '../../../core/theme/tokens.dart';
import '../../../services/auth_service.dart';
import '../../../services/api_service.dart';

class TeacherStudentsPage extends ConsumerStatefulWidget {
  const TeacherStudentsPage({super.key});

  @override
  ConsumerState<TeacherStudentsPage> createState() => _TeacherStudentsPageState();
}

class _TeacherStudentsPageState extends ConsumerState<TeacherStudentsPage>
    with TickerProviderStateMixin {
  late AnimationController _animationController;
  late Animation<double> _fadeAnimation;
  late Animation<Offset> _slideAnimation;
  
  List<Map<String, dynamic>> _students = [];
  bool _isLoading = true;
  String? _error;
  String _searchQuery = '';
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

    _slideAnimation = Tween<Offset>(
      begin: const Offset(0, -0.3),
      end: Offset.zero,
    ).animate(CurvedAnimation(
      parent: _animationController,
      curve: Curves.easeOutCubic,
    ));

    _loadStudents();
  }

  @override
  void dispose() {
    _animationController.dispose();
    super.dispose();
  }

  Future<void> _loadStudents() async {
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

      // جلب الطلاب من API
      final students = await ApiService.getClassStudents();
      
      setState(() {
        _students = students.map((student) {
          // تحويل البيانات من API إلى التنسيق المطلوب
          return {
            'id': student['id'],
            'name': student['name'] ?? 'غير محدد',
            'email': student['email'] ?? '',
            'level': 'مبتدئة', // يمكن إضافتها لاحقاً من API
            'progress': student['progress'] ?? 0,
            'tasks_completed': student['tasks_completed'] ?? 0,
            'total_tasks': student['total_tasks'] ?? 0,
            'last_activity': student['last_activity'] ?? 'لا يوجد نشاط',
            'status': student['status'] ?? 'active',
            'avatar': null,
          };
        }).toList();
        _isLoading = false;
      });
      
      _animationController.forward();
    } catch (e) {
      setState(() {
        _error = 'خطأ في تحميل الطالبات: ${e.toString()}';
        _isLoading = false;
      });
      print('خطأ في تحميل الطالبات: $e');
    }
  }

  List<Map<String, dynamic>> get _filteredStudents {
    var filtered = _students.where((student) {
      final matchesSearch = student['name'].toString().toLowerCase()
          .contains(_searchQuery.toLowerCase());
      final matchesFilter = _selectedFilter == 'all' || 
          student['status'] == _selectedFilter;
      return matchesSearch && matchesFilter;
    }).toList();
    
    return filtered;
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
              'إدارة الطالبات',
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
              _showAddStudentDialog();
            },
          ),
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _loadStudents,
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
                        onPressed: _loadStudents,
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
                    // Search and Filter Section
                    Container(
                      padding: const EdgeInsets.all(AppTokens.spacingMD),
                      color: AppTokens.neutralWhite,
                      child: Column(
                        children: [
                          // Search Bar
                          TextField(
                            onChanged: (value) {
                              setState(() {
                                _searchQuery = value;
                              });
                            },
                            decoration: InputDecoration(
                              hintText: 'البحث عن طالبة...',
                              prefixIcon: const Icon(Icons.search),
                              border: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(AppTokens.radiusMD),
                              ),
                              filled: true,
                              fillColor: AppTokens.neutralLight,
                            ),
                          ),
                          
                          const SizedBox(height: AppTokens.spacingMD),
                          
                          // Filter Chips
                          Row(
                            children: [
                              _buildFilterChip('الكل', 'all'),
                              const SizedBox(width: AppTokens.spacingSM),
                              _buildFilterChip('نشط', 'active'),
                              const SizedBox(width: AppTokens.spacingSM),
                              _buildFilterChip('غير نشط', 'inactive'),
                            ],
                          ),
                        ],
                      ),
                    ),
                    
                    // Students List
                    Expanded(
                      child: AnimatedBuilder(
                        animation: _animationController,
                        builder: (context, child) {
                          return FadeTransition(
                            opacity: _fadeAnimation,
                            child: SlideTransition(
                              position: _slideAnimation,
                              child: _buildStudentsList(),
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

  Widget _buildStudentsList() {
    if (_filteredStudents.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              Icons.people_outline,
              size: 64,
              color: AppTokens.neutralGray,
            ),
            const SizedBox(height: 16),
            Text(
              'لا توجد طالبات',
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
      itemCount: _filteredStudents.length,
      itemBuilder: (context, index) {
        final student = _filteredStudents[index];
        return _buildStudentCard(student);
      },
    );
  }

  Widget _buildStudentCard(Map<String, dynamic> student) {
    final isActive = student['status'] == 'active';
    
    return Card(
      margin: const EdgeInsets.only(bottom: AppTokens.spacingMD),
      child: Padding(
        padding: const EdgeInsets.all(AppTokens.spacingMD),
        child: Row(
          children: [
            // Avatar
            CircleAvatar(
              radius: 30,
              backgroundColor: isActive 
                  ? AppTokens.successGreen.withValues(alpha: 0.1)
                  : AppTokens.neutralGray.withValues(alpha: 0.1),
              child: Icon(
                Icons.person,
                color: isActive ? AppTokens.successGreen : AppTokens.neutralGray,
                size: AppTokens.iconSizeLG,
              ),
            ),
            
            const SizedBox(width: AppTokens.spacingMD),
            
            // Student Info
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Expanded(
                        child: Text(
                          student['name'],
                          style: Theme.of(context).textTheme.titleMedium?.copyWith(
                            fontWeight: AppTokens.fontWeightBold,
                          ),
                        ),
                      ),
                      Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: AppTokens.spacingSM,
                          vertical: AppTokens.spacingXS,
                        ),
                        decoration: BoxDecoration(
                          color: isActive 
                              ? AppTokens.successGreen.withValues(alpha: 0.1)
                              : AppTokens.neutralGray.withValues(alpha: 0.1),
                          borderRadius: BorderRadius.circular(AppTokens.radiusSM),
                        ),
                        child: Text(
                          isActive ? 'نشط' : 'غير نشط',
                          style: TextStyle(
                            color: isActive ? AppTokens.successGreen : AppTokens.neutralGray,
                            fontSize: AppTokens.fontSizeXS,
                            fontWeight: AppTokens.fontWeightMedium,
                          ),
                        ),
                      ),
                    ],
                  ),
                  
                  const SizedBox(height: AppTokens.spacingXS),
                  
                  Text(
                    student['email'],
                    style: Theme.of(context).textTheme.bodySmall?.copyWith(
                      color: AppTokens.neutralMedium,
                    ),
                  ),
                  
                  const SizedBox(height: AppTokens.spacingXS),
                  
                  Row(
                    children: [
                      Text(
                        'المستوى: ${student['level']}',
                        style: Theme.of(context).textTheme.bodySmall?.copyWith(
                          color: AppTokens.neutralMedium,
                        ),
                      ),
                      const SizedBox(width: AppTokens.spacingMD),
                      Text(
                        'آخر نشاط: ${student['last_activity']}',
                        style: Theme.of(context).textTheme.bodySmall?.copyWith(
                          color: AppTokens.neutralMedium,
                        ),
                      ),
                    ],
                  ),
                  
                  const SizedBox(height: AppTokens.spacingSM),
                  
                  // Progress Bar
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Text(
                            'التقدم',
                            style: Theme.of(context).textTheme.bodySmall?.copyWith(
                              fontWeight: AppTokens.fontWeightMedium,
                            ),
                          ),
                          Text(
                            '${student['progress']}%',
                            style: Theme.of(context).textTheme.bodySmall?.copyWith(
                              fontWeight: AppTokens.fontWeightMedium,
                              color: AppTokens.primaryGreen,
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: AppTokens.spacingXS),
                      LinearProgressIndicator(
                        value: student['progress'] / 100,
                        backgroundColor: AppTokens.neutralLight,
                        valueColor: AlwaysStoppedAnimation<Color>(AppTokens.primaryGreen),
                      ),
                      const SizedBox(height: AppTokens.spacingXS),
                      Text(
                        '${student['tasks_completed']} من ${student['total_tasks']} مهمة مكتملة',
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
                  case 'view':
                    _viewStudentDetails(student);
                    break;
                  case 'edit':
                    _editStudent(student);
                    break;
                  case 'tasks':
                    _viewStudentTasks(student);
                    break;
                  case 'delete':
                    _deleteStudent(student);
                    break;
                }
              },
              itemBuilder: (context) => [
                const PopupMenuItem(
                  value: 'view',
                  child: Row(
                    children: [
                      Icon(Icons.visibility),
                      SizedBox(width: 8),
                      Text('عرض التفاصيل'),
                    ],
                  ),
                ),
                const PopupMenuItem(
                  value: 'edit',
                  child: Row(
                    children: [
                      Icon(Icons.edit),
                      SizedBox(width: 8),
                      Text('تعديل'),
                    ],
                  ),
                ),
                const PopupMenuItem(
                  value: 'tasks',
                  child: Row(
                    children: [
                      Icon(Icons.task_alt),
                      SizedBox(width: 8),
                      Text('المهام'),
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
    );
  }

  void _showAddStudentDialog() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('إضافة طالبة جديدة'),
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

  void _viewStudentDetails(Map<String, dynamic> student) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text('عرض تفاصيل ${student['name']}')),
    );
  }

  void _editStudent(Map<String, dynamic> student) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text('تعديل ${student['name']}')),
    );
  }

  void _viewStudentTasks(Map<String, dynamic> student) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text('مهام ${student['name']}')),
    );
  }

  void _deleteStudent(Map<String, dynamic> student) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('تأكيد الحذف'),
        content: Text('هل أنت متأكد من حذف ${student['name']}؟'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('إلغاء'),
          ),
          TextButton(
            onPressed: () {
              Navigator.pop(context);
              ScaffoldMessenger.of(context).showSnackBar(
                SnackBar(content: Text('تم حذف ${student['name']}')),
              );
            },
            child: const Text('حذف'),
          ),
        ],
      ),
    );
  }
}





