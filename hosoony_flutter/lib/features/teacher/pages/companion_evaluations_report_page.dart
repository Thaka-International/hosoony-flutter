import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';
import '../../../core/theme/tokens.dart';
import '../../../services/auth_service.dart';
import '../../../services/api_service.dart';

class CompanionEvaluationsReportPage extends ConsumerStatefulWidget {
  final int? classId;
  final int? sessionId;
  final int? studentId;
  
  const CompanionEvaluationsReportPage({
    super.key,
    this.classId,
    this.sessionId,
    this.studentId,
  });

  @override
  ConsumerState<CompanionEvaluationsReportPage> createState() => 
      _CompanionEvaluationsReportPageState();
}

class _CompanionEvaluationsReportPageState extends ConsumerState<CompanionEvaluationsReportPage> {
  List<Map<String, dynamic>> _evaluations = [];
  Map<String, dynamic>? _statistics;
  Map<String, dynamic>? _session;
  Map<String, dynamic>? _student;
  bool _isLoading = true;
  String? _error;
  String _filterDateFrom = '';
  String _filterDateTo = '';

  @override
  void initState() {
    super.initState();
    _loadEvaluations();
  }

  Future<void> _loadEvaluations() async {
    try {
      setState(() {
        _isLoading = true;
        _error = null;
      });

      final authState = ref.read(authStateProvider);
      if (authState.token != null) {
        ApiService.setToken(authState.token!);
      }

      Map<String, dynamic> response;
      
      if (widget.studentId != null) {
        response = await ApiService.getStudentCompanionEvaluations(
          studentId: widget.studentId!,
        );
        if (response['success'] == true) {
          setState(() {
            _student = response['student'];
          });
        }
      } else if (widget.sessionId != null) {
        response = await ApiService.getSessionCompanionEvaluations(
          sessionId: widget.sessionId!,
        );
        if (response['success'] == true) {
          setState(() {
            _session = response['session'];
          });
        }
      } else {
        // Get class ID from user if not provided
        int? classId = widget.classId;
        if (classId == null) {
          final meData = await ApiService.getMe();
          classId = meData['data']?['class_id'] as int?;
        }
        
        if (classId == null) {
          throw Exception('لا يمكن تحديد الفصل');
        }
        
        response = await ApiService.getClassCompanionEvaluations(
          classId: classId,
        );
      }
      
      if (response['success'] == true) {
        setState(() {
          _evaluations = List<Map<String, dynamic>>.from(response['evaluations'] ?? []);
          _statistics = response['statistics'];
          _isLoading = false;
        });
      } else {
        setState(() {
          _error = response['message'] ?? 'فشل تحميل التقييمات';
          _isLoading = false;
        });
      }
    } catch (e) {
      setState(() {
        _error = 'خطأ: ${e.toString()}';
        _isLoading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(
          widget.studentId != null 
            ? 'تقييمات الطالبة'
            : widget.sessionId != null
              ? 'تقييمات الجلسة'
              : 'تقييمات الرفيقات',
          style: TextStyle(
            fontFamily: AppTokens.primaryFontFamily,
            fontWeight: AppTokens.fontWeightBold,
          ),
        ),
        actions: [
          IconButton(
            icon: Icon(Icons.refresh),
            onPressed: _loadEvaluations,
          ),
        ],
      ),
      body: _isLoading
          ? Center(child: CircularProgressIndicator())
          : _error != null
              ? Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Icon(Icons.error_outline, size: 64, color: AppTokens.errorRed),
                      SizedBox(height: 16),
                      Text(_error!, style: TextStyle(color: AppTokens.errorRed)),
                      SizedBox(height: 16),
                      ElevatedButton(
                        onPressed: _loadEvaluations,
                        child: Text('إعادة المحاولة'),
                      ),
                    ],
                  ),
                )
              : Column(
                  children: [
                    // Statistics Card
                    if (_statistics != null) _buildStatisticsCard(),
                    
                    // Filters
                    _buildFilters(),
                    
                    // Evaluations List
                    Expanded(
                      child: _evaluations.isEmpty
                          ? Center(
                              child: Column(
                                mainAxisAlignment: MainAxisAlignment.center,
                                children: [
                                  Icon(Icons.star_border, size: 64, color: Colors.grey),
                                  SizedBox(height: 16),
                                  Text(
                                    'لا توجد تقييمات',
                                    style: Theme.of(context).textTheme.titleLarge,
                                  ),
                                ],
                              ),
                            )
                          : RefreshIndicator(
                              onRefresh: _loadEvaluations,
                              child: ListView.builder(
                                padding: EdgeInsets.all(AppTokens.spacingMD),
                                itemCount: _evaluations.length,
                                itemBuilder: (context, index) {
                                  return _buildEvaluationCard(_evaluations[index]);
                                },
                              ),
                            ),
                    ),
                  ],
                ),
    );
  }

  Widget _buildStatisticsCard() {
    if (_statistics == null) return SizedBox.shrink();
    
    return Container(
      margin: EdgeInsets.all(AppTokens.spacingMD),
      padding: EdgeInsets.all(AppTokens.spacingLG),
      decoration: BoxDecoration(
        gradient: AppTokens.primaryGradient,
        borderRadius: BorderRadius.circular(AppTokens.radiusLG),
        boxShadow: AppTokens.shadowMD,
      ),
      child: Column(
        children: [
          Text(
            'إحصائيات التقييمات',
            style: TextStyle(
              color: Colors.white,
              fontSize: 18,
              fontWeight: FontWeight.bold,
            ),
          ),
          SizedBox(height: 16),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceAround,
            children: [
              _buildStatItem(
                'إجمالي التقييمات',
                '${_statistics!['total_evaluations'] ?? 0}',
                Icons.rate_review,
              ),
              _buildStatItem(
                'متوسط الحفظ',
                '${_statistics!['average_memorization'] ?? 0}/5',
                Icons.book,
              ),
              _buildStatItem(
                'متوسط التركيز',
                '${_statistics!['average_focus'] ?? 0}/5',
                Icons.psychology,
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildStatItem(String label, String value, IconData icon) {
    return Column(
      children: [
        Icon(icon, color: Colors.white, size: 32),
        SizedBox(height: 8),
        Text(
          value,
          style: TextStyle(
            color: Colors.white,
            fontSize: 24,
            fontWeight: FontWeight.bold,
          ),
        ),
        Text(
          label,
          style: TextStyle(
            color: Colors.white.withOpacity(0.9),
            fontSize: 12,
          ),
          textAlign: TextAlign.center,
        ),
      ],
    );
  }

  Widget _buildFilters() {
    return Container(
      padding: EdgeInsets.all(AppTokens.spacingMD),
      child: Row(
        children: [
          Expanded(
            child: TextField(
              decoration: InputDecoration(
                labelText: 'من تاريخ',
                hintText: 'YYYY-MM-DD',
                border: OutlineInputBorder(),
                prefixIcon: Icon(Icons.calendar_today),
              ),
              controller: TextEditingController(text: _filterDateFrom),
              onChanged: (value) {
                setState(() {
                  _filterDateFrom = value;
                });
              },
            ),
          ),
          SizedBox(width: 8),
          Expanded(
            child: TextField(
              decoration: InputDecoration(
                labelText: 'إلى تاريخ',
                hintText: 'YYYY-MM-DD',
                border: OutlineInputBorder(),
                prefixIcon: Icon(Icons.calendar_today),
              ),
              controller: TextEditingController(text: _filterDateTo),
              onChanged: (value) {
                setState(() {
                  _filterDateTo = value;
                });
              },
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildEvaluationCard(Map<String, dynamic> evaluation) {
    final companionName = evaluation['companion']?['name'] ?? 'غير معروف';
    final evaluatorName = evaluation['evaluator']?['name'] ?? 'غير معروف';
    final memorizationQuality = evaluation['memorization_quality'] ?? 0;
    final focusLevel = evaluation['focus_level'] ?? 0;
    final notes = evaluation['notes'];
    final evaluationDate = evaluation['evaluation_date'];
    
    return Card(
      margin: EdgeInsets.only(bottom: AppTokens.spacingMD),
      child: Padding(
        padding: EdgeInsets.all(AppTokens.spacingMD),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'الرفيقة: $companionName',
                        style: TextStyle(
                          fontWeight: FontWeight.bold,
                          fontSize: 16,
                        ),
                      ),
                      SizedBox(height: 4),
                      Text(
                        'المقيّمة: $evaluatorName',
                        style: TextStyle(
                          color: Colors.grey[600],
                          fontSize: 14,
                        ),
                      ),
                    ],
                  ),
                ),
                if (evaluationDate != null)
                  Text(
                    _formatDate(evaluationDate),
                    style: TextStyle(
                      color: Colors.grey[600],
                      fontSize: 12,
                    ),
                  ),
              ],
            ),
            SizedBox(height: 12),
            Row(
              children: [
                Expanded(
                  child: _buildRatingBadge(
                    'جودة الحفظ',
                    memorizationQuality,
                    Icons.book,
                  ),
                ),
                SizedBox(width: 8),
                Expanded(
                  child: _buildRatingBadge(
                    'مستوى التركيز',
                    focusLevel,
                    Icons.psychology,
                  ),
                ),
              ],
            ),
            if (notes != null && notes.toString().isNotEmpty) ...[
              SizedBox(height: 12),
              Container(
                padding: EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: Colors.grey[100],
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Icon(Icons.note, size: 20, color: Colors.grey[600]),
                    SizedBox(width: 8),
                    Expanded(
                      child: Text(
                        notes.toString(),
                        style: TextStyle(fontSize: 14),
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }

  Widget _buildRatingBadge(String label, int value, IconData icon) {
    Color color;
    if (value >= 4) {
      color = AppTokens.successGreen;
    } else if (value >= 3) {
      color = AppTokens.warningOrange;
    } else {
      color = AppTokens.errorRed;
    }
    
    return Container(
      padding: EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: color.withOpacity(0.1),
        borderRadius: BorderRadius.circular(8),
        border: Border.all(color: color),
      ),
      child: Column(
        children: [
          Icon(icon, color: color, size: 24),
          SizedBox(height: 4),
          Text(
            '$value/5',
            style: TextStyle(
              fontSize: 20,
              fontWeight: FontWeight.bold,
              color: color,
            ),
          ),
          Text(
            label,
            style: TextStyle(
              fontSize: 12,
              color: Colors.grey[600],
            ),
            textAlign: TextAlign.center,
          ),
        ],
      ),
    );
  }

  String _formatDate(dynamic date) {
    if (date == null) return '';
    try {
      DateTime dateTime;
      if (date is String) {
        dateTime = DateTime.parse(date);
      } else {
        dateTime = date as DateTime;
      }
      return DateFormat('yyyy-MM-dd', 'ar').format(dateTime);
    } catch (e) {
      return date.toString();
    }
  }
}

