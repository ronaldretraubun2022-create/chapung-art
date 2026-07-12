<?php

namespace App\Filament\Pages;

use App\Filament\Concerns\HasLocalizedNavigation;
use App\Filament\Pages\Concerns\InteractsWithReportFilters;
use App\Models\Artwork;
use App\Models\Photography;
use App\Models\Post;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;
use UnitEnum;

class ContentReport extends Page
{
    use HasLocalizedNavigation;
    use InteractsWithReportFilters;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-document-chart-bar';

    protected static string|UnitEnum|null $navigationGroup = 'Reports';

    protected static ?int $navigationSort = 20;

    protected static ?string $navigationLabel = 'Content Report';

    protected static ?string $title = 'Content Report';

    protected static ?string $slug = 'reports/content';

    protected string $view = 'filament.pages.content-report';

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
                ->label(__('admin.actions.export_csv'))
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->action('exportCsv'),
        ];
    }

    public function getTitle(): string
    {
        return __('admin.navigation.resources.content_report');
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function getLatestContent(): Collection
    {
        return collect()
            ->merge($this->contentRows(Artwork::class, 'Artwork'))
            ->merge($this->contentRows(Photography::class, 'Photography'))
            ->merge($this->contentRows(Post::class, 'Post'))
            ->sortByDesc('created_at')
            ->take(15)
            ->values();
    }

    /**
     * @return array<string, array<string, int>>
     */
    public function getStatusSummary(): array
    {
        return [
            'Artwork' => $this->statusCounts(Artwork::class),
            'Photography' => $this->statusCounts(Photography::class),
            'Post' => $this->statusCounts(Post::class),
        ];
    }

    public function exportCsv(): StreamedResponse
    {
        $rows = $this->getLatestContent()
            ->map(fn (array $row): array => [
                $row['type'],
                $row['title'],
                $row['status'],
                $row['views'],
                optional($row['created_at'])->format('Y-m-d H:i:s'),
            ])
            ->all();

        return $this->downloadCsv($this->reportFilename('content-report'), [
            'Type',
            'Title',
            'Status',
            'Views',
            'Created At',
        ], $rows);
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function contentRows(string $modelClass, string $type): Collection
    {
        return $this->applyDateFilter($modelClass::query())
            ->select(['id', 'title', 'status', 'views', 'created_at'])
            ->latest()
            ->limit(15)
            ->get()
            ->map(fn ($record): array => [
                'type' => $type,
                'title' => $record->title,
                'status' => $record->status ?? '-',
                'views' => (int) ($record->views ?? 0),
                'created_at' => $record->created_at,
            ]);
    }

    /**
     * @return array<string, int>
     */
    private function statusCounts(string $modelClass): array
    {
        return $this->applyDateFilter($modelClass::query())
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->map(fn ($total): int => (int) $total)
            ->all();
    }
}
