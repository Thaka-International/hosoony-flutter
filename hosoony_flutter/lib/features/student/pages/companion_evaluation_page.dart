import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../../core/router/app_router.dart';
import '../../../core/theme/tokens.dart';
import '../../../services/auth_service.dart';
import '../../../services/api_service.dart';
import 'dart:convert';
import 'package:dio/dio.dart';

class CompanionEvaluationPage extends ConsumerStatefulWidget {
  final String sessionId;
  final Map<String, dynamic> companion;
  
  const CompanionEvaluationPage({
    super.key,
    required this.sessionId,
    required this.companion,
  });

  @override
  ConsumerState<CompanionEvaluationPage> createState() => _CompanionEvaluationPageState();
}

class _CompanionEvaluationPageState extends ConsumerState<CompanionEvaluationPage>
    with TickerProviderStateMixin {
  late AnimationController _animationController;
  
  int _memorizationQuality = 3;
  int _focusLevel = 3;
  final TextEditingController _notesController = TextEditingController();
  bool _isSubmitting = false;
  String? _error;

  @override
  void initState() {
    super.initState();
    
    _animationController = AnimationController(
      duration: const Duration(milliseconds: 800),
      vsync: this,
    );
    
    _animationController.forward();
    
    // Debug: Print companion data to check if it's valid
    print('Companion Evaluation - Session ID: ${widget.sessionId}');
    print('Companion Evaluation - Companion: ${widget.companion}');
    print('Companion Evaluation - Companion ID: ${widget.companion['id']}');
    
    // Validate companion data
    if (widget.companion.isEmpty || widget.companion['id'] == null) {
      WidgetsBinding.instance.addPostFrameCallback((_) {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
              content: Text('خطأ: بيانات الرفيقة غير صحيحة'),
              backgroundColor: AppTokens.errorRed,
              duration: Duration(seconds: 3),
            ),
          );
          // Navigate back after showing error
          Future.delayed(const Duration(seconds: 2), () {
            if (mounted) {
              context.go('/student/home');
            }
          });
        }
      });
    }
  }

  @override
  void dispose() {
    _animationController.dispose();
    _notesController.dispose();
    super.dispose();
  }

  Future<void> _submitEvaluation() async {
    if (_isSubmitting) return;

    setState(() {
      _isSubmitting = true;
      _error = null;
    });

    try {
      final authState = ref.read(authStateProvider);
      if (authState.token != null) {
        ApiService.setToken(authState.token!);
      }

      // Validate companion ID before submitting
      final companionIdValue = widget.companion['id'];
      
      // Debug print
      print('Submit Evaluation - Companion ID value: $companionIdValue');
      print('Submit Evaluation - Companion ID type: ${companionIdValue.runtimeType}');
      print('Submit Evaluation - Full companion: ${widget.companion}');
      print('Submit Evaluation - Session ID: ${widget.sessionId}');
      
      if (companionIdValue == null) {
        final errorMsg = 'معرف الرفيقة غير صحيح. يرجى المحاولة مرة أخرى.\nالبيانات المستلمة: ${widget.companion}';
        setState(() {
          _error = errorMsg;
        });
        print('Error: Companion ID is null');
        return;
      }

      // Ensure companion ID is a valid integer or string
      final companionIdStr = companionIdValue.toString();
      if (companionIdStr.isEmpty || companionIdStr == 'null') {
        setState(() {
          _error = 'معرف الرفيقة غير صحيح. يرجى المحاولة مرة أخرى.';
        });
        print('Error: Companion ID is empty or "null"');
        return;
      }

      print('Submitting evaluation with companion_id: $companionIdStr');

      final response = await ApiService.submitCompanionEvaluation(
        sessionId: widget.sessionId,
        companionId: companionIdStr,
        memorizationQuality: _memorizationQuality,
        focusLevel: _focusLevel,
        notes: _notesController.text.trim().isEmpty ? null : _notesController.text.trim(),
      );

      if (response['success'] == true) {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: const Text('تم تقديم التقييم بنجاح'),
              backgroundColor: AppTokens.successGreen,
              duration: const Duration(seconds: 2),
            ),
          );
          
          // Return to home page
          Future.delayed(const Duration(milliseconds: 500), () {
            if (mounted) {
              context.go('/student/home');
            }
          });
        }
      } else {
        setState(() {
          _error = response['message'] ?? 'حدث خطأ في تقديم التقييم';
        });
      }
    } catch (e) {
      String errorMessage = 'حدث خطأ في تقديم التقييم';
      if (e is DioException && e.response?.data != null) {
        final responseData = e.response!.data;
        if (responseData is Map<String, dynamic>) {
          errorMessage = responseData['message'] ?? errorMessage;
        }
      }
      
      setState(() {
        _error = errorMessage;
      });
    } finally {
      if (mounted) {
        setState(() {
          _isSubmitting = false;
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text(
          'تقييم الرفيقة',
          style: TextStyle(
            fontFamily: AppTokens.primaryFontFamily,
            fontWeight: AppTokens.fontWeightBold,
          ),
        ),
        leading: IconButton(
          icon: const Icon(Icons.arrow_back),
          onPressed: () => context.go('/student/home'),
        ),
      ),
      body: AnimatedBuilder(
        animation: _animationController,
        builder: (context, child) {
          return FadeTransition(
            opacity: _animationController,
            child: SingleChildScrollView(
              padding: const EdgeInsets.all(AppTokens.spacingLG),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Companion Info Card
                  Container(
                    width: double.infinity,
                    padding: const EdgeInsets.all(AppTokens.spacingLG),
                    decoration: BoxDecoration(
                      gradient: AppTokens.primaryGradient,
                      borderRadius: BorderRadius.circular(AppTokens.radiusLG),
                      boxShadow: AppTokens.shadowMD,
                    ),
                    child: Column(
                      children: [
                        const Icon(
                          Icons.people,
                          size: 64,
                          color: AppTokens.neutralWhite,
                        ),
                        const SizedBox(height: AppTokens.spacingMD),
                        Text(
                          widget.companion['name'] ?? 'الرفيقة',
                          style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                            color: AppTokens.neutralWhite,
                            fontWeight: AppTokens.fontWeightBold,
                          ),
                        ),
                        const SizedBox(height: AppTokens.spacingSM),
                        Text(
                          'كيف كانت أداء رفيقتك في جلسة اليوم؟',
                          style: Theme.of(context).textTheme.bodyLarge?.copyWith(
                            color: AppTokens.neutralWhite.withOpacity(0.9),
                          ),
                          textAlign: TextAlign.center,
                        ),
                      ],
                    ),
                  ),
                  
                  const SizedBox(height: AppTokens.spacingXL),
                  
                  // Memorization Quality
                  Text(
                    'جودة الحفظ',
                    style: Theme.of(context).textTheme.titleLarge?.copyWith(
                      fontWeight: AppTokens.fontWeightBold,
                    ),
                  ),
                  const SizedBox(height: AppTokens.spacingMD),
                  _buildRatingSelector(
                    value: _memorizationQuality,
                    onChanged: (value) {
                      setState(() {
                        _memorizationQuality = value;
                      });
                    },
                  ),
                  
                  const SizedBox(height: AppTokens.spacingXL),
                  
                  // Focus Level
                  Text(
                    'مستوى التركيز',
                    style: Theme.of(context).textTheme.titleLarge?.copyWith(
                      fontWeight: AppTokens.fontWeightBold,
                    ),
                  ),
                  const SizedBox(height: AppTokens.spacingMD),
                  _buildRatingSelector(
                    value: _focusLevel,
                    onChanged: (value) {
                      setState(() {
                        _focusLevel = value;
                      });
                    },
                  ),
                  
                  const SizedBox(height: AppTokens.spacingXL),
                  
                  // Notes
                  Text(
                    'ملاحظات إضافية (اختياري)',
                    style: Theme.of(context).textTheme.titleLarge?.copyWith(
                      fontWeight: AppTokens.fontWeightBold,
                    ),
                  ),
                  const SizedBox(height: AppTokens.spacingMD),
                  TextField(
                    controller: _notesController,
                    maxLines: 5,
                    decoration: InputDecoration(
                      hintText: 'اكتب أي ملاحظات أو تعليقات...',
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(AppTokens.radiusMD),
                      ),
                      filled: true,
                      fillColor: AppTokens.neutralWhite,
                    ),
                  ),
                  
                  if (_error != null) ...[
                    const SizedBox(height: AppTokens.spacingMD),
                    Container(
                      padding: const EdgeInsets.all(AppTokens.spacingMD),
                      decoration: BoxDecoration(
                        color: AppTokens.errorRed.withOpacity(0.1),
                        borderRadius: BorderRadius.circular(AppTokens.radiusMD),
                        border: Border.all(color: AppTokens.errorRed),
                      ),
                      child: Row(
                        children: [
                          const Icon(Icons.error_outline, color: AppTokens.errorRed),
                          const SizedBox(width: AppTokens.spacingSM),
                          Expanded(
                            child: Text(
                              _error!,
                              style: const TextStyle(color: AppTokens.errorRed),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                  
                  const SizedBox(height: AppTokens.spacingXL),
                  
                  // Submit Button
                  SizedBox(
                    width: double.infinity,
                    child: ElevatedButton(
                      onPressed: _isSubmitting ? null : _submitEvaluation,
                      style: ElevatedButton.styleFrom(
                        backgroundColor: AppTokens.primaryGreen,
                        foregroundColor: AppTokens.neutralWhite,
                        padding: const EdgeInsets.symmetric(
                          vertical: AppTokens.spacingMD,
                        ),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(AppTokens.radiusMD),
                        ),
                      ),
                      child: _isSubmitting
                          ? const SizedBox(
                              height: 20,
                              width: 20,
                              child: CircularProgressIndicator(
                                strokeWidth: 2,
                                valueColor: AlwaysStoppedAnimation<Color>(AppTokens.neutralWhite),
                              ),
                            )
                          : const Text(
                              'تقديم التقييم',
                              style: TextStyle(
                                fontSize: 16,
                                fontWeight: AppTokens.fontWeightBold,
                              ),
                            ),
                    ),
                  ),
                ],
              ),
            ),
          );
        },
      ),
    );
  }

  Widget _buildRatingSelector({
    required int value,
    required ValueChanged<int> onChanged,
  }) {
    return Container(
      padding: const EdgeInsets.all(AppTokens.spacingMD),
      decoration: BoxDecoration(
        color: AppTokens.neutralWhite,
        borderRadius: BorderRadius.circular(AppTokens.radiusMD),
        border: Border.all(color: AppTokens.neutralLight),
      ),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceEvenly,
        children: List.generate(5, (index) {
          final rating = index + 1;
          final isSelected = rating == value;
          
          return GestureDetector(
            onTap: () => onChanged(rating),
            child: Container(
              width: 48,
              height: 48,
              decoration: BoxDecoration(
                color: isSelected 
                    ? AppTokens.primaryGreen 
                    : AppTokens.neutralLight,
                shape: BoxShape.circle,
              ),
              child: Center(
                child: Text(
                  '$rating',
                  style: TextStyle(
                    color: isSelected 
                        ? AppTokens.neutralWhite 
                        : AppTokens.neutralMedium,
                    fontSize: 20,
                    fontWeight: AppTokens.fontWeightBold,
                  ),
                ),
              ),
            ),
          );
        }),
      ),
    );
  }
}

