<?php

namespace App\Filament\Widgets;

use App\Models\Artwork;
use App\Models\PageView;
use App\Models\Photography;
use App\Models\Post;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class AnalyticsOverviewWidget extends StatsOverviewWidget
{
    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $popularArtwork = $this->popularFor(Artwork::class, 'title');
        $popularPost = $this->popularFor(Post::class, 'title');
        $popularPhotography = $this->popularFor(Photography::class, 'title');

        return [
            Stat::make('Total Views', number_format(PageView::count()))
                ->description('All tracked page views')
                ->color('success'),

            Stat::make('Popular Artworks', number_format($popularArtwork['views']))
                ->description($popularArtwork['title'])
                ->color('warning'),

            Stat::make('Popular Posts', number_format($popularPost['views']))
                ->description($popularPost['title'])
                ->color('info'),

            Stat::make('Popular Photography', number_format($popularPhotography['views']))
                ->description($popularPhotography['title'])
                ->color('gray'),
        ];
    }

    /**
     * @return array{title: string, views: int}
     */
    private function popularFor(string $modelClass, string $titleColumn): array
    {
        $row = PageView::query()
            ->select('viewable_id', DB::raw('COUNT(*) as views_count'))
            ->where('viewable_type', $modelClass)
            ->whereNotNull('viewable_id')
            ->groupBy('viewable_id')
            ->orderByDesc('views_count')
            ->first();

        if (! $row) {
            return [
                'title' => 'Belum ada data',
                'views' => 0,
            ];
        }

        $title = $modelClass::query()->whereKey($row->viewable_id)->value($titleColumn);

        return [
            'title' => $title ?: class_basename($modelClass).' #'.$row->viewable_id,
            'views' => (int) $row->views_count,
        ];
    }
}
