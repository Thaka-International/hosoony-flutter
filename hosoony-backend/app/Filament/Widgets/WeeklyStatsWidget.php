<?php

namespace App\Filament\Widgets;

use App\Models\Session;
use App\Models\User;
use App\Models\Payment;
use App\Models\ActivitySubmission;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class WeeklyStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $weekStart = now()->startOfWeek();
        $weekEnd = now()->endOfWeek();

        return [
            Stat::make('الجلسات هذا الأسبوع', Session::whereBetween('starts_at', [$weekStart, $weekEnd])->count())
                ->description('عدد الجلسات المجدولة هذا الأسبوع')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('success'),

            Stat::make('الطلاب الجدد', User::where('role', 'student')
                ->whereBetween('created_at', [$weekStart, $weekEnd])
                ->count())
                ->description('الطلاب المسجلين هذا الأسبوع')
                ->descriptionIcon('heroicon-m-user-plus')
                ->color('info'),

            Stat::make('الإيرادات الأسبوعية', Payment::whereBetween('created_at', [$weekStart, $weekEnd])
                ->where('status', 'completed')
                ->sum('amount'))
                ->description('إجمالي الإيرادات هذا الأسبوع')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success'),

            Stat::make('تسليمات الأنشطة', ActivitySubmission::whereBetween('submitted_at', [$weekStart, $weekEnd])
                ->count())
                ->description('تسليمات الأنشطة هذا الأسبوع')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('warning'),
        ];
    }
}
