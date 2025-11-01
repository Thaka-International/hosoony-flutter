import 'package:flutter/material.dart';

class AppTokens {
  // Colors - هوية حصوني
  static const Color primaryBrown = Color(0xFF8B4513);
  static const Color primaryBrownDark = Color(0xFF654321);
  static const Color primaryBrownLight = Color(0xFFCD853F);
  
  static const Color primaryGold = Color(0xFFDAA520);
  static const Color primaryGoldDark = Color(0xFFB8860B);
  static const Color primaryGoldLight = Color(0xFFFFD700);
  
  static const Color primaryGreen = Color(0xFF228B22);
  static const Color primaryGreenDark = Color(0xFF006400);
  static const Color primaryGreenLight = Color(0xFF32CD32);
  
  static const Color secondaryGold = Color(0xFFD4AF37);
  static const Color secondaryGoldDark = Color(0xFFB8860B);
  static const Color secondaryGoldLight = Color(0xFFF4E4BC);
  
  static const Color neutralDark = Color(0xFF2C2C2C);
  static const Color neutralMedium = Color(0xFF6B6B6B);
  static const Color neutralLight = Color(0xFFF5F5F5);
  static const Color neutralWhite = Color(0xFFFFFFFF);
  static const Color neutralGray = Color(0xFF9E9E9E);
  
  static const Color successGreen = Color(0xFF4CAF50);
  static const Color warningOrange = Color(0xFFFF9800);
  static const Color errorRed = Color(0xFFF44336);
  static const Color infoBlue = Color(0xFF2196F3);
  static const Color primaryBlue = Color(0xFF2196F3);

  // Gradients
  static const LinearGradient primaryGradient = LinearGradient(
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
    colors: [primaryBrown, primaryGold],
  );
  
  static const LinearGradient secondaryGradient = LinearGradient(
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
    colors: [primaryGreen, primaryGold],
  );

  // Shadows
  static const List<BoxShadow> shadowSM = [
    BoxShadow(
      color: Color(0x1A000000),
      blurRadius: 4,
      offset: Offset(0, 2),
    ),
  ];

  static const List<BoxShadow> shadowMD = [
    BoxShadow(
      color: Color(0x1A000000),
      blurRadius: 8,
      offset: Offset(0, 4),
    ),
  ];

  static const List<BoxShadow> shadowLG = [
    BoxShadow(
      color: Color(0x1A000000),
      blurRadius: 16,
      offset: Offset(0, 8),
    ),
  ];

  // Typography
  static const String primaryFontFamily = 'Cairo';
  
  static const double fontSizeXS = 10.0;
  static const double fontSizeSM = 12.0;
  static const double fontSizeMD = 14.0;
  static const double fontSizeLG = 16.0;
  static const double fontSizeXL = 18.0;
  static const double fontSize2XL = 20.0;
  static const double fontSize3XL = 24.0;
  static const double fontSize4XL = 32.0;

  static const FontWeight fontWeightRegular = FontWeight.w400;
  static const FontWeight fontWeightMedium = FontWeight.w500;
  static const FontWeight fontWeightBold = FontWeight.w700;

  // Spacing
  static const double spacingXS = 4.0;
  static const double spacingSM = 8.0;
  static const double spacingMD = 16.0;
  static const double spacingLG = 24.0;
  static const double spacingXL = 32.0;
  static const double spacing2XL = 48.0;

  // Border Radius
  static const double radiusXS = 2.0;
  static const double radiusSM = 4.0;
  static const double radiusMD = 8.0;
  static const double radiusLG = 12.0;
  static const double radiusXL = 16.0;
  static const double radiusFull = 999.0;

  // Icon Sizes
  static const double iconSizeXS = 12.0;
  static const double iconSizeSM = 16.0;
  static const double iconSizeMD = 20.0;
  static const double iconSizeLG = 24.0;
  static const double iconSizeXL = 32.0;
  static const double iconSize2XL = 48.0;
}