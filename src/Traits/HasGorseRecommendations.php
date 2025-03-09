<?php

namespace JanykSteenbeek\LaravelGorse\Traits;

use DateTime;
use Illuminate\Database\Eloquent\Collection;
use JanykSteenbeek\LaravelGorse\Facades\Gorse;
use JanykSteenbeek\LaravelGorse\Observers\GorseRecommendationObserver;

trait HasGorseRecommendations
{
    use Gorseable;

    /**
     * Boot the trait.
     */
    protected static function bootHasGorseRecommendations(): void
    {
        static::observe(GorseRecommendationObserver::class);
    }

    /**
     * Get the labels for this user in Gorse.
     */
    protected function gorseLabels(): array
    {
        return [self::class];
    }

    /**
     * Get the ID that will be used to identify this model in Gorse.
     */
    protected function gorseItemId(): string
    {
        return (string) $this->getKey();
    }

    /**
     * Sync this user with Gorse.
     */
    public function syncWithGorse(): int
    {
        return Gorse::syncUser($this, $this->gorseLabels());
    }

    /**
     * Get recommendations for this user.
     */
    public function getRecommendations(int $number = 10): Collection
    {
        $recommendations = Gorse::getRecommendations($this->gorseItemId(), $number);

        return $this->resolveRecommendations($recommendations);
    }

    /**
     * Add feedback from this user for an item.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $item  Model instance with HasGorseFeedback trait
     */
    public function feedback(object|string $item, string $type, ?DateTime $timestamp = null): int
    {
        if (is_object($item) && method_exists($item, 'gorseItemId')) {
            $item = $item->gorseItemId();
        } elseif (is_object($item)) {
            $item = get_class($item).':'.$item->getKey();
        }

        return Gorse::insertFeedback($type, $this->gorseItemId(), $item, $timestamp);
    }

    /**
     * Resolve recommendations into model instances.
     */
    protected function resolveRecommendations(array $recommendations): Collection
    {
        return collect($recommendations)
            ->mapToGroups(function ($itemId) {
                $modelClass = static::getModelFromGorseId($itemId);
                $id = static::getIdFromGorseId($itemId);

                return [$modelClass => $id];
            })
            ->filter(fn ($ids, $modelClass) => $modelClass !== null)
            ->map(function ($ids, $modelClass) {
                return (new $modelClass)->whereIn((new $modelClass)->getKeyName(), $ids)->get();
            })
            ->reduce(function (Collection $carry, Collection $models) {
                return $carry->merge($models);
            }, new Collection);
    }
}
