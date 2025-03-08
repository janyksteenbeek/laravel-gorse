<?php

namespace JanykSteenbeek\LaravelGorse\Traits;

use DateTime;
use JanykSteenbeek\LaravelGorse\Facades\Gorse;
use JanykSteenbeek\LaravelGorse\Observers\GorseRecommendationObserver;

trait HasGorseRecommendations
{
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
    public function getGorseLabels(): array
    {
        $labels = [];
        $configuredFields = config('gorse.auto_sync.user_fields.labels', []);

        foreach ($configuredFields as $field) {
            if (isset($this->{$field})) {
                $labels[] = $this->{$field};
            }
        }

        return $labels;
    }

    /**
     * Sync this user with Gorse.
     */
    public function syncWithGorse(): int
    {
        return Gorse::syncUser($this, $this->getGorseLabels());
    }

    /**
     * Get recommendations for this user.
     */
    public function getRecommendations(int $number = 10): array
    {
        return Gorse::getRecommendations((string) $this->getKey(), $number);
    }

    /**
     * Add feedback for an item.
     */
    public function addFeedback(string $type, string $itemId, ?DateTime $timestamp = null): int
    {
        return Gorse::insertFeedback($type, (string) $this->getKey(), $itemId, $timestamp);
    }
} 