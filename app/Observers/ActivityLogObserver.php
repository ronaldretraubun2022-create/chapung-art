<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\Post;
use App\Services\ActivityLogger;
use Illuminate\Database\Eloquent\Model;

class ActivityLogObserver
{
    public function created(Model $model): void
    {
        ActivityLogger::record(
            'create',
            $model,
            $this->description('created', $model),
            ['attributes' => $model->getAttributes()],
        );
    }

    public function updated(Model $model): void
    {
        $changes = ActivityLogger::modelChanges($model);

        if (blank($changes['attributes']) || array_keys($changes['attributes']) === ['updated_at']) {
            return;
        }

        ActivityLogger::record(
            'update',
            $model,
            $this->description('updated', $model),
            $changes,
        );

        if ($model instanceof Order) {
            ActivityLogger::record(
                'order_update',
                $model,
                $this->description('updated order status or payment', $model),
                $changes,
            );
        }

        if ($model instanceof Post && $model->wasChanged('status') && $model->status === 'published') {
            ActivityLogger::record(
                'publish',
                $model,
                $this->description('published', $model),
                $changes,
            );
        }
    }

    public function deleted(Model $model): void
    {
        ActivityLogger::record(
            'delete',
            $model,
            $this->description('deleted', $model),
            ['attributes' => $model->getOriginal()],
        );
    }

    private function description(string $verb, Model $model): string
    {
        return class_basename($model).' '.$this->recordTitle($model).' '.$verb.'.';
    }

    private function recordTitle(Model $model): string
    {
        foreach (['title', 'name', 'order_number', 'certificate_number', 'key', 'section_key', 'email'] as $column) {
            if (filled($model->{$column})) {
                return (string) $model->{$column};
            }
        }

        return '#'.$model->getKey();
    }
}
