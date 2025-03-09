<?php

namespace JanykSteenbeek\LaravelGorse\Traits;

use DateTime;
use JanykSteenbeek\LaravelGorse\Facades\Gorse;
use JanykSteenbeek\LaravelGorse\Observers\GorseObserver;
use Illuminate\Database\Eloquent\Collection;

trait HasGorseFeedback
{
    use Gorseable;

    /**
     * Boot the trait.
     */
    protected static function bootHasGorseFeedback(): void
    {
        static::observe(GorseObserver::class);
    }

    /**
     * Get the labels for this model in Gorse.
     * Override this method to provide custom labels.
     */
    protected function gorseLabels(): array
    {
        return [];
    }

    /**
     * Determine if this model should be hidden in Gorse.
     * Override this method to provide custom hidden logic.
     */
    protected function gorseIsHidden(): bool
    {
        return false;
    }

    /**
     * Get the timestamp for this model in Gorse.
     * Override this method to provide a custom timestamp.
     */
    protected function gorseTimestamp(): ?DateTime
    {
        return $this->updated_at;
    }

    /**
     * Get the categories for this model in Gorse.
     * Override this method to provide custom categories.
     */
    protected function gorseCategories(): array
    {
        return [static::class];
    }

    /**
     * Sync this model with Gorse.
     */
    public function syncWithGorse(): int
    {
        return Gorse::syncItem(
            $this,
            $this->gorseCategories(),
            $this->gorseLabels(),
            $this->gorseIsHidden(),
            $this->gorseTimestamp()
        );
    }

    /**
     * Record feedback for this item from a user.
     */
    public function feedback(string $type, ?string $userId = null, ?DateTime $timestamp = null): int
    {
        return Gorse::insertFeedback($type, $userId, $this->gorseItemId(), $timestamp);
    }

    /**
     * Get similar items (neighbors) for this item.
     */
    public function neighbors(int $number = 10): Collection
    {
        $recommendations = Gorse::getUserNeighbors($this->gorseItemId(), $number);

        return $this->resolveRecommendations($recommendations);
    }

    /**
     * Resolve recommendations into model instances with scores.
     */
    protected function resolveRecommendations(array $recommendations): Collection
    {
        return collect($recommendations)
            ->mapToGroups(function ($recommendation) {
                $modelClass = static::getModelFromGorseId($recommendation['Id']);
                $id = static::getIdFromGorseId($recommendation['Id']);

                return [$modelClass => [
                    'id' => $id,
                    'score' => $recommendation['Score'] ?? 0,
                ]];
            })
            ->filter(fn ($items, $modelClass) => $modelClass !== null)
            ->map(function ($items, $modelClass) {
                $models = (new $modelClass)
                    ->whereIn((new $modelClass)->getKeyName(), collect($items)->pluck('id'))
                    ->get();

                // Attach scores to the models
                return $models->map(function ($model) use ($items) {
                    $item = collect($items)->firstWhere('id', $model->getKey());
                    $model->gorse_score = $item['score'];
                    return $model;
                });
            })
            ->reduce(function (Collection $carry, Collection $models) {
                return $carry->merge($models);
            }, new Collection);
    }
}
