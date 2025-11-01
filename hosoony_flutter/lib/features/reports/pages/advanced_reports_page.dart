import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/router/app_router.dart';
import '../../../core/theme/tokens.dart';
import '../../../services/auth_service.dart';

class AdvancedReportsPage extends ConsumerStatefulWidget {
  const AdvancedReportsPage({super.key});

  @override
  ConsumerState<AdvancedReportsPage> createState() => _AdvancedReportsPageState();
}

class _AdvancedReportsPageState extends ConsumerState<AdvancedReportsPage>
    with TickerProviderStateMixin {
  late AnimationController _animationController;
  late Animation<double> _fadeAnimation;
  late Animation<double> _slideAnimation;
  
  List<Map<String, dynamic>> _reports = [];
  bool _isLoading = true;
  String? _error;
  String _selectedCategory = 'all';
  String _selectedPeriod = 'this_month';

  final List<String> _categories = [
    'all',
    'academic',
    'financial',
    'user_activity',
    'system_performance',
  ];

  final List<String> _periods = [
    'this_week',
    'this_month',
    'this_quarter',
    'this_year',
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
            'title': 'تقرير الأداء الأكاديمي',
            'description': 'تقرير شامل عن أداء الطالبات في جميع المواد',
            'category': 'academic',
            'period': 'this_month',
            'created_at': '2024-01-15',
            'status': 'completed',
            'data': {
              'total_students': 150,
              'average_score': 87.5,
              'completion_rate': 94.2,
              'top_performers': 25,
              'needs_attention': 8,
            },
            'charts': ['bar', 'pie', 'line'],
            'export_formats': ['pdf', 'excel', 'csv'],
          },
          {
            'id': 2,
            'title': 'تقرير الإيرادات المالية',
            'description': 'تقرير مفصل عن الإيرادات والمصروفات',
            'category': 'financial',
            'period': 'this_month',
            'created_at': '2024-01-14',
            'status': 'completed',
            'data': {
              'total_revenue': 7500.0,
              'total_expenses': 3200.0,
              'net_profit': 4300.0,
              'subscription_revenue': 6000.0,
              'additional_revenue': 1500.0,
            },
            'charts': ['bar', 'line', 'area'],
            'export_formats': ['pdf', 'excel'],
          },
          {
            'id': 3,
            'title': 'تقرير نشاط المستخدمين',
            'description': 'تحليل شامل لنشاط المستخدمين على المنصة',
            'category': 'user_activity',
            'period': 'this_month',
            'created_at': '2024-01-13',
            'status': 'completed',
            'data': {
              'total_logins': 2450,
              'average_session_duration': '32 دقيقة',
              'peak_hours': '9:00 - 11:00 صباحاً',
              'most_active_users': 45,
              'new_registrations': 12,
            },
            'charts': ['line', 'bar', 'heatmap'],
            'export_formats': ['pdf', 'excel', 'csv'],
          },
          {
            'id': 4,
            'title': 'تقرير أداء النظام',
            'description': 'مراقبة أداء الخوادم وقاعدة البيانات',
            'category': 'system_performance',
            'period': 'this_month',
            'created_at': '2024-01-12',
            'status': 'completed',
            'data': {
              'uptime': 99.8,
              'average_response_time': '120ms',
              'error_rate': 0.2,
              'cpu_usage': 45.2,
              'memory_usage': 67.8,
            },
            'charts': ['line', 'gauge', 'bar'],
            'export_formats': ['pdf', 'excel'],
          },
          {
            'id': 5,
            'title': 'تقرير التقييمات',
            'description': 'تحليل نتائج التقييمات والاختبارات',
            'category': 'academic',
            'period': 'this_month',
            'created_at': '2024-01-11',
            'status': 'in_progress',
            'data': null,
            'charts': ['bar', 'pie'],
            'export_formats': ['pdf', 'excel'],
          },
          {
            'id': 6,
            'title': 'تقرير المدفوعات',
            'description': 'تتبع المدفوعات والاشتراكات',
            'category': 'financial',
            'period': 'this_month',
            'created_at': '2024-01-10',
            'status': 'completed',
            'data': {
              'total_payments': 150,
              'successful_payments': 142,
              'failed_payments': 8,
              'pending_payments': 5,
              'total_amount': 7500.0,
            },
            'charts': ['pie', 'bar'],
            'export_formats': ['pdf', 'excel', 'csv'],
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

  List<Map<String, dynamic>> get _filteredReports {
    return _reports.where((report) {
      final matchesCategory = _selectedCategory == 'all' || report['category'] == _selectedCategory;
      final matchesPeriod = report['period'] == _selectedPeriod;
      return matchesCategory && matchesPeriod;
    }).toList();
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
              'التقارير المتقدمة',
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
                    // Filters Section
                    Container(
                      padding: const EdgeInsets.all(AppTokens.spacingMD),
                      color: AppTokens.neutralWhite,
                      child: Column(
                        children: [
                          // Category Filter
                          Row(
                            children: [
                              Expanded(
                                child: DropdownButtonFormField<String>(
                                  value: _selectedCategory,
                                  decoration: InputDecoration(
                                    labelText: 'التصنيف',
                                    border: OutlineInputBorder(
                                      borderRadius: BorderRadius.circular(AppTokens.radiusMD),
                                    ),
                                  ),
                                  items: [
                                    const DropdownMenuItem(
                                      value: 'all',
                                      child: Text('الكل'),
                                    ),
                                    const DropdownMenuItem(
                                      value: 'academic',
                                      child: Text('أكاديمي'),
                                    ),
                                    const DropdownMenuItem(
                                      value: 'financial',
                                      child: Text('مالي'),
                                    ),
                                    const DropdownMenuItem(
                                      value: 'user_activity',
                                      child: Text('نشاط المستخدمين'),
                                    ),
                                    const DropdownMenuItem(
                                      value: 'system_performance',
                                      child: Text('أداء النظام'),
                                    ),
                                  ],
                                  onChanged: (value) {
                                    setState(() {
                                      _selectedCategory = value!;
                                    });
                                  },
                                ),
                              ),
                              const SizedBox(width: AppTokens.spacingMD),
                              Expanded(
                                child: DropdownButtonFormField<String>(
                                  value: _selectedPeriod,
                                  decoration: InputDecoration(
                                    labelText: 'الفترة',
                                    border: OutlineInputBorder(
                                      borderRadius: BorderRadius.circular(AppTokens.radiusMD),
                                    ),
                                  ),
                                  items: [
                                    const DropdownMenuItem(
                                      value: 'this_week',
                                      child: Text('هذا الأسبوع'),
                                    ),
                                    const DropdownMenuItem(
                                      value: 'this_month',
                                      child: Text('هذا الشهر'),
                                    ),
                                    const DropdownMenuItem(
                                      value: 'this_quarter',
                                      child: Text('هذا الفصل'),
                                    ),
                                    const DropdownMenuItem(
                                      value: 'this_year',
                                      child: Text('هذا العام'),
                                    ),
                                  ],
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
    if (_filteredReports.isEmpty) {
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
      itemCount: _filteredReports.length,
      itemBuilder: (context, index) {
        final report = _filteredReports[index];
        return _buildReportCard(report);
      },
    );
  }

  Widget _buildReportCard(Map<String, dynamic> report) {
    final status = report['status'];
    final category = report['category'];
    
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

    Color categoryColor;
    IconData categoryIcon;
    String categoryText;
    
    switch (category) {
      case 'academic':
        categoryColor = AppTokens.successGreen;
        categoryIcon = Icons.school;
        categoryText = 'أكاديمي';
        break;
      case 'financial':
        categoryColor = AppTokens.primaryGold;
        categoryIcon = Icons.attach_money;
        categoryText = 'مالي';
        break;
      case 'user_activity':
        categoryColor = AppTokens.infoBlue;
        categoryIcon = Icons.people;
        categoryText = 'نشاط المستخدمين';
        break;
      case 'system_performance':
        categoryColor = AppTokens.warningOrange;
        categoryIcon = Icons.speed;
        categoryText = 'أداء النظام';
        break;
      default:
        categoryColor = AppTokens.neutralGray;
        categoryIcon = Icons.description;
        categoryText = 'عام';
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
                    color: categoryColor.withValues(alpha: 0.1),
                    borderRadius: BorderRadius.circular(AppTokens.radiusSM),
                  ),
                  child: Icon(
                    categoryIcon,
                    color: categoryColor,
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
                        color: categoryColor.withValues(alpha: 0.1),
                        borderRadius: BorderRadius.circular(AppTokens.radiusSM),
                      ),
                      child: Text(
                        categoryText,
                        style: TextStyle(
                          color: categoryColor,
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
                    _buildDataSummary(report['data'], category),
                  ],
                ),
              ),
              const SizedBox(height: AppTokens.spacingMD),
            ],
            
            // Charts and Export Options
            Row(
              children: [
                Expanded(
                  child: _buildInfoChip(
                    'الرسوم البيانية',
                    '${(report['charts'] as List).length}',
                    Icons.bar_chart,
                    AppTokens.infoBlue,
                  ),
                ),
                const SizedBox(width: AppTokens.spacingSM),
                Expanded(
                  child: _buildInfoChip(
                    'صيغ التصدير',
                    '${(report['export_formats'] as List).length}',
                    Icons.download,
                    AppTokens.successGreen,
                  ),
                ),
              ],
            ),
            
            const SizedBox(height: AppTokens.spacingMD),
            
            // Details
            Row(
              children: [
                Expanded(
                  child: _buildDetailItem(
                    'تاريخ الإنشاء',
                    report['created_at'],
                    Icons.access_time,
                  ),
                ),
                Expanded(
                  child: _buildDetailItem(
                    'الفترة',
                    _getPeriodText(report['period']),
                    Icons.calendar_today,
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
                  onPressed: () => _exportReport(report),
                  icon: const Icon(Icons.download, size: 16),
                  label: const Text('تصدير'),
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

  Widget _buildDataSummary(Map<String, dynamic> data, String category) {
    switch (category) {
      case 'academic':
        return Column(
          children: [
            Row(
              children: [
                Expanded(child: _buildSummaryItem('إجمالي الطالبات', '${data['total_students']}')),
                Expanded(child: _buildSummaryItem('متوسط النقاط', '${data['average_score']}')),
              ],
            ),
            const SizedBox(height: AppTokens.spacingSM),
            Row(
              children: [
                Expanded(child: _buildSummaryItem('معدل الإكمال', '${data['completion_rate']}%')),
                Expanded(child: _buildSummaryItem('المتفوقات', '${data['top_performers']}')),
              ],
            ),
          ],
        );
      case 'financial':
        return Column(
          children: [
            Row(
              children: [
                Expanded(child: _buildSummaryItem('إجمالي الإيرادات', '${data['total_revenue']} SAR')),
                Expanded(child: _buildSummaryItem('إجمالي المصروفات', '${data['total_expenses']} SAR')),
              ],
            ),
            const SizedBox(height: AppTokens.spacingSM),
            Row(
              children: [
                Expanded(child: _buildSummaryItem('صافي الربح', '${data['net_profit']} SAR')),
                Expanded(child: _buildSummaryItem('إيرادات الاشتراكات', '${data['subscription_revenue']} SAR')),
              ],
            ),
          ],
        );
      case 'user_activity':
        return Column(
          children: [
            Row(
              children: [
                Expanded(child: _buildSummaryItem('إجمالي الدخول', '${data['total_logins']}')),
                Expanded(child: _buildSummaryItem('متوسط الجلسة', '${data['average_session_duration']}')),
              ],
            ),
            const SizedBox(height: AppTokens.spacingSM),
            Row(
              children: [
                Expanded(child: _buildSummaryItem('ساعات الذروة', '${data['peak_hours']}')),
                Expanded(child: _buildSummaryItem('المستخدمين النشطين', '${data['most_active_users']}')),
              ],
            ),
          ],
        );
      case 'system_performance':
        return Column(
          children: [
            Row(
              children: [
                Expanded(child: _buildSummaryItem('وقت التشغيل', '${data['uptime']}%')),
                Expanded(child: _buildSummaryItem('وقت الاستجابة', '${data['average_response_time']}')),
              ],
            ),
            const SizedBox(height: AppTokens.spacingSM),
            Row(
              children: [
                Expanded(child: _buildSummaryItem('معدل الخطأ', '${data['error_rate']}%')),
                Expanded(child: _buildSummaryItem('استخدام المعالج', '${data['cpu_usage']}%')),
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
              fontSize: AppTokens.fontSizeSM,
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

  Widget _buildInfoChip(String label, String value, IconData icon, Color color) {
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
              children: [
                Text(
                  value,
                  style: TextStyle(
                    fontSize: AppTokens.fontSizeSM,
                    fontWeight: AppTokens.fontWeightBold,
                    color: color,
                  ),
                ),
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

  String _getPeriodText(String period) {
    switch (period) {
      case 'this_week':
        return 'هذا الأسبوع';
      case 'this_month':
        return 'هذا الشهر';
      case 'this_quarter':
        return 'هذا الفصل';
      case 'this_year':
        return 'هذا العام';
      default:
        return 'غير محدد';
    }
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

  void _exportReport(Map<String, dynamic> report) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text('تصدير تقرير ${report['title']}')),
    );
  }

  void _shareReport(Map<String, dynamic> report) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text('مشاركة تقرير ${report['title']}')),
    );
  }
}





