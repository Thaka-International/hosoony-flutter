import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/router/app_router.dart';
import '../../../core/theme/tokens.dart';
import '../../../services/auth_service.dart';

class SupportReportsPage extends ConsumerStatefulWidget {
  const SupportReportsPage({super.key});

  @override
  ConsumerState<SupportReportsPage> createState() => _SupportReportsPageState();
}

class _SupportReportsPageState extends ConsumerState<SupportReportsPage>
    with TickerProviderStateMixin {
  late AnimationController _animationController;
  late Animation<double> _fadeAnimation;
  late Animation<double> _slideAnimation;
  
  List<Map<String, dynamic>> _reports = [];
  bool _isLoading = true;
  String? _error;
  String _selectedPeriod = 'هذا الشهر';

  final List<String> _periods = [
    'هذا الأسبوع',
    'هذا الشهر',
    'هذا الفصل',
    'هذا العام',
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

    _loadReports();
  }

  @override
  void dispose() {
    _animationController.dispose();
    super.dispose();
  }

  Future<void> _loadReports() async {
    try {
      setState(() {
        _isLoading = true;
        _error = null;
      });

      // Mock data
      await Future.delayed(const Duration(milliseconds: 1000));
      
      setState(() {
        _reports = [
          {
            'id': 1,
            'title': 'تقرير أداء الطالبات',
            'description': 'تقرير شامل عن أداء الطالبات في المهام والتقييمات',
            'period': 'هذا الشهر',
            'created_at': '2024-01-15',
            'type': 'performance',
            'status': 'completed',
            'data': {
              'total_students': 25,
              'active_students': 22,
              'completed_tasks': 180,
              'average_score': 85.5,
              'top_performers': 5,
              'needs_attention': 3,
            },
          },
          {
            'id': 2,
            'title': 'تقرير الدعم الفني',
            'description': 'تقرير عن تذاكر الدعم الفني وحل المشاكل',
            'period': 'هذا الشهر',
            'created_at': '2024-01-14',
            'type': 'technical',
            'status': 'completed',
            'data': {
              'total_tickets': 45,
              'resolved_tickets': 42,
              'pending_tickets': 3,
              'average_resolution_time': '2.5 ساعة',
              'satisfaction_rate': 92.5,
            },
          },
          {
            'id': 3,
            'title': 'تقرير الاستفسارات',
            'description': 'تقرير عن الاستفسارات والإجابات المقدمة',
            'period': 'هذا الشهر',
            'created_at': '2024-01-13',
            'type': 'inquiries',
            'status': 'completed',
            'data': {
              'total_inquiries': 38,
              'answered_inquiries': 35,
              'pending_inquiries': 3,
              'average_response_time': '1.2 ساعة',
              'most_common_category': 'أكاديمي',
            },
          },
          {
            'id': 4,
            'title': 'تقرير الجدول الزمني',
            'description': 'تقرير عن الجلسات والجدول الزمني',
            'period': 'هذا الشهر',
            'created_at': '2024-01-12',
            'type': 'schedule',
            'status': 'completed',
            'data': {
              'total_sessions': 120,
              'completed_sessions': 115,
              'cancelled_sessions': 5,
              'attendance_rate': 95.8,
              'average_duration': '45 دقيقة',
            },
          },
          {
            'id': 5,
            'title': 'تقرير شامل',
            'description': 'تقرير شامل عن جميع الأنشطة والإحصائيات',
            'period': 'هذا الشهر',
            'created_at': '2024-01-11',
            'type': 'comprehensive',
            'status': 'in_progress',
            'data': null,
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
              'تقارير الأداء',
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
              _showCreateReportDialog();
            },
          ),
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _loadReports,
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
                        onPressed: _loadReports,
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
                    // Header Section
                    Container(
                      padding: const EdgeInsets.all(AppTokens.spacingMD),
                      color: AppTokens.neutralWhite,
                      child: Column(
                        children: [
                          // Period Selector
                          Row(
                            children: [
                              Expanded(
                                child: DropdownButtonFormField<String>(
                                  value: _selectedPeriod,
                                  decoration: InputDecoration(
                                    labelText: 'الفترة الزمنية',
                                    border: OutlineInputBorder(
                                      borderRadius: BorderRadius.circular(AppTokens.radiusMD),
                                    ),
                                  ),
                                  items: _periods.map((period) {
                                    return DropdownMenuItem(
                                      value: period,
                                      child: Text(period),
                                    );
                                  }).toList(),
                                  onChanged: (value) {
                                    setState(() {
                                      _selectedPeriod = value!;
                                    });
                                  },
                                ),
                              ),
                            ],
                          ),
                        ],
                      ),
                    ),
                    
                    // Reports List
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
                              child: _buildReportsList(),
                            ),
                          );
                        },
                      ),
                    ),
                  ],
                ),
    );
  }

  Widget _buildReportsList() {
    if (_reports.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              Icons.assessment_outlined,
              size: 64,
              color: AppTokens.neutralGray,
            ),
            const SizedBox(height: 16),
            Text(
              'لا توجد تقارير',
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
      itemCount: _reports.length,
      itemBuilder: (context, index) {
        final report = _reports[index];
        return _buildReportCard(report);
      },
    );
  }

  Widget _buildReportCard(Map<String, dynamic> report) {
    final status = report['status'];
    final type = report['type'];
    
    Color statusColor;
    IconData statusIcon;
    String statusText;
    
    switch (status) {
      case 'completed':
        statusColor = AppTokens.successGreen;
        statusIcon = Icons.check_circle;
        statusText = 'مكتمل';
        break;
      case 'in_progress':
        statusColor = AppTokens.warningOrange;
        statusIcon = Icons.hourglass_empty;
        statusText = 'قيد العمل';
        break;
      case 'pending':
        statusColor = AppTokens.infoBlue;
        statusIcon = Icons.pending;
        statusText = 'معلق';
        break;
      default:
        statusColor = AppTokens.neutralGray;
        statusIcon = Icons.help;
        statusText = 'غير محدد';
    }

    Color typeColor;
    IconData typeIcon;
    String typeText;
    
    switch (type) {
      case 'performance':
        typeColor = AppTokens.successGreen;
        typeIcon = Icons.trending_up;
        typeText = 'أداء';
        break;
      case 'technical':
        typeColor = AppTokens.infoBlue;
        typeIcon = Icons.build;
        typeText = 'تقني';
        break;
      case 'inquiries':
        typeColor = AppTokens.warningOrange;
        typeIcon = Icons.help_outline;
        typeText = 'استفسارات';
        break;
      case 'schedule':
        typeColor = AppTokens.primaryGold;
        typeIcon = Icons.schedule;
        typeText = 'جدولة';
        break;
      case 'comprehensive':
        typeColor = AppTokens.primaryGreen;
        typeIcon = Icons.analytics;
        typeText = 'شامل';
        break;
      default:
        typeColor = AppTokens.neutralGray;
        typeIcon = Icons.description;
        typeText = 'عام';
    }

    return Card(
      margin: const EdgeInsets.only(bottom: AppTokens.spacingMD),
      child: Padding(
        padding: const EdgeInsets.all(AppTokens.spacingMD),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Header
            Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(AppTokens.spacingSM),
                  decoration: BoxDecoration(
                    color: typeColor.withValues(alpha: 0.1),
                    borderRadius: BorderRadius.circular(AppTokens.radiusSM),
                  ),
                  child: Icon(
                    typeIcon,
                    color: typeColor,
                    size: AppTokens.iconSizeMD,
                  ),
                ),
                const SizedBox(width: AppTokens.spacingMD),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        report['title'],
                        style: Theme.of(context).textTheme.titleMedium?.copyWith(
                          fontWeight: AppTokens.fontWeightBold,
                        ),
                      ),
                      Text(
                        report['description'],
                        style: Theme.of(context).textTheme.bodySmall?.copyWith(
                          color: AppTokens.neutralMedium,
                        ),
                      ),
                    ],
                  ),
                ),
                Column(
                  children: [
                    Container(
                      padding: const EdgeInsets.symmetric(
                        horizontal: AppTokens.spacingSM,
                        vertical: AppTokens.spacingXS,
                      ),
                      decoration: BoxDecoration(
                        color: statusColor.withValues(alpha: 0.1),
                        borderRadius: BorderRadius.circular(AppTokens.radiusSM),
                      ),
                      child: Text(
                        statusText,
                        style: TextStyle(
                          color: statusColor,
                          fontSize: AppTokens.fontSizeXS,
                          fontWeight: AppTokens.fontWeightMedium,
                        ),
                      ),
                    ),
                    const SizedBox(height: AppTokens.spacingXS),
                    Container(
                      padding: const EdgeInsets.symmetric(
                        horizontal: AppTokens.spacingSM,
                        vertical: AppTokens.spacingXS,
                      ),
                      decoration: BoxDecoration(
                        color: typeColor.withValues(alpha: 0.1),
                        borderRadius: BorderRadius.circular(AppTokens.radiusSM),
                      ),
                      child: Text(
                        typeText,
                        style: TextStyle(
                          color: typeColor,
                          fontSize: AppTokens.fontSizeXS,
                          fontWeight: AppTokens.fontWeightMedium,
                        ),
                      ),
                    ),
                  ],
                ),
              ],
            ),
            
            const SizedBox(height: AppTokens.spacingMD),
            
            // Data Summary (if available)
            if (report['data'] != null) ...[
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(AppTokens.spacingMD),
                decoration: BoxDecoration(
                  color: AppTokens.neutralLight,
                  borderRadius: BorderRadius.circular(AppTokens.radiusMD),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'ملخص البيانات',
                      style: Theme.of(context).textTheme.titleSmall?.copyWith(
                        fontWeight: AppTokens.fontWeightBold,
                      ),
                    ),
                    const SizedBox(height: AppTokens.spacingSM),
                    _buildDataSummary(report['data'], type),
                  ],
                ),
              ),
              const SizedBox(height: AppTokens.spacingMD),
            ],
            
            // Details
            Row(
              children: [
                Expanded(
                  child: _buildDetailItem(
                    'الفترة',
                    report['period'],
                    Icons.calendar_today,
                  ),
                ),
                Expanded(
                  child: _buildDetailItem(
                    'تاريخ الإنشاء',
                    report['created_at'],
                    Icons.access_time,
                  ),
                ),
              ],
            ),
            
            const SizedBox(height: AppTokens.spacingMD),
            
            // Actions
            Row(
              mainAxisAlignment: MainAxisAlignment.end,
              children: [
                TextButton.icon(
                  onPressed: () => _viewReport(report),
                  icon: const Icon(Icons.visibility, size: 16),
                  label: const Text('عرض'),
                ),
                const SizedBox(width: AppTokens.spacingSM),
                TextButton.icon(
                  onPressed: () => _downloadReport(report),
                  icon: const Icon(Icons.download, size: 16),
                  label: const Text('تحميل'),
                ),
                const SizedBox(width: AppTokens.spacingSM),
                TextButton.icon(
                  onPressed: () => _shareReport(report),
                  icon: const Icon(Icons.share, size: 16),
                  label: const Text('مشاركة'),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildDataSummary(Map<String, dynamic> data, String type) {
    switch (type) {
      case 'performance':
        return Column(
          children: [
            Row(
              children: [
                Expanded(child: _buildSummaryItem('إجمالي الطالبات', '${data['total_students']}')),
                Expanded(child: _buildSummaryItem('الطالبات النشطات', '${data['active_students']}')),
              ],
            ),
            const SizedBox(height: AppTokens.spacingSM),
            Row(
              children: [
                Expanded(child: _buildSummaryItem('المهام المكتملة', '${data['completed_tasks']}')),
                Expanded(child: _buildSummaryItem('متوسط النقاط', '${data['average_score']}')),
              ],
            ),
          ],
        );
      case 'technical':
        return Column(
          children: [
            Row(
              children: [
                Expanded(child: _buildSummaryItem('إجمالي التذاكر', '${data['total_tickets']}')),
                Expanded(child: _buildSummaryItem('التذاكر المحلولة', '${data['resolved_tickets']}')),
              ],
            ),
            const SizedBox(height: AppTokens.spacingSM),
            Row(
              children: [
                Expanded(child: _buildSummaryItem('متوسط وقت الحل', '${data['average_resolution_time']}')),
                Expanded(child: _buildSummaryItem('معدل الرضا', '${data['satisfaction_rate']}%')),
              ],
            ),
          ],
        );
      case 'inquiries':
        return Column(
          children: [
            Row(
              children: [
                Expanded(child: _buildSummaryItem('إجمالي الاستفسارات', '${data['total_inquiries']}')),
                Expanded(child: _buildSummaryItem('المُجابة', '${data['answered_inquiries']}')),
              ],
            ),
            const SizedBox(height: AppTokens.spacingSM),
            Row(
              children: [
                Expanded(child: _buildSummaryItem('متوسط وقت الرد', '${data['average_response_time']}')),
                Expanded(child: _buildSummaryItem('أكثر فئة', '${data['most_common_category']}')),
              ],
            ),
          ],
        );
      case 'schedule':
        return Column(
          children: [
            Row(
              children: [
                Expanded(child: _buildSummaryItem('إجمالي الجلسات', '${data['total_sessions']}')),
                Expanded(child: _buildSummaryItem('المكتملة', '${data['completed_sessions']}')),
              ],
            ),
            const SizedBox(height: AppTokens.spacingSM),
            Row(
              children: [
                Expanded(child: _buildSummaryItem('معدل الحضور', '${data['attendance_rate']}%')),
                Expanded(child: _buildSummaryItem('متوسط المدة', '${data['average_duration']}')),
              ],
            ),
          ],
        );
      default:
        return const Text('لا توجد بيانات متاحة');
    }
  }

  Widget _buildSummaryItem(String label, String value) {
    return Container(
      padding: const EdgeInsets.all(AppTokens.spacingSM),
      decoration: BoxDecoration(
        color: AppTokens.neutralWhite,
        borderRadius: BorderRadius.circular(AppTokens.radiusSM),
        border: Border.all(color: AppTokens.neutralMedium.withValues(alpha: 0.2)),
      ),
      child: Column(
        children: [
          Text(
            value,
            style: TextStyle(
              fontSize: AppTokens.fontSizeLG,
              fontWeight: AppTokens.fontWeightBold,
              color: AppTokens.primaryGreen,
            ),
          ),
          const SizedBox(height: AppTokens.spacingXS),
          Text(
            label,
            style: TextStyle(
              fontSize: AppTokens.fontSizeXS,
              color: AppTokens.neutralMedium,
            ),
            textAlign: TextAlign.center,
          ),
        ],
      ),
    );
  }

  Widget _buildDetailItem(String label, String value, IconData icon) {
    return Container(
      padding: const EdgeInsets.all(AppTokens.spacingSM),
      decoration: BoxDecoration(
        color: AppTokens.neutralLight,
        borderRadius: BorderRadius.circular(AppTokens.radiusSM),
      ),
      child: Row(
        children: [
          Icon(icon, color: AppTokens.neutralMedium, size: 16),
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
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  void _showCreateReportDialog() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('إنشاء تقرير جديد'),
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

  void _viewReport(Map<String, dynamic> report) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text('عرض تقرير ${report['title']}')),
    );
  }

  void _downloadReport(Map<String, dynamic> report) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text('تحميل تقرير ${report['title']}')),
    );
  }

  void _shareReport(Map<String, dynamic> report) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text('مشاركة تقرير ${report['title']}')),
    );
  }
}





