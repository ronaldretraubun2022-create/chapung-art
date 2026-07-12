<?php

namespace App\Filament\Widgets;

use App\Models\Artwork;
use App\Models\PageView;
use App\Models\Photography;
use App\Models\Post;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AnalyticsOverviewWidget extends StatsOverviewWidget
{
    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $popularItems = $this->popularItems();
        $popularArtwork = $popularItems[Artwork::class] ?? $this->emptyPopularItem();
        $popularPost = $popularItems[Post::class] ?? $this->emptyPopularItem();
        $popularPhotography = $popularItems[Photography::class] ?? $this->emptyPopularItem();

        return [
            Stat::make(__('admin.stats.total_views'), number_format(PageView::count()))
                ->description(__('admin.stats.total_views_description'))
                ->color('success'),

            Stat::make(__('admin.stats.popular_artworks'), number_format($popularArtwork['views']))
                ->description($popularArtwork['title'])
                ->color('warning'),

            Stat::make(__('admin.stats.popular_posts'), number_format($popularPost['views']))
                ->description($popularPost['title'])
                ->color('info'),

            Stat::make(__('admin.stats.popular_photography'), number_format($popularPhotography['views']))
                ->description($popularPhotography['title'])
                ->color('gray'),
        ];
    }

    /**
     * @return array{title: string, views: int}
     */
    /**
     * @return array<class-string, array{title: string, views: int}>
     */
    private function popularItems(): array
    {
        $rows = PageView::query()
            ->select('viewable_type', 'viewable_id', DB::raw('COUNT(*) as views_count'))
            ->whereIn('viewable_type', [Artwork::class, Post::class, Photography::class])
            ->whereNotNull('viewable_id')
            ->groupBy('viewable_type', 'viewable_id')
            ->orderByDesc('views_count')
            ->get()
            ->groupBy('viewable_type')
            ->map(fn (Collection $items) => $items->first());

        $titles = [
            Artwork::class => Artwork::query()->whereIn('id', $this->idsFor($rows, Artwork::class))->pluck('title', 'id'),
            Post::class => Post::query()->whereIn('id', $this->idsFor($rows, Post::class))->pluck('title', 'id'),
            Photography::class => Photography::query()->whereIn('id', $this->idsFor($rows, Photography::class))->pluck('title', 'id'),
        ];

        return $rows
            ->mapWithKeys(fn ($row, string $type): array => [$type => [
                'title' => $titles[$type][(int) $row->viewable_id] ?? class_basename($type).' #'.$row->viewable_id,
                'views' => (int) $row->views_count,
            ]])
            ->all();
    }

    private function emptyPopularItem(): array
    {
        return [
            'title' => __('admin.stats.empty'),
            'views' => 0,
        ];
    }

    /**
     * @param  Collection<string, object>  $rows
     * @return array<int, int>
     */
    private function idsFor(Collection $rows, string $type): array
    {
        $row = $rows->get($type);

        return $row ? [(int) $row->viewable_id] : [];
    }
}
