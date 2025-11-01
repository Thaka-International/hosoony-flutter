import 'package:flutter/material.dart';
import 'tokens.dart';

class AppTheme {
  static ThemeData get lightTheme {
    return ThemeData(
      useMaterial3: true,
      fontFamily: AppTokens.primaryFontFamily,
      colorScheme: ColorScheme.fromSeed(
        seedColor: AppTokens.primaryGold,
        brightness: Brightness.light,
      ),
      
      // App Bar Theme
      appBarTheme: const AppBarTheme(
        backgroundColor: AppTokens.primaryGold,
        foregroundColor: AppTokens.neutralWhite,
        elevation: 0,
        centerTitle: true,
        titleTextStyle: TextStyle(
          fontFamily: AppTokens.primaryFontFamily,
          fontSize: AppTokens.fontSizeLG,
          fontWeight: AppTokens.fontWeightBold,
          color: AppTokens.neutralWhite,
        ),
      ),

      // Card Theme
      cardTheme: CardThemeData(
        elevation: 2,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(AppTokens.radiusMD),
        ),
        color: AppTokens.neutralWhite,
      ),

      // Elevated Button Theme
      elevatedButtonTheme: ElevatedButtonThemeData(
        style: ElevatedButton.styleFrom(
          backgroundColor: AppTokens.primaryGold,
          foregroundColor: AppTokens.neutralWhite,
          elevation: 2,
          padding: const EdgeInsets.symmetric(
            horizontal: AppTokens.spacingLG,
            vertical: AppTokens.spacingMD,
          ),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(AppTokens.radiusMD),
          ),
          textStyle: const TextStyle(
            fontFamily: AppTokens.primaryFontFamily,
            fontSize: AppTokens.fontSizeMD,
            fontWeight: AppTokens.fontWeightMedium,
          ),
        ),
      ),

      // Text Button Theme
      textButtonTheme: TextButtonThemeData(
        style: TextButton.styleFrom(
          foregroundColor: AppTokens.primaryGold,
          padding: const EdgeInsets.symmetric(
            horizontal: AppTokens.spacingMD,
            vertical: AppTokens.spacingSM,
          ),
          textStyle: const TextStyle(
            fontFamily: AppTokens.primaryFontFamily,
            fontSize: AppTokens.fontSizeMD,
            fontWeight: AppTokens.fontWeightMedium,
          ),
        ),
      ),

      // Input Decoration Theme
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: AppTokens.neutralLight,
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(AppTokens.radiusMD),
          borderSide: BorderSide.none,
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(AppTokens.radiusMD),
          borderSide: BorderSide.none,
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(AppTokens.radiusMD),
          borderSide: const BorderSide(
            color: AppTokens.primaryGold,
            width: 2,
          ),
        ),
        errorBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(AppTokens.radiusMD),
          borderSide: const BorderSide(
            color: AppTokens.errorRed,
            width: 1,
          ),
        ),
        contentPadding: const EdgeInsets.symmetric(
          horizontal: AppTokens.spacingMD,
          vertical: AppTokens.spacingMD,
        ),
        labelStyle: const TextStyle(
          fontFamily: AppTokens.primaryFontFamily,
          fontSize: AppTokens.fontSizeSM,
          color: AppTokens.neutralMedium,
        ),
        hintStyle: const TextStyle(
          fontFamily: AppTokens.primaryFontFamily,
          fontSize: AppTokens.fontSizeSM,
          color: AppTokens.neutralMedium,
        ),
      ),

      // Text Theme
      textTheme: const TextTheme(
        displayLarge: TextStyle(
          fontFamily: AppTokens.primaryFontFamily,
          fontSize: AppTokens.fontSize4XL,
          fontWeight: AppTokens.fontWeightBold,
          color: AppTokens.neutralDark,
        ),
        displayMedium: TextStyle(
          fontFamily: AppTokens.primaryFontFamily,
          fontSize: AppTokens.fontSize3XL,
          fontWeight: AppTokens.fontWeightBold,
          color: AppTokens.neutralDark,
        ),
        displaySmall: TextStyle(
          fontFamily: AppTokens.primaryFontFamily,
          fontSize: AppTokens.fontSize2XL,
          fontWeight: AppTokens.fontWeightBold,
          color: AppTokens.neutralDark,
        ),
        headlineLarge: TextStyle(
          fontFamily: AppTokens.primaryFontFamily,
          fontSize: AppTokens.fontSize2XL,
          fontWeight: AppTokens.fontWeightBold,
          color: AppTokens.neutralDark,
        ),
        headlineMedium: TextStyle(
          fontFamily: AppTokens.primaryFontFamily,
          fontSize: AppTokens.fontSizeXL,
          fontWeight: AppTokens.fontWeightBold,
          color: AppTokens.neutralDark,
        ),
        headlineSmall: TextStyle(
          fontFamily: AppTokens.primaryFontFamily,
          fontSize: AppTokens.fontSizeLG,
          fontWeight: AppTokens.fontWeightBold,
          color: AppTokens.neutralDark,
        ),
        titleLarge: TextStyle(
          fontFamily: AppTokens.primaryFontFamily,
          fontSize: AppTokens.fontSizeLG,
          fontWeight: AppTokens.fontWeightMedium,
          color: AppTokens.neutralDark,
        ),
        titleMedium: TextStyle(
          fontFamily: AppTokens.primaryFontFamily,
          fontSize: AppTokens.fontSizeMD,
          fontWeight: AppTokens.fontWeightMedium,
          color: AppTokens.neutralDark,
        ),
        titleSmall: TextStyle(
          fontFamily: AppTokens.primaryFontFamily,
          fontSize: AppTokens.fontSizeSM,
          fontWeight: AppTokens.fontWeightMedium,
          color: AppTokens.neutralDark,
        ),
        bodyLarge: TextStyle(
          fontFamily: AppTokens.primaryFontFamily,
          fontSize: AppTokens.fontSizeMD,
          fontWeight: AppTokens.fontWeightRegular,
          color: AppTokens.neutralDark,
        ),
        bodyMedium: TextStyle(
          fontFamily: AppTokens.primaryFontFamily,
          fontSize: AppTokens.fontSizeSM,
          fontWeight: AppTokens.fontWeightRegular,
          color: AppTokens.neutralDark,
        ),
        bodySmall: TextStyle(
          fontFamily: AppTokens.primaryFontFamily,
          fontSize: AppTokens.fontSizeXS,
          fontWeight: AppTokens.fontWeightRegular,
          color: AppTokens.neutralMedium,
        ),
        labelLarge: TextStyle(
          fontFamily: AppTokens.primaryFontFamily,
          fontSize: AppTokens.fontSizeSM,
          fontWeight: AppTokens.fontWeightMedium,
          color: AppTokens.neutralDark,
        ),
        labelMedium: TextStyle(
          fontFamily: AppTokens.primaryFontFamily,
          fontSize: AppTokens.fontSizeXS,
          fontWeight: AppTokens.fontWeightMedium,
          color: AppTokens.neutralMedium,
        ),
        labelSmall: TextStyle(
          fontFamily: AppTokens.primaryFontFamily,
          fontSize: AppTokens.fontSizeXS,
          fontWeight: AppTokens.fontWeightRegular,
          color: AppTokens.neutralMedium,
        ),
      ),

      // Icon Theme
      iconTheme: const IconThemeData(
        color: AppTokens.neutralDark,
        size: AppTokens.iconSizeMD,
      ),

      // Divider Theme
      dividerTheme: const DividerThemeData(
        color: AppTokens.neutralLight,
        thickness: 1,
        space: 1,
      ),

      // Bottom Navigation Bar Theme
      bottomNavigationBarTheme: const BottomNavigationBarThemeData(
        backgroundColor: AppTokens.neutralWhite,
        selectedItemColor: AppTokens.primaryGold,
        unselectedItemColor: AppTokens.neutralMedium,
        type: BottomNavigationBarType.fixed,
        elevation: 8,
      ),
    );
  }

  static ThemeData get darkTheme {
    return ThemeData(
      useMaterial3: true,
      fontFamily: AppTokens.primaryFontFamily,
      colorScheme: ColorScheme.fromSeed(
        seedColor: AppTokens.primaryGold,
        brightness: Brightness.dark,
      ),
      
      // App Bar Theme
      appBarTheme: const AppBarTheme(
        backgroundColor: AppTokens.neutralDark,
        foregroundColor: AppTokens.neutralWhite,
        elevation: 0,
        centerTitle: true,
        titleTextStyle: TextStyle(
          fontFamily: AppTokens.primaryFontFamily,
          fontSize: AppTokens.fontSizeLG,
          fontWeight: AppTokens.fontWeightBold,
          color: AppTokens.neutralWhite,
        ),
      ),

      // Card Theme
      cardTheme: CardThemeData(
        elevation: 2,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(AppTokens.radiusMD),
        ),
        color: const Color(0xFF3A3A3A),
      ),

      // Elevated Button Theme
      elevatedButtonTheme: ElevatedButtonThemeData(
        style: ElevatedButton.styleFrom(
          backgroundColor: AppTokens.primaryGold,
          foregroundColor: AppTokens.neutralWhite,
          elevation: 2,
          padding: const EdgeInsets.symmetric(
            horizontal: AppTokens.spacingLG,
            vertical: AppTokens.spacingMD,
          ),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(AppTokens.radiusMD),
          ),
          textStyle: const TextStyle(
            fontFamily: AppTokens.primaryFontFamily,
            fontSize: AppTokens.fontSizeMD,
            fontWeight: AppTokens.fontWeightMedium,
          ),
        ),
      ),

      // Text Button Theme
      textButtonTheme: TextButtonThemeData(
        style: TextButton.styleFrom(
          foregroundColor: AppTokens.primaryGold,
          padding: const EdgeInsets.symmetric(
            horizontal: AppTokens.spacingMD,
            vertical: AppTokens.spacingSM,
          ),
          textStyle: const TextStyle(
            fontFamily: AppTokens.primaryFontFamily,
            fontSize: AppTokens.fontSizeMD,
            fontWeight: AppTokens.fontWeightMedium,
          ),
        ),
      ),

      // Input Decoration Theme
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: const Color(0xFF3A3A3A),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(AppTokens.radiusMD),
          borderSide: BorderSide.none,
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(AppTokens.radiusMD),
          borderSide: BorderSide.none,
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(AppTokens.radiusMD),
          borderSide: const BorderSide(
            color: AppTokens.primaryGold,
            width: 2,
          ),
        ),
        errorBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(AppTokens.radiusMD),
          borderSide: const BorderSide(
            color: AppTokens.errorRed,
            width: 1,
          ),
        ),
        contentPadding: const EdgeInsets.symmetric(
          horizontal: AppTokens.spacingMD,
          vertical: AppTokens.spacingMD,
        ),
        labelStyle: const TextStyle(
          fontFamily: AppTokens.primaryFontFamily,
          fontSize: AppTokens.fontSizeSM,
          color: AppTokens.neutralMedium,
        ),
        hintStyle: const TextStyle(
          fontFamily: AppTokens.primaryFontFamily,
          fontSize: AppTokens.fontSizeSM,
          color: AppTokens.neutralMedium,
        ),
      ),

      // Icon Theme
      iconTheme: const IconThemeData(
        color: AppTokens.neutralWhite,
        size: AppTokens.iconSizeMD,
      ),

      // Divider Theme
      dividerTheme: const DividerThemeData(
        color: Color(0xFF3A3A3A),
        thickness: 1,
        space: 1,
      ),

      // Bottom Navigation Bar Theme
      bottomNavigationBarTheme: const BottomNavigationBarThemeData(
        backgroundColor: AppTokens.neutralDark,
        selectedItemColor: AppTokens.primaryGold,
        unselectedItemColor: AppTokens.neutralMedium,
        type: BottomNavigationBarType.fixed,
        elevation: 8,
      ),
    );
  }
}