<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Widgets\Widget;
use App\Filament\Widgets\DailyStatsWidget;
use App\Filament\Widgets\WeeklyStatsWidget;
use App\Filament\Widgets\MonthlyStatsWidget;
use App\Filament\Widgets\AlertsWidget;
use App\Filament\Widgets\RecentSensitiveActionsWidget;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    
    protected static ?string $navigationLabel = 'لوحة التحكم';
    
    protected static ?string $title = 'لوحة التحكم الرئيسية';
    
    protected static ?string $slug = 'dashboard';
    
    protected static ?int $navigationSort = -1;
    
    
    public function getWidgets(): array
    {
        return [
            DailyStatsWidget::class,
            WeeklyStatsWidget::class,
            MonthlyStatsWidget::class,
            AlertsWidget::class,
            RecentSensitiveActionsWidget::class,
        ];
    }
    
    public function getColumns(): int | string | array
    {
        return [
            'sm' => 1,
            'md' => 2,
            'lg' => 3,
            'xl' => 4,
        ];
    }
}
