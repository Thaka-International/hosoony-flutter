import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:url_launcher/url_launcher.dart';
import 'dart:convert';
import '../../../core/router/app_router.dart';
import '../../../core/theme/tokens.dart';
import '../../../services/auth_service.dart';
import '../../../services/api_service.dart';

class StudentCompanionsPage extends ConsumerStatefulWidget {
  const StudentCompanionsPage({super.key});

  @override
  ConsumerState<StudentCompanionsPage> createState() => _StudentCompanionsPageState();
}

class _StudentCompanionsPageState extends ConsumerState<StudentCompanionsPage>
    with TickerProviderStateMixin {
  late AnimationController _animationController;
  late Animation<double> _fadeAnimation;
  late Animation<Offset> _offsetAnimation;
  
  List<Map<String, dynamic>> _companions = [];
  bool _isLoading = true;
  String? _error;
  String? _date;
  String? _roomNumber;
  String? _zoomUrl;
  String? _zoomPassword;
  String? _sessionId; // Store session_id for evaluation

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

    _offsetAnimation = Tween<Offset>(
      begin: const Offset(0, 0.5), // ابدأ من نصف ارتفاع الويدجت للأسفل
      end: Offset.zero,          // وانتهِ في الموضع الأصلي
    ).animate(CurvedAnimation(
      parent: _animationController,
      curve: Curves.easeOutCubic,
    ));

    _loadCompanions();
  }

  @override
  void dispose() {
    _animationController.dispose();
    super.dispose();
  }

  Future<void> _loadCompanions() async {
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

      final response = await ApiService.getMyCompanions();
      
      if (response['success'] == true) {
        setState(() {
          _companions = List<Map<String, dynamic>>.from(response['companions'] ?? []);
          _date = response['date'];
          _roomNumber = response['room_number'];
          _zoomUrl = response['zoom_url'];
          _zoomPassword = response['zoom_password'];
          _sessionId = response['session_id']?.toString();
          _isLoading = false;
        });
        _animationController.forward();
      } else {
        setState(() {
          _error = response['message'] ?? 'لا توجد رفيقات متاحة';
          _isLoading = false;
        });
      }
    } catch (e) {
      setState(() {
        // Handle different types of errors
        if (e.toString().contains('404')) {
          _error = 'لا توجد رفيقات متاحة حالياً';
        } else if (e.toString().contains('401')) {
          _error = 'انتهت صلاحية الجلسة، يرجى تسجيل الدخول مرة أخرى';
        } else {
          _error = 'خطأ في تحميل الرفيقات: ${e.toString()}';
        }
        _isLoading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    final authState = ref.watch(authStateProvider);
    final user = authState.user;

    return Scaffold(
      appBar: AppBar(
        leading: IconButton(
          icon: const Icon(Icons.arrow_back),
          onPressed: () => context.go('/student/home'),
        ),
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
              'الرفيقات',
              style: TextStyle(
                fontFamily: AppTokens.primaryFontFamily,
                fontWeight: AppTokens.fontWeightBold,
              ),
            ),
          ],
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _loadCompanions,
          ),
          IconButton(
            icon: const Icon(Icons.notifications_outlined),
            onPressed: () {
              ScaffoldMessenger.of(context).showSnackBar(
                const SnackBar(content: Text('الإشعارات')),
              );
            },
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
                        onPressed: _loadCompanions,
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
              : AnimatedBuilder(
                  animation: _animationController,
                  builder: (context, child) {
                    return FadeTransition(
                      opacity: _fadeAnimation,
                      child: SlideTransition(
                        position: _offsetAnimation, // استخدم الأنيميشن الجديد هنا
                        child: _buildCompanionsContent(context, user),
                      ),
                    );
                  },
                ),
      bottomNavigationBar: BottomNavigationBar(
        type: BottomNavigationBarType.fixed,
        currentIndex: 2,
        onTap: (index) {
          switch (index) {
            case 0:
              AppRouter.goToStudentHome(context);
              break;
            case 1:
              AppRouter.goToStudentDailyTasks(context);
              break;
            case 2:
              AppRouter.goToStudentCompanions(context);
              break;
            case 3:
              AppRouter.goToStudentSchedule(context);
              break;
            case 4:
              AppRouter.goToStudentAchievements(context);
              break;
          }
        },
        items: const [
          BottomNavigationBarItem(
            icon: Icon(Icons.home),
            label: 'الرئيسية',
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.task_alt),
            label: 'المهام',
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.people),
            label: 'الرفيقات',
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.schedule),
            label: 'الجدول',
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.star),
            label: 'الإنجازات',
          ),
        ],
      ),
    );
  }

  Future<void> _launchZoomUrl() async {
    if (_zoomUrl == null || _zoomUrl!.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('لا يوجد رابط Zoom متاح'),
          backgroundColor: AppTokens.errorRed,
        ),
      );
      return;
    }

    final Uri url = Uri.parse(_zoomUrl!);
    if (!await launchUrl(url, mode: LaunchMode.externalApplication)) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('لا يمكن فتح رابط Zoom'),
            backgroundColor: AppTokens.errorRed,
          ),
        );
      }
    }
  }

  void _navigateToEvaluation() {
    if (_companions.isEmpty || _sessionId == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('لا توجد بيانات رفيقة متاحة للتقييم'),
          backgroundColor: AppTokens.warningOrange,
        ),
      );
      return;
    }

    // Use first companion for evaluation
    final companion = _companions.first as Map<String, dynamic>;
    final companionId = companion['id'];
    
    if (companionId == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('خطأ: بيانات الرفيقة غير مكتملة'),
          backgroundColor: AppTokens.errorRed,
        ),
      );
      return;
    }

    try {
      // Prepare companion data
      final companionData = {
        'id': companionId is int ? companionId : int.tryParse(companionId.toString()) ?? companionId,
        'name': companion['name'] ?? 'الرفيقة',
      };

      final companionJson = Uri.encodeComponent(jsonEncode(companionData));
      final finalSessionId = _sessionId.toString();

      // Navigate to evaluation page
      context.go('/student/home/companion-evaluation?session_id=$finalSessionId&companion=$companionJson');
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('خطأ في الانتقال إلى صفحة التقييم: $e'),
          backgroundColor: AppTokens.errorRed,
        ),
      );
    }
  }

  Widget _buildCompanionsContent(BuildContext context, user) {
    if (_companions.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              Icons.people_outline,
              size: 64,
              color: AppTokens.primaryGreen,
            ),
            const SizedBox(height: 16),
            Text(
              'لا توجد رفيقات حالياً',
              style: TextStyle(
                fontFamily: AppTokens.primaryFontFamily,
                fontSize: 18,
                color: AppTokens.neutralGray,
              ),
            ),
            const SizedBox(height: 8),
            Text(
              'ستظهر رفيقاتك هنا عند انضمامك لمجموعة',
              style: TextStyle(
                fontFamily: AppTokens.primaryFontFamily,
                fontSize: 14,
                color: AppTokens.neutralGray,
              ),
            ),
          ],
        ),
      );
    }

    // Determine companion text (singular/plural)
    final companionText = _companions.length == 1 
        ? 'رفيقك لهذا اليوم في رحلة حفظ كتاب الله'
        : 'رفيقاتك لهذا اليوم في رحلة حفظ كتاب الله';

    return SingleChildScrollView(
      padding: const EdgeInsets.all(AppTokens.spacingMD),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Welcome Section
          Container(
            width: double.infinity,
            padding: const EdgeInsets.all(AppTokens.spacingLG),
            decoration: BoxDecoration(
              gradient: AppTokens.primaryGradient,
              borderRadius: BorderRadius.circular(AppTokens.radiusLG),
              boxShadow: AppTokens.shadowMD,
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  companionText,
                  style: Theme.of(context).textTheme.titleLarge?.copyWith(
                    color: AppTokens.neutralWhite,
                    fontWeight: AppTokens.fontWeightBold,
                  ),
                ),
                if (_roomNumber != null) ...[
                  const SizedBox(height: AppTokens.spacingMD),
                  Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: AppTokens.spacingMD,
                      vertical: AppTokens.spacingSM,
                    ),
                    decoration: BoxDecoration(
                      color: AppTokens.neutralWhite.withOpacity(0.2),
                      borderRadius: BorderRadius.circular(AppTokens.radiusMD),
                    ),
                    child: Row(
                      children: [
                        Icon(
                          Icons.meeting_room,
                          color: AppTokens.neutralWhite,
                          size: 20,
                        ),
                        const SizedBox(width: AppTokens.spacingSM),
                        Text(
                          'رقم الغرفة: $_roomNumber',
                          style: Theme.of(context).textTheme.bodyLarge?.copyWith(
                            color: AppTokens.neutralWhite,
                            fontWeight: AppTokens.fontWeightMedium,
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ],
            ),
          ),
          
          const SizedBox(height: AppTokens.spacingLG),
          
          // Zoom Button
          if (_zoomUrl != null && _zoomUrl!.isNotEmpty)
            SizedBox(
              width: double.infinity,
              child: ElevatedButton.icon(
                onPressed: _launchZoomUrl,
                icon: const Icon(Icons.video_call, size: 24),
                label: const Text(
                  'الانضمام إلى جلسة Zoom',
                  style: TextStyle(
                    fontSize: 16,
                    fontWeight: AppTokens.fontWeightBold,
                  ),
                ),
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppTokens.infoBlue,
                  foregroundColor: AppTokens.neutralWhite,
                  padding: const EdgeInsets.symmetric(
                    vertical: AppTokens.spacingMD,
                    horizontal: AppTokens.spacingLG,
                  ),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(AppTokens.radiusMD),
                  ),
                ),
              ),
            ),
          
          if (_zoomUrl != null && _zoomUrl!.isNotEmpty)
            const SizedBox(height: AppTokens.spacingLG),
          
          // Zoom Password Info
          if (_zoomPassword != null && _zoomPassword!.isNotEmpty) ...[
            Container(
              padding: const EdgeInsets.all(AppTokens.spacingMD),
              decoration: BoxDecoration(
                color: AppTokens.infoBlue.withOpacity(0.1),
                borderRadius: BorderRadius.circular(AppTokens.radiusMD),
                border: Border.all(
                  color: AppTokens.infoBlue.withOpacity(0.3),
                  width: 1,
                ),
              ),
              child: Row(
                children: [
                  Icon(
                    Icons.lock,
                    color: AppTokens.infoBlue,
                    size: 20,
                  ),
                  const SizedBox(width: AppTokens.spacingSM),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'كلمة مرور Zoom:',
                          style: Theme.of(context).textTheme.bodySmall?.copyWith(
                            color: AppTokens.neutralMedium,
                          ),
                        ),
                        const SizedBox(height: 4),
                        Text(
                          _zoomPassword!,
                          style: Theme.of(context).textTheme.titleMedium?.copyWith(
                            color: AppTokens.infoBlue,
                            fontWeight: AppTokens.fontWeightBold,
                            letterSpacing: 2,
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: AppTokens.spacingLG),
          ],
          
          // Companions List
          if (_companions.isNotEmpty) ...[
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(
                  '${_companions.length == 1 ? 'الرفيقة' : 'الرفيقات'}',
                  style: Theme.of(context).textTheme.titleLarge?.copyWith(
                    fontWeight: AppTokens.fontWeightBold,
                  ),
                ),
                // Evaluation Button
                if (_sessionId != null && _companions.isNotEmpty)
                  TextButton.icon(
                    onPressed: _navigateToEvaluation,
                    icon: const Icon(Icons.rate_review, size: 20),
                    label: const Text(
                      'تقييم الرفيقة',
                      style: TextStyle(fontSize: 14),
                    ),
                    style: TextButton.styleFrom(
                      foregroundColor: AppTokens.primaryGreen,
                    ),
                  ),
              ],
            ),
            const SizedBox(height: AppTokens.spacingMD),
          ],
          
          // Companion Cards
          ..._companions.map((companion) => Padding(
            padding: const EdgeInsets.only(bottom: AppTokens.spacingMD),
            child: _buildCompanionCardFromData(context, companion),
          )),
          
          // Evaluation Button (if companions exist and session_id available)
          if (_companions.isNotEmpty && _sessionId != null) ...[
            const SizedBox(height: AppTokens.spacingLG),
            SizedBox(
              width: double.infinity,
              child: OutlinedButton.icon(
                onPressed: _navigateToEvaluation,
                icon: const Icon(Icons.rate_review, size: 24),
                label: const Text(
                  'تقييم الرفيقة',
                  style: TextStyle(
                    fontSize: 16,
                    fontWeight: AppTokens.fontWeightBold,
                  ),
                ),
                style: OutlinedButton.styleFrom(
                  foregroundColor: AppTokens.primaryGreen,
                  side: BorderSide(color: AppTokens.primaryGreen, width: 2),
                  padding: const EdgeInsets.symmetric(
                    vertical: AppTokens.spacingMD,
                    horizontal: AppTokens.spacingLG,
                  ),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(AppTokens.radiusMD),
                  ),
                ),
              ),
            ),
          ],
        ],
      ),
    );
  }

  Widget _buildCompanionCardFromData(BuildContext context, Map<String, dynamic> companion) {
    final name = companion['name'] ?? 'رفيقة غير محددة';
    final role = companion['role'] ?? 'طالبة';
    final level = companion['level'] ?? 'مبتدئة';
    final points = companion['points'] ?? 0;
    
    return Card(
      elevation: 2,
      child: Padding(
        padding: const EdgeInsets.all(AppTokens.spacingMD),
        child: Row(
          children: [
            Container(
              width: 50,
              height: 50,
              decoration: BoxDecoration(
                color: _getCompanionColor(level).withValues(alpha: 0.1),
                borderRadius: BorderRadius.circular(AppTokens.radiusFull),
              ),
              child: Icon(
                _getCompanionIcon(level),
                color: _getCompanionColor(level),
                size: AppTokens.iconSizeMD,
              ),
            ),
            
            const SizedBox(width: AppTokens.spacingMD),
            
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    name,
                    style: Theme.of(context).textTheme.titleMedium?.copyWith(
                      fontWeight: AppTokens.fontWeightBold,
                    ),
                  ),
                  const SizedBox(height: AppTokens.spacingXS),
                  Text(
                    role,
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
                          color: _getCompanionColor(level).withValues(alpha: 0.1),
                          borderRadius: BorderRadius.circular(AppTokens.radiusSM),
                        ),
                        child: Text(
                          level,
                          style: Theme.of(context).textTheme.labelSmall?.copyWith(
                            color: _getCompanionColor(level),
                            fontWeight: AppTokens.fontWeightMedium,
                          ),
                        ),
                      ),
                      if (points > 0) ...[
                        const SizedBox(width: AppTokens.spacingSM),
                        Container(
                          padding: const EdgeInsets.symmetric(
                            horizontal: AppTokens.spacingSM,
                            vertical: AppTokens.spacingXS,
                          ),
                          decoration: BoxDecoration(
                            color: AppTokens.primaryGold.withValues(alpha: 0.1),
                            borderRadius: BorderRadius.circular(AppTokens.radiusSM),
                          ),
                          child: Text(
                            '$points نقطة',
                            style: Theme.of(context).textTheme.labelSmall?.copyWith(
                              color: AppTokens.primaryGold,
                              fontWeight: AppTokens.fontWeightMedium,
                            ),
                          ),
                        ),
                      ],
                    ],
                  ),
                ],
              ),
            ),
            
            IconButton(
              icon: const Icon(Icons.message_outlined),
              onPressed: () {
                ScaffoldMessenger.of(context).showSnackBar(
                  SnackBar(content: Text('إرسال رسالة إلى $name')),
                );
              },
            ),
          ],
        ),
      ),
    );
  }

  Color _getCompanionColor(String level) {
    switch (level.toLowerCase()) {
      case 'مبتدئة':
        return AppTokens.infoBlue;
      case 'متوسطة':
        return AppTokens.warningOrange;
      case 'متقدمة':
        return AppTokens.successGreen;
      case 'خبيرة':
        return AppTokens.primaryGold;
      default:
        return AppTokens.primaryGreen;
    }
  }

  IconData _getCompanionIcon(String level) {
    switch (level.toLowerCase()) {
      case 'مبتدئة':
        return Icons.school;
      case 'متوسطة':
        return Icons.trending_up;
      case 'متقدمة':
        return Icons.star;
      case 'خبيرة':
        return Icons.emoji_events;
      default:
        return Icons.person;
    }
  }
}
