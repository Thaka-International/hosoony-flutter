<?php

use Carbon\Carbon;

if (!function_exists('hijri_display')) {
    /**
     * Convert a Carbon date to Hijri display format (Umm al-Qura calendar)
     * This is for display purposes only - all storage remains in Gregorian calendar
     *
     * @param Carbon $date The date to convert
     * @return string Formatted Hijri date string
     */
    function hijri_display(Carbon $date): string
    {
        // Simple Hijri conversion for display purposes
        // In a real implementation, you would use a proper Hijri library
        // like alhoqbani/hijri-dates or similar

        $gregorianYear = $date->year;
        $gregorianMonth = $date->month;
        $gregorianDay = $date->day;

        // Approximate conversion (this is simplified - use proper library in production)
        $hijriYear = $gregorianYear - 622;
        if ($gregorianMonth < 3 || ($gregorianMonth == 3 && $gregorianDay < 21)) {
            $hijriYear--;
        }

        // Calculate Hijri month and day (simplified)
        $daysSinceEpoch = $date->diffInDays(Carbon::create(622, 7, 16));
        $hijriMonth = intval(($daysSinceEpoch % 354) / 29.5) + 1;
        $hijriDay = intval($daysSinceEpoch % 29) + 1;

        // Ensure valid ranges
        $hijriMonth = max(1, min(12, $hijriMonth));
        $hijriDay = max(1, min(30, $hijriDay));

        // Arabic month names
        $hijriMonths = [
            1 => 'محرم',
            2 => 'صفر',
            3 => 'ربيع الأول',
            4 => 'ربيع الثاني',
            5 => 'جمادى الأولى',
            6 => 'جمادى الثانية',
            7 => 'رجب',
            8 => 'شعبان',
            9 => 'رمضان',
            10 => 'شوال',
            11 => 'ذو القعدة',
            12 => 'ذو الحجة',
        ];

        return sprintf(
            '%d %s %d هـ',
            $hijriDay,
            $hijriMonths[$hijriMonth],
            $hijriYear
        );
    }
}
