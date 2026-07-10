<?php

namespace App\Filament\Pages;

use App\Filament\Pages\Concerns\InteractsWithReportFilters;
use App\Models\PageView;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;
use UnitEnum;

class VisitorReport extends Page
{
    use InteractsWithReportFilters;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static string|UnitEnum|null $navigationGroup = 'Reports';

    protected static ?int $navigationSort = 30;

    protected static ?string $navigationLabel = 'Visitor Report';

    protected static ?string $title = 'Visitor Report';

    protected static ?string $slug = 'reports/visitors';

    protected string $view = 'filament.pages.visitor-report';

    public function mount(): void
    {
        $this->initializeReportFilters();
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('view_any report') ?? false;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportCsv')
                ->label('Export CSV')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->action('exportCsv'),
        ];
    }

    /**
     * @return Collection<int, PageView>
     */
    public function getRecentViews(): Collection
    {
        return $this->applyDateFilter(PageView::query(), 'viewed_at')
            ->latest('viewed_at')
            ->limit(15)
            ->get();
    }

    /**
     * @return Collection<int, object>
     */
    public function getPopularUrls(): Collection
    {
        return $this->applyDateFilter(PageView::query(), 'viewed_at')
            ->selectRaw('url, COUNT(*) as total')
            ->groupBy('url')
            ->orderByDesc('total')
            ->limit(10)
            ->get();
    }

    /**
     * @return array<string, int>
     */
    public function getDeviceSummary(): array
    {
        return $this->applyDateFilter(PageView::query(), 'viewed_at')
            ->selectRaw("COALESCE(device, 'unknown') as device_name, COUNT(*) as total")
            ->groupBy('device_name')
            ->pluck('total', 'device_name')
            ->map(fn ($total): int => (int) $total)
            ->all();
    }

    public function exportCsv(): StreamedResponse
    {
        $rows = $this->applyDateFilter(PageView::query(), 'viewed_at')
            ->latest('viewed_at')
            ->get()
            ->map(fn (PageView $view): array => [
                $view->url,
                $view->viewable_type,
                $view->viewable_id,
                $view->browser,
                $view->device,
                $view->referer,
                optional($view->viewed_at)->format('Y-m-d H:i:s'),
            ])
            ->all();

        return $this->downloadCsv($this->reportFilename('visitor-report'), [
            'URL',
            'Viewable Type',
            'Viewable ID',
            'Browser',
            'Device',
            'Referer',
            'Viewed At',
        ], $rows);
    }
}
