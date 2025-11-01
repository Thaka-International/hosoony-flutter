import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/router/app_router.dart';
import '../../../core/theme/tokens.dart';
import '../../../services/auth_service.dart';

class PaymentsPage extends ConsumerStatefulWidget {
  const PaymentsPage({super.key});

  @override
  ConsumerState<PaymentsPage> createState() => _PaymentsPageState();
}

class _PaymentsPageState extends ConsumerState<PaymentsPage>
    with TickerProviderStateMixin {
  late AnimationController _animationController;
  late Animation<double> _fadeAnimation;
  late Animation<Offset> _slideAnimation;
  
  List<Map<String, dynamic>> _payments = [];
  bool _isLoading = true;
  String? _error;
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

    _loadPayments();
  }

  @override
  void dispose() {
    _animationController.dispose();
    super.dispose();
  }

  Future<void> _loadPayments() async {
    try {
      setState(() {
        _isLoading = true;
        _error = null;
      });

      // Mock data
      await Future.delayed(const Duration(milliseconds: 1000));
      
      setState(() {
        _payments = [
          {
            'id': 1,
            'amount': 50.0,
            'currency': 'SAR',
            'description': 'اشتراك شهري - الخطة الأساسية',
            'status': 'completed',
            'payment_method': 'credit_card',
            'transaction_id': 'TXN123456789',
            'created_at': '2024-01-15 10:30',
            'due_date': '2024-01-15',
            'type': 'subscription',
          },
          {
            'id': 2,
            'amount': 25.0,
            'currency': 'SAR',
            'description': 'رسوم إضافية - جلسة فردية',
            'status': 'pending',
            'payment_method': 'bank_transfer',
            'transaction_id': 'TXN987654321',
            'created_at': '2024-01-14 14:20',
            'due_date': '2024-01-20',
            'type': 'additional',
          },
          {
            'id': 3,
            'amount': 50.0,
            'currency': 'SAR',
            'description': 'اشتراك شهري - الخطة الأساسية',
            'status': 'completed',
            'payment_method': 'credit_card',
            'transaction_id': 'TXN456789123',
            'created_at': '2023-12-15 10:30',
            'due_date': '2023-12-15',
            'type': 'subscription',
          },
          {
            'id': 4,
            'amount': 100.0,
            'currency': 'SAR',
            'description': 'رسوم تسجيل - فصل جديد',
            'status': 'failed',
            'payment_method': 'credit_card',
            'transaction_id': 'TXN789123456',
            'created_at': '2023-11-15 09:15',
            'due_date': '2023-11-15',
            'type': 'registration',
          },
          {
            'id': 5,
            'amount': 50.0,
            'currency': 'SAR',
            'description': 'اشتراك شهري - الخطة الأساسية',
            'status': 'completed',
            'payment_method': 'credit_card',
            'transaction_id': 'TXN321654987',
            'created_at': '2023-10-15 10:30',
            'due_date': '2023-10-15',
            'type': 'subscription',
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

  List<Map<String, dynamic>> get _filteredPayments {
    if (_selectedFilter == 'all') return _payments;
    return _payments.where((payment) => payment['status'] == _selectedFilter).toList();
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
              'المدفوعات',
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
              _showPaymentDialog();
            },
          ),
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _loadPayments,
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
                        onPressed: _loadPayments,
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
                    // Summary Section
                    Container(
                      padding: const EdgeInsets.all(AppTokens.spacingMD),
                      color: AppTokens.neutralWhite,
                      child: Column(
                        children: [
                          Row(
                            children: [
                              Expanded(
                                child: _buildSummaryCard(
                                  'إجمالي المدفوعات',
                                  '275.0 SAR',
                                  Icons.payments,
                                  AppTokens.successGreen,
                                ),
                              ),
                              const SizedBox(width: AppTokens.spacingMD),
                              Expanded(
                                child: _buildSummaryCard(
                                  'المدفوعات المعلقة',
                                  '25.0 SAR',
                                  Icons.pending,
                                  AppTokens.warningOrange,
                                ),
                              ),
                            ],
                          ),
                        ],
                      ),
                    ),
                    
                    // Filters Section
                    Container(
                      padding: const EdgeInsets.all(AppTokens.spacingMD),
                      color: AppTokens.neutralWhite,
                      child: Column(
                        children: [
                          // Filter Chips
                          Row(
                            children: [
                              _buildFilterChip('الكل', 'all'),
                              const SizedBox(width: AppTokens.spacingSM),
                              _buildFilterChip('مكتملة', 'completed'),
                              const SizedBox(width: AppTokens.spacingSM),
                              _buildFilterChip('معلقة', 'pending'),
                              const SizedBox(width: AppTokens.spacingSM),
                              _buildFilterChip('فاشلة', 'failed'),
                            ],
                          ),
                        ],
                      ),
                    ),
                    
                    // Payments List
                    Expanded(
                      child: AnimatedBuilder(
                        animation: _animationController,
                        builder: (context, child) {
                          return FadeTransition(
                            opacity: _fadeAnimation,
                            child: SlideTransition(
                              position: _slideAnimation,
                              child: _buildPaymentsList(),
                            ),
                          );
                        },
                      ),
                    ),
                  ],
                ),
    );
  }

  Widget _buildSummaryCard(String title, String value, IconData icon, Color color) {
    return Container(
      padding: const EdgeInsets.all(AppTokens.spacingMD),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.1),
        borderRadius: BorderRadius.circular(AppTokens.radiusMD),
        border: Border.all(color: color.withValues(alpha: 0.3)),
      ),
      child: Column(
        children: [
          Icon(icon, color: color, size: AppTokens.iconSizeLG),
          const SizedBox(height: AppTokens.spacingSM),
          Text(
            value,
            style: TextStyle(
              fontSize: AppTokens.fontSizeLG,
              fontWeight: AppTokens.fontWeightBold,
              color: color,
            ),
          ),
          const SizedBox(height: AppTokens.spacingXS),
          Text(
            title,
            style: TextStyle(
              fontSize: AppTokens.fontSizeSM,
              color: AppTokens.neutralMedium,
            ),
            textAlign: TextAlign.center,
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

  Widget _buildPaymentsList() {
    if (_filteredPayments.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              Icons.payments_outlined,
              size: 64,
              color: AppTokens.neutralGray,
            ),
            const SizedBox(height: 16),
            Text(
              'لا توجد مدفوعات',
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
      itemCount: _filteredPayments.length,
      itemBuilder: (context, index) {
        final payment = _filteredPayments[index];
        return _buildPaymentCard(payment);
      },
    );
  }

  Widget _buildPaymentCard(Map<String, dynamic> payment) {
    final status = payment['status'];
    final type = payment['type'];
    
    Color statusColor;
    IconData statusIcon;
    String statusText;
    
    switch (status) {
      case 'completed':
        statusColor = AppTokens.successGreen;
        statusIcon = Icons.check_circle;
        statusText = 'مكتملة';
        break;
      case 'pending':
        statusColor = AppTokens.warningOrange;
        statusIcon = Icons.pending;
        statusText = 'معلقة';
        break;
      case 'failed':
        statusColor = AppTokens.errorRed;
        statusIcon = Icons.error;
        statusText = 'فاشلة';
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
      case 'subscription':
        typeColor = AppTokens.infoBlue;
        typeIcon = Icons.subscriptions;
        typeText = 'اشتراك';
        break;
      case 'additional':
        typeColor = AppTokens.primaryGold;
        typeIcon = Icons.add;
        typeText = 'إضافي';
        break;
      case 'registration':
        typeColor = AppTokens.primaryGreen;
        typeIcon = Icons.app_registration;
        typeText = 'تسجيل';
        break;
      default:
        typeColor = AppTokens.neutralGray;
        typeIcon = Icons.payment;
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
                    color: statusColor.withValues(alpha: 0.1),
                    borderRadius: BorderRadius.circular(AppTokens.radiusSM),
                  ),
                  child: Icon(
                    statusIcon,
                    color: statusColor,
                    size: AppTokens.iconSizeMD,
                  ),
                ),
                const SizedBox(width: AppTokens.spacingMD),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        payment['description'],
                        style: Theme.of(context).textTheme.titleMedium?.copyWith(
                          fontWeight: AppTokens.fontWeightBold,
                        ),
                      ),
                      Text(
                        'رقم المعاملة: ${payment['transaction_id']}',
                        style: Theme.of(context).textTheme.bodySmall?.copyWith(
                          color: AppTokens.neutralMedium,
                        ),
                      ),
                    ],
                  ),
                ),
                Column(
                  children: [
                    Text(
                      '${payment['amount']} ${payment['currency']}',
                      style: Theme.of(context).textTheme.titleLarge?.copyWith(
                        fontWeight: AppTokens.fontWeightBold,
                        color: statusColor,
                      ),
                    ),
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
                  ],
                ),
              ],
            ),
            
            const SizedBox(height: AppTokens.spacingMD),
            
            // Details
            Row(
              children: [
                Expanded(
                  child: _buildDetailItem(
                    'طريقة الدفع',
                    _getPaymentMethodText(payment['payment_method']),
                    Icons.credit_card,
                  ),
                ),
                Expanded(
                  child: _buildDetailItem(
                    'النوع',
                    typeText,
                    typeIcon,
                  ),
                ),
              ],
            ),
            
            const SizedBox(height: AppTokens.spacingSM),
            
            Row(
              children: [
                Expanded(
                  child: _buildDetailItem(
                    'تاريخ الدفع',
                    payment['created_at'],
                    Icons.access_time,
                  ),
                ),
                Expanded(
                  child: _buildDetailItem(
                    'تاريخ الاستحقاق',
                    payment['due_date'],
                    Icons.event,
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
                  onPressed: () => _viewPaymentDetails(payment),
                  icon: const Icon(Icons.visibility, size: 16),
                  label: const Text('عرض'),
                ),
                const SizedBox(width: AppTokens.spacingSM),
                if (status == 'pending') ...[
                  TextButton.icon(
                    onPressed: () => _retryPayment(payment),
                    icon: const Icon(Icons.refresh, size: 16),
                    label: const Text('إعادة المحاولة'),
                  ),
                  const SizedBox(width: AppTokens.spacingSM),
                ],
                TextButton.icon(
                  onPressed: () => _downloadReceipt(payment),
                  icon: const Icon(Icons.download, size: 16),
                  label: const Text('تحميل الإيصال'),
                ),
              ],
            ),
          ],
        ),
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

  String _getPaymentMethodText(String method) {
    switch (method) {
      case 'credit_card':
        return 'بطاقة ائتمان';
      case 'bank_transfer':
        return 'تحويل بنكي';
      case 'cash':
        return 'نقدي';
      default:
        return 'غير محدد';
    }
  }

  void _showPaymentDialog() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('إضافة دفعة جديدة'),
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

  void _viewPaymentDetails(Map<String, dynamic> payment) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text('عرض تفاصيل الدفعة ${payment['transaction_id']}')),
    );
  }

  void _retryPayment(Map<String, dynamic> payment) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text('إعادة محاولة الدفع ${payment['transaction_id']}')),
    );
  }

  void _downloadReceipt(Map<String, dynamic> payment) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text('تحميل إيصال ${payment['transaction_id']}')),
    );
  }
}





