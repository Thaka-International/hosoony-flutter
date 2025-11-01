import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/router/app_router.dart';
import '../../../core/theme/tokens.dart';
import '../../../services/auth_service.dart';

class AdminSystemPage extends ConsumerStatefulWidget {
  const AdminSystemPage({super.key});

  @override
  ConsumerState<AdminSystemPage> createState() => _AdminSystemPageState();
}

class _AdminSystemPageState extends ConsumerState<AdminSystemPage>
    with TickerProviderStateMixin {
  late AnimationController _animationController;
  late Animation<double> _fadeAnimation;
  late Animation<Offset> _slideAnimation;
  
  Map<String, dynamic> _systemInfo = {};
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

    _loadSystemInfo();
  }

  @override
  void dispose() {
    _animationController.dispose();
    super.dispose();
  }

  Future<void> _loadSystemInfo() async {
    try {
      setState(() {
        _isLoading = true;
        _error = null;
      });

      // Mock data
      await Future.delayed(const Duration(milliseconds: 1000));
      
      setState(() {
        _systemInfo = {
          'server': {
            'status': 'online',
            'uptime': '15 يوم، 3 ساعات',
            'cpu_usage': 45.2,
            'memory_usage': 67.8,
            'disk_usage': 23.4,
            'last_restart': '2024-01-01 00:00',
          },
          'database': {
            'status': 'connected',
            'size': '1.2 GB',
            'connections': 25,
            'last_backup': '2024-01-15 02:00',
            'backup_size': '850 MB',
          },
          'security': {
            'firewall_status': 'active',
            'ssl_certificate': 'valid',
            'ssl_expires': '2024-12-31',
            'failed_logins': 3,
            'blocked_ips': 0,
            'last_security_scan': '2024-01-14 23:00',
          },
          'performance': {
            'response_time': '120ms',
            'throughput': '150 req/min',
            'error_rate': 0.2,
            'cache_hit_rate': 89.5,
            'queue_size': 5,
          },
          'maintenance': {
            'last_update': '2024-01-10',
            'next_update': '2024-02-10',
            'auto_backup': true,
            'log_retention': '30 يوم',
            'monitoring': true,
          },
        };
        
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
              'إدارة النظام',
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
            onPressed: _loadSystemInfo,
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
                        onPressed: _loadSystemInfo,
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
                        position: _slideAnimation,
                        child: _buildSystemContent(),
                      ),
                    );
                  },
                ),
    );
  }

  Widget _buildSystemContent() {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(AppTokens.spacingMD),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Server Status Section
          _buildSection(
            'حالة الخادم',
            Icons.dns,
            AppTokens.primaryGreen,
            _buildServerStatus(),
          ),
          
          const SizedBox(height: AppTokens.spacingLG),
          
          // Database Section
          _buildSection(
            'قاعدة البيانات',
            Icons.storage,
            AppTokens.infoBlue,
            _buildDatabaseStatus(),
          ),
          
          const SizedBox(height: AppTokens.spacingLG),
          
          // Security Section
          _buildSection(
            'الأمان',
            Icons.security,
            AppTokens.warningOrange,
            _buildSecurityStatus(),
          ),
          
          const SizedBox(height: AppTokens.spacingLG),
          
          // Performance Section
          _buildSection(
            'الأداء',
            Icons.speed,
            AppTokens.successGreen,
            _buildPerformanceStatus(),
          ),
          
          const SizedBox(height: AppTokens.spacingLG),
          
          // Maintenance Section
          _buildSection(
            'الصيانة',
            Icons.build,
            AppTokens.primaryGold,
            _buildMaintenanceStatus(),
          ),
          
          const SizedBox(height: AppTokens.spacingLG),
          
          // System Actions
          _buildSystemActions(),
          
          const SizedBox(height: AppTokens.spacingXL),
        ],
      ),
    );
  }

  Widget _buildSection(String title, IconData icon, Color color, Widget content) {
    return Container(
      padding: const EdgeInsets.all(AppTokens.spacingMD),
      decoration: BoxDecoration(
        color: AppTokens.neutralWhite,
        borderRadius: BorderRadius.circular(AppTokens.radiusLG),
        border: Border.all(color: color.withValues(alpha: 0.2)),
        boxShadow: [
          BoxShadow(
            color: color.withValues(alpha: 0.1),
            blurRadius: 8,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                padding: const EdgeInsets.all(AppTokens.spacingSM),
                decoration: BoxDecoration(
                  color: color.withValues(alpha: 0.1),
                  borderRadius: BorderRadius.circular(AppTokens.radiusSM),
                ),
                child: Icon(icon, color: color, size: AppTokens.iconSizeMD),
              ),
              const SizedBox(width: AppTokens.spacingMD),
              Text(
                title,
                style: Theme.of(context).textTheme.titleLarge?.copyWith(
                  fontWeight: AppTokens.fontWeightBold,
                  color: color,
                ),
              ),
            ],
          ),
          const SizedBox(height: AppTokens.spacingMD),
          content,
        ],
      ),
    );
  }

  Widget _buildServerStatus() {
    final server = _systemInfo['server'] as Map<String, dynamic>;
    return Column(
      children: [
        Row(
          children: [
            Expanded(
              child: _buildStatusItem(
                'الحالة',
                server['status'] == 'online' ? 'متصل' : 'غير متصل',
                Icons.circle,
                server['status'] == 'online' ? AppTokens.successGreen : AppTokens.errorRed,
              ),
            ),
            Expanded(
              child: _buildStatusItem(
                'وقت التشغيل',
                server['uptime'],
                Icons.schedule,
                AppTokens.infoBlue,
              ),
            ),
          ],
        ),
        const SizedBox(height: AppTokens.spacingSM),
        Row(
          children: [
            Expanded(
              child: _buildProgressItem(
                'استخدام المعالج',
                server['cpu_usage'],
                Icons.memory,
                AppTokens.warningOrange,
              ),
            ),
            Expanded(
              child: _buildProgressItem(
                'استخدام الذاكرة',
                server['memory_usage'],
                Icons.storage,
                AppTokens.infoBlue,
              ),
            ),
          ],
        ),
        const SizedBox(height: AppTokens.spacingSM),
        _buildProgressItem(
          'استخدام القرص',
          server['disk_usage'],
          Icons.storage,
          AppTokens.successGreen,
        ),
        const SizedBox(height: AppTokens.spacingSM),
        _buildStatusItem(
          'آخر إعادة تشغيل',
          server['last_restart'],
          Icons.restart_alt,
          AppTokens.neutralMedium,
        ),
      ],
    );
  }

  Widget _buildDatabaseStatus() {
    final database = _systemInfo['database'] as Map<String, dynamic>;
    return Column(
      children: [
        Row(
          children: [
            Expanded(
              child: _buildStatusItem(
                'الحالة',
                database['status'] == 'connected' ? 'متصل' : 'غير متصل',
                Icons.link,
                database['status'] == 'connected' ? AppTokens.successGreen : AppTokens.errorRed,
              ),
            ),
            Expanded(
              child: _buildStatusItem(
                'الحجم',
                database['size'],
                Icons.storage,
                AppTokens.infoBlue,
              ),
            ),
          ],
        ),
        const SizedBox(height: AppTokens.spacingSM),
        Row(
          children: [
            Expanded(
              child: _buildStatusItem(
                'الاتصالات',
                '${database['connections']}',
                Icons.people,
                AppTokens.warningOrange,
              ),
            ),
            Expanded(
              child: _buildStatusItem(
                'آخر نسخة احتياطية',
                database['last_backup'],
                Icons.backup,
                AppTokens.primaryGold,
              ),
            ),
          ],
        ),
        const SizedBox(height: AppTokens.spacingSM),
        _buildStatusItem(
          'حجم النسخة الاحتياطية',
          database['backup_size'],
          Icons.archive,
          AppTokens.successGreen,
        ),
      ],
    );
  }

  Widget _buildSecurityStatus() {
    final security = _systemInfo['security'] as Map<String, dynamic>;
    return Column(
      children: [
        Row(
          children: [
            Expanded(
              child: _buildStatusItem(
                'جدار الحماية',
                security['firewall_status'] == 'active' ? 'نشط' : 'غير نشط',
                Icons.security,
                security['firewall_status'] == 'active' ? AppTokens.successGreen : AppTokens.errorRed,
              ),
            ),
            Expanded(
              child: _buildStatusItem(
                'شهادة SSL',
                security['ssl_certificate'] == 'valid' ? 'صالحة' : 'منتهية',
                Icons.verified,
                security['ssl_certificate'] == 'valid' ? AppTokens.successGreen : AppTokens.errorRed,
              ),
            ),
          ],
        ),
        const SizedBox(height: AppTokens.spacingSM),
        Row(
          children: [
            Expanded(
              child: _buildStatusItem(
                'انتهاء SSL',
                security['ssl_expires'],
                Icons.calendar_today,
                AppTokens.infoBlue,
              ),
            ),
            Expanded(
              child: _buildStatusItem(
                'محاولات دخول فاشلة',
                '${security['failed_logins']}',
                Icons.block,
                AppTokens.warningOrange,
              ),
            ),
          ],
        ),
        const SizedBox(height: AppTokens.spacingSM),
        Row(
          children: [
            Expanded(
              child: _buildStatusItem(
                'عناوين IP محظورة',
                '${security['blocked_ips']}',
                Icons.block,
                AppTokens.errorRed,
              ),
            ),
            Expanded(
              child: _buildStatusItem(
                'آخر فحص أمني',
                security['last_security_scan'],
                Icons.scanner,
                AppTokens.primaryGold,
              ),
            ),
          ],
        ),
      ],
    );
  }

  Widget _buildPerformanceStatus() {
    final performance = _systemInfo['performance'] as Map<String, dynamic>;
    return Column(
      children: [
        Row(
          children: [
            Expanded(
              child: _buildStatusItem(
                'وقت الاستجابة',
                performance['response_time'],
                Icons.speed,
                AppTokens.successGreen,
              ),
            ),
            Expanded(
              child: _buildStatusItem(
                'الإنتاجية',
                performance['throughput'],
                Icons.trending_up,
                AppTokens.infoBlue,
              ),
            ),
          ],
        ),
        const SizedBox(height: AppTokens.spacingSM),
        Row(
          children: [
            Expanded(
              child: _buildStatusItem(
                'معدل الخطأ',
                '${performance['error_rate']}%',
                Icons.error,
                AppTokens.errorRed,
              ),
            ),
            Expanded(
              child: _buildStatusItem(
                'معدل ضربات التخزين المؤقت',
                '${performance['cache_hit_rate']}%',
                Icons.cached,
                AppTokens.warningOrange,
              ),
            ),
          ],
        ),
        const SizedBox(height: AppTokens.spacingSM),
        _buildStatusItem(
          'حجم الطابور',
          '${performance['queue_size']}',
          Icons.queue,
          AppTokens.primaryGold,
        ),
      ],
    );
  }

  Widget _buildMaintenanceStatus() {
    final maintenance = _systemInfo['maintenance'] as Map<String, dynamic>;
    return Column(
      children: [
        Row(
          children: [
            Expanded(
              child: _buildStatusItem(
                'آخر تحديث',
                maintenance['last_update'],
                Icons.update,
                AppTokens.infoBlue,
              ),
            ),
            Expanded(
              child: _buildStatusItem(
                'التحديث القادم',
                maintenance['next_update'],
                Icons.schedule,
                AppTokens.warningOrange,
              ),
            ),
          ],
        ),
        const SizedBox(height: AppTokens.spacingSM),
        Row(
          children: [
            Expanded(
              child: _buildStatusItem(
                'النسخ الاحتياطي التلقائي',
                maintenance['auto_backup'] ? 'مفعل' : 'معطل',
                Icons.backup,
                maintenance['auto_backup'] ? AppTokens.successGreen : AppTokens.errorRed,
              ),
            ),
            Expanded(
              child: _buildStatusItem(
                'احتفاظ السجلات',
                maintenance['log_retention'],
                Icons.history,
                AppTokens.primaryGold,
              ),
            ),
          ],
        ),
        const SizedBox(height: AppTokens.spacingSM),
        _buildStatusItem(
          'المراقبة',
          maintenance['monitoring'] ? 'مفعلة' : 'معطلة',
          Icons.monitor,
          maintenance['monitoring'] ? AppTokens.successGreen : AppTokens.errorRed,
        ),
      ],
    );
  }

  Widget _buildStatusItem(String label, String value, IconData icon, Color color) {
    return Container(
      padding: const EdgeInsets.all(AppTokens.spacingSM),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.1),
        borderRadius: BorderRadius.circular(AppTokens.radiusSM),
        border: Border.all(color: color.withValues(alpha: 0.3)),
      ),
      child: Row(
        children: [
          Icon(icon, color: color, size: 16),
          const SizedBox(width: AppTokens.spacingXS),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  label,
                  style: TextStyle(
                    fontSize: AppTokens.fontSizeXS,
                    color: AppTokens.neutralMedium,
                  ),
                ),
                Text(
                  value,
                  style: TextStyle(
                    fontSize: AppTokens.fontSizeSM,
                    fontWeight: AppTokens.fontWeightMedium,
                    color: color,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildProgressItem(String label, double value, IconData icon, Color color) {
    return Container(
      padding: const EdgeInsets.all(AppTokens.spacingSM),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.1),
        borderRadius: BorderRadius.circular(AppTokens.radiusSM),
        border: Border.all(color: color.withValues(alpha: 0.3)),
      ),
      child: Column(
        children: [
          Row(
            children: [
              Icon(icon, color: color, size: 16),
              const SizedBox(width: AppTokens.spacingXS),
              Expanded(
                child: Text(
                  label,
                  style: TextStyle(
                    fontSize: AppTokens.fontSizeXS,
                    color: AppTokens.neutralMedium,
                  ),
                ),
              ),
              Text(
                '${value.toStringAsFixed(1)}%',
                style: TextStyle(
                  fontSize: AppTokens.fontSizeSM,
                  fontWeight: AppTokens.fontWeightMedium,
                  color: color,
                ),
              ),
            ],
          ),
          const SizedBox(height: AppTokens.spacingXS),
          LinearProgressIndicator(
            value: value / 100,
            backgroundColor: AppTokens.neutralLight,
            valueColor: AlwaysStoppedAnimation<Color>(color),
          ),
        ],
      ),
    );
  }

  Widget _buildSystemActions() {
    return Container(
      padding: const EdgeInsets.all(AppTokens.spacingMD),
      decoration: BoxDecoration(
        color: AppTokens.neutralWhite,
        borderRadius: BorderRadius.circular(AppTokens.radiusLG),
        border: Border.all(color: AppTokens.neutralMedium.withValues(alpha: 0.2)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'إجراءات النظام',
            style: Theme.of(context).textTheme.titleLarge?.copyWith(
              fontWeight: AppTokens.fontWeightBold,
            ),
          ),
          const SizedBox(height: AppTokens.spacingMD),
          Row(
            children: [
              Expanded(
                child: _buildActionButton(
                  'إعادة تشغيل الخادم',
                  Icons.restart_alt,
                  AppTokens.warningOrange,
                  () => _restartServer(),
                ),
              ),
              const SizedBox(width: AppTokens.spacingMD),
              Expanded(
                child: _buildActionButton(
                  'إنشاء نسخة احتياطية',
                  Icons.backup,
                  AppTokens.infoBlue,
                  () => _createBackup(),
                ),
              ),
            ],
          ),
          const SizedBox(height: AppTokens.spacingMD),
          Row(
            children: [
              Expanded(
                child: _buildActionButton(
                  'تنظيف السجلات',
                  Icons.cleaning_services,
                  AppTokens.successGreen,
                  () => _cleanLogs(),
                ),
              ),
              const SizedBox(width: AppTokens.spacingMD),
              Expanded(
                child: _buildActionButton(
                  'فحص الأمان',
                  Icons.security,
                  AppTokens.primaryGold,
                  () => _securityScan(),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildActionButton(String label, IconData icon, Color color, VoidCallback onPressed) {
    return ElevatedButton.icon(
      onPressed: onPressed,
      icon: Icon(icon),
      label: Text(label),
      style: ElevatedButton.styleFrom(
        backgroundColor: color,
        foregroundColor: AppTokens.neutralWhite,
        padding: const EdgeInsets.symmetric(
          horizontal: AppTokens.spacingMD,
          vertical: AppTokens.spacingSM,
        ),
      ),
    );
  }

  void _restartServer() {
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(content: Text('إعادة تشغيل الخادم')),
    );
  }

  void _createBackup() {
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(content: Text('إنشاء نسخة احتياطية')),
    );
  }

  void _cleanLogs() {
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(content: Text('تنظيف السجلات')),
    );
  }

  void _securityScan() {
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(content: Text('فحص الأمان')),
    );
  }
}
