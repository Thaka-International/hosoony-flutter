import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/theme/tokens.dart';
import '../../../services/auth_service.dart';
import '../../../services/api_service.dart';
import 'tabs/class_schedules_tab.dart';
import 'tabs/task_assignments_tab.dart';
import 'tabs/weekly_plans_tab.dart';
import 'tabs/companions_tab.dart';

class TeacherClassManagementPage extends ConsumerStatefulWidget {
  const TeacherClassManagementPage({super.key});

  @override
  ConsumerState<TeacherClassManagementPage> createState() => _TeacherClassManagementPageState();
}

class _TeacherClassManagementPageState extends ConsumerState<TeacherClassManagementPage>
    with SingleTickerProviderStateMixin {
  late TabController _tabController;
  
  int? _selectedClassId;
  List<Map<String, dynamic>> _classes = [];
  bool _isLoadingClasses = true;
  
  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 4, vsync: this);
    _loadClasses();
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  Future<void> _loadClasses() async {
    try {
      setState(() {
        _isLoadingClasses = true;
      });

      final authState = ref.read(authStateProvider);
      if (authState.token != null) {
        ApiService.setToken(authState.token!);
      }

      final classes = await ApiService.getMyClasses();
      
      setState(() {
        _classes = classes;
        if (classes.isNotEmpty) {
          _selectedClassId = classes.first['id'] as int?;
        }
        _isLoadingClasses = false;
      });
    } catch (e) {
      setState(() {
        _isLoadingClasses = false;
      });
      print('خطأ في تحميل الفصول: $e');
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('إدارة الفصول'),
        backgroundColor: AppTokens.primaryBrown,
        foregroundColor: AppTokens.neutralWhite,
        elevation: 0,
        bottom: TabBar(
          controller: _tabController,
          indicatorColor: AppTokens.neutralWhite,
          labelColor: AppTokens.neutralWhite,
          unselectedLabelColor: AppTokens.neutralWhite.withOpacity(0.7),
          tabs: const [
            Tab(text: 'جداول الفصل'),
            Tab(text: 'المهام الموكلة'),
            Tab(text: 'الخطة الأسبوعية'),
            Tab(text: 'نشرة الرفيقات'),
          ],
        ),
      ),
      body: _isLoadingClasses
          ? const Center(child: CircularProgressIndicator())
          : _classes.isEmpty
              ? Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Icon(Icons.class_, size: 64, color: Colors.grey),
                      const SizedBox(height: 16),
                      Text(
                        'لا توجد فصول متاحة',
                        style: Theme.of(context).textTheme.titleLarge,
                      ),
                    ],
                  ),
                )
                     : Column(
                         children: [
                           // Class Selector - دائماً يظهر حتى لو فصل واحد ليكون واضحاً
                           Container(
                             padding: const EdgeInsets.all(AppTokens.spacingMD),
                             color: AppTokens.primaryGreen.withOpacity(0.1),
                             child: Row(
                               children: [
                                 Icon(Icons.class_, color: AppTokens.primaryBrown),
                                 const SizedBox(width: AppTokens.spacingSM),
                                 Expanded(
                                   child: DropdownButtonFormField<int>(
                                     value: _selectedClassId,
                                     decoration: InputDecoration(
                                       labelText: _classes.length > 1 ? 'اختر الفصل' : 'الفصل',
                                       border: OutlineInputBorder(
                                         borderRadius: BorderRadius.circular(8),
                                       ),
                                       filled: true,
                                       fillColor: Colors.white,
                                     ),
                                     items: _classes.map((class_) {
                                       return DropdownMenuItem<int>(
                                         value: class_['id'] as int?,
                                         child: Text(
                                           class_['name'] ?? 'غير محدد',
                                           style: const TextStyle(fontWeight: FontWeight.bold),
                                         ),
                                       );
                                     }).toList(),
                                     onChanged: _classes.length > 1 
                                       ? (value) {
                                           setState(() {
                                             _selectedClassId = value;
                                           });
                                         }
                                       : null, // Disable if only one class
                                   ),
                                 ),
                               ],
                             ),
                           ),
                    
                    // Tab Content
                    Expanded(
                      child: TabBarView(
                        controller: _tabController,
                        children: [
                          ClassSchedulesTab(classId: _selectedClassId),
                          TaskAssignmentsTab(classId: _selectedClassId),
                          WeeklyPlansTab(classId: _selectedClassId),
                          CompanionsTab(classId: _selectedClassId),
                        ],
                      ),
                    ),
                  ],
                ),
    );
  }
}
