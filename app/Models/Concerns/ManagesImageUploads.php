<?php

namespace App\Models\Concerns;

use App\Services\ImageUploadService;

trait ManagesImageUploads
{
    protected static function bootManagesImageUploads(): void
    {
        static::updating(function ($model): void {
            foreach ($model->imageUploadAttributes() as $attribute => $disk) {
                if (! $model->isDirty($attribute)) {
                    continue;
                }

                app(ImageUploadService::class)->delete($model->getOriginal($attribute), $disk);
            }
        });

        static::deleted(function ($model): void {
            if (method_exists($model, 'isForceDeleting') && ! $model->isForceDeleting()) {
                return;
            }

            foreach ($model->imageUploadAttributes() as $attribute => $disk) {
                app(ImageUploadService::class)->delete($model->getAttribute($attribute), $disk);
            }
        });
    }

    /**
     * @return array<string, string>
     */
    protected function imageUploadAttributes(): array
    {
        return property_exists($this, 'imageUploads') ? $this->imageUploads : [];
    }
}
