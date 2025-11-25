import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../../core/theme/tokens.dart';
import '../../../services/api_service.dart';
import '../../../services/auth_service.dart';
import '../../../shared_ui/widgets/copyright_widget.dart';

class StudentHomePage extends ConsumerStatefulWidget {
  const StudentHomePage({super.key});

  @override
  ConsumerState<StudentHomePage> createState() => _StudentHomePageState();
}

class _StudentHomePageState extends ConsumerState<StudentHomePage>
    with TickerProviderStateMixin {
  late AnimationController _welcomeAnimationController;
  late AnimationController _cardsAnimationController;
  late Animation<double> _welcomeFadeAnimation;
  late Animation<Offset> _welcomeSlideAnimation;
  late Animation<double> _cardsFadeAnimation;
  late Animation<Offset> _cardsSlideAnimation;

  List<Map<String, dynamic>> _dailyTasks = [];
  List<Map<String, dynamic>> _companions = [];
  List<Map<String, dynamic>> _notifications = [];
  Map<String, dynamic>? _memorizationData; // ⭐ بيانات صفحة الحفظ
  bool _isLoading = true;
  String? _error;
  
  // حالة تحميل منفصلة لكل قسم
  bool _isLoadingTasks = false;
  bool _isLoadingCompanions = false;
  bool _isLoadingNotifications = false;
  bool _isLoadingMemorization = false;
  
  @override
  void initState() {
    super.initState();
    
    // ⭐ تهيئة بيانات صفحة الحفظ بقيم افتراضية لضمان عرض الكارت دائماً
    _memorizationData = {
      'current_page': 1,
      'surah_name_ar': 'الفاتحة',
      'juz_number': 1,
      'memorization_percentage': 0.17,
      'pages_completed': 1,
      'total_pages': 604,
    };
    
    _welcomeAnimationController = AnimationController(
      duration: const Duration(milliseconds: 800),
      vsync: this,
    );
    
    _cardsAnimationController = AnimationController(
      duration: const Duration(milliseconds: 1000),
      vsync: this,
    );
    
    _welcomeFadeAnimation = Tween<double>(
      begin: 0.0,
      end: 1.0,
    ).animate(CurvedAnimation(
      parent: _welcomeAnimationController,
      curve: Curves.easeInOut,
    ));
    
    _welcomeSlideAnimation = Tween<Offset>(
      begin: const Offset(0, -0.3),
      end: Offset.zero,
    ).animate(CurvedAnimation(
      parent: _welcomeAnimationController,
      curve: Curves.easeOutCubic,
    ));
    
    _cardsFadeAnimation = Tween<double>(
      begin: 0.0,
      end: 1.0,
    ).animate(CurvedAnimation(
      parent: _cardsAnimationController,
      curve: Curves.easeInOut,
    ));
    
    _cardsSlideAnimation = Tween<Offset>(
      begin: const Offset(0, 0.3),
      end: Offset.zero,
    ).animate(CurvedAnimation(
      parent: _cardsAnimationController,
      curve: Curves.easeOutCubic,
    ));
    
    _welcomeAnimationController.forward();
    _cardsAnimationController.forward();
    
    _loadData();
  }

  @override
  void dispose() {
    _welcomeAnimationController.dispose();
    _cardsAnimationController.dispose();
    super.dispose();
  }

  @override
  void didChangeDependencies() {
    super.didChangeDependencies();
    // Reload data when returning to this page to update completed tasks count
    final authState = ref.read(authStateProvider);
    if (authState.user?.id != null) {
      _loadDailyTasks(authState.user!.id.toString());
    }
  }

  Future<void> _loadData() async {
    try {
      // الحصول على معرف المستخدم من حالة المصادقة
      final authState = ref.read(authStateProvider);
      final userId = authState.user?.id.toString() ?? '1';
      
      // تحميل البيانات بشكل منفصل للتعامل مع الأخطاء بشكل أفضل
      await _loadDailyTasks(userId);
      await _loadCompanions();
      await _loadNotifications();
      await _loadMemorization(); // ⭐ تحميل بيانات صفحة الحفظ

      setState(() {
        _isLoading = false;
      });
    } catch (e) {
      setState(() {
        _error = e.toString();
        _isLoading = false;
      });
    }
  }

  Future<void> _loadDailyTasks(String userId) async {
    setState(() {
      _isLoadingTasks = true;
    });
    try {
      // Ensure token is set before making API calls
      final authState = ref.read(authStateProvider);
      if (authState.token != null) {
        ApiService.setToken(authState.token!);
      }
      
      final response = await ApiService.getDailyTasks(userId);
      
      // Handle new API response structure
      final tasks = response['tasks'] ?? [];
      
      setState(() {
        _dailyTasks = List<Map<String, dynamic>>.from(tasks);
        _isLoadingTasks = false;
      });
      
      // Refresh the page to update completed tasks count
      if (mounted) {
        setState(() {});
      }
    } catch (e) {
      // تسجيل الخطأ ولكن لا نوقف التطبيق
      print('خطأ في تحميل المهام اليومية: $e');
      setState(() {
        _dailyTasks = [];
        _isLoadingTasks = false;
      });
    }
  }

  Future<void> _loadCompanions() async {
    setState(() {
      _isLoadingCompanions = true;
    });
    try {
      // Ensure token is set before making API calls
      final authState = ref.read(authStateProvider);
      if (authState.token != null) {
        ApiService.setToken(authState.token!);
      }
      
      final companionsData = await ApiService.getMyCompanions();
      setState(() {
        _companions = List<Map<String, dynamic>>.from(companionsData['companions'] ?? []);
        _isLoadingCompanions = false;
      });
    } catch (e) {
      // تسجيل الخطأ ولكن لا نوقف التطبيق
      print('خطأ في تحميل الرفيقات: $e');
      setState(() {
        _companions = [];
        _isLoadingCompanions = false;
      });
    }
  }

  Future<void> _loadNotifications() async {
    setState(() {
      _isLoadingNotifications = true;
    });
    try {
      // Ensure token is set before making API calls
      final authState = ref.read(authStateProvider);
      if (authState.token != null) {
        ApiService.setToken(authState.token!);
      }
      
      final notifications = await ApiService.getNotifications();
      setState(() {
        _notifications = notifications;
        _isLoadingNotifications = false;
      });
    } catch (e) {
      // تسجيل الخطأ ولكن لا نوقف التطبيق
      print('خطأ في تحميل الإشعارات: $e');
      setState(() {
        _notifications = [];
        _isLoadingNotifications = false;
      });
    }
  }

  // ⭐ تحميل بيانات صفحة الحفظ
  Future<void> _loadMemorization() async {
    setState(() {
      _isLoadingMemorization = true;
    });
    try {
      final authState = ref.read(authStateProvider);
      if (authState.token != null) {
        ApiService.setToken(authState.token!);
      }
      
      final response = await ApiService.getClassMemorization();
      print('📖 Memorization API Response: $response'); // Debug log
      
      if (response['success'] == true && response['data'] != null) {
        setState(() {
          _memorizationData = response['data'];
          _isLoadingMemorization = false;
        });
        print('✅ Memorization data loaded: $_memorizationData'); // Debug log
      } else {
        // حتى لو لم تكن هناك بيانات، نستخدم قيم افتراضية لعرض الكارت
        print('⚠️ No memorization data, using defaults'); // Debug log
        setState(() {
          _memorizationData = {
            'current_page': 1,
            'surah_name_ar': 'الفاتحة',
            'juz_number': 1,
            'memorization_percentage': 0.17, // تقريباً 1/604
            'pages_completed': 1,
            'total_pages': 604,
          };
          _isLoadingMemorization = false;
        });
      }
    } catch (e, stackTrace) {
      print('❌ خطأ في تحميل بيانات صفحة الحفظ: $e');
      print('Stack trace: $stackTrace'); // Debug log
      // حتى في حالة الخطأ، نعرض الكارت بقيم افتراضية
      setState(() {
        _memorizationData = {
          'current_page': 1,
          'surah_name_ar': 'الفاتحة',
          'juz_number': 1,
          'memorization_percentage': 0.17,
          'pages_completed': 1,
          'total_pages': 604,
        };
        _isLoadingMemorization = false;
      });
    }
  }

  Widget _buildHomeContent() {
    if (_isLoading) {
      return const Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            CircularProgressIndicator(),
            SizedBox(height: 16),
            Text('جاري تحميل البيانات...'),
          ],
        ),
      );
    }

    if (_error != null) {
      return Center(
        child: Padding(
          padding: const EdgeInsets.all(24.0),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              const Icon(Icons.error_outline, size: 64, color: Colors.red),
              const SizedBox(height: 16),
              Text(
                'خطأ في تحميل البيانات',
                style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                  color: Colors.red,
                  fontWeight: FontWeight.bold,
                ),
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: 8),
              Text(
                'حدث خطأ أثناء تحميل بعض البيانات. يمكنك المحاولة مرة أخرى.',
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
                  _loadData();
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
        ),
      );
    }

    return SingleChildScrollView(
      physics: const AlwaysScrollableScrollPhysics(),
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // قسم الترحيب مع الحركة
          AnimatedBuilder(
            animation: _welcomeAnimationController,
            builder: (context, child) {
              return FadeTransition(
                opacity: _welcomeFadeAnimation,
                child: SlideTransition(
                  position: _welcomeSlideAnimation,
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
                              child: ClipRRect(
                                borderRadius: BorderRadius.circular(30),
                                child: Image.asset(
                                  'assets/images/hosoony-logo.png',
                                  fit: BoxFit.cover,
                                ),
                              ),
                            ),
                            const SizedBox(width: 16),
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    'مرحباً بك،',
                                    style: Theme.of(context).textTheme.titleMedium?.copyWith(
                                      color: AppTokens.neutralWhite.withOpacity(0.8),
                                    ),
                                  ),
                                  Text(
                                    ref.read(authStateProvider).user?.name ?? 'طالب',
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
                        Text(
                          'استمري في رحلتك التعليمية الممتعة',
                          style: Theme.of(context).textTheme.bodyLarge?.copyWith(
                            color: AppTokens.neutralWhite.withOpacity(0.8),
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              );
            },
          ),
          
          const SizedBox(height: 24),
          
          // ⭐ كارت صفحة الحفظ في القرآن الكريم
          // عرض الكارت دائماً حتى لو لم تكن هناك بيانات
          _buildMemorizationCard(),
          const SizedBox(height: 24),
          
          // الإحصائيات السريعة
          Row(
            children: [
              Expanded(
                child: _buildStatCard(
                  'المهام المكتملة',
                  '${_dailyTasks.where((task) => task['completed'] == true).length}',
                  Icons.check_circle,
                  AppTokens.primaryGreen,
                  onTap: () => context.go('/student/home/daily-tasks'),
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: _buildStatCard(
                  'الرفقاء',
                  '${_companions.length}',
                  Icons.people,
                  AppTokens.primaryBrown,
                  onTap: () => context.go('/student/home/companions'),
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: _buildStatCard(
                  'الإشعارات',
                  '${_notifications.length}',
                  Icons.notifications,
                  AppTokens.primaryGold,
                  onTap: () => context.go('/student/home/notifications'),
                ),
              ),
            ],
          ),
          
          const SizedBox(height: 24),
          
          // الإجراءات السريعة مع الحركة
          AnimatedBuilder(
            animation: _cardsAnimationController,
            builder: (context, child) {
              return FadeTransition(
                opacity: _cardsFadeAnimation,
                child: SlideTransition(
                  position: _cardsSlideAnimation,
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'الإجراءات السريعة',
                        style: Theme.of(context).textTheme.titleLarge?.copyWith(
                          fontWeight: FontWeight.bold,
                          color: AppTokens.neutralDark,
                        ),
                      ),
                      
                      const SizedBox(height: 16),
                      
                      GridView.count(
                        shrinkWrap: true,
                        physics: const NeverScrollableScrollPhysics(),
                        crossAxisCount: 2,
                        crossAxisSpacing: 12,
                        mainAxisSpacing: 12,
                        childAspectRatio: 1.2,
                        children: [
                          _buildActionCard(
                            'المهام اليومية',
                            Icons.assignment,
                            AppTokens.primaryBrown,
                            () => context.go('/student/home/daily-tasks'),
                          ),
                          _buildActionCard(
                            'الرفقاء',
                            Icons.people,
                            AppTokens.primaryGreen,
                            () => context.go('/student/home/companions'),
                          ),
                          _buildActionCard(
                            'الجدول الزمني',
                            Icons.schedule,
                            AppTokens.primaryGold,
                            () => context.go('/student/home/schedule'),
                          ),
                          _buildActionCard(
                            'الإنجازات',
                            Icons.emoji_events,
                            AppTokens.secondaryGold,
                            () => context.go('/student/home/achievements'),
                          ),
                        ],
                      ),
                    ],
                  ),
                ),
              );
            },
          ),
          
          const SizedBox(height: 24),
          
          // النشاط الأخير
          if (_notifications.isNotEmpty) ...[
            Text(
              'النشاط الأخير',
              style: Theme.of(context).textTheme.titleLarge?.copyWith(
                fontWeight: FontWeight.bold,
                color: AppTokens.neutralDark,
              ),
            ),
            
            const SizedBox(height: 16),
            
            ..._notifications.take(3).map((notification) => Container(
              margin: const EdgeInsets.only(bottom: 8),
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: AppTokens.neutralWhite,
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: AppTokens.neutralLight),
                boxShadow: AppTokens.shadowSM,
              ),
              child: Row(
                children: [
                  Container(
                    padding: const EdgeInsets.all(8),
                    decoration: BoxDecoration(
                      color: _getNotificationColor(notification['type']).withOpacity(0.1),
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: Icon(
                      _getNotificationIcon(notification['type']),
                      color: _getNotificationColor(notification['type']),
                      size: 20,
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          notification['title'] ?? 'إشعار جديد',
                          style: const TextStyle(
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                        const SizedBox(height: 4),
                        Text(
                          notification['message'] ?? '',
                          style: TextStyle(
                            color: AppTokens.neutralMedium,
                            fontSize: 12,
                          ),
                        ),
                      ],
                    ),
                  ),
                  Text(
                    _formatTime(notification['created_at']),
                    style: TextStyle(
                      color: AppTokens.neutralMedium.withOpacity(0.7),
                      fontSize: 10,
                    ),
                  ),
                ],
              ),
            )),
          ],
          
          // Copyright widget as part of scrollable content
          const Padding(
            padding: EdgeInsets.symmetric(vertical: 16.0),
            child: CopyrightWidget(),
          ),
        ],
      ),
    );
  }

  Widget _buildStatCard(String title, String value, IconData icon, Color color, {VoidCallback? onTap}) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(12),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.05),
              blurRadius: 10,
              offset: const Offset(0, 2),
            ),
          ],
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
      ),
    );
  }

  // ⭐ كارت صفحة الحفظ
  Widget _buildMemorizationCard() {
    // استخدام البيانات المحملة أو قيم افتراضية
    final pageNumber = _memorizationData?['current_page'] ?? 1;
    final surahName = _memorizationData?['surah_name_ar'] ?? 'الفاتحة';
    final juzNumber = _memorizationData?['juz_number'] ?? 1;
    final percentage = ((_memorizationData?['memorization_percentage'] ?? 0.0) as num).toDouble();
    final pagesCompleted = _memorizationData?['pages_completed'] ?? pageNumber;
    final totalPages = _memorizationData?['total_pages'] ?? 604;
    
    print('🎨 Building memorization card with: page=$pageNumber, surah=$surahName, percentage=$percentage'); // Debug
    
    // إذا كان لا يزال جاري التحميل، عرض indicator
    if (_isLoadingMemorization) {
      return Card(
        elevation: 4,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
          child: Container(
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(16),
            gradient: LinearGradient(
              colors: [
                AppTokens.primaryGreen.withOpacity(0.1),
                AppTokens.primaryGreen.withOpacity(0.05),
              ],
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
            ),
          ),
          child: const Center(
            child: CircularProgressIndicator(),
          ),
        ),
      );
    }
    
    return Card(
      elevation: 4,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(16),
      ),
      child: Container(
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(16),
          gradient: LinearGradient(
            colors: [
              AppTokens.primaryGreen.withOpacity(0.1),
              AppTokens.primaryGreen.withOpacity(0.05),
            ],
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
          ),
        ),
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // العنوان بدون أيقونة
            Text(
              'إنجازي',
              style: Theme.of(context).textTheme.titleMedium?.copyWith(
                fontWeight: FontWeight.bold,
                color: AppTokens.primaryGreen,
              ),
            ),
            
            const SizedBox(height: 12),
            
            // معلومات الصفحة - كل معلومة في عمود منفصل، الثلاثة في نفس السطر
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: AppTokens.primaryGreen.withOpacity(0.3)),
              ),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                crossAxisAlignment: CrossAxisAlignment.center,
                children: [
                  // رقم الصفحة - في عمود منفصل
                  Expanded(
                    child: Text(
                      'صفحة $pageNumber',
                      style: Theme.of(context).textTheme.bodySmall?.copyWith(
                        fontWeight: FontWeight.w600,
                        color: AppTokens.primaryGreen,
                        fontSize: 12,
                      ),
                      textAlign: TextAlign.center,
                    ),
                  ),
                  
                  // فاصل عمودي
                  Container(
                    width: 1,
                    height: 20,
                    color: Colors.grey[300],
                  ),
                  
                  // اسم السورة - في عمود منفصل
                  Expanded(
                    child: Text(
                      surahName,
                      style: Theme.of(context).textTheme.bodySmall?.copyWith(
                        fontWeight: FontWeight.w600,
                        fontSize: 12,
                        color: Colors.black87,
                      ),
                      textAlign: TextAlign.center,
                    ),
                  ),
                  
                  // فاصل عمودي
                  Container(
                    width: 1,
                    height: 20,
                    color: Colors.grey[300],
                  ),
                  
                  // الجزء - في عمود منفصل
                  Expanded(
                    child: Text(
                      'الجزء $juzNumber',
                      style: Theme.of(context).textTheme.bodySmall?.copyWith(
                        color: AppTokens.primaryBrown,
                        fontWeight: FontWeight.w600,
                        fontSize: 12,
                      ),
                      textAlign: TextAlign.center,
                    ),
                  ),
                ],
              ),
            ),
            
            const SizedBox(height: 12),
            
            // بار التقدم
            Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text(
                      'نسبة الحفظ من القرآن الكريم',
                      style: Theme.of(context).textTheme.bodySmall?.copyWith(
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                    Text(
                      '${percentage.toStringAsFixed(1)}%',
                      style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                        fontWeight: FontWeight.bold,
                        color: AppTokens.primaryGreen,
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 8),
                ClipRRect(
                  borderRadius: BorderRadius.circular(10),
                  child: LinearProgressIndicator(
                    value: percentage / 100,
                    minHeight: 8,
                    backgroundColor: Colors.grey[200],
                    valueColor: const AlwaysStoppedAnimation<Color>(AppTokens.primaryGreen),
                  ),
                ),
                const SizedBox(height: 6),
                Text(
                  '$pagesCompleted من $totalPages صفحة',
                  style: Theme.of(context).textTheme.bodySmall?.copyWith(
                    color: Colors.grey[600],
                    fontSize: 11,
                  ),
                  textAlign: TextAlign.center,
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildActionCard(String title, IconData icon, Color color, VoidCallback onTap) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(16),
          boxShadow: [
            BoxShadow(
              color: color.withOpacity(0.1),
              blurRadius: 10,
              offset: const Offset(0, 5),
            ),
          ],
          border: Border.all(color: color.withOpacity(0.2)),
        ),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: color.withOpacity(0.1),
                borderRadius: BorderRadius.circular(12),
              ),
              child: Icon(icon, color: color, size: 28),
            ),
            const SizedBox(height: 12),
            Text(
              title,
              style: TextStyle(
                fontWeight: FontWeight.bold,
                color: Colors.grey[800],
                fontSize: 14,
              ),
              textAlign: TextAlign.center,
            ),
          ],
        ),
      ),
    );
  }

  String _formatTime(String? timeString) {
    if (timeString == null) return '';
    try {
      final time = DateTime.parse(timeString);
      final now = DateTime.now();
      final difference = now.difference(time);
      
      if (difference.inMinutes < 60) {
        return 'منذ ${difference.inMinutes} دقيقة';
      } else if (difference.inHours < 24) {
        return 'منذ ${difference.inHours} ساعة';
      } else {
        return 'منذ ${difference.inDays} يوم';
      }
    } catch (e) {
      return '';
    }
  }

  IconData _getNotificationIcon(String? type) {
    switch (type) {
      case 'task':
        return Icons.assignment;
      case 'achievement':
        return Icons.emoji_events;
      case 'companion':
        return Icons.people;
      case 'schedule':
        return Icons.schedule;
      default:
        return Icons.notifications;
    }
  }

  Color _getNotificationColor(String? type) {
    switch (type) {
      case 'task':
        return AppTokens.primaryBrown;
      case 'achievement':
        return AppTokens.primaryGold;
      case 'companion':
        return AppTokens.primaryGreen;
      case 'schedule':
        return AppTokens.secondaryGold;
      default:
        return AppTokens.neutralMedium;
    }
  }

  Future<void> _showClearDataDialog(BuildContext context) async {
    final result = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('مسح جميع البيانات'),
        content: const Text(
          'هل أنت متأكد من أنك تريد مسح جميع البيانات المحفوظة؟\n'
          'سيتم تسجيل خروجك من التطبيق.',
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(false),
            child: const Text('إلغاء'),
          ),
          TextButton(
            onPressed: () => Navigator.of(context).pop(true),
            style: TextButton.styleFrom(foregroundColor: Colors.red),
            child: const Text('مسح'),
          ),
        ],
      ),
    );

    if (result == true) {
      try {
        await AuthService.clearAllStoredData();
        ref.read(authStateProvider.notifier).logout();
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
              content: Text('تم مسح جميع البيانات بنجاح'),
              backgroundColor: Colors.green,
            ),
          );
          context.go('/login');
        }
      } catch (e) {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text('خطأ في مسح البيانات: $e'),
              backgroundColor: Colors.red,
            ),
          );
        }
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('الرئيسية'),
        backgroundColor: AppTokens.primaryBrown,
        foregroundColor: AppTokens.neutralWhite,
        elevation: 0,
        actions: [
          IconButton(
            icon: const Icon(Icons.notifications),
            onPressed: () => context.go('/student/home/notifications'),
          ),
          IconButton(
            icon: const Icon(Icons.settings),
            onPressed: () => context.go('/student/home/settings'),
          ),
          PopupMenuButton<String>(
            icon: const Icon(Icons.more_vert),
            onSelected: (value) async {
              switch (value) {
                case 'logout':
                  await ref.read(authStateProvider.notifier).logout();
                  if (context.mounted) {
                    context.go('/login');
                  }
                  break;
                case 'clear_data':
                  await _showClearDataDialog(context);
                  break;
              }
            },
            itemBuilder: (context) => [
              const PopupMenuItem(
                value: 'logout',
                child: Row(
                  children: [
                    Icon(Icons.logout),
                    SizedBox(width: 8),
                    Text('تسجيل الخروج'),
                  ],
                ),
              ),
              const PopupMenuItem(
                value: 'clear_data',
                child: Row(
                  children: [
                    Icon(Icons.clear_all, color: Colors.red),
                    SizedBox(width: 8),
                    Text('مسح جميع البيانات', style: TextStyle(color: Colors.red)),
                  ],
                ),
              ),
            ],
          ),
        ],
      ),
      body: _buildHomeContent(),
    );
  }
}