import 'package:flutter/material.dart';
import '../../core/theme/tokens.dart';

class CopyrightWidget extends StatelessWidget {
  const CopyrightWidget({super.key});

  @override
  Widget build(BuildContext context) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.symmetric(
        vertical: AppTokens.spacingSM,
        horizontal: AppTokens.spacingMD,
      ),
      decoration: BoxDecoration(
        color: AppTokens.neutralLight,
        border: Border(
          top: BorderSide(
            color: AppTokens.neutralMedium.withValues(alpha: 0.2),
            width: 1,
          ),
        ),
      ),
      child: Text(
        'حقوق الملكية لأكاديمية ذكاء للتدريب ٢٠٢٥',
        style: Theme.of(context).textTheme.bodySmall?.copyWith(
          fontFamily: AppTokens.primaryFontFamily,
          color: AppTokens.neutralMedium,
          fontSize: AppTokens.fontSizeXS,
        ),
        textAlign: TextAlign.center,
      ),
    );
  }
}





