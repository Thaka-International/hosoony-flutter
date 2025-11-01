import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/router/app_router.dart';
import '../../../core/theme/tokens.dart';
import '../../../services/auth_service.dart';

class SupportStudentsPage extends ConsumerStatefulWidget {
  const SupportStudentsPage({super.key});

  @override
  ConsumerState<SupportStudentsPage> createState() => _SupportStudentsPageState();
}

class _SupportStudentsPageState extends ConsumerState<SupportStudentsPage>
    with TickerProviderStateMixin {
  late AnimationController _animationController;
  late Animation<double> _fadeAnimation;
  late Animation<double> _slideAnimation;
  
  List<Map<String, dynamic>> _supportTickets = [];
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

    _loadSupportTickets();
  }

  @override
  void dispose() {
    _animationController.dispose();
    super.dispose();
  }

  Future<void> _loadSupportTickets() async {
    try {
      setState(() {
        _isLoading = true;
        _error = null;
      });

      // TODO: Replace with actual API call when support ticket endpoints are available
      await Future.delayed(const Duration(milliseconds: 1000));
      
      setState(() {
        _supportTickets = []; // Empty list until API is implemented
        
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

  List<Map<String, dynamic>> get _filteredTickets {
    if (_selectedFilter == 'all') return _supportTickets;
    return _supportTickets.where((ticket) => ticket['status'] == _selectedFilter).toList();
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
              'دعم الطالبات',
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
              _showCreateTicketDialog();
            },
          ),
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _loadSupportTickets,
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
                        onPressed: _loadSupportTickets,
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
                              _buildFilterChip('مفتوح', 'open'),
                              const SizedBox(width: AppTokens.spacingSM),
                              _buildFilterChip('قيد العمل', 'in_progress'),
                              const SizedBox(width: AppTokens.spacingSM),
                              _buildFilterChip('محلول', 'resolved'),
                            ],
                          ),
                        ],
                      ),
                    ),
                    
                    // Tickets List
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
                              child: _buildTicketsList(),
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

  Widget _buildTicketsList() {
    if (_filteredTickets.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              Icons.support_agent_outlined,
              size: 64,
              color: AppTokens.neutralGray,
            ),
            const SizedBox(height: 16),
            Text(
              'لا توجد تذاكر دعم',
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
      itemCount: _filteredTickets.length,
      itemBuilder: (context, index) {
        final ticket = _filteredTickets[index];
        return _buildTicketCard(ticket);
      },
    );
  }

  Widget _buildTicketCard(Map<String, dynamic> ticket) {
    final status = ticket['status'];
    final priority = ticket['priority'];
    
    Color statusColor;
    IconData statusIcon;
    String statusText;
    
    switch (status) {
      case 'open':
        statusColor = AppTokens.errorRed;
        statusIcon = Icons.circle_outlined;
        statusText = 'مفتوح';
        break;
      case 'in_progress':
        statusColor = AppTokens.warningOrange;
        statusIcon = Icons.hourglass_empty;
        statusText = 'قيد العمل';
        break;
      case 'resolved':
        statusColor = AppTokens.successGreen;
        statusIcon = Icons.check_circle;
        statusText = 'محلول';
        break;
      default:
        statusColor = AppTokens.neutralGray;
        statusIcon = Icons.help;
        statusText = 'غير محدد';
    }

    Color priorityColor;
    switch (priority) {
      case 'high':
        priorityColor = AppTokens.errorRed;
        break;
      case 'medium':
        priorityColor = AppTokens.warningOrange;
        break;
      case 'low':
        priorityColor = AppTokens.successGreen;
        break;
      default:
        priorityColor = AppTokens.neutralGray;
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
                        ticket['subject'],
                        style: Theme.of(context).textTheme.titleMedium?.copyWith(
                          fontWeight: AppTokens.fontWeightBold,
                        ),
                      ),
                      Text(
                        'من: ${ticket['student_name']}',
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
                        color: priorityColor.withValues(alpha: 0.1),
                        borderRadius: BorderRadius.circular(AppTokens.radiusSM),
                      ),
                      child: Text(
                        _getPriorityText(priority),
                        style: TextStyle(
                          color: priorityColor,
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
            
            // Description
            Text(
              ticket['description'],
              style: Theme.of(context).textTheme.bodyMedium,
              maxLines: 3,
              overflow: TextOverflow.ellipsis,
            ),
            
            const SizedBox(height: AppTokens.spacingMD),
            
            // Details
            Row(
              children: [
                Expanded(
                  child: _buildDetailItem(
                    'التاريخ',
                    ticket['created_at'],
                    Icons.access_time,
                  ),
                ),
                Expanded(
                  child: _buildDetailItem(
                    'التصنيف',
                    _getCategoryText(ticket['category']),
                    Icons.category,
                  ),
                ),
              ],
            ),
            
            if (ticket['assigned_to'] != null) ...[
              const SizedBox(height: AppTokens.spacingSM),
              _buildDetailItem(
                'المكلف',
                ticket['assigned_to'],
                Icons.person,
              ),
            ],
            
            const SizedBox(height: AppTokens.spacingMD),
            
            // Actions
            Row(
              mainAxisAlignment: MainAxisAlignment.end,
              children: [
                TextButton.icon(
                  onPressed: () => _viewTicketDetails(ticket),
                  icon: const Icon(Icons.visibility, size: 16),
                  label: const Text('عرض'),
                ),
                const SizedBox(width: AppTokens.spacingSM),
                TextButton.icon(
                  onPressed: () => _respondToTicket(ticket),
                  icon: const Icon(Icons.reply, size: 16),
                  label: const Text('رد'),
                ),
                const SizedBox(width: AppTokens.spacingSM),
                TextButton.icon(
                  onPressed: () => _assignTicket(ticket),
                  icon: const Icon(Icons.person_add, size: 16),
                  label: const Text('تكليف'),
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

  String _getPriorityText(String priority) {
    switch (priority) {
      case 'high':
        return 'عالي';
      case 'medium':
        return 'متوسط';
      case 'low':
        return 'منخفض';
      default:
        return 'غير محدد';
    }
  }

  String _getCategoryText(String category) {
    switch (category) {
      case 'technical':
        return 'تقني';
      case 'academic':
        return 'أكاديمي';
      case 'schedule':
        return 'جدولة';
      default:
        return 'عام';
    }
  }

  void _showCreateTicketDialog() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('إنشاء تذكرة دعم'),
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

  void _viewTicketDetails(Map<String, dynamic> ticket) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text('عرض تفاصيل ${ticket['subject']}')),
    );
  }

  void _respondToTicket(Map<String, dynamic> ticket) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text('الرد على ${ticket['subject']}')),
    );
  }

  void _assignTicket(Map<String, dynamic> ticket) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text('تكليف ${ticket['subject']}')),
    );
  }
}





