<?php

namespace JanykSteenbeek\LaravelGorse\Observers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use JanykSteenbeek\LaravelGorse\Services\GorseService;

class GorseRecommendationObserver
{
    public function __construct(
        protected GorseService $gorse
    ) {}

    /**
     * Handle the Model "created" event.
     */
    public function created(Model $model): void
    {
        if (Config::get('gorse.auto_sync.enabled')) {
            $model->syncWithGorse();
        }
    }

    /**
     * Handle the Model "updated" event.
     */
    public function updated(Model $model): void
    {
        if (Config::get('gorse.auto_sync.enabled')) {
            $model->syncWithGorse();
        }
    }

    /**
     * Handle the Model "deleted" event.
     */
    public function deleted(Model $model): void
    {
        if (Config::get('gorse.auto_sync.enabled')) {
            $this->gorse->deleteUser((string) $model->getKey());
        }
    }
}
