<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\AnalyticsOverviewWidget;
use App\Filament\Widgets\OrderManagementOverviewWidget;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Widgets\Widget;
use Filament\Widgets\WidgetConfiguration;
use Illuminate\Contracts\Support\Htmlable;

class Dashboard extends BaseDashboard
{
    public function getTitle(): string|Htmlable
    {
        return __('admin.dashboard.title');
    }

    public function getSubheading(): string|Htmlable|null
    {
        return __('admin.dashboard.subheading');
    }

    /**
     * @return array<class-string<Widget> | WidgetConfiguration>
     */
    public function getWidgets(): array
    {
        return [];
    }

    /**
     * @return array<class-string<Widget> | WidgetConfiguration>
     */
    public function getHeaderWidgets(): array
    {
        return [
            OrderManagementOverviewWidget::class,
            AnalyticsOverviewWidget::class,
        ];
    }

    /**
     * @return array<class-string<Widget> | WidgetConfiguration>
     */
    public function getFooterWidgets(): array
    {
        return [];
    }
}
