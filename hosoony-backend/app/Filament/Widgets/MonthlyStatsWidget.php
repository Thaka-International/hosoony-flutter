<?php

namespace App\Filament\Widgets;

use App\Models\Session;
use App\Models\User;
use App\Models\Payment;
use App\Models\Subscription;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MonthlyStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();

        return [
            Stat::make('الجلسات هذا الشهر', Session::whereBetween('starts_at', [$monthStart, $monthEnd])->count())
                ->description('عدد الجلسات المجدولة هذا الشهر')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('success'),

            Stat::make('الطلاب المسجلين', User::where('role', 'student')
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->count())
                ->description('الطلاب المسجلين هذا الشهر')
                ->descriptionIcon('heroicon-m-user-plus')
                ->color('info'),

            Stat::make('الإيرادات الشهرية', Payment::whereBetween('created_at', [$monthStart, $monthEnd])
                ->where('status', 'completed')
                ->sum('amount'))
                ->description('إجمالي الإيرادات هذا الشهر')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success'),

            Stat::make('الاشتراكات النشطة', Subscription::where('status', 'active')
                ->whereBetween('start_date', [$monthStart, $monthEnd])
                ->count())
                ->description('الاشتراكات النشطة هذا الشهر')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
        ];
    }
}
