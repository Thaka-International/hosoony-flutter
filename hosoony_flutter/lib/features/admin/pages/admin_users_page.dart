import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/router/app_router.dart';
import '../../../core/theme/tokens.dart';
import '../../../services/auth_service.dart';

class AdminUsersPage extends ConsumerStatefulWidget {
  const AdminUsersPage({super.key});

  @override
  ConsumerState<AdminUsersPage> createState() => _AdminUsersPageState();
}

class _AdminUsersPageState extends ConsumerState<AdminUsersPage>
    with TickerProviderStateMixin {
  late AnimationController _animationController;
  late Animation<double> _fadeAnimation;
  late Animation<double> _slideAnimation;
  
  List<Map<String, dynamic>> _users = [];
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

    _slideAnimation = Tween<double>(
      begin: 30.0,
      end: 0.0,
    ).animate(CurvedAnimation(
      parent: _animationController,
      curve: Curves.easeOutCubic,
    ));

    _loadUsers();
  }

  @override
  void dispose() {
    _animationController.dispose();
    super.dispose();
  }

  Future<void> _loadUsers() async {
    try {
      setState(() {
        _isLoading = true;
        _error = null;
      });

      // Mock data
      await Future.delayed(const Duration(milliseconds: 1000));
      
      setState(() {
        _users = [
          {
            'id': 1,
            'name': 'فاطمة أحمد محمد',
            'email': 'fatima@example.com',
            'role': 'student',
            'status': 'active',
            'created_at': '2024-01-01',
            'last_login': '2024-01-15 10:30',
            'avatar': null,
          },
          {
            'id': 2,
            'name': 'أ. مريم حسن علي',
            'email': 'mariam@example.com',
            'role': 'teacher',
            'status': 'active',
            'created_at': '2024-01-02',
            'last_login': '2024-01-15 09:15',
            'avatar': null,
          },
          {
            'id': 3,
            'name': 'سارة محمد أحمد',
            'email': 'sara@example.com',
            'role': 'student',
            'status': 'active',
            'created_at': '2024-01-03',
            'last_login': '2024-01-15 08:45',
            'avatar': null,
          },
          {
            'id': 4,
            'name': 'أ. نور الدين محمد',
            'email': 'nour@example.com',
            'role': 'teacher',
            'status': 'inactive',
            'created_at': '2024-01-04',
            'last_login': '2024-01-10 14:20',
            'avatar': null,
          },
          {
            'id': 5,
            'name': 'آمنة عبدالله',
            'email': 'amna@example.com',
            'role': 'student',
            'status': 'active',
            'created_at': '2024-01-05',
            'last_login': '2024-01-15 07:30',
            'avatar': null,
          },
          {
            'id': 6,
            'name': 'أ. خديجة أحمد',
            'email': 'khadija@example.com',
            'role': 'assistant',
            'status': 'active',
            'created_at': '2024-01-06',
            'last_login': '2024-01-15 11:00',
            'avatar': null,
          },
          {
            'id': 7,
            'name': 'أ. محمد علي',
            'email': 'mohammed@example.com',
            'role': 'admin',
            'status': 'active',
            'created_at': '2024-01-07',
            'last_login': '2024-01-15 12:15',
            'avatar': null,
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

  List<Map<String, dynamic>> get _filteredUsers {
    var filtered = _users.where((user) {
      final matchesSearch = user['name'].toString().toLowerCase()
          .contains(_searchQuery.toLowerCase()) ||
          user['email'].toString().toLowerCase()
          .contains(_searchQuery.toLowerCase());
      final matchesFilter = _selectedFilter == 'all' || 
          user['role'] == _selectedFilter;
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
              'إدارة المستخدمين',
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
              _showAddUserDialog();
            },
          ),
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _loadUsers,
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
                        onPressed: _loadUsers,
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
                              hintText: 'البحث عن مستخدم...',
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
                              _buildFilterChip('طالبات', 'student'),
                              const SizedBox(width: AppTokens.spacingSM),
                              _buildFilterChip('معلمات', 'teacher'),
                              const SizedBox(width: AppTokens.spacingSM),
                              _buildFilterChip('مساعدات', 'assistant'),
                              const SizedBox(width: AppTokens.spacingSM),
                              _buildFilterChip('مديرين', 'admin'),
                            ],
                          ),
                        ],
                      ),
                    ),
                    
                    // Users List
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
                              child: _buildUsersList(),
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

  Widget _buildUsersList() {
    if (_filteredUsers.isEmpty) {
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
              'لا توجد مستخدمين',
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
      itemCount: _filteredUsers.length,
      itemBuilder: (context, index) {
        final user = _filteredUsers[index];
        return _buildUserCard(user);
      },
    );
  }

  Widget _buildUserCard(Map<String, dynamic> user) {
    final isActive = user['status'] == 'active';
    final role = user['role'];
    
    Color roleColor;
    IconData roleIcon;
    String roleText;
    
    switch (role) {
      case 'student':
        roleColor = AppTokens.successGreen;
        roleIcon = Icons.school;
        roleText = 'طالبة';
        break;
      case 'teacher':
        roleColor = AppTokens.infoBlue;
        roleIcon = Icons.person;
        roleText = 'معلمة';
        break;
      case 'assistant':
        roleColor = AppTokens.warningOrange;
        roleIcon = Icons.support_agent;
        roleText = 'مساعدة';
        break;
      case 'admin':
        roleColor = AppTokens.primaryGold;
        roleIcon = Icons.admin_panel_settings;
        roleText = 'مدير';
        break;
      default:
        roleColor = AppTokens.neutralGray;
        roleIcon = Icons.person;
        roleText = 'مستخدم';
    }
    
    return Card(
      margin: const EdgeInsets.only(bottom: AppTokens.spacingMD),
      child: Padding(
        padding: const EdgeInsets.all(AppTokens.spacingMD),
        child: Row(
          children: [
            // Avatar
            CircleAvatar(
              radius: 30,
              backgroundColor: roleColor.withValues(alpha: 0.1),
              child: Icon(
                roleIcon,
                color: roleColor,
                size: AppTokens.iconSizeLG,
              ),
            ),
            
            const SizedBox(width: AppTokens.spacingMD),
            
            // User Info
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Expanded(
                        child: Text(
                          user['name'],
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
                    user['email'],
                    style: Theme.of(context).textTheme.bodySmall?.copyWith(
                      color: AppTokens.neutralMedium,
                    ),
                  ),
                  
                  const SizedBox(height: AppTokens.spacingXS),
                  
                  Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: AppTokens.spacingSM,
                          vertical: AppTokens.spacingXS,
                        ),
                        decoration: BoxDecoration(
                          color: roleColor.withValues(alpha: 0.1),
                          borderRadius: BorderRadius.circular(AppTokens.radiusSM),
                        ),
                        child: Text(
                          roleText,
                          style: TextStyle(
                            color: roleColor,
                            fontSize: AppTokens.fontSizeXS,
                            fontWeight: AppTokens.fontWeightMedium,
                          ),
                        ),
                      ),
                      const SizedBox(width: AppTokens.spacingMD),
                      Text(
                        'آخر دخول: ${user['last_login']}',
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
                    _viewUserDetails(user);
                    break;
                  case 'edit':
                    _editUser(user);
                    break;
                  case 'reset_password':
                    _resetPassword(user);
                    break;
                  case 'toggle_status':
                    _toggleUserStatus(user);
                    break;
                  case 'delete':
                    _deleteUser(user);
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
                  value: 'reset_password',
                  child: Row(
                    children: [
                      Icon(Icons.lock_reset),
                      SizedBox(width: 8),
                      Text('إعادة تعيين كلمة المرور'),
                    ],
                  ),
                ),
                const PopupMenuItem(
                  value: 'toggle_status',
                  child: Row(
                    children: [
                      Icon(Icons.toggle_on),
                      SizedBox(width: 8),
                      Text('تغيير الحالة'),
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

  void _showAddUserDialog() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('إضافة مستخدم جديد'),
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

  void _viewUserDetails(Map<String, dynamic> user) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text('عرض تفاصيل ${user['name']}')),
    );
  }

  void _editUser(Map<String, dynamic> user) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text('تعديل ${user['name']}')),
    );
  }

  void _resetPassword(Map<String, dynamic> user) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text('إعادة تعيين كلمة مرور ${user['name']}')),
    );
  }

  void _toggleUserStatus(Map<String, dynamic> user) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text('تغيير حالة ${user['name']}')),
    );
  }

  void _deleteUser(Map<String, dynamic> user) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('تأكيد الحذف'),
        content: Text('هل أنت متأكد من حذف ${user['name']}؟'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('إلغاء'),
          ),
          TextButton(
            onPressed: () {
              Navigator.pop(context);
              ScaffoldMessenger.of(context).showSnackBar(
                SnackBar(content: Text('تم حذف ${user['name']}')),
              );
            },
            child: const Text('حذف'),
          ),
        ],
      ),
    );
  }
}





