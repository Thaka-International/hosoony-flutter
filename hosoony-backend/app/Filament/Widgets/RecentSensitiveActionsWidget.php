<?php

namespace App\Filament\Widgets;

use App\Models\ActivityLog;
use Filament\Widgets\Widget;

class RecentSensitiveActionsWidget extends Widget
{
    protected static string $view = 'filament.widgets.recent-sensitive-actions-widget';

    protected int | string | array $columnSpan = 'full';

    public function getViewData(): array
    {
        return [
            'recentActions' => ActivityLog::whereIn('action', [
                'user_created',
                'user_deleted',
                'payment_created',
                'payment_completed',
                'subscription_created',
                'subscription_cancelled',
                'class_created',
                'class_deleted',
                'session_created',
                'session_cancelled',
            ])
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get(),
        ];
    }
}


