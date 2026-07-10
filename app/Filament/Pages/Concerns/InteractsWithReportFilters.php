<?php

namespace App\Filament\Pages\Concerns;

use App\Models\Artwork;
use App\Models\Order;
use App\Models\PageView;
use App\Models\Photography;
use App\Models\Post;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\StreamedResponse;

trait InteractsWithReportFilters
{
    public ?string $startDate = null;

    public ?string $endDate = null;

    public function initializeReportFilters(): void
    {
        $this->startDate ??= now()->startOfMonth()->toDateString();
        $this->endDate ??= now()->toDateString();
    }

    protected function applyDateFilter(Builder $query, string $column = 'created_at'): Builder
    {
        if (filled($this->startDate)) {
            $query->where($column, '>=', CarbonImmutable::parse($this->startDate)->startOfDay());
        }

        if (filled($this->endDate)) {
            $query->where($column, '<=', CarbonImmutable::parse($this->endDate)->endOfDay());
        }

        return $query;
    }

    /**
     * @return array<string, string|int|float>
     */
    public function getOverviewSummary(): array
    {
        return [
            'total_artwork' => $this->applyDateFilter(Artwork::query())->count(),
            'total_photography' => $this->applyDateFilter(Photography::query())->count(),
            'total_posts' => $this->applyDateFilter(Post::query())->count(),
            'total_orders' => $this->applyDateFilter(Order::query())->count(),
            'total_revenue' => (float) $this->applyDateFilter(Order::query())->sum('grand_total'),
            'total_visitors' => $this->applyDateFilter(PageView::query(), 'viewed_at')->count(),
        ];
    }

    /**
     * @param  array<int, array<int, mixed>>  $rows
     * @param  array<int, string>  $headings
     */
    protected function downloadCsv(string $filename, array $headings, array $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($headings, $rows): void {
            $handle = fopen('php://output', 'w');

            if ($handle === false) {
                return;
            }

            fputcsv($handle, $headings);

            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    protected function reportFilename(string $name): string
    {
        return $name.'-'.$this->startDate.'-to-'.$this->endDate.'.csv';
    }
}
