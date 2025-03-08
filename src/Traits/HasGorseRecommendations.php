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
    protected function gorseLabels(): array
    {
        return [self::class];
    }

    /**
     * Get the ID that will be used to identify this model in Gorse.
     */
    protected function getGorseItemId(): string
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
    public function getRecommendations(int $number = 10): array
    {
        return Gorse::getRecommendations($this->getGorseItemId(), $number);
    }

    /**
     * Add feedback for an item.
     */
    public function addFeedback(string $type, string $itemId, ?DateTime $timestamp = null): int
    {
        return Gorse::insertFeedback($type, $this->getGorseItemId(), $itemId, $timestamp);
    }
} 