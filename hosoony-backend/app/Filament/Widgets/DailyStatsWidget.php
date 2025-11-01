<?php

namespace App\Filament\Widgets;

use App\Models\Session;
use App\Models\User;
use App\Models\Payment;
use App\Models\Notification;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DailyStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $today = now()->startOfDay();
        $tomorrow = now()->addDay()->startOfDay();

        return [
            Stat::make('الجلسات اليوم', Session::whereBetween('starts_at', [$today, $tomorrow])->count())
                ->description('عدد الجلسات المجدولة اليوم')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('success'),

            Stat::make('الطلاب النشطين', User::where('role', 'student')
                ->where('status', 'active')
                ->whereDate('last_seen_at', $today)
                ->count())
                ->description('الطلاب الذين دخلوا اليوم')
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),

            Stat::make('المدفوعات اليوم', Payment::whereDate('created_at', $today)
                ->where('status', 'completed')
                ->sum('amount'))
                ->description('إجمالي المدفوعات المكتملة اليوم')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success'),

            Stat::make('التنبيهات المرسلة', Notification::whereDate('created_at', $today)
                ->where('status', 'sent')
                ->count())
                ->description('التنبيهات المرسلة اليوم')
                ->descriptionIcon('heroicon-m-bell')
                ->color('warning'),
        ];
    }
}
