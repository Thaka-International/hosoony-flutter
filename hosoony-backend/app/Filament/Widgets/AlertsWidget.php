<?php

namespace App\Filament\Widgets;

use App\Models\Payment;
use App\Models\Subscription;
use App\Models\Session;
use Filament\Widgets\Widget;

class AlertsWidget extends Widget
{
    protected static string $view = 'filament.widgets.alerts-widget';

    protected int | string | array $columnSpan = 'full';

    public function getViewData(): array
    {
        $sevenDaysFromNow = now()->addDays(7);
        $threeDaysFromNow = now()->addDays(3);
        $oneDayFromNow = now()->addDays(1);

        return [
            'sevenDayAlerts' => [
                'expiringSubscriptions' => Subscription::where('status', 'active')
                    ->where('end_date', '<=', $sevenDaysFromNow)
                    ->where('end_date', '>', now())
                    ->count(),
                'upcomingPayments' => Payment::where('status', 'pending')
                    ->where('due_date', '<=', $sevenDaysFromNow)
                    ->where('due_date', '>', now())
                    ->count(),
            ],
            'threeDayAlerts' => [
                'expiringSubscriptions' => Subscription::where('status', 'active')
                    ->where('end_date', '<=', $threeDaysFromNow)
                    ->where('end_date', '>', now())
                    ->count(),
                'upcomingPayments' => Payment::where('status', 'pending')
                    ->where('due_date', '<=', $threeDaysFromNow)
                    ->where('due_date', '>', now())
                    ->count(),
            ],
            'oneDayAlerts' => [
                'expiringSubscriptions' => Subscription::where('status', 'active')
                    ->where('end_date', '<=', $oneDayFromNow)
                    ->where('end_date', '>', now())
                    ->count(),
                'upcomingPayments' => Payment::where('status', 'pending')
                    ->where('due_date', '<=', $oneDayFromNow)
                    ->where('due_date', '>', now())
                    ->count(),
                'upcomingSessions' => Session::where('starts_at', '<=', $oneDayFromNow)
                    ->where('starts_at', '>', now())
                    ->count(),
            ],
        ];
    }
}
