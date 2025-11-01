import 'package:flutter/material.dart';
import '../../core/router/app_router.dart';
import '../../core/theme/tokens.dart';

class ErrorPage extends StatelessWidget {
  final Object? error;
  final VoidCallback? onRetry;

  const ErrorPage({
    super.key,
    this.error,
    this.onRetry,
  });

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('خطأ'),
        backgroundColor: AppTokens.errorRed,
        foregroundColor: AppTokens.neutralWhite,
      ),
      body: Center(
        child: Padding(
          padding: const EdgeInsets.all(AppTokens.spacingLG),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              const Icon(
                Icons.error_outline,
                size: AppTokens.iconSize2XL,
                color: AppTokens.errorRed,
              ),
              
              const SizedBox(height: AppTokens.spacingLG),
              
              Text(
                'حدث خطأ غير متوقع',
                style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                  fontWeight: AppTokens.fontWeightBold,
                ),
                textAlign: TextAlign.center,
              ),
              
              const SizedBox(height: AppTokens.spacingMD),
              
              Text(
                'نعتذر عن هذا الخطأ. يرجى المحاولة مرة أخرى.',
                style: Theme.of(context).textTheme.bodyLarge?.copyWith(
                  color: AppTokens.neutralMedium,
                ),
                textAlign: TextAlign.center,
              ),
              
              if (error != null) ...[
                const SizedBox(height: AppTokens.spacingLG),
                Container(
                  padding: const EdgeInsets.all(AppTokens.spacingMD),
                  decoration: BoxDecoration(
                    color: AppTokens.neutralLight,
                    borderRadius: BorderRadius.circular(AppTokens.radiusSM),
                  ),
                  child: Text(
                    error.toString(),
                    style: Theme.of(context).textTheme.bodySmall?.copyWith(
                      fontFamily: 'monospace',
                      color: AppTokens.neutralMedium,
                    ),
                    textAlign: TextAlign.center,
                  ),
                ),
              ],
              
              const SizedBox(height: AppTokens.spacing2XL),
              
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                children: [
                  ElevatedButton.icon(
                    onPressed: onRetry,
                    icon: const Icon(Icons.refresh),
                    label: const Text(
                      'إعادة المحاولة',
                      style: TextStyle(fontFamily: AppTokens.primaryFontFamily),
                    ),
                  ),
                  ElevatedButton.icon(
                    onPressed: () => AppRouter.goToSplash(context),
                    icon: const Icon(Icons.home),
                    label: const Text(
                      'العودة للرئيسية',
                      style: TextStyle(fontFamily: AppTokens.primaryFontFamily),
                    ),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: AppTokens.neutralMedium,
                    ),
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }
}
