import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/router/app_router.dart';
import '../../../core/theme/tokens.dart';
import '../../../services/auth_service.dart';

class SupportInquiriesPage extends ConsumerStatefulWidget {
  const SupportInquiriesPage({super.key});

  @override
  ConsumerState<SupportInquiriesPage> createState() => _SupportInquiriesPageState();
}

class _SupportInquiriesPageState extends ConsumerState<SupportInquiriesPage>
    with TickerProviderStateMixin {
  late AnimationController _animationController;
  late Animation<double> _fadeAnimation;
  late Animation<double> _slideAnimation;
  
  List<Map<String, dynamic>> _inquiries = [];
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

    _slideAnimation = Tween<double>(
      begin: 30.0,
      end: 0.0,
    ).animate(CurvedAnimation(
      parent: _animationController,
      curve: Curves.easeOutCubic,
    ));

    _loadInquiries();
  }

  @override
  void dispose() {
    _animationController.dispose();
    super.dispose();
  }

  Future<void> _loadInquiries() async {
    try {
      setState(() {
        _isLoading = true;
        _error = null;
      });

      // TODO: Replace with actual API call when support inquiry endpoints are available
      await Future.delayed(const Duration(milliseconds: 1000));
      
      setState(() {
        _inquiries = []; // Empty list until API is implemented
        
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

  List<Map<String, dynamic>> get _filteredInquiries {
    if (_selectedFilter == 'all') return _inquiries;
    return _inquiries.where((inquiry) => inquiry['status'] == _selectedFilter).toList();
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
              'إدارة الاستفسارات',
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
            onPressed: _loadInquiries,
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
                        onPressed: _loadInquiries,
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
                          // Filter Chips
                          Row(
                            children: [
                              _buildFilterChip('الكل', 'all'),
                              const SizedBox(width: AppTokens.spacingSM),
                              _buildFilterChip('معلقة', 'pending'),
                              const SizedBox(width: AppTokens.spacingSM),
                              _buildFilterChip('مُجابة', 'answered'),
                            ],
                          ),
                        ],
                      ),
                    ),
                    
                    // Inquiries List
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
                              child: _buildInquiriesList(),
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

  Widget _buildInquiriesList() {
    if (_filteredInquiries.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              Icons.help_outline,
              size: 64,
              color: AppTokens.neutralGray,
            ),
            const SizedBox(height: 16),
            Text(
              'لا توجد استفسارات',
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
      itemCount: _filteredInquiries.length,
      itemBuilder: (context, index) {
        final inquiry = _filteredInquiries[index];
        return _buildInquiryCard(inquiry);
      },
    );
  }

  Widget _buildInquiryCard(Map<String, dynamic> inquiry) {
    final status = inquiry['status'];
    final isAnswered = status == 'answered';
    
    Color statusColor;
    IconData statusIcon;
    String statusText;
    
    if (isAnswered) {
      statusColor = AppTokens.successGreen;
      statusIcon = Icons.check_circle;
      statusText = 'مُجابة';
    } else {
      statusColor = AppTokens.warningOrange;
      statusIcon = Icons.pending;
      statusText = 'معلقة';
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
                        inquiry['question'],
                        style: Theme.of(context).textTheme.titleMedium?.copyWith(
                          fontWeight: AppTokens.fontWeightBold,
                        ),
                      ),
                      Text(
                        'من: ${inquiry['student_name']}',
                        style: Theme.of(context).textTheme.bodySmall?.copyWith(
                          color: AppTokens.neutralMedium,
                        ),
                      ),
                    ],
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
            
            const SizedBox(height: AppTokens.spacingMD),
            
            // Question
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
                    'السؤال',
                    style: Theme.of(context).textTheme.titleSmall?.copyWith(
                      fontWeight: AppTokens.fontWeightBold,
                    ),
                  ),
                  const SizedBox(height: AppTokens.spacingXS),
                  Text(
                    inquiry['question'],
                    style: Theme.of(context).textTheme.bodyMedium,
                  ),
                ],
              ),
            ),
            
            // Answer (if exists)
            if (isAnswered) ...[
              const SizedBox(height: AppTokens.spacingMD),
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(AppTokens.spacingMD),
                decoration: BoxDecoration(
                  color: AppTokens.successGreen.withValues(alpha: 0.1),
                  borderRadius: BorderRadius.circular(AppTokens.radiusMD),
                  border: Border.all(
                    color: AppTokens.successGreen.withValues(alpha: 0.3),
                  ),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        Icon(
                          Icons.check_circle,
                          color: AppTokens.successGreen,
                          size: AppTokens.iconSizeSM,
                        ),
                        const SizedBox(width: AppTokens.spacingXS),
                        Text(
                          'الإجابة',
                          style: Theme.of(context).textTheme.titleSmall?.copyWith(
                            fontWeight: AppTokens.fontWeightBold,
                            color: AppTokens.successGreen,
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: AppTokens.spacingXS),
                    Text(
                      inquiry['answer'],
                      style: Theme.of(context).textTheme.bodyMedium,
                    ),
                    const SizedBox(height: AppTokens.spacingXS),
                    Text(
                      'أجاب عليها: ${inquiry['answered_by']}',
                      style: Theme.of(context).textTheme.bodySmall?.copyWith(
                        color: AppTokens.neutralMedium,
                      ),
                    ),
                  ],
                ),
              ),
            ],
            
            const SizedBox(height: AppTokens.spacingMD),
            
            // Details
            Row(
              children: [
                Expanded(
                  child: _buildDetailItem(
                    'التاريخ',
                    inquiry['created_at'],
                    Icons.access_time,
                  ),
                ),
                Expanded(
                  child: _buildDetailItem(
                    'التصنيف',
                    _getCategoryText(inquiry['category']),
                    Icons.category,
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
                  onPressed: () => _viewInquiryDetails(inquiry),
                  icon: const Icon(Icons.visibility, size: 16),
                  label: const Text('عرض'),
                ),
                const SizedBox(width: AppTokens.spacingSM),
                if (!isAnswered) ...[
                  TextButton.icon(
                    onPressed: () => _answerInquiry(inquiry),
                    icon: const Icon(Icons.reply, size: 16),
                    label: const Text('إجابة'),
                  ),
                  const SizedBox(width: AppTokens.spacingSM),
                ],
                TextButton.icon(
                  onPressed: () => _deleteInquiry(inquiry),
                  icon: const Icon(Icons.delete, size: 16),
                  label: const Text('حذف'),
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

  String _getCategoryText(String category) {
    switch (category) {
      case 'account':
        return 'حساب';
      case 'schedule':
        return 'جدولة';
      case 'academic':
        return 'أكاديمي';
      case 'communication':
        return 'تواصل';
      default:
        return 'عام';
    }
  }

  void _viewInquiryDetails(Map<String, dynamic> inquiry) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text('عرض تفاصيل الاستفسار')),
    );
  }

  void _answerInquiry(Map<String, dynamic> inquiry) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('الإجابة على الاستفسار'),
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

  void _deleteInquiry(Map<String, dynamic> inquiry) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('تأكيد الحذف'),
        content: const Text('هل أنت متأكد من حذف هذا الاستفسار؟'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('إلغاء'),
          ),
          TextButton(
            onPressed: () {
              Navigator.pop(context);
              ScaffoldMessenger.of(context).showSnackBar(
                const SnackBar(content: Text('تم حذف الاستفسار')),
              );
            },
            child: const Text('حذف'),
          ),
        ],
      ),
    );
  }
}





